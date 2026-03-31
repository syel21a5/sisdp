<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado e é administrador
        if (Auth::check() && Auth::user()->nivel_acesso === 'administrador') {
            return $next($request);
        }

        // Se não for administrador, redireciona com mensagem de erro
        return redirect('/')->with('error', 'Acesso restrito aos administradores.');
        
       // return redirect('/wf-geral')->with('error', 'Acesso restrito aos administradores.');
    }
}