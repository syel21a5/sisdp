<?php

namespace App\Http\Controllers;
use App\Exports\AdministrativoGeralExport;
use App\Exports\AdministrativoCrimesExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Exports\AdministrativoPessoasExport;
use App\Exports\AdministrativoApreensoesExport;

use Illuminate\Http\Request;
use App\Services\PdfService;
use App\Models\Administrativo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AdministrativoPessoa;

class AdministrativoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perms = $user->permissions ?? [];
        if (isset($perms['menu_lateral']) && !$perms['menu_lateral']) {
            return redirect()->route('geral')->with('error', 'Acesso ao menu lateral desabilitado para seu usuário.');
        }
        if (isset($perms['administrativo']) && !$perms['administrativo']) {
            return redirect()->route('geral')->with('error', 'Você não tem permissão para acessar Administrativo.');
        }
        return view('wf_administrativo');
    }


    public function salvar(Request $request)
    {
        $validated = $request->validate([
            'data_cadastro' => 'required|date_format:d/m/Y',
            'boe' => 'required|string|max:255|unique:administrativo,boe',
            'ip' => 'nullable|string|max:255',
            'crime' => 'nullable|string|max:255',
            'tipificacao' => 'nullable|string|max:255',
            'apreensao' => 'nullable|string',
            'cartorio' => 'nullable|string|max:255',
            'envolvidos' => 'sometimes|string|nullable',
        ], [
            'data_cadastro.required' => 'O campo Data de Cadastro é obrigatório.',
            'data_cadastro.date_format' => 'O campo Data de Cadastro deve estar no formato DD/MM/AAAA.',
            'boe.required' => 'O campo BOE é obrigatório.',
            'boe.unique' => 'Já existe um registro com este BOE.',
        ]);

        try {
            $dataCadastro = Carbon::createFromFormat('d/m/Y', $request->data_cadastro)->format('Y-m-d');

            $administrativo = new Administrativo();
            $userId = Auth::id();
            $administrativo->user_id = ($userId && User::where('id', $userId)->exists()) ? $userId : null;
            $administrativo->data_cadastro = $dataCadastro;
            $administrativo->boe = $request->boe;
            $administrativo->ip = $request->ip;
            $administrativo->crime = $request->crime;
            $administrativo->tipificacao = $request->tipificacao;
            $administrativo->apreensao = $request->apreensao;
            $administrativo->cartorio = $request->cartorio;
            $administrativo->save();

            $envolvidos = $request->input('envolvidos');
            if ($envolvidos) {
                $parsed = json_decode($envolvidos, true);
                if (is_array($parsed)) {
                    $ordem = 0;
                    foreach ([
                        'vitimas' => 'VITIMA',
                        'autores' => 'AUTOR',
                        'testemunhas' => 'TESTEMUNHA',
                        'capturados' => 'CAPTURADO',
                        'outros' => 'OUTRO'
                    ] as $key => $papel) {
                        if (isset($parsed[$key]) && is_array($parsed[$key])) {
                            foreach ($parsed[$key] as $nome) {
                                if (!is_string($nome) || trim($nome) === '')
                                    continue;
                                AdministrativoPessoa::create([
                                    'administrativo_id' => $administrativo->id,
                                    'pessoa_id' => null,
                                    'nome' => trim($nome),
                                    'papel' => $papel,
                                    'ordem' => $ordem++,
                                    'observacao' => null,
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Registro salvo com sucesso!',
                'id' => $administrativo->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar administrativo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar o registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pesquisar(Request $request)
    {
        $request->validate([
            'filtro' => 'sometimes|in:boe,ip,vitima,autor,envolvido',
            'termo' => 'sometimes|string|max:100|nullable'
        ]);

        $filtro = $request->filtro ?? 'boe';
        $termo = $request->termo ?? '';
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
        }

        if (in_array($filtro, ['vitima', 'autor', 'envolvido'])) {
            $registros = Administrativo::query()
                ->whereIn('id', function ($q) use ($filtro, $termo) {
                    $q->select('administrativo_id')
                        ->from('administrativo_pessoas')
                        ->when($filtro !== 'envolvido', function ($qq) use ($filtro) {
                            $qq->where('papel', $filtro === 'vitima' ? 'VITIMA' : 'AUTOR');
                        })
                        ->when(!empty($termo), function ($qq) use ($termo) {
                            $qq->where('nome', 'LIKE', "%{$termo}%");
                        });
                })
                ->orderBy('data_cadastro', 'desc')
                ->get();
        } else {
            $query = Administrativo::query();
            if (!empty($termo)) {
                $query->where($filtro, 'LIKE', "%{$termo}%");
            }
            $registros = $query->orderBy('data_cadastro', 'desc')->get();
        }
        $registros->load('pessoas');
        $registros->each(function ($r) {
            $vit = $r->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
            $aut = $r->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
            $tes = $r->pessoas->where('papel', 'TESTEMUNHA')->pluck('nome')->implode(', ');
            $r->vitimas_list = $vit;
            $r->autores_list = $aut;
            $r->testemunhas_list = $tes;
        });

        return response()->json(['success' => true, 'data' => $registros]);
    }

    public function buscar($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
        }

        $administrativo = Administrativo::where('id', $id)->first();

        if (!$administrativo) {
            return response()->json(['success' => false, 'message' => 'Registro não encontrado.'], 404);
        }

        $administrativo->data_cadastro = Carbon::parse($administrativo->data_cadastro)->format('d/m/Y');

        $pessoas = AdministrativoPessoa::where('administrativo_id', $administrativo->id)->orderBy('ordem')->get();
        $envolvidos = [
            'vitimas' => $pessoas->where('papel', 'VITIMA')->pluck('nome')->values()->all(),
            'autores' => $pessoas->where('papel', 'AUTOR')->pluck('nome')->values()->all(),
            'testemunhas' => $pessoas->where('papel', 'TESTEMUNHA')->pluck('nome')->values()->all(),
            'capturados' => $pessoas->where('papel', 'CAPTURADO')->pluck('nome')->values()->all(),
            'outros' => $pessoas->where('papel', 'OUTRO')->pluck('nome')->values()->all(),
        ];

        $data = $administrativo->toArray();
        $data['envolvidos'] = $envolvidos;

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function atualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'data_cadastro' => 'required|date_format:d/m/Y',
            'boe' => 'required|string|max:255|unique:administrativo,boe,' . $id . ',id',
            'ip' => 'nullable|string|max:255',
            'crime' => 'nullable|string|max:255',
            'tipificacao' => 'nullable|string|max:255',
            'apreensao' => 'nullable|string',
            'cartorio' => 'nullable|string|max:255',
            'envolvidos' => 'sometimes|string|nullable',
        ], [
            'data_cadastro.required' => 'O campo Data de Cadastro é obrigatório.',
            'data_cadastro.date_format' => 'O campo Data de Cadastro deve estar no formato DD/MM/AAAA.',
            'boe.required' => 'O campo BOE é obrigatório.',
            'boe.unique' => 'Já existe um registro com este BOE.',
        ]);

        try {
            $dataCadastro = Carbon::createFromFormat('d/m/Y', $validated['data_cadastro'])->format('Y-m-d');
            $userId = Auth::id();

            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
            }

            $administrativo = Administrativo::where('id', $id)->first();

            if (!$administrativo) {
                return response()->json(['success' => false, 'message' => 'Registro não encontrado ou sem permissão.'], 404);
            }

            $administrativo->data_cadastro = $dataCadastro;
            $administrativo->boe = $validated['boe'];
            $administrativo->ip = $validated['ip'];
            $administrativo->crime = $validated['crime'];
            $administrativo->tipificacao = $validated['tipificacao'];
            $administrativo->apreensao = $validated['apreensao'];
            $administrativo->cartorio = $validated['cartorio'];
            $administrativo->save();

            $envolvidos = $request->input('envolvidos');
            if ($envolvidos) {
                AdministrativoPessoa::where('administrativo_id', $administrativo->id)->delete();
                $parsed = json_decode($envolvidos, true);
                if (is_array($parsed)) {
                    $ordem = 0;
                    foreach ([
                        'vitimas' => 'VITIMA',
                        'autores' => 'AUTOR',
                        'testemunhas' => 'TESTEMUNHA',
                        'capturados' => 'CAPTURADO',
                        'outros' => 'OUTRO'
                    ] as $key => $papel) {
                        if (isset($parsed[$key]) && is_array($parsed[$key])) {
                            foreach ($parsed[$key] as $nome) {
                                if (!is_string($nome) || trim($nome) === '')
                                    continue;
                                AdministrativoPessoa::create([
                                    'administrativo_id' => $administrativo->id,
                                    'pessoa_id' => null,
                                    'nome' => trim($nome),
                                    'papel' => $papel,
                                    'ordem' => $ordem++,
                                    'observacao' => null,
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Registro atualizado com sucesso!']);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar administrativo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function excluir($id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
            }

            $administrativo = Administrativo::where('id', $id)->first();

            if (!$administrativo) {
                return response()->json(['success' => false, 'message' => 'Registro não encontrado ou sem permissão.'], 404);
            }

            $administrativo->delete();

            return response()->json(['success' => true, 'message' => 'Registro excluído com sucesso!']);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir administrativo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir o registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ultimos()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
        }

        $registros = Administrativo::query()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $registros->load('pessoas');
        $registros->each(function ($r) {
            $vit = $r->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
            $aut = $r->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
            $tes = $r->pessoas->where('papel', 'TESTEMUNHA')->pluck('nome')->implode(', ');
            $r->vitimas_list = $vit;
            $r->autores_list = $aut;
            $r->testemunhas_list = $tes;
        });

        return response()->json(['success' => true, 'data' => $registros]);
    }

    public function importarBoeTexto(Request $request, \App\Services\BoeExtractorService $extractorService)
    {
        $result = $extractorService->extract($request, 'administrativo');
        
        if (!($result['success'] ?? false)) {
            return response()->json($result, $result['status'] ?? 500);
        }
        
        $dt = $result['dados'];
        $crime = null;
        $tipificacao = null;
        if (!empty($dt['natureza'])) {
            $parts = preg_split('/\s*-\s*/', $dt['natureza'], 2);
            $crime = trim($parts[0] ?? '');
            $tipificacao = trim($parts[1] ?? '');
        }

        $dados_adaptados = [
            'boe' => $dt['boe'] ?? null,
            'ip' => $dt['ip'] ?? null,
            'crime' => $crime,
            'tipificacao' => $tipificacao,
            'apreensao' => '',
            'cartorio' => null,
            'hora' => $dt['hora_fato'] ?? null,
            'motivacao' => '',
            'endereco' => $dt['end_fato'] ?? null,
            'local_fato' => null,
            'envolvidos' => [
                'vitimas' => $dt['vitimas'] ?? [],
                'autores' => $dt['autores'] ?? [],
                'testemunhas' => $dt['testemunhas'] ?? [],
                'capturados' => [],
                'outros' => array_merge($dt['condutor'] ?? [], $dt['outros'] ?? [])
            ],
            'celulares' => $dt['celulares'] ?? [],
            'veiculos' => $dt['veiculos'] ?? []
        ];

        return response()->json(['success' => true, 'dados' => $dados_adaptados, 'fallback' => false]);
    }

    // ✅ RELATÓRIO COMPLETO COM NOVAS CONSULTAS (CORRIGIDO)
    public function relatorio(Request $request)
    {
        $request->validate([
            'periodo' => 'sometimes|in:hoje,semana,mes,ano,personalizado,todos',
            'data_inicio' => 'sometimes|date_format:Y-m-d|nullable',
            'data_fim' => 'sometimes|date_format:Y-m-d|nullable',
            'tipo' => 'sometimes|in:resumo,crime,vitima,autor,cartorio,apreensao',
            'crime' => 'sometimes|string|max:255|nullable',
            'vitima' => 'sometimes|string|max:255|nullable',
            'autor' => 'sometimes|string|max:255|nullable',
            'cartorio' => 'sometimes|string|max:255|nullable'
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
            }

            $query = Administrativo::query();

            $periodo = $request->periodo ?? 'personalizado';
            if ($periodo !== 'todos') {
                if ($periodo === 'personalizado') {
                    $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : Carbon::today();
                    $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : Carbon::today();
                } else {
                    $dataInicio = $this->getDataInicioPorPeriodo($periodo);
                    $dataFim = Carbon::today();
                }
                $query->whereBetween('data_cadastro', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')]);
            }

            if ($request->filled('crime')) {
                $query->where('crime', 'LIKE', "%{$request->crime}%");
            }
            if ($request->filled('vitima')) {
                $query->whereIn('id', function ($q) use ($request) {
                    $q->select('administrativo_id')
                        ->from('administrativo_pessoas')
                        ->where('papel', 'VITIMA')
                        ->where('nome', 'LIKE', "%{$request->vitima}%");
                });
            }
            if ($request->filled('autor')) {
                $query->whereIn('id', function ($q) use ($request) {
                    $q->select('administrativo_id')
                        ->from('administrativo_pessoas')
                        ->where('papel', 'AUTOR')
                        ->where('nome', 'LIKE', "%{$request->autor}%");
                });
            }
            if ($request->filled('cartorio')) {
                $query->where('cartorio', 'LIKE', "%{$request->cartorio}%");
            }

            $dados = $query->orderBy('data_cadastro', 'desc')->get();
            $dados->load('pessoas');
            $dados->each(function ($r) {
                $r->vitima = $r->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
                $r->autor = $r->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
                $r->testemunha = $r->pessoas->where('papel', 'TESTEMUNHA')->pluck('nome')->implode(', ');
            });
            $metricas = $this->calcularMetricas($dados);
            $graficos = $this->prepararDadosGraficos($dados);
            $estatisticas = method_exists($this, 'calcularEstatisticasAvancadas') ? $this->calcularEstatisticasAvancadas($dados) : [];

            return response()->json([
                'success' => true,
                'data' => $dados,
                'metricas' => $metricas,
                'graficos' => $graficos,
                'estatisticas' => $estatisticas,
                'filtros' => $request->all()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório administrativo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ RELATÓRIO ESPECÍFICO PARA CRIMES
    public function relatorioCrimes(Request $request)
    {
        $request->validate([
            'periodo' => 'required|in:hoje,semana,mes,ano,todos',
            'agrupar_por' => 'required|in:crime,tipificacao'
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
            }

            $query = Administrativo::query();

            // Aplicar filtro de período
            $dataInicio = $this->getDataInicioPorPeriodo($request->periodo);
            if ($dataInicio) {
                $query->where('data_cadastro', '>=', $dataInicio->format('Y-m-d'));
            }

            // Agrupar por tipo de crime
            $campoAgrupamento = $request->agrupar_por === 'tipificacao' ? 'tipificacao' : 'crime';

            $resultados = $query->leftJoin('administrativo_pessoas as ap', 'ap.administrativo_id', '=', 'administrativo.id')
                ->selectRaw("{$campoAgrupamento} as agrupamento,
                    COUNT(DISTINCT administrativo.id) as total,
                    COUNT(DISTINCT CASE WHEN ap.papel = 'VITIMA' THEN ap.nome END) as vitimas_unicas,
                    COUNT(DISTINCT CASE WHEN ap.papel = 'AUTOR' THEN ap.nome END) as autores_unicos,
                    MIN(administrativo.data_cadastro) as primeira_ocorrencia,
                    MAX(administrativo.data_cadastro) as ultima_ocorrencia")
                ->whereNotNull($campoAgrupamento)
                ->where($campoAgrupamento, '!=', '')
                ->groupBy('agrupamento')
                ->orderBy('total', 'desc')
                ->get();

            $estatisticas = [
                'total_ocorrencias' => $resultados->sum('total'),
                'tipos_diferentes' => $resultados->count(),
                'mais_comum' => $resultados->first(),
                'periodo' => $request->periodo,
                'agrupar_por' => $request->agrupar_por
            ];

            return response()->json([
                'success' => true,
                'dados' => $resultados,
                'estatisticas' => $estatisticas,
                'filtros' => $request->all()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de crimes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de crimes: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ RELATÓRIO POR PESSOA
    public function relatorioPessoas(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:vitima,autor,ambos',
            'periodo' => 'required|in:hoje,semana,mes,ano,todos',
            'limite' => 'sometimes|integer|min:1|max:100'
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
            }

            $limite = $request->limite ?? 50;
            $resultados = collect();

            $base = DB::table('administrativo');
            $aplicaPeriodo = function ($q) use ($request) {
                $inicio = $this->getDataInicioPorPeriodo($request->periodo);
                if ($inicio) {
                    $q->where('administrativo.data_cadastro', '>=', $inicio->format('Y-m-d'));
                }
            };

            if ($request->tipo === 'vitima' || $request->tipo === 'ambos') {
                $vitimas = $base->clone()
                    ->join('administrativo_pessoas as ap', 'ap.administrativo_id', '=', 'administrativo.id')
                    ->where('ap.papel', 'VITIMA')
                    ->tap($aplicaPeriodo)
                    ->selectRaw('ap.nome as nome, COUNT(*) as total_ocorrencias, COUNT(DISTINCT administrativo.crime) as crimes_diferentes, GROUP_CONCAT(DISTINCT administrativo.crime) as crimes, MIN(administrativo.data_cadastro) as primeira_ocorrencia, MAX(administrativo.data_cadastro) as ultima_ocorrencia')
                    ->groupBy('ap.nome')
                    ->orderBy('total_ocorrencias', 'desc')
                    ->limit($limite)
                    ->get()
                    ->map(function ($i) {
                        $i->tipo = 'Vítima';
                        $i->crimes_array = $i->crimes ? explode(',', $i->crimes) : [];
                        return $i;
                    });
                $resultados = $resultados->merge($vitimas);
            }

            if ($request->tipo === 'autor' || $request->tipo === 'ambos') {
                $autores = $base->clone()
                    ->join('administrativo_pessoas as ap', 'ap.administrativo_id', '=', 'administrativo.id')
                    ->where('ap.papel', 'AUTOR')
                    ->tap($aplicaPeriodo)
                    ->selectRaw('ap.nome as nome, COUNT(*) as total_ocorrencias, COUNT(DISTINCT administrativo.crime) as crimes_diferentes, GROUP_CONCAT(DISTINCT administrativo.crime) as crimes, MIN(administrativo.data_cadastro) as primeira_ocorrencia, MAX(administrativo.data_cadastro) as ultima_ocorrencia')
                    ->groupBy('ap.nome')
                    ->orderBy('total_ocorrencias', 'desc')
                    ->limit($limite)
                    ->get()
                    ->map(function ($i) {
                        $i->tipo = 'Autor';
                        $i->crimes_array = $i->crimes ? explode(',', $i->crimes) : [];
                        return $i;
                    });
                $resultados = $resultados->merge($autores);
            }

            $resultados = $resultados->sortByDesc('total_ocorrencias')->values();

            return response()->json([
                'success' => true,
                'dados' => $resultados,
                'estatisticas' => [
                    'total_pessoas' => $resultados->count(),
                    'periodo' => $request->periodo,
                    'tipo' => $request->tipo,
                    'limite' => $limite
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de pessoas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de pessoas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ RELATÓRIO DE APREENSÕES (CORRIGIDO)
    public function relatorioApreensoes(Request $request)
    {
        $request->validate([
            'periodo' => 'required|in:hoje,semana,mes,ano,todos',
            'tipo_apreensao' => 'sometimes|string|max:255|nullable'
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado.'], 401);
            }

            $query = Administrativo::query();

            // Aplicar filtro de período
            $dataInicio = $this->getDataInicioPorPeriodo($request->periodo);
            if ($dataInicio) {
                $query->where('data_cadastro', '>=', $dataInicio->format('Y-m-d'));
            }

            // Filtrar por tipo de apreensão se especificado
            if ($request->filled('tipo_apreensao')) {
                $query->where('apreensao', 'LIKE', "%{$request->tipo_apreensao}%");
            }

            $resultados = $query->whereNotNull('apreensao')
                ->where('apreensao', '!=', '')
                ->orderBy('data_cadastro', 'desc')
                ->get(['id', 'apreensao', 'crime', 'data_cadastro', 'boe']);
            $resultados->load('pessoas');
            $resultados->each(function ($r) {
                $r->vitima = $r->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
                $r->autor = $r->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
            });

            // Análise de apreensões
            $analise = [
                'total_apreensoes' => $resultados->count(),
                'apreensoes_por_crime' => $resultados->groupBy('crime')->map->count(),
                'itens_apreendidos' => $this->extrairItensApreendidos($resultados),
                'periodo' => $request->periodo
            ];

            return response()->json([
                'success' => true,
                'dados' => $resultados,
                'analise' => $analise,
                'filtros' => $request->all()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de apreensões: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório de apreensões: ' . $e->getMessage()
            ], 500);
        }
    }


    // ✅ EXPORTAR PARA EXCEL/PDF - IMPLEMENTAÇÃO REAL
    public function exportarRelatorio(Request $request)
    {
        $request->validate(['formato' => 'required|in:excel,pdf,csv', 'tipo_relatorio' => 'required|in:geral,crimes,pessoas,apreensoes', 'filtros' => 'nullable']);
        try {
            $filtrosRaw = $request->input('filtros', []);
            $filtros = is_string($filtrosRaw) ? (json_decode($filtrosRaw, true) ?? []) : (is_array($filtrosRaw) ? $filtrosRaw : []);
            $nomeArquivo = "relatorio_{$request->tipo_relatorio}_" . date('Y-m-d_His');
            if ($request->formato === 'excel')
                return $this->exportarExcel($request->tipo_relatorio, $filtros, $nomeArquivo);
            if ($request->formato === 'csv')
                return $this->exportarCsv($request->tipo_relatorio, $filtros, $nomeArquivo);
            return $this->exportarPdf($request->tipo_relatorio, $filtros, $nomeArquivo);
        } catch (\Exception $e) {
            Log::error('Erro ao exportar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function exportarRelatorio_antigo(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Função depreciada. Use exportarRelatorio().'], 410);

        /* CÓDIGO LEGADO MANTIDO APENAS PARA HISTÓRICO - REMOVIDO DA ANÁLISE ESTÁTICA
        $request->validate([
            'formato' => 'required|in:excel,pdf,csv',
            'tipo_relatorio' => 'required|in:geral,crimes,pessoas,apreensoes',
            'filtros' => 'nullable'
        ]);

        try {
            // ... (código antigo omitido para limpeza e performance) ...
        } catch (\Exception $e) {
            Log::error('Erro ao exportar relatório: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
        */
    }

    // ========== MÉTODOS PRIVADOS AUXILIARES ==========

    private function calcularMetricas($dados)
    {
        $hoje = Carbon::today();
        $inicioSemana = Carbon::today()->startOfWeek();
        $inicioMes = Carbon::today()->startOfMonth();
        $inicioAno = Carbon::today()->startOfYear();

        return [
            'total' => $dados->count(),
            'hoje' => $dados->where('data_cadastro', $hoje->format('Y-m-d'))->count(),
            'esta_semana' => $dados->where('data_cadastro', '>=', $inicioSemana->format('Y-m-d'))->count(),
            'este_mes' => $dados->where('data_cadastro', '>=', $inicioMes->format('Y-m-d'))->count(),
            'este_ano' => $dados->where('data_cadastro', '>=', $inicioAno->format('Y-m-d'))->count(),
            'com_apreensao' => $dados->whereNotNull('apreensao')->where('apreensao', '!=', '')->count(),
            'vitimas_unicas' => $dados->whereNotNull('vitima')->where('vitima', '!=', '')->pluck('vitima')->unique()->count(),
            'autores_unicos' => $dados->whereNotNull('autor')->where('autor', '!=', '')->pluck('autor')->unique()->count()
        ];
    }

    private function prepararDadosGraficos($dados)
    {
        // Gráfico de crimes
        $crimes = $dados->groupBy('crime')
            ->map(function ($group, $crime) use ($dados) {
                $total = $group->count();
                $percentual = $dados->count() > 0 ? round(($total / $dados->count()) * 100, 1) : 0;

                return [
                    'crime' => $crime ?: 'Não informado',
                    'total' => $total,
                    'percentual' => $percentual
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(10);

        // Gráfico mensal (últimos 6 meses)
        $graficoMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::today()->subMonths($i);
            $mesLegenda = $mes->translatedFormat('M/Y');

            $totalMes = $dados->filter(function ($item) use ($mes) {
                $data = Carbon::parse($item->data_cadastro);
                return $data->month == $mes->month && $data->year == $mes->year;
            })->count();

            $graficoMensal[] = [
                'mes' => $mesLegenda,
                'total' => $totalMes
            ];
        }

        // Gráfico de cartórios
        $cartorios = $dados->groupBy('cartorio')
            ->map(function ($group, $cartorio) use ($dados) {
                $total = $group->count();
                $percentual = $dados->count() > 0 ? round(($total / $dados->count()) * 100, 1) : 0;

                return [
                    'cartorio' => $cartorio ?: 'Não informado',
                    'total' => $total,
                    'percentual' => $percentual
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(8);

        return [
            'crimes' => $crimes,
            'mensal' => $graficoMensal,
            'cartorios' => $cartorios
        ];
    }

    private function calcularEstatisticasAvancadas($dados)
    {
        $comApreensao = $dados->whereNotNull('apreensao')->where('apreensao', '!=', '');

        return [
            'taxa_apreensao' => $dados->count() > 0 ? round(($comApreensao->count() / $dados->count()) * 100, 1) : 0,
            'crimes_mais_comuns' => $dados->groupBy('crime')->map->count()->sortDesc()->take(5),
            'cartorios_mais_utilizados' => $dados->groupBy('cartorio')->map->count()->sortDesc()->take(5),
            'vitimas_recorrentes' => $dados->groupBy('vitima')->map->count()->sortDesc()->take(5),
            'autores_recorrentes' => $dados->groupBy('autor')->map->count()->sortDesc()->take(5)
        ];
    }

    private function getDataInicioPorPeriodo($periodo)
    {
        return match ($periodo) {
            'hoje' => Carbon::today(),
            'semana' => Carbon::today()->startOfWeek(),
            'mes' => Carbon::today()->startOfMonth(),
            'ano' => Carbon::today()->startOfYear(),
            default => null
        };
    }

    private function extrairItensApreendidos($apreensoes)
    {
        $itens = [];

        foreach ($apreensoes as $apreensao) {
            if (!empty($apreensao->apreensao)) {
                // Simples extração de itens - em produção use parser mais sofisticado
                $texto = strtolower($apreensao->apreensao);

                if (str_contains($texto, 'celular') || str_contains($texto, 'smartphone')) {
                    $itens['Celulares'] = ($itens['Celulares'] ?? 0) + 1;
                }
                if (str_contains($texto, 'arma') || str_contains($texto, 'revólver') || str_contains($texto, 'pistola')) {
                    $itens['Armas'] = ($itens['Armas'] ?? 0) + 1;
                }
                if (str_contains($texto, 'droga') || str_contains($texto, 'entorpecente') || str_contains($texto, 'cocaína') || str_contains($texto, 'maconha')) {
                    $itens['Drogas'] = ($itens['Drogas'] ?? 0) + 1;
                }
                if (str_contains($texto, 'dinheiro') || str_contains($texto, 'moeda') || str_contains($texto, 'real')) {
                    $itens['Dinheiro'] = ($itens['Dinheiro'] ?? 0) + 1;
                }
                if (str_contains($texto, 'veículo') || str_contains($texto, 'carro') || str_contains($texto, 'moto')) {
                    $itens['Veículos'] = ($itens['Veículos'] ?? 0) + 1;
                }
            }
        }

        return $itens;
    }

    private function prepararDadosExportacao($request)
    {
        // Simulação - em produção, busque os dados reais baseado nos filtros
        return [
            'cabecalho' => ['Data', 'BOE', 'IP', 'Vítima', 'Autor', 'Crime', 'Tipificação', 'Apreensão', 'Cartório'],
            'dados' => [],
            'filtros' => $request->all(),
            'gerado_em' => now()->format('d/m/Y H:i:s')
        ];
    }
    // ========== MÉTODOS DE EXPORTAÇÃO REFATORADOS (FIX PHP6613) ==========

    private function exportarExcel($tipo, $filtros, $nomeArquivo)
    {
        // NOVO: Usar Python (Pandas) para exportação ultrarrápida e sem limite de memória PHP
        $query = Administrativo::query();
        $select = '*';

        if ($tipo === 'geral') {
            $this->aplicarFiltrosGerais($query, $filtros);
            $select = "boe as BOE,
                       (SELECT GROUP_CONCAT(nome SEPARATOR ', ') FROM administrativo_pessoas ap WHERE ap.administrativo_id = administrativo.id AND ap.papel = 'VITIMA') AS Vitima,
                       (SELECT GROUP_CONCAT(nome SEPARATOR ', ') FROM administrativo_pessoas ap WHERE ap.administrativo_id = administrativo.id AND ap.papel = 'AUTOR') AS Autor,
                       crime as Crime, cartorio as Cartorio, data_cadastro as Data_Fato";
            $query->selectRaw($select)->orderBy('data_cadastro', 'desc');

        } elseif ($tipo === 'crimes') {
            $campo = isset($filtros['agrupar_por']) && $filtros['agrupar_por'] === 'tipificacao' ? 'tipificacao' : 'crime';
            $this->aplicarFiltroPeriodo($query, $filtros);
            $query->selectRaw("{$campo} as Grupo, COUNT(*) as Total")
                  ->whereNotNull($campo)->where($campo, '!=', '')
                  ->groupBy('Grupo')->orderBy('Total', 'desc');

        } elseif ($tipo === 'pessoas') {
            $this->aplicarFiltroPeriodo($query, $filtros);
            $limite = isset($filtros['limite']) ? (int) $filtros['limite'] : 50;
            $tipoPesq = $filtros['tipo'] ?? 'ambos';
            
            // Query complexa refatorada para DB puro, ideal para o Python
            $query = \DB::table('administrativo_pessoas as ap')
                ->join('administrativo as a', 'a.id', '=', 'ap.administrativo_id')
                ->selectRaw("ap.nome as Nome, ap.papel as Tipo, COUNT(*) as Total_Ocorrencias, COUNT(DISTINCT a.crime) as Crimes_Diferentes, MIN(a.data_cadastro) as Primeira_Ocorrencia, MAX(a.data_cadastro) as Ultima_Ocorrencia");
            if ($tipoPesq === 'vitima') $query->where('ap.papel', 'VITIMA');
            elseif ($tipoPesq === 'autor') $query->where('ap.papel', 'AUTOR');
            else $query->whereIn('ap.papel', ['VITIMA', 'AUTOR']);
            
            if (isset($filtros['periodo']) && $filtros['periodo'] !== 'todos') {
                $query->where('a.data_cadastro', '>=', now()->startOfMonth()->format('Y-m-d'));
            }
            
            $query->groupBy('ap.nome', 'ap.papel')->orderBy('Total_Ocorrencias', 'desc')->limit($limite);

        } elseif ($tipo === 'apreensoes') {
            $this->aplicarFiltroPeriodo($query, $filtros);
            if (!empty($filtros['tipo_apreensao']))
                $query->where('apreensao', 'LIKE', "%{$filtros['tipo_apreensao']}%");
            $query->selectRaw("apreensao as Apreensao, crime as Crime, 
                               (SELECT GROUP_CONCAT(nome SEPARATOR ', ') FROM administrativo_pessoas ap WHERE ap.administrativo_id = administrativo.id AND ap.papel = 'VITIMA') AS Vitima,
                               (SELECT GROUP_CONCAT(nome SEPARATOR ', ') FROM administrativo_pessoas ap WHERE ap.administrativo_id = administrativo.id AND ap.papel = 'AUTOR') AS Autor,
                               data_cadastro as Data_Fato, boe as BOE")
                  ->whereNotNull('apreensao')->where('apreensao', '!=', '')
                  ->orderBy('data_cadastro', 'desc');
        } else {
            return response()->json(['success' => false, 'message' => 'Tipo não suportado'], 400);
        }

        // Gera JSON de entrada para o script Python
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        
        $tempJson = sys_get_temp_dir() . '/excel_input_' . uniqid() . '.json';
        $tempExcel = sys_get_temp_dir() . '/' . $nomeArquivo . '.xlsx';
        
        file_put_contents($tempJson, json_encode([
            'sql' => $sql,
            'bindings' => $bindings
        ]));
        
        $scriptPath = base_path('scripts/python/gerar_excel.py');
        $pythonCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';
        $command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempJson) . " " . escapeshellarg($tempExcel) . " 2>&1";
        
        $output = shell_exec($command);
        $jsonStartPos = strpos($output, '{');
        $result = $jsonStartPos !== false ? json_decode(substr($output, $jsonStartPos), true) : null;
        
        @unlink($tempJson);
        
        if ($result && isset($result['success']) && $result['success']) {
            return response()->download($result['path'], $nomeArquivo . '.xlsx')->deleteFileAfterSend(true);
        } else {
            \Log::error("Falha ao gerar Excel com Python: " . $output);
            // Fallback usando a logica antiga se o python falhar (garantia)
            try {
                if ($tipo === 'geral') return Excel::download(new AdministrativoGeralExport($filtros), $nomeArquivo . '.xlsx');
                if ($tipo === 'crimes') return Excel::download(new AdministrativoCrimesExport($filtros), $nomeArquivo . '.xlsx');
                if ($tipo === 'pessoas') return Excel::download(new AdministrativoPessoasExport($filtros), $nomeArquivo . '.xlsx');
                if ($tipo === 'apreensoes') return Excel::download(new AdministrativoApreensoesExport($filtros), $nomeArquivo . '.xlsx');
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Falha dupla Python/PHP: ' . $output . ' | ' . $e->getMessage()], 500);
            }
        }
    }

    private function exportarCsv($tipo, $filtros, $nomeArquivo)
    {
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename=' . $nomeArquivo . '.csv'];
        $callback = function () use ($tipo, $filtros) {
            $out = fopen('php://output', 'w');
            if ($tipo === 'geral') {
                $query = Administrativo::query();
                $this->aplicarFiltrosGerais($query, $filtros);
                $dados = $query->orderBy('data_cadastro', 'desc')->get();
                $dados->load('pessoas');
                fputcsv($out, ['BOE', 'Vítima', 'Autor', 'Crime', 'Cartório', 'Data']);
                foreach ($dados as $d) {
                    $vitima = $d->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
                    $autor = $d->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
                    fputcsv($out, [$d->boe, $vitima, $autor, $d->crime, $d->cartorio, $d->data_cadastro]);
                }
            } elseif ($tipo === 'crimes') {
                $campo = isset($filtros['agrupar_por']) && $filtros['agrupar_por'] === 'tipificacao' ? 'tipificacao' : 'crime';
                $query = Administrativo::query();
                $this->aplicarFiltroPeriodo($query, $filtros);
                $resultados = $query->selectRaw("{$campo} as grupo, COUNT(*) as total")->whereNotNull($campo)->where($campo, '!=', '')->groupBy('grupo')->orderBy('total', 'desc')->get();
                fputcsv($out, ['Grupo', 'Total']);
                foreach ($resultados as $r) {
                    fputcsv($out, [$r->grupo, $r->total]);
                }
            } elseif ($tipo === 'pessoas') {
                $resultados = $this->obterDadosRelatorioPessoas($filtros);
                fputcsv($out, ['Nome', 'Tipo', 'Total Ocorrências', 'Crimes Diferentes', 'Primeira Ocorrência', 'Última Ocorrência']);
                foreach ($resultados as $r) {
                    fputcsv($out, [$r->nome, $r->tipo, $r->total_ocorrencias, $r->crimes_diferentes, $r->primeira_ocorrencia, $r->ultima_ocorrencia]);
                }
            } elseif ($tipo === 'apreensoes') {
                $query = Administrativo::query();
                $this->aplicarFiltroPeriodo($query, $filtros);
                if (!empty($filtros['tipo_apreensao']))
                    $query->where('apreensao', 'LIKE', "%{$filtros['tipo_apreensao']}%");
                $dados = $query->whereNotNull('apreensao')->where('apreensao', '!=', '')->orderBy('data_cadastro', 'desc')->get();
                $dados->load('pessoas');
                fputcsv($out, ['Apreensão', 'Crime', 'Vítima', 'Autor', 'Data', 'BOE']);
                foreach ($dados as $d) {
                    $vitima = $d->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
                    $autor = $d->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
                    fputcsv($out, [$d->apreensao, $d->crime, $vitima, $autor, $d->data_cadastro, $d->boe]);
                }
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function exportarPdf($tipo, $filtros, $nomeArquivo)
    {
        
        $html = $this->gerarHtmlRelatorio($tipo, $filtros, 'Relatório Administrativo');
        return PdfService::generatePdf($html, $nomeArquivo . '.pdf');
    }

    private function aplicarFiltrosGerais($query, $filtros)
    {
        if (isset($filtros['periodo']) && $filtros['periodo'] !== 'todos') {
            $inicio = $filtros['data_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
            $fim = $filtros['data_fim'] ?? now()->format('Y-m-d');
            $query->whereBetween('data_cadastro', [$inicio, $fim]);
        }
        if (!empty($filtros['crime']))
            $query->where('crime', 'LIKE', "%{$filtros['crime']}%");
        if (!empty($filtros['cartorio']))
            $query->where('cartorio', 'LIKE', "%{$filtros['cartorio']}%");
    }

    private function aplicarFiltroPeriodo($query, $filtros)
    {
        if (isset($filtros['periodo']) && $filtros['periodo'] !== 'todos') {
            $inicio = now()->startOfMonth()->format('Y-m-d');
            $query->where('data_cadastro', '>=', $inicio);
        }
    }

    private function obterDadosRelatorioPessoas($filtros)
    {
        $query = Administrativo::query();
        $this->aplicarFiltroPeriodo($query, $filtros);
        $limite = isset($filtros['limite']) ? (int) $filtros['limite'] : 50;
        $resultados = collect();
        if (!isset($filtros['tipo']) || $filtros['tipo'] === 'vitima' || $filtros['tipo'] === 'ambos') {
            $vitimas = (clone $query)->selectRaw('vitima as nome, COUNT(*) as total_ocorrencias, COUNT(DISTINCT crime) as crimes_diferentes, MIN(data_cadastro) as primeira_ocorrencia, MAX(data_cadastro) as ultima_ocorrencia')
                ->whereNotNull('vitima')->where('vitima', '!=', '')->groupBy('vitima')->orderBy('total_ocorrencias', 'desc')->limit($limite)->get()
                ->map(function ($i) {
                    $i->tipo = 'Vítima';
                    return $i;
                });
            $resultados = $resultados->merge($vitimas);
        }
        if (!isset($filtros['tipo']) || $filtros['tipo'] === 'autor' || $filtros['tipo'] === 'ambos') {
            $autores = (clone $query)->selectRaw('autor as nome, COUNT(*) as total_ocorrencias, COUNT(DISTINCT crime) as crimes_diferentes, MIN(data_cadastro) as primeira_ocorrencia, MAX(data_cadastro) as ultima_ocorrencia')
                ->whereNotNull('autor')->where('autor', '!=', '')->groupBy('autor')->orderBy('total_ocorrencias', 'desc')->limit($limite)->get()
                ->map(function ($i) {
                    $i->tipo = 'Autor';
                    return $i;
                });
            $resultados = $resultados->merge($autores);
        }
        return $resultados->sortByDesc('total_ocorrencias')->values();
    }

    private function gerarHtmlRelatorio($tipo, $filtros, $titulo)
    {
        $html = '<html><head><meta charset="utf-8"><style>body{font-family: DejaVu Sans, sans-serif; font-size:12px} h2{margin:0 0 10px 0} table{width:100%; border-collapse:collapse} th,td{border:1px solid #ccc; padding:6px; text-align:left} th{background:#f5f5f5}</style></head><body>';
        if ($tipo === 'geral') {
            $query = Administrativo::query();
            $this->aplicarFiltrosGerais($query, $filtros);
            $dados = $query->orderBy('data_cadastro', 'desc')->get();
            $dados->load('pessoas');
            $html .= '<h2>' . $titulo . ' - Geral</h2><table><thead><tr><th>BOE</th><th>Vítima</th><th>Autor</th><th>Crime</th><th>Cartório</th><th>Data</th></tr></thead><tbody>';
            foreach ($dados as $d) {
                $vitima = $d->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
                $autor = $d->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
                $html .= '<tr><td>' . e($d->boe) . '</td><td>' . e($vitima) . '</td><td>' . e($autor) . '</td><td>' . e($d->crime) . '</td><td>' . e($d->cartorio) . '</td><td>' . e($d->data_cadastro) . '</td></tr>';
            }
        } elseif ($tipo === 'crimes') {
            $campo = isset($filtros['agrupar_por']) && $filtros['agrupar_por'] === 'tipificacao' ? 'tipificacao' : 'crime';
            $query = Administrativo::query();
            $this->aplicarFiltroPeriodo($query, $filtros);
            $resultados = $query->selectRaw("{$campo} as grupo, COUNT(*) as total")->whereNotNull($campo)->where($campo, '!=', '')->groupBy('grupo')->orderBy('total', 'desc')->get();
            $html .= '<h2>' . $titulo . ' - Crimes</h2><table><thead><tr><th>Grupo</th><th>Total</th></tr></thead><tbody>';
            foreach ($resultados as $r) {
                $html .= '<tr><td>' . e($r->grupo) . '</td><td>' . e($r->total) . '</td></tr>';
            }
        } elseif ($tipo === 'pessoas') {
            $resultados = $this->obterDadosRelatorioPessoas($filtros);
            $html .= '<h2>' . $titulo . ' - Pessoas</h2><table><thead><tr><th>Nome</th><th>Tipo</th><th>Total</th><th>Crimes Diferentes</th><th>Primeira</th><th>Última</th></tr></thead><tbody>';
            foreach ($resultados as $r) {
                $html .= '<tr><td>' . e($r->nome) . '</td><td>' . e($r->tipo) . '</td><td>' . e($r->total_ocorrencias) . '</td><td>' . e($r->crimes_diferentes) . '</td><td>' . e($r->primeira_ocorrencia) . '</td><td>' . e($r->ultima_ocorrencia) . '</td></tr>';
            }
        } elseif ($tipo === 'apreensoes') {
            $query = Administrativo::query();
            $this->aplicarFiltroPeriodo($query, $filtros);
            if (!empty($filtros['tipo_apreensao']))
                $query->where('apreensao', 'LIKE', "%{$filtros['tipo_apreensao']}%");
            $dados = $query->whereNotNull('apreensao')->where('apreensao', '!=', '')->orderBy('data_cadastro', 'desc')->get();
            $dados->load('pessoas');
            $html .= '<h2>' . $titulo . ' - Apreensões</h2><table><thead><tr><th>Apreensão</th><th>Crime</th><th>Vítima</th><th>Autor</th><th>Data</th><th>BOE</th></tr></thead><tbody>';
            foreach ($dados as $d) {
                $vitima = $d->pessoas->where('papel', 'VITIMA')->pluck('nome')->implode(', ');
                $autor = $d->pessoas->where('papel', 'AUTOR')->pluck('nome')->implode(', ');
                $html .= '<tr><td>' . e($d->apreensao) . '</td><td>' . e($d->crime) . '</td><td>' . e($vitima) . '</td><td>' . e($autor) . '</td><td>' . e($d->data_cadastro) . '</td><td>' . e($d->boe) . '</td></tr>';
            }
        }
        $html .= '</tbody></table></body></html>';
        return $html;
    }

    // ✅ RELATÓRIO DE AUDITORIA DE CHIPS (LEGADO)
    public function relatorioSemChips(Request $request)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        if (!$userId) {
            return redirect('/login');
        }

        // Recupera todos os usuários para o filtro
        $usuarios = \Illuminate\Support\Facades\DB::table('usuario')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        $query = \Illuminate\Support\Facades\DB::table('cadprincipal')
            ->leftJoin('usuario', 'cadprincipal.usuario_id', '=', 'usuario.id')
            ->where(function ($q) {
                $q->whereNotNull('cadprincipal.BOE')->where('cadprincipal.BOE', '!=', '');
            })
            // A mágica: Garantir que não existe na tabela de vínculos
            ->whereNotExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('boe_pessoas_vinculos')
                  ->whereRaw('boe_pessoas_vinculos.boe = cadprincipal.BOE');
            });

        // Filtrar por usuário, se solicitado
        if ($request->filled('usuario_id')) {
            $query->where('cadprincipal.usuario_id', $request->usuario_id);
        }

        $registros = $query->select(
                'cadprincipal.id',
                'cadprincipal.data',
                'cadprincipal.BOE as boe',
                'cadprincipal.IP as ip',
                'cadprincipal.incidencia_penal',
                'usuario.nome as responsavel',
                'cadprincipal.created_at'
            )
            ->orderBy('cadprincipal.created_at', 'desc')
            ->limit(300) // Limite de exibições para não travar
            ->get();

        return view('administrativo.relatorio_sem_chips', compact('registros', 'usuarios'));
    }
}
