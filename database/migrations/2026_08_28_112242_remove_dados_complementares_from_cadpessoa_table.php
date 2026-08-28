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
        Schema::table('cadpessoa', function (Blueprint $table) {
            $table->dropColumn([
                'TipoPenal', 'Fianca', 'FiancaExt', 'FiancaPago', 
                'Parente', 'Familia', 'Advogado', 'JuizMandado', 
                'ComarcaMandado', 'Nmandado', 'DataMandado', 'Recolher', 
                'OfJuiz', 'OfPromotor', 'OfDefensor', 'OfCustodia'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadpessoa', function (Blueprint $table) {
            $table->string('TipoPenal', 100)->nullable();
            $table->decimal('Fianca', 10, 2)->nullable();
            $table->string('FiancaExt', 100)->nullable();
            $table->boolean('FiancaPago')->default(0);
            $table->string('Parente', 50)->nullable();
            $table->string('Familia', 50)->nullable();
            $table->string('Advogado', 100)->nullable();
            $table->string('JuizMandado', 100)->nullable();
            $table->string('ComarcaMandado', 100)->nullable();
            $table->string('Nmandado', 50)->nullable();
            $table->string('DataMandado', 50)->nullable();
            $table->string('Recolher', 100)->nullable();
            $table->string('OfJuiz', 100)->nullable();
            $table->string('OfPromotor', 100)->nullable();
            $table->string('OfDefensor', 100)->nullable();
            $table->string('OfCustodia', 100)->nullable();
        });
    }
};
