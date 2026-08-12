<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoePessoaVinculo extends Model
{
    use HasFactory;

    protected $table = 'boe_pessoas_vinculos';

    protected $fillable = [
        'boe',
        'pessoa_id',
        'tipo_vinculo',
        'status_aprovacao',
        'criado_por'
    ];

    // Relacionamento com a tabela CadPessoa (legada)
    // Assumindo que não existe um Model 'Pessoa' padrão, ou se existir, ajustar.
    // O controller usava DB::table('CadPessoa'), então vou assumir que talvez não tenha model.
    // Mas se tiver, seria melhor usar. Vou deixar genérico ou tentar descobrir se existe Model.
}
