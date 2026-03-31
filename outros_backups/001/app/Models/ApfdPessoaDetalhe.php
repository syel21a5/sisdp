<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApfdPessoaDetalhe extends Model
{
    use HasFactory;

    protected $table = 'apfd_pessoas_detalhes';

    protected $fillable = [
        'cadprincipal_id',
        'pessoa_id',
        'papel',
        'interrogatorio',
        'nota_culpa',
        'dados_complementares'
    ];
}

