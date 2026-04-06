<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

// Carrega o framework manualmente para ter acesso ao .env e helpers
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DIAGNÓSTICO GITHUB ACTIONS ---\n\n";

$token = env('GITHUB_TOKEN');
$repo = env('GITHUB_REPO', 'syel21a5/sisdp');

echo "Token configurado? " . ($token ? "SIM (Começa com " . substr($token, 0, 7) . "...)" : "NÃO") . "\n";
echo "Repositorio: " . $repo . "\n\n";

if (!$token) {
    die("ERRO: GITHUB_TOKEN não encontrado no seu arquivo .env!\n");
}

echo "1. Testando conexão com a API do GitHub...\n";

$response = Http::withToken($token)
    ->get("https://api.github.com/repos/{$repo}");

if ($response->successful()) {
    echo "SUCESSO: O Token tem acesso ao repositório!\n";
} else {
    echo "FALHA: O GitHub recusou o acesso. Detalhes: " . $response->body() . "\n";
    die();
}

echo "\n2. Verificando Workflows disponíveis...\n";

$wfResponse = Http::withToken($token)
    ->get("https://api.github.com/repos/{$repo}/actions/workflows");

if ($wfResponse->successful()) {
    $data = $wfResponse->json();
    echo "Workflows encontrados: " . ($data['total_count'] ?? 0) . "\n";
    foreach ($data['workflows'] ?? [] as $wf) {
        echo "- " . $wf['path'] . " (" . $wf['state'] . ")\n";
    }
} else {
    echo "FALHA ao carregar workflows: " . $wfResponse->body() . "\n";
}

echo "\n--- FIM DO DIAGNÓSTICO ---\n";
