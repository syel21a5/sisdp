<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeralController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $perms = $user->permissions ?? [];
        $moduleKeys = ['administrativo','apfd','intimacao','apreensao','veiculo','celular'];
        $allowed = [];
        foreach ($moduleKeys as $key) {
            if (isset($perms[$key]) && $perms[$key]) {
                $allowed[] = $key;
            }
        }
        if (count($allowed) === 1 && in_array('administrativo', $allowed)) {
            return redirect()->route('administrativo.index');
        }
        return view('wf_geral');
    }

    public function pesquisar(Request $request)
    {
        $request->validate([
            'filtro' => 'required|in:BOE,IP',
            'termo' => 'required|string|max:100'
        ]);

        $registros = DB::table('cadprincipal')
            ->where($request->filtro, 'LIKE', "%{$request->termo}%")
            ->orderBy('data', 'desc')
            ->limit(5)
            ->get(['id', 'BOE', 'IP', 'data']);

        $registros->transform(function ($item) {
            $item->data_formatada = Carbon::parse($item->data)->format('d/m/Y');
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
            'status' => 'nullable|in:Em andamento,Arquivado,Encaminhado ao MP,Concluído',
            'Apreensao' => 'nullable|string'
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
            $id = DB::table('cadprincipal')->insertGetId([
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
                'Apreensao' => $request->Apreensao,
                'created_at' => now(),
                'updated_at' => now()
            ]);

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
            'status' => 'nullable|in:Em andamento,Arquivado,Encaminhado ao MP,Concluído',
            'Apreensao' => 'nullable|string'
        ]);

        // Verifica se existe outro registro com o mesmo BOE (exceto o atual)
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

        try {
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
                    'Apreensao' => $request->Apreensao,
                    'updated_at' => now()
                ]);

            if ($afetados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro não encontrado ou sem alterações'
                ], 404);
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
}
