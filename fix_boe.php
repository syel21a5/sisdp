<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$celulares = DB::table('cadcelular')->get();
$countCelular = 0;
foreach($celulares as $c) {
    if(empty($c->boe)) continue;
    $novo = strtoupper($c->boe);
    // Replace letter O with digit 0
    $novo = str_replace(['O', 'o'], '0', $novo);
    // Se o valor contiver APENAS dígitos e pontuações, e no máx 1 caractere alfabético
    if(preg_match('/^[\d\-\/]+[A-Z]?[\d\-\/]+$/i', $novo) && $c->boe !== $novo) {
        DB::table('cadcelular')->where('id', $c->id)->update(['boe' => $novo]);
        $countCelular++;
    }
}

$veiculos = DB::table('cadveiculo')->get();
$countVeiculo = 0;
foreach($veiculos as $c) {
    if(empty($c->boe)) continue;
    $novo = strtoupper($c->boe);
    $novo = str_replace(['O', 'o'], '0', $novo);
    if(preg_match('/^[\d\-\/]+[A-Z]?[\d\-\/]+$/i', $novo) && $c->boe !== $novo) {
        DB::table('cadveiculo')->where('id', $c->id)->update(['boe' => $novo]);
        $countVeiculo++;
    }
}

echo "Sucesso: Corrigidos $countCelular celulares e $countVeiculo veiculos no Banco de Dados.\n";
