<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OutrosController extends Controller
{
    public function index()
    {
        // Verifica permissões (mesma lógica do InicioController)
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('apreensao.outros');
    }

    public function pesquisar(Request $request)
    {
        $request->validate([
            'filtro' => 'required|in:Nome,CPF,RG,Alcunha',
            'termo' => 'required|string|max:100'
        ]);

        $query = DB::table('cadpessoa');
        $termo = $request->termo;

        if ($request->filtro === 'CPF') {
            $cpfLimpo = preg_replace('/[^\d]/', '', $termo);
            $query->where(function ($q) use ($termo, $cpfLimpo) {
                $q->where('CPF', 'LIKE', "%{$termo}%")
                  ->orWhereRaw("REGEXP_REPLACE(CPF, '[^0-9]', '') LIKE ?", ["%{$cpfLimpo}%"]);
            });
        } else {
            $query->where($request->filtro, 'LIKE', "%{$termo}%");
        }

        $registros = $query->orderBy('Nome', 'asc')
            ->limit(10)
            ->get(['IdCad', 'Nome', 'CPF', 'RG', 'Alcunha', 'Nascimento']);

        return response()->json(['success' => true, 'data' => $registros]);
    }

    public function ultimos()
    {
        $registros = DB::table('cadpessoa')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['IdCad', 'Nome', 'CPF', 'RG', 'Alcunha', 'Nascimento']);

        return response()->json([
            'success' => true,
            'data' => $registros
        ]);
    }

    public function buscar($id)
    {
        $registro = DB::table('cadpessoa')->where('IdCad', $id)->first();

        if (!$registro) {
            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado'
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
            'Nome' => 'required|string|max:100',
            'CPF' => 'required|string|max:15',
            'Nascimento' => 'nullable|date_format:d/m/Y',
            'RG' => 'nullable|string|max:50',
            'Alcunha' => 'nullable|string|max:100',
            'EstCivil' => 'nullable|string|max:50',
            'Naturalidade' => 'nullable|string|max:50',
            'Profissao' => 'nullable|string|max:50',
            'Instrucao' => 'nullable|string|max:50',
            'Telefone' => 'nullable|string|max:20',
            'Mae' => 'nullable|string|max:100',
            'Pai' => 'nullable|string|max:100',
            'Endereco' => 'nullable|string|max:200'
        ], [
            'Nome.required' => 'O campo Nome é obrigatório',
            'CPF.required' => 'O campo CPF é obrigatório',
            'Nome.max' => 'O Nome não pode ter mais que 100 caracteres',
            'CPF.max' => 'O CPF não pode ter mais que 15 caracteres',
            'Nascimento.date_format' => 'O campo Nascimento deve estar no formato DD/MM/AAAA'
        ]);

        if ($request->CPF !== '000.000.000-00') {
            $cpfExistente = DB::table('cadpessoa')
                ->where('CPF', $request->CPF)
                ->exists();

            if ($cpfExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este CPF já está cadastrado no sistema.'
                ], 422);
            }

            if (!$this->validarCPF($request->CPF)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CPF inválido.'
                ], 422);
            }
        }

        try {
            // Converter data para formato MySQL
            $nascimento = null;
            if ($request->Nascimento) {
                $nascimento = Carbon::createFromFormat('d/m/Y', $request->Nascimento)->format('Y-m-d');
            }

            $instrucaoPermitida = in_array($request->Instrucao, [
                'Fundamental Completo',
                'Fundamental Incompleto',
                'Médio Completo',
                'Médio Incompleto',
                'Superior Completo',
                'Superior Incompleto',
                'Pós-graduação',
                'Analfabeto'
            ]) ? $request->Instrucao : null;

            $id = DB::table('cadpessoa')->insertGetId([
                'Nome' => $request->Nome,
                'Alcunha' => $request->Alcunha,
                'Nascimento' => $nascimento,
                'EstCivil' => $request->EstCivil,
                'Naturalidade' => $request->Naturalidade,
                'RG' => $request->RG,
                'CPF' => $request->CPF,
                'Profissao' => $request->Profissao,
                'Instrucao' => $instrucaoPermitida,
                'Telefone' => $request->Telefone,
                'Mae' => $request->Mae,
                'Pai' => $request->Pai,
                'Endereco' => $request->Endereco,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registro cadastrado com sucesso',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function atualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'Nome' => 'required|string|max:100',
            'CPF' => 'required|string|max:15',
            'Nascimento' => 'nullable|date_format:d/m/Y',
            'RG' => 'nullable|string|max:50',
            'Alcunha' => 'nullable|string|max:100',
            'EstCivil' => 'nullable|string|max:50',
            'Naturalidade' => 'nullable|string|max:50',
            'Profissao' => 'nullable|string|max:50',
            'Instrucao' => 'nullable|string|max:50',
            'Telefone' => 'nullable|string|max:20',
            'Mae' => 'nullable|string|max:100',
            'Pai' => 'nullable|string|max:100',
            'Endereco' => 'nullable|string|max:200'
        ], [
            'Nome.required' => 'O campo Nome é obrigatório',
            'CPF.required' => 'O campo CPF é obrigatório',
            'Nascimento.date_format' => 'O campo Nascimento deve estar no formato DD/MM/AAAA'
        ]);

        if ($request->CPF !== '000.000.000-00') {
            $cpfExistente = DB::table('cadpessoa')
                ->where('CPF', $request->CPF)
                ->where('IdCad', '!=', $id)
                ->exists();

            if ($cpfExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este CPF já está cadastrado em outro registro.'
                ], 422);
            }

            if (!$this->validarCPF($request->CPF)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CPF inválido.'
                ], 422);
            }
        }

        try {
            // Converter data para formato MySQL
            $nascimento = null;
            if ($request->Nascimento) {
                $nascimento = Carbon::createFromFormat('d/m/Y', $request->Nascimento)->format('Y-m-d');
            }

            $instrucaoPermitida = in_array($request->Instrucao, [
                'Fundamental Completo',
                'Fundamental Incompleto',
                'Médio Completo',
                'Médio Incompleto',
                'Superior Completo',
                'Superior Incompleto',
                'Pós-graduação',
                'Analfabeto'
            ]) ? $request->Instrucao : null;

            $afetados = DB::table('cadpessoa')
                ->where('IdCad', $id)
                ->update([
                    'Nome' => $request->Nome,
                    'Alcunha' => $request->Alcunha,
                    'Nascimento' => $nascimento,
                    'EstCivil' => $request->EstCivil,
                    'Naturalidade' => $request->Naturalidade,
                    'RG' => $request->RG,
                    'CPF' => $request->CPF,
                    'Profissao' => $request->Profissao,
                    'Instrucao' => $instrucaoPermitida,
                    'Telefone' => $request->Telefone,
                    'Mae' => $request->Mae,
                    'Pai' => $request->Pai,
                    'Endereco' => $request->Endereco,
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
            $deleted = DB::table('cadpessoa')
                ->where('IdCad', $id)
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

    private function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        if (strlen($cpf) != 11) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}
