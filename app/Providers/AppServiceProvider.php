<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ CARREGA TODOS OS ARQUIVOS COM AUTENTICAÇÃO
        Route::middleware(['web', 'auth'])->group(function () {
            require base_path('routes/sistema.php');
            require base_path('routes/documentos_pecas.php');
            require base_path('routes/documentos_oitivas.php');
            require base_path('routes/documentos_aafai_apfd.php');
            require base_path('routes/documentos_pericias.php'); // ✅ ESTÁ AQUI!
            require base_path('routes/documentos_oficios.php');
            require base_path('routes/documentos_preliminares.php');
        });
    }
}
