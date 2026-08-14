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
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('default_delegado')->nullable();
            $table->string('default_escrivao')->nullable();
            $table->string('default_delegacia')->nullable();
            $table->string('default_cidade')->nullable();
            $table->string('default_policial1')->nullable();
            $table->string('default_policial2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn([
                'default_delegado',
                'default_escrivao',
                'default_delegacia',
                'default_cidade',
                'default_policial1',
                'default_policial2',
            ]);
        });
    }
};
