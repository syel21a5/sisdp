<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvolvidoFoto extends Model
{
    protected $table = 'envolvidos_fotos';
    
    protected $fillable = [
        'tipo_envolvido',
        'envolvido_id',
        'caminho_foto',
        'is_principal'
    ];
}
