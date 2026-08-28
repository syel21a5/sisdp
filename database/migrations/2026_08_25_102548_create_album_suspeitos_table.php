<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('album_suspeitos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('alcunha')->nullable();
            
            // Características
            $table->string('cor_pele')->nullable();
            $table->string('cabelo')->nullable();
            $table->string('olhos')->nullable();
            $table->string('idade_aparente')->nullable();
            $table->string('estatura')->nullable();
            $table->text('marcas_peculiares')->nullable(); // Tatuagens, cicatrizes, etc.
            
            // Arquivo
            $table->string('caminho_foto'); // Caminho armazenado em storage
            $table->text('observacoes')->nullable();
            
            $table->unsignedBigInteger('usuario_id')->nullable(); // Para saber quem cadastrou
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_suspeitos');
    }
};
