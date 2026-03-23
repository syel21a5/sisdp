<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Registra um evento na tabela de auditoria.
     *
     * @param string $acao
     * @param string|null $detalhe
     * @return void
     */
    public static function log(string $acao, ?string $detalhe = null): void
    {
        $user = Auth::user();

        AuditLog::create([
            'usuario_id' => $user ? $user->id : null,
            'username'   => $user ? $user->username : Request::input('username'),
            'acao'       => $acao,
            'detalhe'    => $detalhe,
            'ip'         => request()->ip(),
        ]);
    }
}
