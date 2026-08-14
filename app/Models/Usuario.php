<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $fillable = [
        'nome',
        'username',
        'password',
        'nivel_acesso',
        'ativo',
        'permissions',
        'default_delegado',
        'default_escrivao',
        'default_delegacia',
        'default_cidade',
        'default_policial1',
        'default_policial2',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'permissions' => 'array'
    ];

    public $timestamps = true;
}
