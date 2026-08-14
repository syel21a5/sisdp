<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    public function carregar()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado.']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'default_delegado' => $user->default_delegado,
                'default_escrivao' => $user->default_escrivao,
                'default_delegacia' => $user->default_delegacia,
                'default_cidade' => $user->default_cidade,
                'default_policial1' => $user->default_policial1,
                'default_policial2' => $user->default_policial2,
            ]
        ]);
    }

    public function salvar(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado.']);
        }

        $user->update([
            'default_delegado' => $request->default_delegado,
            'default_escrivao' => $request->default_escrivao,
            'default_delegacia' => $request->default_delegacia,
            'default_cidade' => $request->default_cidade,
            'default_policial1' => $request->default_policial1,
            'default_policial2' => $request->default_policial2,
        ]);

        return response()->json(['success' => true, 'message' => 'Configurações salvas com sucesso.']);
    }
}
