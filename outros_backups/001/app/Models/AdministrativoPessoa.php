<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministrativoPessoa extends Model
{
    use HasFactory;

    protected $table = 'administrativo_pessoas';

    protected $fillable = [
        'administrativo_id',
        'pessoa_id',
        'nome',
        'papel',
        'ordem',
        'observacao',
    ];

    public function administrativo()
    {
        return $this->belongsTo(Administrativo::class);
    }
}