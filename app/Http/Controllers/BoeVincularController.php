<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\BoePessoaVinculo;

class BoeVincularController extends Controller
{
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

            foreach ($vinculos as $v) {
                $pessoa = $pessoas->get($v->pessoa_id);
                if ($pessoa) {
                    $dadosPessoa = (array) $pessoa;
                    $dadosPessoa['vinculo_id'] = $v->id;
                    $dadosPessoa['status_aprovacao'] = $v->status_aprovacao ?? 'aprovado';
                    $dadosPessoa['criado_por'] = $v->criado_por;
                    $dadosPessoa['criado_por_nome'] = $v->criado_por ? ($criadores[$v->criado_por] ?? 'Desconhecido') : null;

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
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $boe)->first();
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

            // ✅ REFORÇO DE SEGURANÇA: Verificar se o usuário é o dono do procedimento
            $user = Auth::user();
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $request->boe)->first();

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

            // 1. Tentar encontrar a pessoa pelo nome exato
            $pessoa = DB::table('cadpessoa')->where('Nome', $nome)->first();
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
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $vinculo->boe)->first();

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
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $vinculo->boe)->first();

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
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $vinculo->boe)->first();

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
            $cadprincipal = DB::table('cadprincipal')->where('BOE', $boe)->first();
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

}
