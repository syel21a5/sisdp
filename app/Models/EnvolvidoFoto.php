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

    /**
     * URL da foto com cache-busting (?v=timestamp do arquivo)
     * Evita o navegador exibir imagem antiga/quebrada do cache.
     */
    public function url()
    {
        $path = storage_path('app/public/' . $this->caminho_foto);
        $v = file_exists($path) ? filemtime($path) : time();
        return asset('storage/' . $this->caminho_foto) . '?v=' . $v;
    }
}
