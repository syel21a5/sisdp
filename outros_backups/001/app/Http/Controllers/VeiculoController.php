<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        return view('veiculo');
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

        $query = DB::table('cadveiculo');

        // Se o usuário não for o administrador (ID 4), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

        $registros = $query->where($filtro, 'LIKE', "%{$termo}%")
            ->orderBy('pessoa', 'asc')
            ->limit(5)
            ->get(['id', 'data', 'ip', 'boe', 'pessoa', 'veiculo', 'placa', 'chassi', 'sei', 'status']);

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

            $query = DB::table('cadveiculo');

            // Se o usuário não for o administrador (ID 4), filtra por seu ID
            if ($userId != 4) {
                $query->where('user_id', $userId);
            }

            // Usar ordenação por ID para evitar erro se não existir created_at
            $registros = $query->orderBy('id', 'desc')
                ->limit(5)
                ->get(['id', 'data', 'ip', 'boe', 'pessoa', 'veiculo', 'placa', 'chassi', 'sei', 'status']);

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

        $query = DB::table('cadveiculo')->where('id', $id);

        // Se o usuário não for o administrador (ID 4), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

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

            $deleted = DB::table('cadveiculo')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->delete();

            if ($deleted) {
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

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : Carbon::today()->subMonth();
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : Carbon::today();

        $query = DB::table('cadveiculo');

        // Se o usuário não for o administrador (ID 4), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

        // Buscar veículos por período
        $veiculos = $query->whereBetween('data', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
            ->orderBy('data', 'desc')
            ->get(['id', 'data', 'ip', 'boe', 'pessoa', 'veiculo', 'placa', 'chassi', 'sei', 'status']);

        return response()->json([
            'success' => true,
            'data' => $veiculos,
            'periodo' => [
                'inicio' => $dataInicio->format('d/m/Y'),
                'fim' => $dataFim->format('d/m/Y')
            ]
        ]);
    }
}
