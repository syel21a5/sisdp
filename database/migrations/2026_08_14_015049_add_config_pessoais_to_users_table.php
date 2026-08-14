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
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_delegado')->nullable()->after('password');
            $table->string('default_escrivao')->nullable()->after('default_delegado');
            $table->string('default_delegacia')->nullable()->after('default_escrivao');
            $table->string('default_cidade')->nullable()->after('default_delegacia');
            $table->string('default_policial1')->nullable()->after('default_cidade');
            $table->string('default_policial2')->nullable()->after('default_policial1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'default_delegado',
                'default_escrivao',
                'default_delegacia',
                'default_cidade',
                'default_policial1',
                'default_policial2'
            ]);
        });
    }
};
