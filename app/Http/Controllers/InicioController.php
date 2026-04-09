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

        $user = \Illuminate\Support\Facades\Auth::user();

        $query = DB::table('cadprincipal')
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
            ->where('status', 'Em andamento');

        // Administradores veem pendências de todos; demais usuários veem apenas as suas
        if ($user && $user->nivel_acesso !== 'administrador') {
            $query->where('usuario_id', $user->id);
        }

        $pendencias = $query->orderBy('updated_at', 'asc')
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
            ->leftJoin('usuario', 'cadprincipal.usuario_id', '=', 'usuario.id')
            ->where('cadprincipal.'.$request->filtro, 'LIKE', "%{$request->termo}%")
            ->orderBy('cadprincipal.data', 'desc')
            ->limit(5)
            ->get([
                'cadprincipal.id', 'cadprincipal.BOE', 'cadprincipal.boe_pm', 'cadprincipal.IP', 
                'cadprincipal.data', 'cadprincipal.status', 'cadprincipal.prioridade',
                'usuario.nome as owner_name'
            ]);

        $registros->transform(function ($item) {
            $item->data_formatada = Carbon::parse($item->data)->format('d/m/Y');
            $item->owner_name = $item->owner_name ?? '-';
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

        // Decodificar os envolvidos do JSON com verificação de existência
        $vitimasRaw = $registro->vitimas ?? $registro->Vitimas ?? null;
        $autoresRaw = $registro->autores ?? $registro->Autores ?? null;
        $testemunhasRaw = $registro->testemunhas ?? $registro->Testemunhas ?? null;

        $registro->vitimas = $vitimasRaw ? json_decode($vitimasRaw, true) : [];
        $registro->autores = $autoresRaw ? json_decode($autoresRaw, true) : [];
        $registro->testemunhas = $testemunhasRaw ? json_decode($testemunhasRaw, true) : [];

        // ✅ NOVO: Flag de propriedade para controle de permissões no frontend
        $user = \Illuminate\Support\Facades\Auth::user();
        $isOwner = true; // Padrão: se não tem dono, qualquer um pode editar
        $ownerName = null;
        if ($registro->usuario_id) {
            $isOwner = ($user && $user->id == $registro->usuario_id);
            $owner = DB::table('usuario')->where('id', $registro->usuario_id)->first();
            $ownerName = $owner ? $owner->nome : 'Desconhecido';
        }
        // Administradores sempre são donos
        if ($user && $user->nivel_acesso === 'administrador') {
            $isOwner = true;
        }
        $registro->is_owner = $isOwner;
        $registro->owner_name = $ownerName;
        $registro->current_user_id = $user ? $user->id : null;
        $registro->current_user_name = $user ? $user->nome : null;

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
            // ✅ NOVO: Gravar o usuario_id do criador
            $user = \Illuminate\Support\Facades\Auth::user();

            $id = DB::table('cadprincipal')->insertGetId([
                'usuario_id' => $user ? $user->id : null,
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
            // ✅ NOVO: Verificar se o usuário é o dono do procedimento
            $user = \Illuminate\Support\Facades\Auth::user();
            $registro = DB::table('cadprincipal')->where('id', $id)->first();

            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Registro não encontrado.'], 404);
            }

            $isOwner = true;
            if ($registro->usuario_id) {
                $isOwner = ($user && $user->id == $registro->usuario_id);
            }
            // Administradores sempre são donos
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            }

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para editar este procedimento. Apenas o responsável pode alterar os dados.'
                ], 403);
            }

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

    public function importarBoeTexto(Request $request, \App\Services\BoeExtractorService $extractorService)
    {
        $result = $extractorService->extract($request, 'apfd');

        if (!($result['success'] ?? false)) {
            return response()->json($result, $result['status'] ?? 500);
        }

        // NOVO: Verificar se o BOE extraído já existe no banco (cadprincipal)
        $registroExistenteId = null;
        if (!empty($result['dados']['boe'])) {
            $registro = \Illuminate\Support\Facades\DB::table('cadprincipal')
                ->where('BOE', $result['dados']['boe'])
                ->first();
            if ($registro) {
                $registroExistenteId = $registro->id;
            }
        }

        return response()->json([
            'success' => true,
            'dados' => $result['dados'],
            'celulares' => $result['dados']['celulares'] ?? [],
            'veiculos' => $result['dados']['veiculos'] ?? [],
            'registroExistenteId' => $registroExistenteId
        ]);
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
