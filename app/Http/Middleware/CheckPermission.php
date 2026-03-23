<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Administradores têm acesso total por padrão
        if ($user->nivel_acesso === 'administrador') {
            return $next($request);
        }

        // Verifica se a permissão existe no array de permissões do usuário
        $permissions = $user->permissions ?? [];
        
        if (is_array($permissions) && in_array($permission, $permissions)) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Você não tem permissão para acessar este módulo (' . $permission . ').');
    }
}
