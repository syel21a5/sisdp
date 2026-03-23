<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario; // ← DEVE SER Usuario (com U maiúsculo)
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Services\AuditService;

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

        $throttleKey = Str::transliterate(Str::lower($request->input('username')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            AuditService::log('BLOQUEIO_LOGIN', "IP bloqueado por excesso de tentativas para usuario: " . $request->username);

            return back()->withErrors([
                'username' => "Muitas tentativas de login. Por favor, tente novamente em {$seconds} segundos.",
            ])->withInput();
        }

        // Buscar usuário
        $user = Usuario::where('username', $request->username)->first();

        // Verificar se usuário existe, está ativo e senha está correta
        if ($user && $user->ativo && Hash::check($request->password, $user->password)) {
            RateLimiter::clear($throttleKey);
            Auth::login($user);
            
            AuditService::log('LOGIN_SUCESSO', "Usuario logado com sucesso.");

            // Redirecionar para a página principal
            return redirect()->intended('/');
        }

        RateLimiter::hit($throttleKey);
        
        AuditService::log('LOGIN_FALHA', "Tentativa de login invalida para usuario: " . $request->username);

        // Se falhar, voltar com erro
        return back()->withErrors([
            'username' => 'Usuário ou senha incorretos.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        AuditService::log('LOGOUT', "Usuario deslogou do sistema.");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}