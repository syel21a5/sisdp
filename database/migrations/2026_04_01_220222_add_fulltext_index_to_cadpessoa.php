<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adiciona o índice FULLTEXT para pesquisas super rápidas
        DB::statement('ALTER TABLE cadpessoa ADD FULLTEXT fulltext_nome_alcunha (Nome, Alcunha)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE cadpessoa DROP INDEX fulltext_nome_alcunha');
    }
};
