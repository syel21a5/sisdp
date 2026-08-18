<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Services\PdfService;

class ApfdPessoaDetalheController extends Controller
{
    public function salvar(Request $request)
    {
        $request->validate([
            'cadprincipal_id' => 'nullable|integer',
            'pessoa_id' => 'required|integer',
            'papel' => 'required|string|in:AUTOR,VITIMA,TESTEMUNHA,CONDUTOR,OUTRO',
            'interrogatorio' => 'nullable|string',
            'nota_culpa' => 'nullable|string',
            'dados_complementares' => 'nullable',
            'boe' => 'nullable|string'
        ]);

        // Resolve o cadprincipal_id pelo BOE (usado quando salvo a partir do editor de documento)
        $cadprincipalId = $request->cadprincipal_id;
        if (!$cadprincipalId && $request->boe) {
            $cad = DB::table('cadprincipal')->where('BOE', $request->boe)->first();
            $cadprincipalId = $cad ? $cad->id : null;
        }
        if (!$cadprincipalId) {
            return response()->json(['success' => false, 'message' => 'Procedimento não identificado (BOE/cadprincipal_id).'], 422);
        }

        $data = [
            'interrogatorio' => PdfService::limparMarcacaoCyan($request->interrogatorio ?? ''),
            'nota_culpa' => $request->nota_culpa,
            'dados_complementares' => $request->dados_complementares ? json_encode($request->dados_complementares) : null,
            'updated_at' => now()
        ];

        $exists = DB::table('apfd_pessoas_detalhes')
            ->where('cadprincipal_id', $cadprincipalId)
            ->where('pessoa_id', $request->pessoa_id)
            ->where('papel', $request->papel)
            ->exists();

        if ($exists) {
            DB::table('apfd_pessoas_detalhes')
                ->where('cadprincipal_id', $cadprincipalId)
                ->where('pessoa_id', $request->pessoa_id)
                ->where('papel', $request->papel)
                ->update($data);
        } else {
            DB::table('apfd_pessoas_detalhes')->insert(array_merge([
                'cadprincipal_id' => $cadprincipalId,
                'pessoa_id' => $request->pessoa_id,
                'papel' => $request->papel,
                'created_at' => now()
            ], $data));
        }

        // ✅ NOVO: Salvar oitiva como arquivo JSON em storage/oitivas/{boe}/
        if ($request->interrogatorio && $request->boe) {
            $this->salvarOitivaArquivo($request->boe, $request->pessoa_id, $request->papel, PdfService::limparMarcacaoCyan($request->interrogatorio));
        }

        \Log::info('[OITIVA] Salvar chamado', [
            'pessoa_id' => $request->pessoa_id,
            'boe' => $request->boe,
            'papel' => $request->papel,
            'tem_interrogatorio' => !empty($request->interrogatorio),
            'tamanho' => strlen($request->interrogatorio ?? ''),
            'preview' => substr($request->interrogatorio ?? '', 0, 100)
        ]);

        return response()->json(['success' => true]);
    }

    public function buscar($cadprincipalId, $pessoaId, $papel)
    {
        $registro = DB::table('apfd_pessoas_detalhes')
            ->where('cadprincipal_id', $cadprincipalId)
            ->where('pessoa_id', $pessoaId)
            ->where('papel', $papel)
            ->first();

        if (!$registro) {
            return response()->json(['success' => false, 'message' => 'Não encontrado'], 404);
        }

        $dados = [
            'interrogatorio' => $registro->interrogatorio,
            'nota_culpa' => $registro->nota_culpa,
            'dados_complementares' => $registro->dados_complementares ? json_decode($registro->dados_complementares, true) : null
        ];

        return response()->json(['success' => true, 'data' => $dados]);
    }

    /**
     * Busca o texto da oitiva (interrogatorio) de uma pessoa num procedimento,
     * localizando o cadprincipal pelo nº do BOE.
     */
    public function buscarPorBoe($boe, $pessoaId, $papel)
    {
        try {
            // ✅ FONTE PRIMÁRIA: Arquivo JSON (preserva formatação HTML perfeitamente)
            $conteudoArquivo = $this->lerOitivaArquivo($boe, $pessoaId, $papel);
            if ($conteudoArquivo) {
                return response()->json(['success' => true, 'data' => [
                    'interrogatorio' => PdfService::limparMarcacaoCyan($conteudoArquivo),
                    'nota_culpa' => '',
                    'fonte' => 'arquivo'
                ]]);
            }

            // Fallback: se mudaram o papel, busca qualquer arquivo dessa pessoa neste BOE
            $dir = storage_path('oitivas/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $boe));
            if (is_dir($dir)) {
                $pattern = $dir . '/' . $pessoaId . '_*.json';
                $arquivos = glob($pattern);
                if (!empty($arquivos)) {
                    $json = json_decode(file_get_contents(end($arquivos)), true);
                    if (!empty($json['conteudo'])) {
                        return response()->json(['success' => true, 'data' => [
                            'interrogatorio' => PdfService::limparMarcacaoCyan($json['conteudo']),
                            'nota_culpa' => '',
                            'fonte' => 'arquivo_fallback'
                        ]]);
                    }
                }
            }

            // ✅ FONTE SECUNDÁRIA: Banco de dados (fallback)
            $cad = DB::table('cadprincipal')->where('BOE', $boe)->first();
            if (!$cad) {
                return response()->json(['success' => true, 'data' => ['interrogatorio' => '', 'nota_culpa' => '']]);
            }

            $registro = DB::table('apfd_pessoas_detalhes')
                ->where('cadprincipal_id', $cad->id)
                ->where('pessoa_id', $pessoaId)
                ->where('papel', $papel)
                ->first();

            if (!$registro) {
                $registro = DB::table('apfd_pessoas_detalhes')
                    ->where('cadprincipal_id', $cad->id)
                    ->where('pessoa_id', $pessoaId)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!$registro || empty($registro->interrogatorio)) {
                return response()->json(['success' => true, 'data' => ['interrogatorio' => '', 'nota_culpa' => '']]);
            }

            return response()->json(['success' => true, 'data' => [
                'interrogatorio' => PdfService::limparMarcacaoCyan($registro->interrogatorio),
                'nota_culpa' => $registro->nota_culpa,
                'fonte' => 'banco'
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao buscar oitiva: ' . $e->getMessage()], 500);
        }
    }

    public function listarPorCadprincipal($cadprincipalId)
    {
        $rows = DB::table('apfd_pessoas_detalhes')
            ->where('cadprincipal_id', $cadprincipalId)
            ->get();

        $result = [
            'AUTOR' => [],
            'VITIMA' => [],
            'TESTEMUNHA' => []
        ];

        foreach ($rows as $r) {
            $entry = [
                'pessoa_id' => $r->pessoa_id,
                'interrogatorio' => $r->interrogatorio,
                'nota_culpa' => $r->nota_culpa,
                'dados_complementares' => $r->dados_complementares ? json_decode($r->dados_complementares, true) : null
            ];
            $result[$r->papel][] = $entry;
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    // =============================================
    // MÉTODOS DE ARQUIVO (storage/oitivas/)
    // =============================================

    /**
     * Salva o conteúdo da oitiva como arquivo JSON.
     * Estrutura: storage/oitivas/{boe_sanitizado}/{pessoa_id}_{papel}.json
     */
    private function salvarOitivaArquivo($boe, $pessoaId, $papel, $conteudo)
    {
        try {
            $boeSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $boe);
            $dir = storage_path('oitivas/' . $boeSafe);

            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $arquivo = $dir . '/' . $pessoaId . '_' . $papel . '.json';
            $dados = [
                'pessoa_id' => (int) $pessoaId,
                'boe' => $boe,
                'papel' => $papel,
                'conteudo' => $conteudo,
                'salvo_em' => now()->toDateTimeString()
            ];

            File::put($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            \Log::info('[OITIVA] Arquivo salvo: ' . $arquivo);
        } catch (\Throwable $e) {
            \Log::warning('[OITIVA] Erro ao salvar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Lê o conteúdo da oitiva do arquivo JSON.
     * Retorna o HTML da oitiva ou null se não encontrar.
     */
    private function lerOitivaArquivo($boe, $pessoaId, $papel)
    {
        try {
            $boeSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $boe);
            $arquivo = storage_path('oitivas/' . $boeSafe . '/' . $pessoaId . '_' . $papel . '.json');

            if (!File::exists($arquivo)) {
                return null;
            }

            $json = json_decode(File::get($arquivo), true);
            return $json['conteudo'] ?? null;
        } catch (\Throwable $e) {
            \Log::warning('[OITIVA] Erro ao ler arquivo: ' . $e->getMessage());
            return null;
        }
    }
}
