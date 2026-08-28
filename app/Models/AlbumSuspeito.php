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

    /**
     * URL da foto com cache-busting (?v=timestamp do arquivo)
     */
    public function url()
    {
        $path = storage_path('app/public/' . $this->caminho_foto);
        $v = file_exists($path) ? filemtime($path) : time();
        return asset('storage/' . $this->caminho_foto) . '?v=' . $v;
    }
}
