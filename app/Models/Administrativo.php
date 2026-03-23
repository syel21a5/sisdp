<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrativo extends Model
{
    use HasFactory;

    protected $table = 'administrativo';

    protected $fillable = [
        'user_id',
        'data_cadastro',
        'boe',
        'ip',
        'crime',
        'tipificacao',
        'apreensao',
        'cartorio'
    ];

    protected $dates = [
        'data_cadastro',
        'created_at',
        'updated_at'
    ];

    public function pessoas()
    {
        return $this->hasMany(AdministrativoPessoa::class);
    }
}
