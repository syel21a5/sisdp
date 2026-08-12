<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ╔════════════════════════════════════════════════════════════╗
// ║  LIMPEZA AUTOMÁTICA DE CACHE DE BOEs                     ║
// ║  Executa no dia 1° de cada trimestre (Jan, Abr, Jul, Out)║
// ║  Remove arquivos de hash com mais de 90 dias (3 meses)   ║
// ╚════════════════════════════════════════════════════════════╝
Schedule::command('boe:limpar-cache --dias=90')
    ->quarterly()
    ->at('03:00')
    ->appendOutputTo(storage_path('logs/boe_cache_cleanup.log'));
