<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InicioController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $perms = $user->permissions ?? [];
        if (isset($perms['menu_lateral']) && !$perms['menu_lateral']) {
            return redirect()->route('geral')->with('error', 'Acesso ao menu lateral desabilitado para seu usuário.');
        }
        if (isset($perms['apfd']) && !$perms['apfd']) {
            return redirect()->route('geral')->with('error', 'Você não tem permissão para acessar APFD | IP.');
        }
        return view('wf_inicio');
    }



    public function buscarPendencias()
    {
        // Regras de Inatividade:
        // ALTA PRIORIDADE: > 8 dias
        // MEDIA PRIORIDADE: > 20 dias
        // BAIXA PRIORIDADE: > 50 dias

        $dataLimiteAlta = now()->subDays(8);
        $dataLimiteMedia = now()->subDays(20);
        $dataLimiteBaixa = now()->subDays(50);

        $pendencias = DB::table('cadprincipal')
            ->where(function ($query) use ($dataLimiteAlta, $dataLimiteMedia, $dataLimiteBaixa) {
                $query->where(function ($q) use ($dataLimiteAlta) {
                    $q->where('prioridade', 'ALTA PRIORIDADE')
                        ->where('updated_at', '<', $dataLimiteAlta);
                })
                    ->orWhere(function ($q) use ($dataLimiteMedia) {
                        $q->where('prioridade', 'MEDIA PRIORIDADE')
                            ->where('updated_at', '<', $dataLimiteMedia);
                    })
                    ->orWhere(function ($q) use ($dataLimiteBaixa) {
                        $q->where('prioridade', 'BAIXA PRIORIDADE')
                            ->where('updated_at', '<', $dataLimiteBaixa);
                    });
            })
            ->where('status', 'Em andamento')
            ->orderBy('updated_at', 'asc')
            ->get(['id', 'BOE', 'IP', 'updated_at', 'prioridade', 'status', 'data_fato']);

        $pendencias->transform(function ($item) {
            $item->dias_parado = (int) Carbon::parse($item->updated_at)->diffInDays(now());
            $item->data_ult_mov = Carbon::parse($item->updated_at)->format('d/m/Y');
            $item->data_fato = isset($item->data_fato) ? Carbon::parse($item->data_fato)->format('d/m/Y') : '-';
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $pendencias,
            'count' => $pendencias->count()
        ]);
    }

    public function pesquisar(Request $request)
    {
        $request->validate([
            'filtro' => 'required|in:BOE,IP',
            'termo' => 'required|string|max:100'
        ]);

        $registros = DB::table('cadprincipal')
            ->where($request->filtro, 'LIKE', "%{$request->termo}%")
            ->orderBy('data', 'desc')
            ->limit(5)
            ->get(['id', 'BOE', 'boe_pm', 'IP', 'data', 'status', 'prioridade']);

        $registros->transform(function ($item) {
            $item->data_formatada = Carbon::parse($item->data)->format('d/m/Y');
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $registros
        ]);
    }

    public function buscar($id)
    {
        $registro = DB::table('cadprincipal')->where('id', $id)->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado'
            ], 404);
        }

        $registro->data_formatada = Carbon::parse($registro->data)->format('d/m/Y');

        // Decodificar os envolvidos do JSON
        // Decodificar os envolvidos do JSON com verificação de existência
        $vitimasRaw = $registro->vitimas ?? $registro->Vitimas ?? null;
        $autoresRaw = $registro->autores ?? $registro->Autores ?? null;
        $testemunhasRaw = $registro->testemunhas ?? $registro->Testemunhas ?? null;

        $registro->vitimas = $vitimasRaw ? json_decode($vitimasRaw, true) : [];
        $registro->autores = $autoresRaw ? json_decode($autoresRaw, true) : [];
        $registro->testemunhas = $testemunhasRaw ? json_decode($testemunhasRaw, true) : [];

        return response()->json([
            'success' => true,
            'data' => $registro
        ]);
    }

    public function salvar(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|date_format:d/m/Y',
            'delegado' => 'required|string|max:100',
            'delegacia' => 'required|string|max:100',
            'boe' => 'required|string|max:50',
            'data_comp' => 'nullable|string|max:100',
            'data_ext' => 'nullable|string|max:100',
            'ip' => 'nullable|string|max:50',
            'boe_pm' => 'nullable|string|max:50',
            'escrivao' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'policial_1' => 'nullable|string|max:100',
            'policial_2' => 'nullable|string|max:100',
            'dp_resp' => 'nullable|string|max:100',
            'cid_resp' => 'nullable|string|max:100',
            'bel_resp' => 'nullable|string|max:100',
            'escr_resp' => 'nullable|string|max:100',
            'data_fato' => 'nullable|date',
            'data_instauracao' => 'nullable|date',
            'hora_fato' => 'nullable|date_format:H:i',
            'end_fato' => 'nullable|string|max:200',
            'meios_empregados' => 'nullable|string',
            'motivacao' => 'nullable|string',
            'incidencia_penal' => 'nullable|string',
            'comarca' => 'nullable|string|max:100',
            'status' => 'nullable|string',
            'prioridade' => 'nullable|string',
            'Apreensao' => 'nullable|string',
            'vitimas' => 'nullable|array',
            'vitimas.*' => 'string|max:100',
            'autores' => 'nullable|array',
            'autores.*' => 'string|max:100',
            'testemunhas' => 'nullable|array',
            'testemunhas.*' => 'string|max:100',
            'condutores' => 'nullable|array',
            'condutores.*' => 'string|max:100',
            'outros' => 'nullable|array',
            'outros.*' => 'string|max:100'
        ]);

        // Verifica se já existe um BOE igual
        $boeExistente = DB::table('cadprincipal')->where('BOE', $request->boe)->exists();

        if ($boeExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe um registro com esse BOE.'
            ], 409);
        }

        try {
            $id = DB::table('cadprincipal')->insertGetId([
                'data' => Carbon::createFromFormat('d/m/Y', $request->data),
                'data_comp' => $request->data_comp,
                'data_ext' => $request->data_ext,
                'ip' => $request->ip,
                'BOE' => $request->boe,
                'boe_pm' => $request->boe_pm,
                'delegado' => $request->delegado,
                'escrivao' => $request->escrivao,
                'delegacia' => $request->delegacia,
                'cidade' => $request->cidade,
                'policial_1' => $request->policial_1,
                'policial_2' => $request->policial_2,
                'dp_resp' => $request->dp_resp,
                'cid_resp' => $request->cid_resp,
                'bel_resp' => $request->bel_resp,
                'escr_resp' => $request->escr_resp,
                'data_fato' => $request->data_fato,
                'data_instauracao' => $request->data_instauracao,
                'hora_fato' => $request->hora_fato,
                'end_fato' => $request->end_fato,
                'meios_empregados' => $request->meios_empregados,
                'motivacao' => $request->motivacao,
                'incidencia_penal' => $request->incidencia_penal,
                'comarca' => $request->comarca,
                'status' => $request->status,
                'prioridade' => $request->prioridade,
                'Apreensao' => $request->Apreensao,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->vincularNomesAoBoe($request->boe, $request->vitimas, 'VITIMA');
            $this->vincularNomesAoBoe($request->boe, $request->autores, 'AUTOR');
            $this->vincularNomesAoBoe($request->boe, $request->testemunhas, 'TESTEMUNHA');
            $this->vincularNomesAoBoe($request->boe, $request->condutores, 'CONDUTOR');
            $this->vincularNomesAoBoe($request->boe, $request->outros, 'OUTRO');

            return response()->json([
                'success' => true,
                'message' => 'Registro salvo com sucesso',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function atualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'data' => 'required|date_format:d/m/Y',
            'delegado' => 'required|string|max:100',
            'delegacia' => 'required|string|max:100',
            'boe' => 'required|string|max:50',
            'data_comp' => 'nullable|string|max:100',
            'data_ext' => 'nullable|string|max:100',
            'ip' => 'nullable|string|max:50',
            'boe_pm' => 'nullable|string|max:50',
            'escrivao' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'policial_1' => 'nullable|string|max:100',
            'policial_2' => 'nullable|string|max:100',
            'dp_resp' => 'nullable|string|max:100',
            'cid_resp' => 'nullable|string|max:100',
            'bel_resp' => 'nullable|string|max:100',
            'escr_resp' => 'nullable|string|max:100',
            'data_fato' => 'nullable|date',
            'data_instauracao' => 'nullable|date',
            'hora_fato' => 'nullable|date_format:H:i',
            'end_fato' => 'nullable|string|max:200',
            'meios_empregados' => 'nullable|string',
            'motivacao' => 'nullable|string',
            'incidencia_penal' => 'nullable|string',
            'comarca' => 'nullable|string|max:100',
            'status' => 'nullable|string',
            'prioridade' => 'nullable|string',
            'Apreensao' => 'nullable|string',
            'vitimas' => 'nullable|array',
            'vitimas.*' => 'string|max:100',
            'autores' => 'nullable|array',
            'autores.*' => 'string|max:100',
            'testemunhas' => 'nullable|array',
            'testemunhas.*' => 'string|max:100',
            'condutores' => 'nullable|array',
            'condutores.*' => 'string|max:100',
            'outros' => 'nullable|array',
            'outros.*' => 'string|max:100'
        ]);

        // Verifica duplicidade de BOE somente quando o BOE está sendo alterado
        $boeAtual = DB::table('cadprincipal')->where('id', $id)->value('BOE');
        if ($boeAtual === null) {
            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado.'
            ], 404);
        }

        if ($request->boe !== $boeAtual) {
            $boeRepetido = DB::table('cadprincipal')
                ->where('BOE', $request->boe)
                ->where('id', '!=', $id)
                ->exists();

            if ($boeRepetido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe outro registro com esse BOE.'
                ], 409);
            }
        }

        try {
            $afetados = DB::table('cadprincipal')
                ->where('id', $id)
                ->update([
                    'data' => Carbon::createFromFormat('d/m/Y', $request->data),
                    'data_comp' => $request->data_comp,
                    'data_ext' => $request->data_ext,
                    'ip' => $request->ip,
                    'BOE' => $request->boe,
                    'boe_pm' => $request->boe_pm,
                    'delegado' => $request->delegado,
                    'escrivao' => $request->escrivao,
                    'delegacia' => $request->delegacia,
                    'cidade' => $request->cidade,
                    'policial_1' => $request->policial_1,
                    'policial_2' => $request->policial_2,
                    'dp_resp' => $request->dp_resp,
                    'cid_resp' => $request->cid_resp,
                    'bel_resp' => $request->bel_resp,
                    'escr_resp' => $request->escr_resp,
                    'data_fato' => $request->data_fato,
                    'data_instauracao' => $request->data_instauracao,
                    'hora_fato' => $request->hora_fato,
                    'end_fato' => $request->end_fato,
                    'meios_empregados' => $request->meios_empregados,
                    'motivacao' => $request->motivacao,
                    'incidencia_penal' => $request->incidencia_penal,
                    'comarca' => $request->comarca,
                    'status' => $request->status,
                    'prioridade' => $request->prioridade, // Added persistence for prioridade
                    'Apreensao' => $request->Apreensao,
                    'updated_at' => now()
                ]);

            $idsVinculados = [
                'VITIMA' => $this->vincularNomesAoBoe($request->boe, $request->vitimas, 'VITIMA'),
                'AUTOR' => $this->vincularNomesAoBoe($request->boe, $request->autores, 'AUTOR'),

                'TESTEMUNHA' => $this->vincularNomesAoBoe($request->boe, $request->testemunhas, 'TESTEMUNHA'),
                'CONDUTOR' => $this->vincularNomesAoBoe($request->boe, $request->condutores, 'CONDUTOR'),
                'OUTRO' => $this->vincularNomesAoBoe($request->boe, $request->outros, 'OUTRO')
            ];

            // ✅ FIX: Pruning (Remover vínculos que não existem mais no array enviado)
            // Apenas para as categorias gerenciadas por arrays (VITIMA, AUTOR, TESTEMUNHA)
            foreach ($idsVinculados as $tipo => $ids) {
                // Se $ids array estiver vazio, significa que não foi enviado nenhum nome para esse tipo
                // Então devemos remover TODOS os vínculos desse tipo para este BOE?
                // Sim, desde que o request['tipo'] tenha sido enviado (para diferenciar de "não alterar").
                // O método atualizar recebe os arrays, mesmo vazios (se o form enviar).
                // Mas validate 'nullable|array' permite null.
                // Se for null, assumimos que não mudou ou que limpou?
                // No frontend, se limparmos os chips, o campo não é enviado? name="vitimas[0]"...
                // Se não houver inputs, o Laravel pode não trazer o campo.
                // Mas 'prepararDadosEnvolvidos' remove e recria os inputs. Se vazio, não cria.
                // Se $request->vitimas é null, pode ser que deletou tudo.

                // Melhor abordagem: Se $ids é array (mesmo vazio), sincronizamos.
                if (is_array($ids)) {
                    DB::table('boe_pessoas_vinculos')
                        ->where('boe', $request->boe)
                        ->where('tipo_vinculo', $tipo)
                        ->whereNotIn('pessoa_id', $ids)
                        ->delete();
                }
            }

            if ($afetados === 0) {
                // ... (rest usual)
                // Nota: se afetados for 0 mas mudamos vínculos, ainda é sucesso?
                // O update do CadPrincipal pode ser 0.
            }

            return response()->json([
                'success' => true,
                'message' => 'Registro atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar registro: ' . $e->getMessage()
            ], 500);
        }
    }

    private function vincularNomesAoBoe($boe, $entradas, $tipo)
    {
        // Se não houver entradas, retorna array vazio para indicar que não existem pessoas vinculadas desse tipo
        // Assim o pruning removerá todos.
        if (!$boe || !$entradas || !is_array($entradas))
            return [];

        $idsProcessados = [];

        foreach ($entradas as $item) {
            $registro = is_array($item) ? $item : ['Nome' => $item];
            $nome = trim($registro['Nome'] ?? '');
            if ($nome === '')
                continue;

            $cpf = trim($registro['CPF'] ?? '');
            $rg = trim($registro['RG'] ?? '');
            $mae = trim($registro['Mae'] ?? '');
            $pai = trim($registro['Pai'] ?? '');
            $nascimento = trim($registro['Nascimento'] ?? '');
            $naturalidade = trim($registro['Naturalidade'] ?? '');

            $cpfClean = preg_replace('/[^\d]/', '', $cpf);
            $cpfIsPlaceholder = ($cpfClean === '' || $cpfClean === '00000000000');

            $query = DB::table('cadpessoa');

            if (!$cpfIsPlaceholder && $cpfClean !== '') {
                $pessoa = $query->where('CPF', $cpfClean)->first();
            } else {
                $query = $query->where('Nome', $nome);
                if ($mae !== '')
                    $query = $query->where('Mae', $mae);
                if ($pai !== '')
                    $query = $query->where('Pai', $pai);
                if ($nascimento !== '')
                    $query = $query->where('Nascimento', $nascimento);
                if ($naturalidade !== '')
                    $query = $query->where('Naturalidade', $naturalidade);
                $pessoa = $query->first();
                if (!$pessoa) {
                    $candidatos = DB::table('cadpessoa')->where('Nome', $nome)->get();
                    if ($candidatos && $candidatos->count() > 0) {
                        $pessoa = $candidatos->first(function ($c) {
                            $cpfC = preg_replace('/[^\d]/', '', $c->CPF ?? '');
                            return $cpfC !== '' && $cpfC !== '00000000000';
                        }) ?? $candidatos->first();
                    }
                }
            }

            $pessoaId = $pessoa->IdCad ?? null;

            if (!$pessoaId) {
                $pessoaId = DB::table('cadpessoa')->insertGetId([
                    'Nome' => $nome,
                    'CPF' => (!$cpfIsPlaceholder && $cpfClean !== '') ? $cpfClean : null,
                    'RG' => ($rg !== '') ? $rg : null,
                    'Mae' => ($mae !== '') ? $mae : null,
                    'Pai' => ($pai !== '') ? $pai : null,
                    'Nascimento' => ($nascimento !== '') ? $nascimento : null,
                    'Naturalidade' => ($naturalidade !== '') ? $naturalidade : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $existe = DB::table('boe_pessoas_vinculos')
                ->where('boe', $boe)
                ->where('pessoa_id', $pessoaId)
                ->where('tipo_vinculo', $tipo)
                ->exists();

            if (!$existe) {
                DB::table('boe_pessoas_vinculos')->insert([
                    'boe' => $boe,
                    'pessoa_id' => $pessoaId,
                    'tipo_vinculo' => $tipo,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $idsProcessados[] = $pessoaId;
        }

        return $idsProcessados;
    }

    public function excluir($id)
    {
        try {
            $deleted = DB::table('cadprincipal')
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro excluído com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importarBoeTexto(Request $request)
    {
        try {
            // Suporte a PDF ou Texto colado
            if ($request->hasFile('pdfBOE') && $request->file('pdfBOE')->isValid()) {
                // Modo PDF: salva o arquivo temp com ext .pdf
                $pdf = $request->file('pdfBOE');
                $tmpPath = sys_get_temp_dir() . '/boe_upload_' . uniqid() . '.pdf';
                $pdf->move(sys_get_temp_dir(), basename($tmpPath));
            } else {
                // Modo Texto: salva o texto em arquivo temp .txt
                $texto = $request->input('textoBOE', '');
                if (empty(trim($texto))) {
                    return response()->json(['success' => false, 'message' => 'Escolha um PDF ou cole o texto do BOE antes de processar.']);
                }
                $tmpPath = sys_get_temp_dir() . '/boe_texto_' . uniqid() . '.txt';
                file_put_contents($tmpPath, $texto);
            }

            $scriptPath = base_path('scripts/python/boe_extractor.py');

            // Detectar se está rodando no Windows ou Linux para chamar o python correto
            $pythonCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';

            // Comando de shell nativo para burlar o isolamento do Symfony Process no Windows
            $command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpPath) . " 2>&1";
            $output = shell_exec($command);

            // Limpa o arquivo temporário
            @unlink($tmpPath);

            if (!$output) {
                return response()->json(['success' => false, 'message' => "Falha silenciosa ao executar o extrator Python."], 500);
            }

            $json = json_decode($output, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($json['success'])) {
                if ($json['success']) {
                    return response()->json($json);
                } else {
                    $msg = $json['error'] ?? 'Erro no script Python (JSON recebido).';
                    \Log::error("IA retornou erro mapeado: " . $msg);
                    return response()->json(['success' => false, 'message' => "Falha na IA: " . $msg], 500);
                }
            } else {
                \Log::error("Script Python falhou brutalmente: " . $output);
                return response()->json(['success' => false, 'message' => "Falha estrutural ao executar Python:\n" . $output], 500);
            }
        } catch (\Exception $e) {
            \Log::error("Falha fatal na rota importarBoeTexto: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Falha interna no servidor: " . $e->getMessage()], 500);
        }
    }
    private function normalizarTexto($string)
    {
        $string = trim($string);
        $table = array(
            'Š' => 'S',
            'š' => 's',
            'Đ' => 'Dj',
            'đ' => 'dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'Č' => 'C',
            'č' => 'c',
            'Ć' => 'C',
            'ć' => 'c',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',

            'þ' => 'b',
            'ÿ' => 'y',
            'R' => 'R',
            'r' => 'r',
        );
        $string = strtr($string, $table);
        return strtoupper($string);
    }
    public function gerarMassaTeste()
    {
        // 1. Alta Prioridade (> 8 dias) -> Criar com 9 dias atrás
        DB::table('cadprincipal')->insert([
            'BOE' => 'TESTE-ALTA-999',
            'IP' => 'IP-TESTE-ALTA',
            'data' => now()->subDays(9),
            'status' => 'Em andamento',
            'prioridade' => 'ALTA PRIORIDADE',
            'updated_at' => now()->subDays(9),
            'created_at' => now()->subDays(9)
        ]);

        // 2. Média Prioridade (> 20 dias) -> Criar com 22 dias atrás
        DB::table('cadprincipal')->insert([
            'BOE' => 'TESTE-MEDIA-888',
            'IP' => 'IP-TESTE-MEDIA',
            'data' => now()->subDays(22),
            'status' => 'Em andamento',
            'prioridade' => 'MEDIA PRIORIDADE',
            'updated_at' => now()->subDays(22),
            'created_at' => now()->subDays(22)
        ]);

        // 3. Baixa Prioridade (> 50 dias) -> Criar com 55 dias atrás
        DB::table('cadprincipal')->insert([
            'BOE' => 'TESTE-BAIXA-777',
            'IP' => 'IP-TESTE-BAIXA',
            'data' => now()->subDays(55),
            'status' => 'Em andamento',
            'prioridade' => 'BAIXA PRIORIDADE',
            'updated_at' => now()->subDays(55),
            'created_at' => now()->subDays(55)
        ]);

        // 4. Controle (Alta Prioridade recente) -> Criar com 2 dias atrás (NÃO deve aparecer)
        DB::table('cadprincipal')->insert([
            'BOE' => 'TESTE-RECENTE-000',
            'IP' => 'IP-TESTE-RECENTE',
            'data' => now()->subDays(2),
            'status' => 'Em andamento',
            'prioridade' => 'ALTA PRIORIDADE',
            'updated_at' => now()->subDays(2),
            'created_at' => now()->subDays(2)
        ]);

        return response()->json(['message' => 'Dados de teste gerados com sucesso! Atualize a página inicial.']);
    }
}
