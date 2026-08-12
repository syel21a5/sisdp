<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DocumentoSessionController extends Controller
{
    /**
     * Salva os dados do documento temporariamente no cache e retorna um UUID (Hash Curto).
     */
    public function salvarDadosSessao(Request $request)
    {
        try {
            $dados = $request->except('_token');
            
            // Gerar um identificador único para essa sessão
            $uuid = (string) Str::uuid();
            
            // O cache dura 3 horas, tempo suficiente para a aba ficar aberta
            Cache::put('doc_sessao_' . $uuid, $dados, now()->addHours(3));
            
            return response()->json([
                'success' => true,
                'uuid' => $uuid,
                'message' => 'Sessão temporária do documento gerada com sucesso.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar os dados do documento: ' . $e->getMessage()
            ], 500);
        }
    }
}
