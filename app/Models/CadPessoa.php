<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CadPessoa extends Model
{
    use HasFactory;

    protected $table = 'cadpessoa';
    protected $primaryKey = 'IdCad';
    public $timestamps = false; // Assuming legacy table might not have timestamps, or user didn't specify. Safer to disable if unsure, or check schema.

    protected $fillable = [
        'Nome',
        'Alcunha',
        'Nascimento',
        'RG',
        'CPF',
        'Mae',
        'Pai',
        // Add other fields as needed based on usage in Controller or JS
    ];
}
