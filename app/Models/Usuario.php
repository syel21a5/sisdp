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
        'permissions'
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
