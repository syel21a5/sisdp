<?php

use Illuminate\Support\Facades\Artisan;

// Carrega o autoloader do Composer
require __DIR__ . '/../vendor/autoload.php';

// Inicializa a aplicação Laravel (bootstrap)
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Cria o Kernel HTTP para que possamos usar o Artisan
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Lida com a requisição apenas para "bootar" a aplicação corretamente
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Limpando Caches do Laravel...</h1>";

try {
    // Limpa cache de rotas
    Artisan::call('route:clear');
    echo "<p>✅ Rotas limpas: <pre>" . Artisan::output() . "</pre></p>";

    // Limpa cache de configuração
    Artisan::call('config:clear');
    echo "<p>✅ Configurações limpas: <pre>" . Artisan::output() . "</pre></p>";

    // Limpa cache da aplicação
    Artisan::call('cache:clear');
    echo "<p>✅ Cache da aplicação limpo: <pre>" . Artisan::output() . "</pre></p>";

    // Limpa cache de views (opcional, mas bom)
    Artisan::call('view:clear');
    echo "<p>✅ Views limpas: <pre>" . Artisan::output() . "</pre></p>";

} catch (\Exception $e) {
    echo "<p style='color:red'>❌ Erro ao limpar cache: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p>Agora tente acessar o sistema novamente.</p>";
