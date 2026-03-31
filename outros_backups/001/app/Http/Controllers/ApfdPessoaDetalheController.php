<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApfdPessoaDetalheController extends Controller
{
    public function salvar(Request $request)
    {
        $request->validate([
            'cadprincipal_id' => 'required|integer',
            'pessoa_id' => 'required|integer',
            'papel' => 'required|string|in:AUTOR,VITIMA,TESTEMUNHA',
            'interrogatorio' => 'nullable|string',
            'nota_culpa' => 'nullable|string',
            'dados_complementares' => 'nullable'
        ]);

        $data = [
            'interrogatorio' => $request->interrogatorio,
            'nota_culpa' => $request->nota_culpa,
            'dados_complementares' => $request->dados_complementares ? json_encode($request->dados_complementares) : null,
            'updated_at' => now()
        ];

        $exists = DB::table('apfd_pessoas_detalhes')
            ->where('cadprincipal_id', $request->cadprincipal_id)
            ->where('pessoa_id', $request->pessoa_id)
            ->where('papel', $request->papel)
            ->exists();

        if ($exists) {
            DB::table('apfd_pessoas_detalhes')
                ->where('cadprincipal_id', $request->cadprincipal_id)
                ->where('pessoa_id', $request->pessoa_id)
                ->where('papel', $request->papel)
                ->update($data);
        } else {
            DB::table('apfd_pessoas_detalhes')->insert(array_merge([
                'cadprincipal_id' => $request->cadprincipal_id,
                'pessoa_id' => $request->pessoa_id,
                'papel' => $request->papel,
                'created_at' => now()
            ], $data));
        }

        return response()->json(['success' => true]);
    }

    public function buscar($cadprincipalId, $pessoaId, $papel)
    {
        $registro = DB::table('apfd_pessoas_detalhes')
            ->where('cadprincipal_id', $cadprincipalId)
            ->where('pessoa_id', $pessoaId)
            ->where('papel', $papel)
            ->first();

        if (!$registro) {
            return response()->json(['success' => false, 'message' => 'Não encontrado'], 404);
        }

        $dados = [
            'interrogatorio' => $registro->interrogatorio,
            'nota_culpa' => $registro->nota_culpa,
            'dados_complementares' => $registro->dados_complementares ? json_decode($registro->dados_complementares, true) : null
        ];

        return response()->json(['success' => true, 'data' => $dados]);
    }

    public function listarPorCadprincipal($cadprincipalId)
    {
        $rows = DB::table('apfd_pessoas_detalhes')
            ->where('cadprincipal_id', $cadprincipalId)
            ->get();

        $result = [
            'AUTOR' => [],
            'VITIMA' => [],
            'TESTEMUNHA' => []
        ];

        foreach ($rows as $r) {
            $entry = [
                'pessoa_id' => $r->pessoa_id,
                'interrogatorio' => $r->interrogatorio,
                'nota_culpa' => $r->nota_culpa,
                'dados_complementares' => $r->dados_complementares ? json_decode($r->dados_complementares, true) : null
            ];
            $result[$r->papel][] = $entry;
        }

        return response()->json(['success' => true, 'data' => $result]);
    }
}
