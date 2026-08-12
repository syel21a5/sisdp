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
        Schema::table('boe_pessoas_vinculos', function (Blueprint $table) {
            $table->string('status_aprovacao')->default('aprovado')->after('tipo_vinculo');
            $table->unsignedBigInteger('criado_por')->nullable()->after('status_aprovacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boe_pessoas_vinculos', function (Blueprint $table) {
            $table->dropColumn(['status_aprovacao', 'criado_por']);
        });
    }
};
