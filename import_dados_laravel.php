<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $sql = file_get_contents(__DIR__ . '/outros_backups/inserts_producao.sql');
    Illuminate\Support\Facades\DB::unprepared($sql);
    echo "DADOS IMPORTADOS COM SUCESSO!\n";
} catch (\Exception $e) {
    echo "Erro ao importar: " . $e->getMessage() . "\n";
}
