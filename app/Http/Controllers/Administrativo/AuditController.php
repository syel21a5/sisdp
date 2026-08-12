<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query();

        // Filtro por Data
        $query->when($request->data, function ($q) use ($request) {
            return $q->whereDate('created_at', $request->data);
        });

        // Filtro por Usuário
        $query->when($request->usuario, function ($q) use ($request) {
            return $q->where('username', 'LIKE', '%' . $request->usuario . '%');
        });

        // Filtro por Ação
        $query->when($request->acao, function ($q) use ($request) {
            return $q->where('acao', $request->acao);
        });

        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        
        // Buscar lista de ações únicas para o select do filtro
        $acoes = AuditLog::select('acao')->distinct()->pluck('acao');

        return view('administrativo.auditoria', compact('logs', 'acoes'));
    }
}
