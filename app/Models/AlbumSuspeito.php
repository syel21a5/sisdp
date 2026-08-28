<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlbumSuspeito extends Model
{
    use HasFactory;
    
    protected $table = 'album_suspeitos';
    
    protected $fillable = [
        'nome',
        'alcunha',
        'sexo',
        'cor_pele',
        'cabelo',
        'olhos',
        'idade_aparente',
        'estatura',
        'marcas_peculiares',
        'caminho_foto',
        'observacoes',
        'usuario_id'
    ];
}
