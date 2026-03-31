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
        Schema::table('cadprincipal', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->nullable()->after('id')->comment('Usuário responsável pelo procedimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadprincipal', function (Blueprint $table) {
            $table->dropColumn('usuario_id');
        });
    }
};
