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
            // Assumindo que CadPessoa não tem Model, usamos Query Builder
            $pessoasIds = $vinculos->pluck('pessoa_id')->unique();
            $pessoas = DB::table('cadpessoa')->whereIn('IdCad', $pessoasIds)->get()->keyBy('IdCad');

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
                    $dadosPessoa['vinculo_id'] = $v->id; // ID do vínculo para exclusão

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

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao listar vínculos do BOE {$boe}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao listar vínculos'], 500);
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
            'tipo' => 'required|string|in:CONDUTOR,VITIMA,AUTOR,TESTEMUNHA'
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

            // Para CONDUTOR, talvez queira garantir apenas um? 
            // Se sim, descomente abaixo. Se não, deixe múltiplo.
            /*
            if ($request->tipo === 'CONDUTOR') {
                BoePessoaVinculo::where('boe', $request->boe)->where('tipo_vinculo', 'CONDUTOR')->delete();
            }
            */

            $vinculo = BoePessoaVinculo::create([
                'boe' => $request->boe,
                'pessoa_id' => $request->pessoa_id,
                'tipo_vinculo' => $request->tipo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vínculo adicionado com sucesso!',
                'id' => $vinculo->id
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao adicionar vínculo: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao salvar vínculo'], 500);
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

            $vinculo->delete();

            return response()->json(['success' => true, 'message' => 'Vínculo removido com sucesso']);

        } catch (\Exception $e) {
            Log::error("Erro ao remover vínculo {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao remover vínculo'], 500);
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

            // Opcional: Limpar vínculos anteriores se for um "salvamento completo" que substitui tudo?
            // O comportamento antigo era UPDATE ou INSERT na tabela única.
            // Aqui, se recebermos vitima1_id, vamos assumir que é para ADICIONAR ou ATUALIZAR.
            // Como não sabemos qual ID de vínculo corresponde à "vitima1", 
            // a melhor estratégia de compatibilidade é:
            // Se o frontend enviar esses campos, nós limpamos os vínculos desse tipo e recriamos.
            // ISSO É DESTRUTIVO PARA VÍNCULOS EXTRAS, MAS MANTÉM O COMPORTAMENTO "SLOT".

            // Mas para ser seguro, vamos apenas ADICIONAR se não existir.

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
                                'tipo_vinculo' => $tipo
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
