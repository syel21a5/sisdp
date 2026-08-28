<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('envolvidos_fotos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_envolvido', 20)->comment('Autor, Vitima, Testemunha');
            $table->unsignedBigInteger('envolvido_id');
            $table->string('caminho_foto');
            $table->boolean('is_principal')->default(false);
            $table->timestamps();
            
            // Índices para otimizar busca
            $table->index(['tipo_envolvido', 'envolvido_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('envolvidos_fotos');
    }
};
