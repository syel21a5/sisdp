<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\BoePessoaVinculo;
use App\Models\EnvolvidoFoto;

class BoeVincularController extends Controller
{
    /**
     * Nomes-placeholder que nunca devem virar pessoa/vínculo.
     * O auto-hidratar e o sugerirVinculo ignoram estes valores.
     */
    const NOMES_PLACEHOLDER = [
        'DESCONHECIDO',
        'DESCONHECIDA',
        'NAO INFORMADO',
        'NÃO INFORMADO',
        'NAO CONSTA',
        'NÃO CONSTA',
        'IGNORADO',
        'IGNORADA',
        'SEM IDENTIFICACAO',
        'SEM IDENTIFICAÇÃO',
        'NAO IDENTIFICADO',
        'NÃO IDENTIFICADO',
    ];

    /**
     * Normaliza um nome: maiúsculas, sem acentos, espaços extras removidos.
     * Usado para comparar nomes sem duplicar pessoas por diferença de grafia.
     */
    private static function normalizarNome(string $nome): string
    {
        $nome = mb_strtoupper(trim($nome), 'UTF-8');
        // Remove acentos (ex: LEUGIM vs LEUGIN não é acento, mas Á vs A sim)
        $nome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome) ?: $nome;
        // Colapsa espaços múltiplos
        $nome = preg_replace('/\s+/', ' ', $nome) ?: $nome;
        return trim($nome);
    }

    /**
     * Busca uma pessoa na cadpessoa por nome NORMALIZADO (sem acentos, maiúsculas).
     * Se não achar exato, tenta variação sem acentos. Nunca cria pessoa duplicada
     * por diferença boba de grafia (ex: LEUGIN vs LEUGIM -> N vs M pega via levenshtein).
     * Retorna null se não achar.
     */
    private static function buscarPessoaPorNomeNormalizado(string $nome): ?object
    {
        $nomeNormalizado = self::normalizarNome($nome);

        // 1. Busca exata primeiro (mais rápido)
        $pessoa = DB::table('cadpessoa')->where('Nome', $nome)->first();
        if ($pessoa) {
            return $pessoa;
        }

        // 2. Busca por nome normalizado (ignora acentos/caixa)
        $pessoas = DB::table('cadpessoa')->select('IdCad', 'Nome')->get();
        $melhor = null;
        $melhorDist = 2; // tolerância de até 2 caracteres de diferença
        foreach ($pessoas as $p) {
            $norm = self::normalizarNome($p->Nome);
            if ($norm === $nomeNormalizado) {
                return $p;
            }
            // Fallback por similaridade (pega LEUGIN vs LEUGIM, digitação etc.)
            if (mb_strlen($nomeNormalizado) >= 10 && mb_strlen($norm) >= 10) {
                $dist = levenshtein($norm, $nomeNormalizado);
                if ($dist <= $melhorDist) {
                    $melhor = $p;
                    $melhorDist = $dist;
                }
            }
        }

        return $melhor;
    }

    /**
     * Lista todos os vínculos de um BOE, agrupados por tipo.
     */
    public function listarVinculos($boe)
    {
        try {
            $vinculos = BoePessoaVinculo::where('boe', $boe)->get();

            // Buscar detalhes das pessoas na tabela CadPessoa
            $pessoasIds = $vinculos->pluck('pessoa_id')->unique();
            $pessoas = DB::table('cadpessoa')->whereIn('IdCad', $pessoasIds)->get()->keyBy('IdCad');

            // Buscar nomes dos criadores para chips pendentes
            $criadorIds = $vinculos->pluck('criado_por')->filter()->unique();
            $criadores = DB::table('usuario')->whereIn('id', $criadorIds)->pluck('nome', 'id');

            $resultado = [
                'condutor' => [],
                'vitimas' => [],
                'autores' => [],
                'testemunhas' => [],
                'outros' => []
            ];

            // Buscar o cadprincipal (procedimento) para saber o id e verificar oitivas
            $cadprincipalInfo = DB::table('cadprincipal')->where('BOE', $boe)->orderByDesc('id')->first();
            $cadprincipalId = $cadprincipalInfo ? $cadprincipalInfo->id : null;

            // Pré-carregar pessoas que têm oitiva/interrogatório salvo neste procedimento
            $pessoasComOitiva = [];
            if ($cadprincipalId) {
                $pessoasComOitiva = DB::table('apfd_pessoas_detalhes')
                    ->where('cadprincipal_id', $cadprincipalId)
                    ->where(function ($q) {
                        $q->whereNotNull('interrogatorio')->where('interrogatorio', '!=', '');
                    })
                    ->pluck('pessoa_id')
                    ->flip()
                    ->all();
            }

            foreach ($vinculos as $v) {
                $pessoa = $pessoas->get($v->pessoa_id);
                if ($pessoa) {
                    $dadosPessoa = (array) $pessoa;
                    $dadosPessoa['vinculo_id'] = $v->id;
                    $dadosPessoa['status_aprovacao'] = $v->status_aprovacao ?? 'aprovado';
                    $dadosPessoa['criado_por'] = $v->criado_por;
                    $dadosPessoa['criado_por_nome'] = $v->criado_por ? ($criadores[$v->criado_por] ?? 'Desconhecido') : null;
                    // Flag: esta pessoa já tem oitiva/interrogatório salvo neste procedimento?
                    $dadosPessoa['tem_oitiva'] = $cadprincipalId && isset($pessoasComOitiva[$v->pessoa_id]);

                    switch (strtoupper($v->tipo_vinculo)) {
                        case 'CONDUTOR':
                            $resultado['condutor'][] = $dadosPessoa;
                            break;
                        case 'VITIMA':
                            $resultado['vitimas'][] = $dadosPessoa;
                            break;
                        case 'AUTOR':
                            $resultado['autores'][] = $dadosPessoa;
                            break;
                        case 'TESTEMUNHA':
                            $resultado['testemunhas'][] = $dadosPessoa;
                            break;
                        default:
                            $resultado['outros'][] = $dadosPessoa;
                    }
                }
            }

            // ✅ NOVO: Retornar info sobre o dono do procedimento
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $boe)->orderByDesc('id')->first();
            $user = Auth::user();
            $isOwner = true;
            $ownerName = null;
            if ($cadprincipal && $cadprincipal->usuario_id) {
                $isOwner = ($user && $user->id == $cadprincipal->usuario_id);
                $owner = DB::table('usuario')->where('id', $cadprincipal->usuario_id)->first();
                $ownerName = $owner ? $owner->nome : 'Desconhecido';
            }
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'is_owner' => $isOwner,
                'owner_name' => $ownerName
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao listar vínculos do BOE {$boe}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao listar vínculos'], 500);
        }
    }

    /**
     * ✅ NOVO: Lista todas as sugestões PENDENTES de colaboradores
     * para os BOEs onde o usuário logado é o DONO.
     */
    public function listarSugestoesPendentes()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'count' => 0, 'data' => []]);
            }

            // Pegar todos os BOEs que pertencem ao usuário logado
            $boesDono = DB::table('cadprincipal')
                ->where('usuario_id', $user->id)
                ->pluck('BOE');

            if ($boesDono->isEmpty()) {
                return response()->json(['success' => true, 'count' => 0, 'data' => []]);
            }

            // Buscar todos os vínculos pendentes nesses BOEs (criados por OUTRA pessoa)
            $vinculos = BoePessoaVinculo::whereIn('boe', $boesDono)
                ->where('status_aprovacao', 'pendente')
                ->where('criado_por', '!=', $user->id)
                ->whereNotNull('criado_por')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($vinculos->isEmpty()) {
                return response()->json(['success' => true, 'count' => 0, 'data' => []]);
            }

            // Enriquecer com dados da pessoa e do criador
            $pessoasIds = $vinculos->pluck('pessoa_id')->unique();
            $pessoas = DB::table('cadpessoa')->whereIn('IdCad', $pessoasIds)->get()->keyBy('IdCad');

            $criadorIds = $vinculos->pluck('criado_por')->unique();
            $criadores = DB::table('usuario')->whereIn('id', $criadorIds)->get()->keyBy('id');

            // Agrupar por BOE
            $agrupado = [];
            foreach ($vinculos as $v) {
                $boe = $v->boe;
                if (!isset($agrupado[$boe])) {
                    $agrupado[$boe] = [
                        'boe' => $boe,
                        'sugestoes' => []
                    ];
                }
                $pessoa = $pessoas->get($v->pessoa_id);
                $criador = $criadores->get($v->criado_por);
                $agrupado[$boe]['sugestoes'][] = [
                    'vinculo_id'      => $v->id,
                    'tipo_vinculo'    => $v->tipo_vinculo,
                    'pessoa_nome'     => $pessoa ? $pessoa->Nome : 'Desconhecido',
                    'criado_por_nome' => $criador ? $criador->nome : 'Desconhecido',
                    'created_at'      => $v->created_at,
                ];
            }

            return response()->json([
                'success' => true,
                'count'   => $vinculos->count(),
                'data'    => array_values($agrupado)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar sugestões pendentes: ' . $e->getMessage());
            return response()->json(['success' => false, 'count' => 0, 'data' => []]);
        }
    }

    /**
     * Adiciona um novo vínculo (Pessoa -> BOE).
     */
    public function adicionarVinculo(Request $request)
    {
        $request->validate([
            'boe' => 'required|string',
            'pessoa_id' => 'required|integer',
            'tipo' => 'required|string|in:CONDUTOR,VITIMA,AUTOR,TESTEMUNHA,OUTRO'
        ]);

        try {
            // Verifica se já existe este vínculo exato para evitar duplicatas
            $existe = BoePessoaVinculo::where('boe', $request->boe)
                ->where('pessoa_id', $request->pessoa_id)
                ->where('tipo_vinculo', $request->tipo)
                ->exists();

            if ($existe) {
                return response()->json(['success' => false, 'message' => 'Esta pessoa já está vinculada com este papel.']);
            }

            // ✅ FIX (17/08/2026): Hierarquia estrita — uma pessoa tem UM papel por BOE.
            // Ao vincular a pessoa a um papel, remove vínculos da MESMA pessoa em OUTROS papéis
            // (ex: pessoa estava em OUTRO e agora virou AUTOR → remove o OUTRO).
            // Isso evita a duplicação AUTOR+OUTRO no mesmo procedimento.
            BoePessoaVinculo::where('boe', $request->boe)
                ->where('pessoa_id', $request->pessoa_id)
                ->where('tipo_vinculo', '!=', $request->tipo)
                ->delete();

            // ✅ REFORÇO DE SEGURANÇA: Verificar se o usuário é o dono do procedimento
            $user = Auth::user();
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $request->boe)->orderByDesc('id')->first();

            $isOwner = false;
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            } elseif ($cadprincipal && $cadprincipal->usuario_id) {
                $isOwner = ($user && $user->id == $cadprincipal->usuario_id);
            }

            // Se for o dono: aprovado direto. Se não: pendente.
            $statusAprovacao = $isOwner ? 'aprovado' : 'pendente';

            $vinculo = BoePessoaVinculo::create([
                'boe' => $request->boe,
                'pessoa_id' => $request->pessoa_id,
                'tipo_vinculo' => $request->tipo,
                'status_aprovacao' => $statusAprovacao,
                'criado_por' => $user ? $user->id : null
            ]);

            $message = $isOwner
                ? 'Vínculo adicionado com sucesso!'
                : 'Sugestão de vínculo enviada. Aguarde a aprovação do responsável.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'vinculo_id' => $vinculo->id,
                'status_aprovacao' => $statusAprovacao
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao adicionar vínculo: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao salvar vínculo'], 500);
        }
    }

    /**
     * ✅ NOVO: Sugerir um vínculo (Exclusivo para o fluxo de Colaborador em tempo real)
     * Diferente de adicionarVinculo, este aceita apenas um "nome". Faz a busca na cadpessoa 
     * e, se não existir, cria a pessoa, então insere como "pendente".
     */
    public function sugerirVinculo(Request $request)
    {
        $request->validate([
            'boe' => 'required|string',
            'nome' => 'required|string',
            'tipo' => 'required|string|in:CONDUTOR,VITIMA,AUTOR,TESTEMUNHA,OUTRO'
        ]);

        try {
            $user = Auth::user();
            $nome = mb_strtoupper(trim($request->nome), 'UTF-8');

            // 🚫 Nomes-placeholder não viram pessoa/vínculo
            if (in_array(self::normalizarNome($nome), self::NOMES_PLACEHOLDER, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nome-placeholder (ex: DESCONHECIDO) não pode virar vínculo.',
                ], 422);
            }

            // 1. Tentar encontrar a pessoa pelo nome (exato ou normalizado p/ evitar duplicata)
            $pessoa = self::buscarPessoaPorNomeNormalizado($nome);
            $pessoaId = $pessoa ? $pessoa->IdCad : null;

            // 2. Se não existir, criar um registro stub no cadpessoa para pegar um ID
            if (!$pessoaId) {
                $pessoaId = DB::table('cadpessoa')->insertGetId([
                    'Nome' => $nome,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 3. Verifica se já existe uma sugestão PENDENTE deste mesmo colaborador para este BOE
            $vinculoExistente = BoePessoaVinculo::where('boe', $request->boe)
                ->where('pessoa_id', $pessoaId)
                ->where('tipo_vinculo', $request->tipo)
                ->where('status_aprovacao', 'pendente')
                ->where('criado_por', $user ? $user->id : null)
                ->first();

            if ($vinculoExistente) {
                // Já sugerido por este colaborador - retorna o vinculo existente como sucesso
                return response()->json([
                    'success' => true,
                    'message' => 'Sugestão já registrada.',
                    'vinculo_id' => $vinculoExistente->id,
                    'pessoa_id' => $pessoaId,
                    'nome' => $nome
                ]);
            }

            // 4. Inserir como pendente, associado ao colaborador atual
            $vinculo = BoePessoaVinculo::create([
                'boe' => $request->boe,
                'pessoa_id' => $pessoaId,
                'tipo_vinculo' => $request->tipo,
                'status_aprovacao' => 'pendente',
                'criado_por' => $user ? $user->id : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sugestão enviada com sucesso.',
                'vinculo_id' => $vinculo->id,
                'pessoa_id' => $pessoaId,
                'nome' => $nome
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao sugerir vínculo: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno ao sugerir'], 500);
        }
    }

    /**
     * Remove um vínculo específico pelo ID do vínculo.
     */
    public function removerVinculo($id)
    {
        try {
            $vinculo = BoePessoaVinculo::find($id);
            if (!$vinculo) {
                return response()->json(['success' => false, 'message' => 'Vínculo não encontrado'], 404);
            }

            // ✅ NOVO: Verificar permissão de exclusão
            $user = Auth::user();
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $vinculo->boe)->orderByDesc('id')->first();

            $isOwner = true;
            if ($cadprincipal && $cadprincipal->usuario_id) {
                $isOwner = ($user && $user->id == $cadprincipal->usuario_id);
            }
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            }

            // Não-donos só podem remover chips *pendentes* que eles mesmos criaram
            if (!$isOwner) {
                if ($vinculo->status_aprovacao === 'aprovado') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Apenas o responsável pelo procedimento pode remover envolvidos aprovados.'
                    ], 403);
                }
                if ($vinculo->criado_por !== ($user ? $user->id : null)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você só pode remover sugestões que você mesmo criou.'
                    ], 403);
                }
            }

            $vinculo->delete();

            return response()->json(['success' => true, 'message' => 'Vínculo removido com sucesso']);

        } catch (\Exception $e) {
            Log::error("Erro ao remover vínculo {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao remover vínculo'], 500);
        }
    }

    /**
     * ✅ NOVO: Aprovar um vínculo pendente (apenas o dono pode).
     */
    public function aprovarVinculo($id)
    {
        try {
            $vinculo = BoePessoaVinculo::find($id);
            if (!$vinculo) {
                return response()->json(['success' => false, 'message' => 'Vínculo não encontrado'], 404);
            }

            $user = Auth::user();
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $vinculo->boe)->orderByDesc('id')->first();

            $isOwner = true;
            if ($cadprincipal && $cadprincipal->usuario_id) {
                $isOwner = ($user && $user->id == $cadprincipal->usuario_id);
            }
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            }

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas o responsável pode aprovar sugestões.'
                ], 403);
            }

            $vinculo->status_aprovacao = 'aprovado';
            $vinculo->save();

            return response()->json(['success' => true, 'message' => 'Envolvido aprovado com sucesso!']);

        } catch (\Exception $e) {
            Log::error("Erro ao aprovar vínculo {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao aprovar vínculo'], 500);
        }
    }

    /**
     * ✅ NOVO: Rejeitar (excluir) um vínculo pendente (apenas o dono pode).
     */
    public function rejeitarVinculo($id)
    {
        try {
            $vinculo = BoePessoaVinculo::find($id);
            if (!$vinculo) {
                return response()->json(['success' => false, 'message' => 'Vínculo não encontrado'], 404);
            }

            $user = Auth::user();
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $vinculo->boe)->orderByDesc('id')->first();

            $isOwner = true;
            if ($cadprincipal && $cadprincipal->usuario_id) {
                $isOwner = ($user && $user->id == $cadprincipal->usuario_id);
            }
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            }

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas o responsável pode rejeitar sugestões.'
                ], 403);
            }

            $vinculo->delete();

            return response()->json(['success' => true, 'message' => 'Sugestão rejeitada e removida.']);

        } catch (\Exception $e) {
            Log::error("Erro ao rejeitar vínculo {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao rejeitar vínculo'], 500);
        }
    }

    // =================================================================================
    // MÉTODOS DE COMPATIBILIDADE (ADAPTADOS PARA A NOVA ESTRUTURA)
    // =================================================================================

    public function salvarVinculos(Request $request)
    {
        // Este método antigo recebia campos fixos (vitima1_id, etc).
        // Vamos tentar adaptar ou pedir para usar o novo 'adicionarVinculo'.
        // Por enquanto, vamos manter a lógica antiga mas salvando na nova tabela.

        $boe = $request->input('boe');
        if (!$boe)
            return response()->json(['success' => false, 'message' => 'BOE obrigatório'], 400);

        try {
            $user = Auth::user();
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $boe)->orderByDesc('id')->first();
            $isOwner = false;
            if ($user && $user->nivel_acesso === 'administrador') {
                $isOwner = true;
            } elseif ($cadprincipal && $cadprincipal->usuario_id) {
                $isOwner = ($user && $user->id == $cadprincipal->usuario_id);
            }
            $statusAprovacao = $isOwner ? 'aprovado' : 'pendente';

            // Mapeamento de campos antigos para tipos novos
            $map = [
                'condutor_id' => 'CONDUTOR',
                'vitima1_id' => 'VITIMA',
                'vitima2_id' => 'VITIMA',
                'vitima3_id' => 'VITIMA',
                'autor1_id' => 'AUTOR',
                'autor2_id' => 'AUTOR',
                'autor3_id' => 'AUTOR',
                'testemunha1_id' => 'TESTEMUNHA',
                'testemunha2_id' => 'TESTEMUNHA',
                'testemunha3_id' => 'TESTEMUNHA',
                'outro_id' => 'OUTRO',
            ];

            DB::beginTransaction();

            foreach ($map as $campo => $tipo) {
                if ($request->has($campo)) {
                    $pessoaId = $request->input($campo);
                    if ($pessoaId) {
                        // Verifica se já existe
                        $exists = BoePessoaVinculo::where('boe', $boe)
                            ->where('pessoa_id', $pessoaId)
                            ->where('tipo_vinculo', $tipo)
                            ->exists();

                        if (!$exists) {
                            BoePessoaVinculo::create([
                                'boe' => $boe,
                                'pessoa_id' => $pessoaId,
                                'tipo_vinculo' => $tipo,
                                'status_aprovacao' => $statusAprovacao,
                                'criado_por' => $user ? $user->id : null
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Vínculos processados com sucesso']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function excluirTodosVinculos($boe)
    {
        BoePessoaVinculo::where('boe', $boe)->delete();
        return response()->json(['success' => true, 'message' => 'Todos os vínculos excluídos']);
    }

    // Métodos específicos de exclusão (agora removem TODOS do tipo, ou o último? 
    // Melhor remover TODOS desse tipo para garantir limpeza se o frontend antigo chamar)
    public function excluirVinculoCondutor($boe)
    {
        return $this->excluirPorTipo($boe, 'CONDUTOR');
    }

    public function excluirVinculoVitima1($boe)
    {
        return $this->excluirPorTipo($boe, 'VITIMA');
    } // Cuidado: remove TODAS as vítimas
    public function excluirVinculoVitima2($boe)
    {
        return $this->excluirPorTipo($boe, 'VITIMA');
    }
    public function excluirVinculoVitima3($boe)
    {
        return $this->excluirPorTipo($boe, 'VITIMA');
    }

    public function excluirVinculoAutor1($boe)
    {
        return $this->excluirPorTipo($boe, 'AUTOR');
    }
    public function excluirVinculoAutor2($boe)
    {
        return $this->excluirPorTipo($boe, 'AUTOR');
    }
    public function excluirVinculoAutor3($boe)
    {
        return $this->excluirPorTipo($boe, 'AUTOR');
    }

    public function excluirVinculoTestemunha1($boe)
    {
        return $this->excluirPorTipo($boe, 'TESTEMUNHA');
    }
    public function excluirVinculoTestemunha2($boe)
    {
        return $this->excluirPorTipo($boe, 'TESTEMUNHA');
    }
    public function excluirVinculoTestemunha3($boe)
    {
        return $this->excluirPorTipo($boe, 'TESTEMUNHA');
    }

    public function excluirVinculoOutro($boe)
    {
        return $this->excluirPorTipo($boe, 'OUTRO');
    }

    private function excluirPorTipo($boe, $tipo)
    {
        // ATENÇÃO: Métodos legados de "excluir vitima 1" agora excluem TODAS as vítimas se não tivermos ID.
        // Isso é um comportamento de risco na migração.
        // Idealmente o frontend deve ser atualizado para chamar removerVinculo($id).
        // Vou manter deletando apenas UM registro desse tipo (o mais recente) para tentar simular "um slot".

        $vinculo = BoePessoaVinculo::where('boe', $boe)->where('tipo_vinculo', $tipo)->latest()->first();
        if ($vinculo) {
            $vinculo->delete();
            return response()->json(['success' => true, 'message' => "Um vínculo de {$tipo} foi removido"]);
        }
        return response()->json(['success' => false, 'message' => "Nenhum vínculo de {$tipo} encontrado"], 404);
    }

    // Métodos de busca legados (retornam o primeiro encontrado do tipo)
    public function buscarCondutorPorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'CONDUTOR');
    }
    public function buscarVitima1PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'VITIMA');
    }
    public function buscarVitima2PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'VITIMA', 1);
    } // Pula 1
    public function buscarVitima3PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'VITIMA', 2);
    } // Pula 2
    // ... repetir para outros ...

    private function buscarUmPorTipo($boe, $tipo, $skip = 0)
    {
        $vinculo = BoePessoaVinculo::where('boe', $boe)->where('tipo_vinculo', $tipo)->skip($skip)->first();
        if ($vinculo) {
            $pessoa = DB::table('cadpessoa')->where('IdCad', $vinculo->pessoa_id)->first();
            if ($pessoa) {
                // Incluir URL da foto principal (busca por PESSOA, não por papel)
                $foto = EnvolvidoFoto::where('envolvido_id', $vinculo->pessoa_id)
                            ->where('is_principal', true)
                            ->first();
                $pessoa->foto_url = $foto ? asset('storage/' . $foto->caminho_foto) : null;

                return response()->json(['success' => true, 'data' => $pessoa, 'vinculo_id' => $vinculo->id]);
            }
        }
        return response()->json(['success' => false, 'message' => 'Não encontrado'], 404);
    }

    // Adicionando os métodos faltantes para completar a compatibilidade
    public function buscarTestemunha1PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'TESTEMUNHA');
    }
    public function buscarTestemunha2PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'TESTEMUNHA', 1);
    }
    public function buscarTestemunha3PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'TESTEMUNHA', 2);
    }
    public function buscarOutroPorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'OUTRO');
    }
    public function buscarAutor1PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'AUTOR');
    }
    public function buscarAutor2PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'AUTOR', 1);
    }
    public function buscarAutor3PorBoe($boe)
    {
        return $this->buscarUmPorTipo($boe, 'AUTOR', 2);
    }

    /**
     * AUTO-HIDRATAÇÃO: dados da extração da IA -> sugestões PENDENTES de vínculo.
     * O preenchimento é automático, mas nada fica 'aprovado' sem validação humana.
     * Cria BoePessoaVinculo com status_aprovacao='pendente' para cada envolvido extraído.
     */
    public function autoHidratar(Request $request)
    {
        try {
            $request->validate([
                'boe' => 'required|string',
                'dados' => 'required|array',
            ]);

            $boe = $request->boe;
            $dados = $request->input('dados');
            $usuario = Auth::user();
            $usuarioId = $usuario ? $usuario->id : null;

            // Mapeamento: chave do array extraído -> tipo de vínculo (padrão do sistema)
            $mapeamento = [
                'condutores'   => 'CONDUTOR',
                'condutor'     => 'CONDUTOR',
                'vitimas'      => 'VITIMA',
                'vitima'       => 'VITIMA',
                'autores'      => 'AUTOR',
                'autor'        => 'AUTOR',
                'testemunhas'  => 'TESTEMUNHA',
                'testemunha'   => 'TESTEMUNHA',
            ];

            $criados = [];
            $jaExistiam = [];
            $ignorados = [];

            foreach ($mapeamento as $chaveArray => $tipoVinculo) {
                if (empty($dados[$chaveArray])) {
                    continue;
                }

                // Aceita array de strings OU string única
                $listaNomes = is_array($dados[$chaveArray]) ? $dados[$chaveArray] : [$dados[$chaveArray]];

                foreach ($listaNomes as $nomeBruto) {
                    // Pode ser string (nome) ou array (detalhe com 'nome')
                    if (is_array($nomeBruto)) {
                        $nomeBruto = $nomeBruto['nome'] ?? null;
                    }
                    if (!$nomeBruto || !is_string($nomeBruto)) {
                        continue;
                    }

                    $nome = mb_strtoupper(trim($nomeBruto), 'UTF-8');
                    if ($nome === '' || mb_strlen($nome) < 3) {
                        $ignorados[] = ['nome' => $nomeBruto, 'motivo' => 'Nome inválido'];
                        continue;
                    }

                    // 🚫 Nomes-placeholder não viram pessoa/vínculo (evita 'DESCONHECIDO' recriado)
                    $nomeNormalizado = self::normalizarNome($nome);
                    if (in_array($nomeNormalizado, self::NOMES_PLACEHOLDER, true)) {
                        $ignorados[] = ['nome' => $nomeBruto, 'motivo' => 'Nome-placeholder (ex: DESCONHECIDO)'];
                        continue;
                    }

                    // 1. Busca ou cria a pessoa na cadpessoa, usando nome NORMALIZADO (ignora acentos/caixa)
                    //    para não duplicar pessoas com grafia levemente diferente (ex: LEUGIN vs LEUGIM)
                    $pessoa = self::buscarPessoaPorNomeNormalizado($nome);
                    if ($pessoa) {
                        $pessoaId = $pessoa->IdCad;
                    } else {
                        $pessoaId = DB::table('cadpessoa')->insertGetId(['Nome' => $nome]);
                    }

                    // 2. Anti-duplicação: se já existe vínculo (pendente ou aprovado) p/ este BOE+pessoa+tipo, pula
                    $existente = BoePessoaVinculo::where('boe', $boe)
                        ->where('pessoa_id', $pessoaId)
                        ->where('tipo_vinculo', $tipoVinculo)
                        ->first();

                    if ($existente) {
                        $jaExistiam[] = [
                            'vinculo_id' => $existente->id,
                            'nome' => $nome,
                            'tipo' => $tipoVinculo,
                            'status' => $existente->status_aprovacao,
                        ];
                        continue;
                    }

                    // 3. Cria como PENDENTE para validação humana
                    $novo = BoePessoaVinculo::create([
                        'boe' => $boe,
                        'pessoa_id' => $pessoaId,
                        'tipo_vinculo' => $tipoVinculo,
                        'status_aprovacao' => 'pendente', // NUNCA 'aprovado' automaticamente
                        'criado_por' => $usuarioId,
                    ]);

                    $criados[] = [
                        'vinculo_id' => $novo->id,
                        'nome' => $nome,
                        'tipo' => $tipoVinculo,
                        'status' => 'pendente',
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Auto-hidratação concluída. Envolvidos enviados para aprovação humana.',
                'resumo' => [
                    'criados' => count($criados),
                    'ja_existiam' => count($jaExistiam),
                    'ignorados' => count($ignorados),
                ],
                'detalhes' => [
                    'criados' => $criados,
                    'ja_existiam' => $jaExistiam,
                    'ignorados' => $ignorados,
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Erro na auto-hidratação do BOE', [
                'boe' => $request->boe ?? null,
                'erro' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Falha ao auto-hidratar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retorna os dados já extraídos de um BOE a partir do cache de extração
     * (boe_pcpe_{BOE}_apfd.json). Evita o retrabalho de extrair novamente
     * quando o BOE já foi processado em outra tela (ex: /ip-apfd).
     */
    public function dadosExtraidos($boe)
    {
        try {
            if (!$boe || !trim($boe)) {
                return response()->json(['success' => false, 'message' => 'BOE não informado.'], 400);
            }

            $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $boe);
            $cacheDir = storage_path('app/boe_cache');

            // Procura o cache mais completo: apfd (mestre) ou ia como fallback
            $candidatos = [
                $cacheDir . "/boe_pcpe_{$boeLimpo}_apfd.json",
                $cacheDir . "/boe_pcpe_{$boeLimpo}_ia.json",
                $cacheDir . "/boe_pm_{$boeLimpo}_apfd.json",
                $cacheDir . "/boe_pm_{$boeLimpo}_ia.json",
            ];

            $arquivo = null;
            foreach ($candidatos as $c) {
                if (file_exists($c)) { $arquivo = $c; break; }
            }

            if (!$arquivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este BOE ainda não foi extraído/processado. Use "Colar Texto" ou "Enviar PDF" para extrair os dados.',
                    'boe_buscado' => $boe,
                ], 404);
            }

            $dados = json_decode(file_get_contents($arquivo), true);
            if (!is_array($dados) || empty($dados)) {
                return response()->json(['success' => false, 'message' => 'Cache do BOE vazio ou inválido.'], 404);
            }

            return response()->json([
                'success' => true,
                'dados'    => $dados,
                'cache_file' => basename($arquivo),
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro ao consultar dados extraídos do BOE: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno ao buscar dados do BOE.'], 500);
        }
    }

    /**
     * Resolve o pessoa_id e o papel de um envolvido pelo NOME + BOE.
     * Usado quando o editor de oitiva é aberto por um caminho que não
     * passa o _pessoa_id diretamente (ex: formulário de busca de documento do chip).
     */
    public function resolverPessoaPorNome(Request $request)
    {
        try {
            $request->validate([
                'boe' => 'required|string',
                'nome' => 'required|string',
            ]);

            $boe = $request->boe;
            $nome = mb_strtoupper(trim($request->nome), 'UTF-8');

            // Busca a pessoa na cadpessoa pelo nome
            $pessoa = DB::table('cadpessoa')->where('Nome', $nome)->first();
            if (!$pessoa) {
                // fallback: busca por similaridade simples (LIKE)
                $pessoa = DB::table('cadpessoa')->where('Nome', 'like', '%' . $nome . '%')->first();
            }
            if (!$pessoa) {
                return response()->json(['success' => false, 'message' => 'Pessoa não encontrada no cadastro pelo nome informado.'], 404);
            }

            $pessoaId = $pessoa->IdCad;

            // Descobre o papel pelo vínculo no BOE
            $vinculo = DB::table('boe_pessoas_vinculos')
                ->where('boe', $boe)
                ->where('pessoa_id', $pessoaId)
                ->first();

            $papel = $vinculo ? $vinculo->tipo_vinculo : null;

            return response()->json([
                'success' => true,
                'pessoa_id' => $pessoaId,
                'papel' => $papel,
                'nome' => $pessoa->Nome,
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro ao resolver pessoa por nome/BOE: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao resolver a pessoa.'], 500);
        }
    }

}
