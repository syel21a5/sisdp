<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IntimacaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perms = $user->permissions ?? [];
        if (isset($perms['menu_lateral']) && !$perms['menu_lateral']) {
            return redirect()->route('geral')->with('error', 'Acesso ao menu lateral desabilitado para seu usuário.');
        }
        if (isset($perms['intimacao']) && !$perms['intimacao']) {
            return redirect()->route('geral')->with('error', 'Você não tem permissão para acessar Intimação.');
        }
        return view('wf_intimacao');
    }

    public function pesquisar(Request $request)
    {
        // Validação ajustada para GET
        $request->validate([
            'filtro' => 'sometimes|in:Nome,BOE',
            'termo' => 'sometimes|string|max:100'
        ]);

        $filtro = $request->filtro ?? 'Nome';
        $termo = $request->termo ?? '';

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = DB::table('cadintimacao');

        // Se o usuário não for o administrador (ID 1), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

        $registros = $query->where($filtro, 'LIKE', "%{$termo}%")
            ->orderBy('Nome', 'asc')
            ->limit(5)
            ->get(['id', 'data', 'data_comp', 'delegado', 'escrivao', 'delegacia', 'cidade', 'BOE', 'Nome', 'Tipo', 'Endereco', 'Telefone', 'dataoitiva', 'hora', 'situacao', 'observacoes']);

        return response()->json([
            'success' => true,
            'data' => $registros
        ]);
    }

    public function buscarPorBoe($boe)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = DB::table('cadintimacao')->where('BOE', $boe);

        // Se o usuário não for o administrador (ID 1), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

        $registros = $query->get(['id', 'Nome', 'Tipo']); // Precisamos dos IDs, nomes e tipos para os chips

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

            $query = DB::table('cadintimacao');

            if ($userId != 4) {
                $query->where('user_id', $userId);
            }

            $registros = $query->orderBy('id', 'desc')
                ->limit(5)
                ->get(['id', 'data', 'data_comp', 'delegado', 'escrivao', 'delegacia', 'cidade', 'BOE', 'Nome', 'Tipo', 'Endereco', 'Telefone', 'dataoitiva', 'hora', 'situacao', 'observacoes']);

            return response()->json([
                'success' => true,
                'data' => $registros
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar últimas intimações',
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

        $query = DB::table('cadintimacao')->where('id', $id);

        // Se o usuário não for o administrador (ID 1), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

        $registro = $query->select(['id', 'data', 'data_comp', 'delegado', 'escrivao', 'delegacia', 'cidade', 'BOE', 'Nome', 'Tipo', 'Endereco', 'Telefone', 'dataoitiva', 'hora', 'situacao', 'observacoes'])->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'Intimação não encontrada'
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
            'data' => 'required|date_format:d/m/Y',
            'data_comp' => 'nullable|string|max:50',
            'delegado' => 'required|string|max:50',
            'escrivao' => 'required|string|max:50',
            'delegacia' => 'required|string|max:50',
            'cidade' => 'required|string|max:100',
            'BOE' => 'required|string|max:50',
            'Nome' => 'required|string|max:100',
            'Tipo' => 'nullable|string|max:50',
            'Endereco' => 'required|string|max:200',
            'Telefone' => 'nullable|string|max:20',
            'dataoitiva' => 'nullable|date_format:d/m/Y',
            'hora' => 'nullable|date_format:H:i',
            'situacao' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string'
        ], [
            'data.required' => 'O campo Data é obrigatório',
            'data.date_format' => 'O campo Data deve estar no formato DD/MM/AAAA',
            'delegado.required' => 'O campo Delegado é obrigatório',
            'escrivao.required' => 'O campo Escrivão é obrigatório',
            'delegacia.required' => 'O campo Delegacia é obrigatório',
            'cidade.required' => 'O campo Cidade é obrigatório',
            'BOE.required' => 'O campo BOE é obrigatório',
            'Nome.required' => 'O campo Nome é obrigatório',
            'Endereco.required' => 'O campo Endereço é obrigatório',
            'dataoitiva.date_format' => 'O campo Data da Oitiva deve estar no formato DD/MM/AAAA',
            'hora.date_format' => 'O campo Hora deve estar no formato HH:MM'
        ]);

        try {
            // Converter datas para formato MySQL
            $data = null;
            if ($request->data) {
                $data = Carbon::createFromFormat('d/m/Y', $request->data)->format('Y-m-d');
            }

            $dataoitiva = null;
            if ($request->dataoitiva) {
                $dataoitiva = Carbon::createFromFormat('d/m/Y', $request->dataoitiva)->format('Y-m-d');
            }

            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $id = DB::table('cadintimacao')->insertGetId([
                'user_id' => $userId, // ID DO USUÁRIO LOGADO
                'created_by' => $userId, // ID de quem criou
                'updated_by' => $userId, // ID de quem atualizou pela última vez
                'data' => $data,
                'data_comp' => $request->data_comp,
                'delegado' => $request->delegado,
                'escrivao' => $request->escrivao,
                'delegacia' => $request->delegacia,
                'cidade' => $request->cidade,
                'BOE' => $request->BOE,
                'Nome' => $request->Nome,
                'Tipo' => $request->Tipo,
                'Endereco' => $request->Endereco,
                'Telefone' => $request->Telefone,
                'dataoitiva' => $dataoitiva,
                'hora' => $request->hora,
                'situacao' => $request->situacao ?? 'PENDENTE',
                'observacoes' => $request->observacoes,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // RESETAR TIMER DE INATIVIDADE
            $this->touchProcedure($request->BOE);

            return response()->json([
                'success' => true,
                'message' => 'Intimação cadastrada com sucesso',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar intimação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function atualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'data' => 'required|date_format:d/m/Y',
            'data_comp' => 'nullable|string|max:50',
            'delegado' => 'required|string|max:50',
            'escrivao' => 'required|string|max:50',
            'delegacia' => 'required|string|max:50',
            'cidade' => 'required|string|max:100',
            'BOE' => 'required|string|max:50',
            'Nome' => 'required|string|max:100',
            'Tipo' => 'nullable|string|max:50',
            'Endereco' => 'required|string|max:200',
            'Telefone' => 'nullable|string|max:20',
            'dataoitiva' => 'nullable|date_format:d/m/Y',
            'hora' => 'nullable|date_format:H:i',
            'situacao' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string'
        ], [
            'data.required' => 'O campo Data é obrigatório',
            'data.date_format' => 'O campo Data deve estar no formato DD/MM/AAAA',
            'delegado.required' => 'O campo Delegado é obrigatório',
            'escrivao.required' => 'O campo Escrivão é obrigatório',
            'delegacia.required' => 'O campo Delegacia é obrigatório',
            'cidade.required' => 'O campo Cidade é obrigatório',
            'BOE.required' => 'O campo BOE é obrigatório',
            'Nome.required' => 'O campo Nome é obrigatório',
            'Endereco.required' => 'O campo Endereço é obrigatório',
            'dataoitiva.date_format' => 'O campo Data da Oitiva deve estar no formato DD/MM/AAAA',
            'hora.date_format' => 'O campo Hora deve estar no formato HH:MM'
        ]);

        \Log::info('🔄 Tentativa de atualização de intimação:', [
            'id' => $id,
            'Tipo' => $request->Tipo,
            'Nome' => $request->Nome,
            'user_id' => Auth::id()
        ]);

        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Se não for administrador, verifica se a intimação pertence ao usuário
            if (Auth::id() != 4) {
                $intimacao = DB::table('cadintimacao')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$intimacao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Intimação não encontrada ou você não tem permissão para editá-la'
                    ], 404);
                }
            }

            // Converter datas para formato MySQL
            $data = null;
            if ($request->data) {
                $data = Carbon::createFromFormat('d/m/Y', $request->data)->format('Y-m-d');
            }

            $dataoitiva = null;
            if ($request->dataoitiva) {
                $dataoitiva = Carbon::createFromFormat('d/m/Y', $request->dataoitiva)->format('Y-m-d');
            }

            $afetados = DB::table('cadintimacao')
                ->where('id', $id)
                ->update([
                    'updated_by' => $userId, // ID de quem atualizou pela última vez
                    'data' => $data,
                    'data_comp' => $request->data_comp,
                    'delegado' => $request->delegado,
                    'escrivao' => $request->escrivao,
                    'delegacia' => $request->delegacia,
                    'cidade' => $request->cidade,
                    'BOE' => $request->BOE,
                    'Nome' => $request->Nome,
                    'Tipo' => $request->Tipo,
                    'Endereco' => $request->Endereco,
                    'Telefone' => $request->Telefone,
                    'dataoitiva' => $dataoitiva,
                    'hora' => $request->hora,
                    'situacao' => $request->situacao,
                    'observacoes' => $request->observacoes,
                    'updated_at' => now()
                ]);

            if ($afetados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Intimação não encontrada ou sem alterações'
                ], 404);
            }

            // RESETAR TIMER DE INATIVIDADE
            $this->touchProcedure($request->BOE);

            return response()->json([
                'success' => true,
                'message' => 'Intimação atualizada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar intimação: ' . $e->getMessage()
            ], 500);
        }
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

            // Se não for administrador, verifica se a intimação pertence ao usuário
            if (Auth::id() != 4) {
                $intimacao = DB::table('cadintimacao')
                    ->where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

                if (!$intimacao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Intimação não encontrada ou você não tem permissão para excluí-la'
                    ], 404);
                }
            }

            // Busca o BOE antes de deletar para poder dar o "touch"
            $intimacao = DB::table('cadintimacao')->where('id', $id)->first();
            $boe = $intimacao ? $intimacao->BOE : null;

            $deleted = DB::table('cadintimacao')
                ->where('id', $id)
                ->where('user_id', $userId) // GARANTIR QUE SÓ EXCLUI DO PRÓPRIO USUÁRIO
                ->delete();

            if ($deleted) {
                // RESETAR TIMER DE INATIVIDADE
                if ($boe) $this->touchProcedure($boe);

                return response()->json([
                    'success' => true,
                    'message' => 'Intimação excluída com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Intimação não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir intimação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function controlePorPeriodo(Request $request)
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

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : Carbon::today();
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : Carbon::today()->addDays(30);

        $query = DB::table('cadintimacao');

        // Se o usuário não for o administrador (ID 1), filtra por seu ID
        if ($userId != 4) {
            $query->where('user_id', $userId);
        }

        // Buscar intimações por data da oitiva
        $intimacoesQuery = $query->where(function ($query) use ($dataInicio, $dataFim) {
            $query->whereBetween('dataoitiva', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')])
                ->orWhere(function ($q) use ($dataInicio, $dataFim) {
                    $q->whereNull('dataoitiva')
                        ->whereBetween('data', [$dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')]);
                });
        })
            ->orderBy('dataoitiva', 'asc');
        $intimacoes = $intimacoesQuery->get(['id', 'data', 'data_comp', 'delegado', 'escrivao', 'delegacia', 'cidade', 'BOE', 'Nome', 'Endereco', 'Telefone', 'dataoitiva', 'hora', 'situacao', 'observacoes']);

        return response()->json([
            'success' => true,
            'data' => $intimacoes,
            'periodo' => [
                'inicio' => $dataInicio->format('d/m/Y'),
                'fim' => $dataFim->format('d/m/Y')
            ]
        ]);
    }

    public function importarBoeTexto(Request $request, \App\Services\BoeExtractorService $extractorService)
    {
        $result = $extractorService->extract($request, 'intimacao');

        if (!($result['success'] ?? false)) {
            return response()->json($result, $result['status'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'dados' => $result['dados']
        ]);
    }
}
