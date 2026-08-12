<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsultaPessoaController extends Controller
{
    /**
     * Exibe a página inicial de consulta de pessoas.
     */
    public function index()
    {
        return view('consulta_pessoa');
    }

    /**
     * Busca todos os procedimentos (APFD, IP, Administrativo) vinculados a uma pessoa.
     *
     * @param  int  $id  ID da pessoa (IdCad)
     * @return \Illuminate\Http\JsonResponse
     */
    public function detalhes($id)
    {
        try {
            $pessoa = DB::table('cadpessoa')->where('IdCad', $id)->first();
            
            if (!$pessoa) {
                return response()->json(['success' => false, 'message' => 'Pessoa não encontrada'], 404);
            }

            // --- Inteligência de Busca: Agrupar IDs duplicados (por CPF limpo ou Nome Exato) ---
            $idsPessoasAliases = [$id];
            
            $cpf = $pessoa->CPF ?? '';
            $cpfLimpo = preg_replace('/[^\d]/', '', $cpf);
            $nomeExato = $pessoa->Nome ?? '';

            if (strlen($cpfLimpo) === 11 && $cpfLimpo !== '00000000000') {
                // Se tem CPF válido, busca todas as pessoas no banco que tem esse mesmo CPF limpo
                $outrasPorCpf = DB::table('cadpessoa')
                    ->whereRaw("REGEXP_REPLACE(CPF, '[^0-9]', '') = ?", [$cpfLimpo])
                    ->pluck('IdCad')
                    ->toArray();
                $idsPessoasAliases = array_merge($idsPessoasAliases, $outrasPorCpf);
            } 
            
            // Sempre busca pelo nome exato para garantir que homônimos sem CPF não escapem
            if (!empty($nomeExato)) {
                $outrasPorNome = DB::table('cadpessoa')
                    ->where('Nome', $nomeExato)
                    ->pluck('IdCad')
                    ->toArray();
                $idsPessoasAliases = array_merge($idsPessoasAliases, $outrasPorNome);
            }

            $idsBusca = array_unique($idsPessoasAliases);

            // 1. Buscar em APFD/IP via boe_pessoas_vinculos (usa o campo 'boe' como link)
            $vinculosBoe = DB::table('boe_pessoas_vinculos')
                ->join('cadprincipal', 'boe_pessoas_vinculos.boe', '=', 'cadprincipal.boe')
                ->whereIn('boe_pessoas_vinculos.pessoa_id', $idsBusca)
                ->select(
                    'cadprincipal.id', 
                    'cadprincipal.boe', 
                    'cadprincipal.ip', 
                    'cadprincipal.incidencia_penal as crime', 
                    'cadprincipal.data', 
                    'boe_pessoas_vinculos.tipo_vinculo as papel',
                    DB::raw("'APFD / IP' as modulo")
                )
                ->get();

            // 2. Buscar em APFD/IP via apfd_pessoas_detalhes (usa 'cadprincipal_id' como link direto)
            $vinculosApfd = DB::table('apfd_pessoas_detalhes')
                ->join('cadprincipal', 'apfd_pessoas_detalhes.cadprincipal_id', '=', 'cadprincipal.id')
                ->whereIn('apfd_pessoas_detalhes.pessoa_id', $idsBusca)
                ->select(
                    'cadprincipal.id', 
                    'cadprincipal.boe', 
                    'cadprincipal.ip', 
                    'cadprincipal.incidencia_penal as crime', 
                    'cadprincipal.data', 
                    'apfd_pessoas_detalhes.papel',
                    DB::raw("'APFD / IP' as modulo")
                )
                ->get();

            // 3. Buscar em Módulo Administrativo (ID ou menção nominal text-based)
            $vinculosAdmin = DB::table('administrativo_pessoas')
                ->join('administrativo', 'administrativo_pessoas.administrativo_id', '=', 'administrativo.id')
                ->where(function($q) use ($idsBusca, $nomeExato) {
                    $q->whereIn('administrativo_pessoas.pessoa_id', $idsBusca);
                    if (!empty($nomeExato)) {
                        $q->orWhere('administrativo_pessoas.nome', 'LIKE', '%' . trim($nomeExato) . '%');
                    }
                })
                ->select(
                    'administrativo.id', 
                    'administrativo.boe', 
                    'administrativo.ip', 
                    'administrativo.crime', 
                    'administrativo.data_cadastro as data', 
                    'administrativo_pessoas.papel',
                    DB::raw("'ADMINISTRATIVO' as modulo")
                )
                ->get();

            // Consolidar e remover duplicatas (especialmente entre BoeVinculo e ApfdDetalhe que apontam pro mesmo cadprincipal)
            $procedimentos = collect();

            // Processa vínculos de BOE
            foreach ($vinculosBoe as $v) {
                $procedimentos->push([
                    'modulo' => $v->modulo,
                    'id' => $v->id,
                    'boe' => $v->boe,
                    'ip' => $v->ip,
                    'crime' => $v->crime,
                    'data' => $v->data,
                    'papel' => $v->papel,
                ]);
            }

            // Processa vínculos de APFD (evita duplicar se o mesmo ID de cadprincipal já entrou)
            foreach ($vinculosApfd as $v) {
                $existe = $procedimentos->where('modulo', 'APFD / IP')
                                        ->where('id', $v->id)
                                        ->where('papel', $v->papel)
                                        ->first();
                if (!$existe) {
                    $procedimentos->push([
                        'modulo' => $v->modulo,
                        'id' => $v->id,
                        'boe' => $v->boe,
                        'ip' => $v->ip,
                        'crime' => $v->crime,
                        'data' => $v->data,
                        'papel' => $v->papel,
                    ]);
                }
            }

            // Processa vínculos de Administrativo
            foreach ($vinculosAdmin as $v) {
                $procedimentos->push([
                    'modulo' => $v->modulo,
                    'id' => $v->id,
                    'boe' => $v->boe,
                    'ip' => $v->ip,
                    'crime' => $v->crime,
                    'data' => $v->data,
                    'papel' => $v->papel,
                ]);
            }

            return response()->json([
                'success' => true,
                'pessoa' => $pessoa,
                'procedimentos' => $procedimentos->sortByDesc('data')->values()
            ]);

        } catch (\Exception $e) {
            Log::error("Erro na consulta de detalhes de pessoa ({$id}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno ao processar a consulta.'], 500);
        }
    }
}
