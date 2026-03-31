<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario; // ← DEVE SER Usuario (com U maiúsculo)
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validação
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Buscar usuário
        $user = Usuario::where('username', $request->username)->first();

        // Verificar se usuário existe, está ativo e senha está correta
        if ($user && $user->ativo && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            
            // Redirecionar para a página principal
           // return redirect()->intended('/wf-geral');
            return redirect()->intended('/');
        }

        // Se falhar, voltar com erro
        return back()->withErrors([
            'username' => 'Usuário ou senha incorretos.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}