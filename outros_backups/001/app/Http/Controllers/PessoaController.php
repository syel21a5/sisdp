<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CadPessoa;

class PessoaController extends Controller
{
    /**
     * Busca pessoas no banco de dados com base em um termo de pesquisa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->input('term');

        if (empty($term)) {
            return response()->json([]);
        }

        // Limpa o termo se for busca por CPF (remove pontos e traços)
        $cleanTerm = preg_replace('/[^\d]/', '', $term);

        // Usa Query Builder com nome de tabela compatível com HostGator (case-sensitive)
        $query = DB::table('cadpessoa');

        // Se o termo limpo tiver números, tenta buscar por CPF primeiro
        if (!empty($cleanTerm) && strlen($cleanTerm) >= 3) {
            $query->where(function ($q) use ($term, $cleanTerm) {
                // Busca por CPF com formatação (ex: usuário digitou "111.111")
                $q->where('CPF', 'LIKE', '%' . $term . '%')
                  // Busca por CPF sem formatação: normaliza ambos os lados para comparar só dígitos
                  ->orWhereRaw("REGEXP_REPLACE(CPF, '[^0-9]', '') LIKE ?", ['%' . $cleanTerm . '%'])
                  // Busca por Nome
                  ->orWhere('Nome', 'LIKE', '%' . $term . '%');
            });
        } else {
            // Busca apenas por Nome
            $query->where('Nome', 'LIKE', '%' . $term . '%');
        }

        $pessoas = $query->select('IdCad as id', 'Nome as nome', 'CPF as cpf', 'Mae as mae', 'Nascimento as nascimento')
            ->limit(10)
            ->get();

        return response()->json($pessoas);
    }

    /**
     * Cria uma nova pessoa no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:100', // Adjusted to match DB limit
            'alcunha' => 'nullable|string|max:100', // Adjusted to match DB limit
            'nascimento' => 'nullable|date',
        ]);

        try {
            // Map inputs to DB columns (TitleCase)
            $dbData = [
                'Nome' => $validatedData['nome'],
                'Alcunha' => $validatedData['alcunha'] ?? null,
                'Nascimento' => $validatedData['nascimento'] ?? null,
                // Add default values/other fields if necessary
                'created_at' => now(),
                'updated_at' => now()
            ];

            $pessoa = CadPessoa::create($dbData);

            // Return with lowercase keys if your frontend expects them, or just the model
            return response()->json($pessoa, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao salvar a pessoa: ' . $e->getMessage()], 500);
        }
    }
}
