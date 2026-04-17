<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromptGeneratorController extends Controller
{
    /**
     * Gera um prompt montado com base no papel do envolvido,
     * dados do procedimento e texto bruto do BOE.
     */
    public function gerarPrompt(Request $request)
    {
        $request->validate([
            'boe' => 'required|string',
            'pessoa_id' => 'nullable|integer',
            'nome' => 'required|string',
            'papel' => 'required|string|in:CONDUTOR,VITIMA,AUTOR,TESTEMUNHA,OUTRO',
            'tipo_prompt' => 'nullable|string',
        ]);
        // Verifica permissão (se existir a regra)
        $user = auth()->user();
        if ($user && isset($user->permissions['gerar_prompts']) && !$user->permissions['gerar_prompts']) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para usar o Gerador de Prompts (IA).'
            ], 403);
        }

        try {
            $boe = $request->boe;
            $nome = $request->nome;
            $papel = $request->papel;
            $tipoPrompt = $request->tipo_prompt;

            // 1. Buscar dados do procedimento
            $cadprincipal = DB::table('cadprincipal')
                ->where('BOE', $boe)
                ->orWhere('boe_pm', $boe)
                ->first();

            // 2. Buscar dados da pessoa (se tiver pessoa_id)
            $pessoa = null;
            if ($request->pessoa_id) {
                $pessoa = DB::table('cadpessoa')->where('IdCad', $request->pessoa_id)->first();
            }
            if (!$pessoa) {
                $pessoa = DB::table('cadpessoa')->where('Nome', $nome)->first();
            }

            // 3. Buscar texto bruto do BOE (do cache)
            $textoRaw = $this->buscarTextoBoeCache($boe);

            // 4. Buscar lista de envolvidos
            $listaEnvolvidos = $this->montarListaEnvolvidos($boe);

            // 5. Detectar se é crime de trânsito
            $isTransito = $this->detectarTransito($cadprincipal);

            // 6. Detectar se a testemunha é PM
            $isPM = $this->detectarPM($nome, $cadprincipal);

            // 7. Selecionar template automaticamente (se não veio tipo_prompt)
            if (!$tipoPrompt) {
                $tipoPrompt = $this->selecionarTemplate($papel, $isTransito, $isPM);
            }

            // 8. Carregar template
            $templates = config('prompts_templates');
            if (!isset($templates[$tipoPrompt])) {
                return response()->json([
                    'success' => false,
                    'message' => "Template '$tipoPrompt' não encontrado."
                ], 404);
            }

            $template = $templates[$tipoPrompt];

            // 9. Montar variáveis de substituição
            $variaveis = [
                '{{NOME}}' => $nome,
                '{{CPF}}' => $pessoa->CPF ?? '',
                '{{RG}}' => $pessoa->RG ?? '',
                '{{MAE}}' => $pessoa->Mae ?? '',
                '{{PAI}}' => $pessoa->Pai ?? '',
                '{{NASCIMENTO}}' => $pessoa->Nascimento ?? '',
                '{{NATURALIDADE}}' => $pessoa->Naturalidade ?? '',
                '{{PROFISSAO}}' => $pessoa->Profissao ?? '',
                '{{ENDERECO}}' => $pessoa->Endereco ?? '',
                '{{DATA_FATO}}' => $cadprincipal->data_fato ?? '',
                '{{HORA_FATO}}' => $cadprincipal->hora_fato ?? '',
                '{{LOCAL_FATO}}' => $cadprincipal->end_fato ?? '',
                '{{INCIDENCIA_PENAL}}' => $cadprincipal->incidencia_penal ?? '',
                '{{BOE_NUMERO}}' => $boe,
                '{{DELEGACIA}}' => $cadprincipal->delegacia ?? '',
                '{{DELEGADO}}' => $cadprincipal->delegado ?? '',
                '{{ESCRIVAO}}' => $cadprincipal->escrivao ?? '',
                '{{POLICIAL_1}}' => $cadprincipal->policial_1 ?? '',
                '{{POLICIAL_2}}' => $cadprincipal->policial_2 ?? '',
                '{{LISTA_ENVOLVIDOS}}' => $listaEnvolvidos,
                '{{HISTORICO_BOE}}' => $textoRaw ?: '[TEXTO DO BOE NÃO DISPONÍVEL NO CACHE - Cole o texto do BOE aqui]',
            ];

            // 10. Substituir variáveis no template
            $promptFinal = str_replace(
                array_keys($variaveis),
                array_values($variaveis),
                $template['template']
            );

            // 11. Listar templates disponíveis para o seletor
            $templatesList = [];
            foreach ($templates as $key => $tpl) {
                $templatesList[] = [
                    'id' => $key,
                    'titulo' => $tpl['titulo'],
                    'descricao' => $tpl['descricao'],
                ];
            }

            return response()->json([
                'success' => true,
                'prompt' => $promptFinal,
                'tipo_usado' => $tipoPrompt,
                'titulo' => $template['titulo'],
                'descricao' => $template['descricao'],
                'is_transito' => $isTransito,
                'is_pm' => $isPM,
                'tem_historico' => !empty($textoRaw),
                'templates_disponiveis' => $templatesList,
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao gerar prompt: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca o texto bruto do BOE no cache (storage/app/boe_cache).
     */
    private function buscarTextoBoeCache(string $boe): ?string
    {
        $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $boe);
        $cacheFile = storage_path("app/boe_cache/boe_{$boeLimpo}_apfd.json");

        if (file_exists($cacheFile)) {
            $dados = json_decode(file_get_contents($cacheFile), true);
            if ($dados && !empty($dados['texto_raw'])) {
                return $dados['texto_raw'];
            }
        }

        // Tenta também por hash
        $cacheDir = storage_path('app/boe_cache');
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/hash_*_apfd.json');
            foreach ($files as $file) {
                $dados = json_decode(file_get_contents($file), true);
                if ($dados && !empty($dados['boe']) && $dados['boe'] === $boe && !empty($dados['texto_raw'])) {
                    return $dados['texto_raw'];
                }
            }
        }

        return null;
    }

    /**
     * Monta uma lista textual dos envolvidos para incluir no prompt.
     */
    private function montarListaEnvolvidos(string $boe): string
    {
        $vinculos = DB::table('boe_pessoas_vinculos')
            ->where('boe', $boe)
            ->get();

        if ($vinculos->isEmpty()) {
            return '- Nenhum envolvido vinculado';
        }

        $pessoaIds = $vinculos->pluck('pessoa_id')->unique();
        $pessoas = DB::table('cadpessoa')
            ->whereIn('IdCad', $pessoaIds)
            ->get()
            ->keyBy('IdCad');

        $linhas = [];
        foreach ($vinculos as $v) {
            $pessoa = $pessoas->get($v->pessoa_id);
            $nome = $pessoa ? ($pessoa->Nome ?? 'Desconhecido') : 'Desconhecido';
            $linhas[] = "- {$nome} ({$v->tipo_vinculo})";
        }

        return implode("\n", $linhas);
    }

    /**
     * Detecta se a ocorrência é de trânsito pela natureza/incidência penal.
     */
    private function detectarTransito($cadprincipal): bool
    {
        if (!$cadprincipal) return false;

        $campos = [
            $cadprincipal->incidencia_penal ?? '',
            $cadprincipal->motivacao ?? '',
        ];

        $termos = ['TRANSITO', 'TRÂNSITO', 'CTB', 'DIREÇÃO PERIGOSA', 'DIRECAO PERIGOSA',
                   'ART. 309', 'ART. 310', 'ART. 311', 'ART. 312', 
                   'EMBRIAGUEZ AO VOLANTE', 'SEM HABILITAÇÃO', 'SEM CNH'];

        foreach ($campos as $campo) {
            foreach ($termos as $termo) {
                if (stripos($campo, $termo) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detecta se a pessoa é um Policial Militar.
     * Verifica pelo nome nos campos policial_1/policial_2 e por patentes no nome.
     */
    private function detectarPM(string $nome, $cadprincipal): bool
    {
        $nomeUpper = mb_strtoupper(trim($nome), 'UTF-8');

        if ($cadprincipal) {
            // Verifica se o nome bate com policial_1 ou policial_2
            $pol1 = mb_strtoupper(trim($cadprincipal->policial_1 ?? ''), 'UTF-8');
            $pol2 = mb_strtoupper(trim($cadprincipal->policial_2 ?? ''), 'UTF-8');

            if ($pol1 && strpos($pol1, $nomeUpper) !== false) return true;
            if ($pol2 && strpos($pol2, $nomeUpper) !== false) return true;
            if ($pol1 && strpos($nomeUpper, $pol1) !== false) return true;
            if ($pol2 && strpos($nomeUpper, $pol2) !== false) return true;
        }

        // Verifica por patentes militares no nome
        $patentes = ['SD ', 'CB ', 'SGT ', '1SGT ', '2SGT ', '3SGT ',
                     'ST ', 'TEN ', '1TEN ', '2TEN ', 'CAP ', 'MAJ ',
                     'TC ', 'CEL ', 'SOLDADO ', 'CABO ', 'SARGENTO ',
                     'TENENTE ', 'CAPITAO ', 'CAPITÃO ', 'MAJOR '];

        foreach ($patentes as $pat) {
            if (strpos($nomeUpper, $pat) === 0 || strpos($nomeUpper, " $pat") !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Seleciona automaticamente o template com base no papel e contexto.
     */
    private function selecionarTemplate(string $papel, bool $isTransito, bool $isPM): string
    {
        switch ($papel) {
            case 'CONDUTOR':
                return $isTransito ? 'transito_pm' : 'pm_condutor';

            case 'TESTEMUNHA':
                if ($isPM) {
                    return $isTransito ? 'transito_pm' : 'pm_testemunha';
                }
                return 'testemunha_civil';

            case 'VITIMA':
                return 'vitima';

            case 'AUTOR':
                return $isTransito ? 'transito_interrogatorio' : 'interrogatorio_autor';

            default:
                return 'testemunha_civil'; // Fallback para OUTRO/NOTICIANTE
        }
    }
}
