<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = Session::get('lastActivityTime');
            $timeout = config('session.lifetime') * 60; // segundos

            if ($lastActivity && (time() - $lastActivity > $timeout)) {
                Auth::logout();
                Session::forget('lastActivityTime');
                return redirect('/login')->with('error', 'Sessão expirada por inatividade. Por favor, logue novamente.');
            }

            Session::put('lastActivityTime', time());
        }

        return $next($request);
    }
}
