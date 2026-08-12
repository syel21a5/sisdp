<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Autor1Controller extends Controller
{
    public function pesquisar(Request $request)
    {
        $request->validate([
            'filtro' => 'required|in:Nome,CPF,RG,Alcunha',
            'termo' => 'required|string|max:100'
        ]);

        $filtro = $request->filtro;
        $termo = $request->termo;
        $query = DB::table('cadpessoa');

        if ($filtro === 'CPF') {
            $cpfLimpo = preg_replace('/[^0-9]/', '', $termo);
            $query->where(function ($q) use ($termo, $cpfLimpo) {
                $q->where('CPF', 'LIKE', "%{$termo}%")
                  ->orWhereRaw("REGEXP_REPLACE(CPF, '[^0-9]', '') LIKE ?", ["%{$cpfLimpo}%"]);
            });
        } else {
            $query->where($filtro, 'LIKE', "%{$termo}%");
        }

        $registros = $query->orderBy('Nome', 'asc')
            ->limit(5)
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
                'message' => 'Autor não encontrado'
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
            'Endereco' => 'nullable|string|max:200',
            // Dados complementares
            'TipoPenal' => 'nullable|string|max:100',
            'Fianca' => 'nullable|numeric',
            'FiancaExt' => 'nullable|string|max:100',
            'FiancaPago' => 'nullable|in:0,1',
            'Parente' => 'nullable|string|max:50',
            'Familia' => 'nullable|string|max:50',
            'Advogado' => 'nullable|string|max:100',
            'JuizMandado' => 'nullable|string|max:100',
            'ComarcaMandado' => 'nullable|string|max:100',
            'Nmandado' => 'nullable|string|max:50',
            'DataMandado' => 'nullable|string|max:50',
            'Recolher' => 'nullable|string|max:100',
            'OfJuiz' => 'nullable|string|max:100',
            'OfPromotor' => 'nullable|string|max:100',
            'OfDefensor' => 'nullable|string|max:100',
            'OfCustodia' => 'nullable|string|max:100'
        ], [
            'Nome.required' => 'O campo Nome é obrigatório',
            'CPF.required' => 'O campo CPF é obrigatório',
            'Nome.max' => 'O Nome não pode ter mais que 100 caracteres',
            'CPF.max' => 'O CPF não pode ter mais que 15 caracteres',
            'Nascimento.date_format' => 'O campo Nascimento deve estar no formato DD/MM/AAAA',
            'Fianca.numeric' => 'O campo Fiança deve ser um valor numérico'
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

            $data = [
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
                // Dados complementares
                'TipoPenal' => $request->TipoPenal,
                'Fianca' => $request->Fianca,
                'FiancaExt' => $request->FiancaExt,
                'FiancaPago' => $request->FiancaPago ? 1 : 0,
                'Parente' => $request->Parente,
                'Familia' => $request->Familia,
                'Advogado' => $request->Advogado,
                'JuizMandado' => $request->JuizMandado,
                'ComarcaMandado' => $request->ComarcaMandado,
                'Nmandado' => $request->Nmandado,
                'DataMandado' => $request->DataMandado,
                'Recolher' => $request->Recolher,
                'OfJuiz' => $request->OfJuiz,
                'OfPromotor' => $request->OfPromotor,
                'OfDefensor' => $request->OfDefensor,
                'OfCustodia' => $request->OfCustodia,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $id = DB::table('cadpessoa')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => 'Autor cadastrado com sucesso',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar autor: ' . $e->getMessage()
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
            'Endereco' => 'nullable|string|max:200',
            // Dados complementares
            'TipoPenal' => 'nullable|string|max:100',
            'Fianca' => 'nullable|numeric',
            'FiancaExt' => 'nullable|string|max:100',
            'FiancaPago' => 'nullable|in:0,1',
            'Parente' => 'nullable|string|max:50',
            'Familia' => 'nullable|string|max:50',
            'Advogado' => 'nullable|string|max:100',
            'JuizMandado' => 'nullable|string|max:100',
            'ComarcaMandado' => 'nullable|string|max:100',
            'Nmandado' => 'nullable|string|max:50',
            'DataMandado' => 'nullable|string|max:50',
            'Recolher' => 'nullable|string|max:100',
            'OfJuiz' => 'nullable|string|max:100',
            'OfPromotor' => 'nullable|string|max:100',
            'OfDefensor' => 'nullable|string|max:100',
            'OfCustodia' => 'nullable|string|max:100'
        ], [
            'Nome.required' => 'O campo Nome é obrigatório',
            'CPF.required' => 'O campo CPF é obrigatório',
            'Nascimento.date_format' => 'O campo Nascimento deve estar no formato DD/MM/AAAA',
            'Fianca.numeric' => 'O campo Fiança deve ser um valor numérico'
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

            $data = [
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
                // Dados complementares
                'TipoPenal' => $request->TipoPenal,
                'Fianca' => $request->Fianca,
                'FiancaExt' => $request->FiancaExt,
                'FiancaPago' => $request->FiancaPago ? 1 : 0,
                'Parente' => $request->Parente,
                'Familia' => $request->Familia,
                'Advogado' => $request->Advogado,
                'JuizMandado' => $request->JuizMandado,
                'ComarcaMandado' => $request->ComarcaMandado,
                'Nmandado' => $request->Nmandado,
                'DataMandado' => $request->DataMandado,
                'Recolher' => $request->Recolher,
                'OfJuiz' => $request->OfJuiz,
                'OfPromotor' => $request->OfPromotor,
                'OfDefensor' => $request->OfDefensor,
                'OfCustodia' => $request->OfCustodia,
                'updated_at' => now()
            ];

            $afetados = DB::table('cadpessoa')
                ->where('IdCad', $id)
                ->update($data);

            if ($afetados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autor não encontrado ou sem alterações'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Autor atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar autor: ' . $e->getMessage()
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
                    'message' => 'Autor excluído com sucesso'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Autor não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir autor: ' . $e->getMessage()
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
