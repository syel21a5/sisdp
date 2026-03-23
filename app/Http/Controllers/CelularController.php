<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\CelularExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CelularController extends Controller
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
        if (isset($perms['celular']) && !$perms['celular']) {
            return redirect()->route('geral')->with('error', 'Você não tem permissão para acessar Celulares.');
        }
        return view('wf_celular', [
            'userId' => Auth::id(),
            'isAdmin' => Auth::id() == 4
        ]);
    }

    public function pesquisar(Request $request)
    {
        // Validação ajustada para GET
        $request->validate([
            'filtro' => 'sometimes|in:pessoa,boe,processo,imei1,imei2',
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

        $query = DB::table('cadcelular')
            ->leftJoin('usuario', 'cadcelular.user_id', '=', 'usuario.id');

        $registros = $query->where("cadcelular.{$filtro}", 'LIKE', "%{$termo}%")
            ->orderBy('cadcelular.data', 'desc')
            ->get([
                'cadcelular.id', 'cadcelular.data', 'cadcelular.ip', 'cadcelular.boe', 
                'cadcelular.pessoa', 'cadcelular.telefone', 'cadcelular.imei1', 
                'cadcelular.imei2', 'cadcelular.processo', 'cadcelular.status', 
                'cadcelular.user_id', 'usuario.nome as responsavel'
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

            $query = DB::table('cadcelular')
                ->leftJoin('usuario', 'cadcelular.user_id', '=', 'usuario.id');

            // Usar ordenação por ID para evitar erro se não existir created_at
            $registros = $query->orderBy('cadcelular.id', 'desc')
                ->limit(5)
                ->get([
                    'cadcelular.id', 'cadcelular.data', 'cadcelular.ip', 'cadcelular.boe', 
                    'cadcelular.pessoa', 'cadcelular.telefone', 'cadcelular.imei1', 
                    'cadcelular.imei2', 'cadcelular.processo', 'cadcelular.status',
                    'cadcelular.user_id', 'usuario.nome as responsavel'
                ]);

            return response()->json([
                'success' => true,
                'data' => $registros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar últimos celulares',
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

        $query = DB::table('cadcelular')
            ->leftJoin('usuario', 'cadcelular.user_id', '=', 'usuario.id')
            ->where('cadcelular.id', $id)
            ->select('cadcelular.*', 'usuario.nome as responsavel');

        $registro = $query->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'Celular não encontrado'
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
            'telefone' => 'nullable|string|max:100',
            'imei1' => 'nullable|string|max:30',
            'imei2' => 'nullable|string|max:30',
            'processo' => 'nullable|string|max:30',
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

            $telefoneVal = $this->limitarString($request->telefone, 100);
            $id = DB::table('cadcelular')->insertGetId([
                'user_id' => $userId,
                'data' => $data,
                'ip' => $request->ip,
                'boe' => $request->boe,
                'pessoa' => $request->pessoa,
                'telefone' => $telefoneVal,
                'imei1' => $request->imei1,
                'imei2' => $request->imei2,
                'processo' => $request->processo,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Celular cadastrado com sucesso',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar celular: ' . $e->getMessage()
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
            'telefone' => 'nullable|string|max:100',
            'imei1' => 'nullable|string|max:30',
            'imei2' => 'nullable|string|max:30',
            'processo' => 'nullable|string|max:30',
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

            // Se não for administrador, verifica se o celular pertence ao usuário
            if (Auth::id() != 4) {
                $celular = DB::table('cadcelular')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$celular) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Celular não encontrado ou você não tem permissão para editá-lo'
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

            $queryUpdate = DB::table('cadcelular')->where('id', $id);
            if ($userId != 4) {
                $queryUpdate->where('user_id', $userId);
            }

            $telefoneVal = $this->limitarString($request->telefone, 100);
            $afetados = $queryUpdate->update([
                'data' => $data,
                'ip' => $request->ip,
                'boe' => $request->boe,
                'pessoa' => $request->pessoa,
                'telefone' => $telefoneVal,
                'imei1' => $request->imei1,
                'imei2' => $request->imei2,
                'processo' => $request->processo,
                'status' => $request->status,
                'updated_at' => now()
            ]);

            if ($afetados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Celular não encontrado ou sem alterações'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Celular atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar celular: ' . $e->getMessage()
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

            // Se não for administrador, verifica se o celular pertence ao usuário
            if (Auth::id() != 4) {
                $celular = DB::table('cadcelular')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$celular) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Celular não encontrado ou você não tem permissão para excluí-lo'
                    ], 404);
                }
            }

            $deleted = DB::table('cadcelular')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Celular excluído com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Celular não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir celular: ' . $e->getMessage()
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

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

        $query = DB::table('cadcelular')
            ->leftJoin('usuario', 'cadcelular.user_id', '=', 'usuario.id');

        // Buscar celulares por período apenas se informado
        if ($dataInicio && $dataFim) {
            $query->whereBetween('cadcelular.data', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')]);
        }

        $celulares = $query->orderBy('cadcelular.data', 'desc')
            ->get([
                'cadcelular.id', 'cadcelular.data', 'cadcelular.ip', 'cadcelular.boe', 
                'cadcelular.pessoa', 'cadcelular.telefone', 'cadcelular.imei1', 
                'cadcelular.imei2', 'cadcelular.processo', 'cadcelular.status',
                'cadcelular.user_id', 'usuario.nome as responsavel'
            ]);

        return response()->json([
            'success' => true,
            'data' => $celulares,
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
        $nomeArquivo = 'celulares_' . $dtIn . '_' . $dtFi . '.xlsx';

        return Excel::download(new CelularExport($dataInicio, $dataFim, $status), $nomeArquivo);
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

        $query = DB::table('cadcelular');
        
        if ($dataInicio && $dataFim) {
            $query->whereBetween('data', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')]);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }
        $registros = $query->orderBy('data', 'desc')
            ->get(['id', 'data', 'boe', 'processo', 'ip', 'pessoa', 'telefone', 'imei1', 'imei2', 'status']);

        $registros->transform(function ($r) {
            $r->data = $r->data ? Carbon::parse($r->data)->format('d/m/Y') : '';
            return $r;
        });

        $pdf = Pdf::loadView('exports.celular-pdf', [
            'registros'  => $registros,
            'dataInicio' => $dataInicio ? $dataInicio->format('d/m/Y') : 'Início',
            'dataFim'    => $dataFim ? $dataFim->format('d/m/Y') : 'Hoje',
            'status'     => $status,
            'geradoEm'   => now()->format('d/m/Y H:i'),
            'usuario'    => Auth::user()->name ?? 'Sistema',
        ])->setPaper('a4', 'landscape');

        $dtIn = $dataInicio ? $dataInicio->format('Ymd') : 'inicio';
        $dtFi = $dataFim ? $dataFim->format('Ymd') : 'hoje';
        $nomeArquivo = 'celulares_' . $dtIn . '_' . $dtFi . '.pdf';
        return $pdf->download($nomeArquivo);
    }
}
