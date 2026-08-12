<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\VeiculoExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class VeiculoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perms = $user->permissions ?? [];
        if (isset($perms['menu_lateral']) && !$perms['menu_lateral']) {
            return redirect()->route('geral')->with('error', 'Acesso ao menu lateral desabilitado para seu usuário.');
        }
        if (isset($perms['apreensao']) && !$perms['apreensao']) {
            return redirect()->route('geral')->with('error', 'Você não tem permissão para acessar Apreensão.');
        }
        if (isset($perms['veiculo']) && !$perms['veiculo']) {
            return redirect()->route('geral')->with('error', 'Você não tem permissão para acessar Veículos.');
        }
        return view('wf_veiculo', [
            'userId' => Auth::id(),
            'isAdmin' => Auth::id() == 4,
            'canVerificarSei' => isset($perms['verificar_sei']) ? $perms['verificar_sei'] : true,
            'verApenasProrias' => isset($perms['ver_apenas_proprias']) ? $perms['ver_apenas_proprias'] : false
        ]);
    }

    public function pesquisar(Request $request)
    {
        // Validação ajustada para GET
        $request->validate([
            'filtro' => 'sometimes|in:pessoa,boe,sei,placa,chassi',
            'termo' => 'sometimes|string|max:100'
        ]);

        $filtro = $request->filtro ?? 'pessoa';
        $termo = $request->termo ?? '';

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = DB::table('cadveiculo')
            ->leftJoin('usuario', 'cadveiculo.user_id', '=', 'usuario.id');

        $user = Auth::user();
        $perms = $user->permissions ?? [];
        $verApenas = isset($perms['ver_apenas_proprias']) ? $perms['ver_apenas_proprias'] : false;
        
        if ($verApenas && $userId != 4) {
            $query->where('cadveiculo.user_id', $userId);
        }

        $registros = $query->where("cadveiculo.{$filtro}", 'LIKE', "%{$termo}%")
            ->orderBy('cadveiculo.data', 'desc')
            ->get([
                'cadveiculo.id', 'cadveiculo.data', 'cadveiculo.ip', 'cadveiculo.boe', 
                'cadveiculo.pessoa', 'cadveiculo.veiculo', 'cadveiculo.placa', 
                'cadveiculo.chassi', 'cadveiculo.sei', 'cadveiculo.status',
                'cadveiculo.user_id', 'usuario.nome as responsavel'
            ]);

        return response()->json([
            'success' => true,
            'data' => $registros
        ]);
    }

    public function ultimos()
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $query = DB::table('cadveiculo')
                ->leftJoin('usuario', 'cadveiculo.user_id', '=', 'usuario.id');

            $user = Auth::user();
            $perms = $user->permissions ?? [];
            $verApenas = isset($perms['ver_apenas_proprias']) ? $perms['ver_apenas_proprias'] : false;
            
            if ($verApenas && $userId != 4) {
                $query->where('cadveiculo.user_id', $userId);
            }

            // Usar ordenação por ID para evitar erro se não existir created_at
            $registros = $query->orderBy('cadveiculo.id', 'desc')
                ->limit(5)
                ->get([
                    'cadveiculo.id', 'cadveiculo.data', 'cadveiculo.ip', 'cadveiculo.boe', 
                    'cadveiculo.pessoa', 'cadveiculo.veiculo', 'cadveiculo.placa', 
                    'cadveiculo.chassi', 'cadveiculo.sei', 'cadveiculo.status',
                    'cadveiculo.user_id', 'usuario.nome as responsavel'
                ]);

            return response()->json([
                'success' => true,
                'data' => $registros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar últimos veículos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function buscar($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = DB::table('cadveiculo')
            ->leftJoin('usuario', 'cadveiculo.user_id', '=', 'usuario.id')
            ->where('cadveiculo.id', $id)
            ->select('cadveiculo.*', 'usuario.nome as responsavel');

        $registro = $query->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $registro
        ]);
    }

    public function salvar(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|string',
            'ip' => 'nullable|string|max:20',
            'boe' => 'required|string|max:20',
            'pessoa' => 'nullable|string|max:100',
            'veiculo' => 'nullable|string|max:150',
            'placa' => 'nullable|string|max:30',
            'chassi' => 'nullable|string|max:30',
            'sei' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:30'
        ], [
            'data.required' => 'O campo Data é obrigatório',
            'boe.required' => 'O campo BOE é obrigatório',
            'boe.unique' => 'Já existe um registro com este BOE'
        ]);

        try {
            $data = null;
            if ($request->data) {
                $data = $this->formatarDataInput($request->data);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data inválida'
                    ], 422);
                }
            }

            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Garantir limites para evitar erro de truncamento no banco
            $veiculoVal = $this->limitarString($request->veiculo, 150);
            $placaVal = $this->limitarString($request->placa, 30);
            $chassiVal = $this->limitarString($request->chassi, 30);
            $seiVal = $this->limitarString($request->sei, 30);
            $statusVal = $this->limitarString($request->status, 30);

            $id = DB::table('cadveiculo')->insertGetId([
                'user_id' => $userId,
                'data' => $data,
                'ip' => $request->ip,
                'boe' => $request->boe,
                'pessoa' => $request->pessoa,
                'veiculo' => $veiculoVal,
                'placa' => $placaVal,
                'chassi' => $chassiVal,
                'sei' => $seiVal,
                'status' => $statusVal,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // RESETAR TIMER DE INATIVIDADE
            $this->touchProcedure($request->boe);

            return response()->json([
                'success' => true,
                'message' => 'Veículo cadastrado com sucesso',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar veículo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function atualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'data' => 'required|string',
            'ip' => 'nullable|string|max:20',
            'boe' => 'required|string|max:20',
            'pessoa' => 'nullable|string|max:100',
            'veiculo' => 'nullable|string|max:150',
            'placa' => 'nullable|string|max:30',
            'chassi' => 'nullable|string|max:30',
            'sei' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:30'
        ], [
            'data.required' => 'O campo Data é obrigatório',
            'boe.required' => 'O campo BOE é obrigatório',
            'boe.unique' => 'Já existe um registro com este BOE'
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Se não for administrador, verifica se o veículo pertence ao usuário
            if (Auth::id() != 4) {
                $veiculo = DB::table('cadveiculo')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$veiculo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veículo não encontrado ou você não tem permissão para editá-lo'
                    ], 404);
                }
            }

            $data = null;
            if ($request->data) {
                $data = $this->formatarDataInput($request->data);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data inválida'
                    ], 422);
                }
            }

            $queryUpdate = DB::table('cadveiculo')->where('id', $id);
            if ($userId != 4) {
                $queryUpdate->where('user_id', $userId);
            }

            // Garantir limites para evitar erro de truncamento no banco
            $veiculoVal = $this->limitarString($request->veiculo, 150);
            $placaVal = $this->limitarString($request->placa, 30);
            $chassiVal = $this->limitarString($request->chassi, 30);
            $seiVal = $this->limitarString($request->sei, 30);
            $statusVal = $this->limitarString($request->status, 30);

            $afetados = $queryUpdate->update([
                'data' => $data,
                'ip' => $request->ip,
                'boe' => $request->boe,
                'pessoa' => $request->pessoa,
                'veiculo' => $veiculoVal,
                'placa' => $placaVal,
                'chassi' => $chassiVal,
                'sei' => $seiVal,
                'status' => $statusVal,
                'updated_at' => now()
            ]);

            if ($afetados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veículo não encontrado ou sem alterações'
                ], 404);
            }

            // RESETAR TIMER DE INATIVIDADE
            $this->touchProcedure($request->boe);

            return response()->json([
                'success' => true,
                'message' => 'Veículo atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar veículo: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function formatarDataInput($valor)
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $valor)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('Y-m-d', $valor)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    private function limitarString($valor, $limite)
    {
        if ($valor === null)
            return null;
        $valor = (string) $valor;
        if (function_exists('mb_substr')) {
            return mb_strlen($valor) > $limite ? mb_substr($valor, 0, $limite) : $valor;
        }
        return strlen($valor) > $limite ? substr($valor, 0, $limite) : $valor;
    }

    public function excluir($id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Se não for administrador, verifica se o veículo pertence ao usuário
            if (Auth::id() != 4) {
                $veiculo = DB::table('cadveiculo')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$veiculo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veículo não encontrado ou você não tem permissão para excluí-lo'
                    ], 404);
                }
            }

            // Busca o BOE antes de deletar para poder dar o "touch"
            $veiculo = DB::table('cadveiculo')->where('id', $id)->first();
            $boe = $veiculo ? $veiculo->boe : null;

            $queryDelete = DB::table('cadveiculo')->where('id', $id);
            if ($userId != 4) {
                $queryDelete->where('user_id', $userId);
            }
            $deleted = $queryDelete->delete();

            if ($deleted) {
                // RESETAR TIMER DE INATIVIDADE
                if ($boe) $this->touchProcedure($boe);

                return response()->json([
                    'success' => true,
                    'message' => 'Veículo excluído com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Veículo não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir veículo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importarBoeTexto(Request $request, \App\Services\BoeExtractorService $extractorService)
    {
        $result = $extractorService->extract($request, 'veiculo');
        
        if (!($result['success'] ?? false)) {
            return response()->json($result, $result['status'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'dados' => [
                'boe' => $result['dados']['boe'] ?? null,
                'ip' => $result['dados']['ip'] ?? null,
                'veiculos' => $result['dados']['veiculos'] ?? []
            ]
        ]);
    }

    public function controlePorStatus(Request $request)
    {
        $request->validate([
            'data_inicio' => 'sometimes|date_format:Y-m-d',
            'data_fim' => 'sometimes|date_format:Y-m-d'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

        $query = DB::table('cadveiculo')
            ->leftJoin('usuario', 'cadveiculo.user_id', '=', 'usuario.id');

        $user = Auth::user();
        $perms = $user->permissions ?? [];
        $verApenas = isset($perms['ver_apenas_proprias']) ? $perms['ver_apenas_proprias'] : false;
        
        if ($verApenas && $userId != 4) {
            $query->where('cadveiculo.user_id', $userId);
        }

        // Buscar veículos por período apenas se informado
        if ($dataInicio && $dataFim) {
            $query->whereBetween('cadveiculo.data', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')]);
        }

        $veiculos = $query->orderBy('cadveiculo.data', 'desc')
            ->get([
                'cadveiculo.id', 'cadveiculo.data', 'cadveiculo.ip', 'cadveiculo.boe', 
                'cadveiculo.pessoa', 'cadveiculo.veiculo', 'cadveiculo.placa', 
                'cadveiculo.chassi', 'cadveiculo.sei', 'cadveiculo.status',
                'cadveiculo.user_id', 'usuario.nome as responsavel'
            ]);

        return response()->json([
            'success' => true,
            'data' => $veiculos,
            'periodo' => [
                'inicio' => $dataInicio ? $dataInicio->format('d/m/Y') : 'Início',
                'fim' => $dataFim ? $dataFim->format('d/m/Y') : 'Hoje'
            ]
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'data_inicio' => 'sometimes|nullable|date_format:Y-m-d',
            'data_fim'    => 'sometimes|nullable|date_format:Y-m-d',
            'status'      => 'sometimes|nullable|string|max:30',
        ]);

        $dataInicio = $request->data_inicio ?? null;
        $dataFim    = $request->data_fim    ?? null;
        $status     = $request->status      ?? null;

        $dtIn = $dataInicio ? str_replace('-', '', $dataInicio) : 'inicio';
        $dtFi = $dataFim ? str_replace('-', '', $dataFim) : 'hoje';
        $nomeArquivo = 'veiculos_' . $dtIn . '_' . $dtFi . '.xlsx';

        return Excel::download(new VeiculoExport($dataInicio, $dataFim, $status), $nomeArquivo);
    }

    public function exportarPdf(Request $request)
    {
        $request->validate([
            'data_inicio' => 'sometimes|nullable|date_format:Y-m-d',
            'data_fim'    => 'sometimes|nullable|date_format:Y-m-d',
            'status'      => 'sometimes|nullable|string|max:30',
        ]);

        $userId     = Auth::id();
        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim    = $request->data_fim    ? Carbon::parse($request->data_fim)    : null;
        $status     = $request->status      ?? null;

        $query = DB::table('cadveiculo');
        
        $user = Auth::user();
        $perms = $user->permissions ?? [];
        $verApenas = isset($perms['ver_apenas_proprias']) ? $perms['ver_apenas_proprias'] : false;
        
        if ($verApenas && $userId != 4) {
            $query->where('user_id', $userId);
        }

        if ($dataInicio && $dataFim) {
            $query->whereBetween('data', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')]);
        }
        
        if (!empty($status)) {
            $query->where('status', $status);
        }
        $registros = $query->orderBy('data', 'desc')
            ->get(['id', 'data', 'boe', 'sei', 'ip', 'pessoa', 'veiculo', 'placa', 'chassi', 'status']);

        $registros->transform(function ($r) {
            $r->data = $r->data ? Carbon::parse($r->data)->format('d/m/Y') : '';
            return $r;
        });

        $pdf = Pdf::loadView('exports.veiculo-pdf', [
            'registros'  => $registros,
            'dataInicio' => $dataInicio ? $dataInicio->format('d/m/Y') : 'Início',
            'dataFim'    => $dataFim ? $dataFim->format('d/m/Y') : 'Hoje',
            'status'     => $status,
            'geradoEm'   => now()->format('d/m/Y H:i'),
            'usuario'    => Auth::user()->name ?? 'Sistema',
        ])->setPaper('a4', 'landscape');

        $dtIn = $dataInicio ? $dataInicio->format('Ymd') : 'inicio';
        $dtFi = $dataFim ? $dataFim->format('Ymd') : 'hoje';
        $nomeArquivo = 'veiculos_' . $dtIn . '_' . $dtFi . '.pdf';
        return $pdf->download($nomeArquivo);
    }
}
