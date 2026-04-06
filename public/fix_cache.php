<?php
// Script para limpar o cache do Laravel via Navegador (Contornando o erro do terminal)

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<h3>Limpando Caches do Laravel...</h3>";
    
    $kernel->call('config:clear');
    echo "✅ Cache de Configuração: Limpo!<br>";
    
    $kernel->call('cache:clear');
    echo "✅ Cache de Dados: Limpo!<br>";
    
    $kernel->call('view:clear');
    echo "✅ Cache de Views: Limpo!<br>";
    
    $kernel->call('route:clear');
    echo "✅ Cache de Rotas: Limpo!<br>";

    echo "<br><b style='color:green'>TUDO PRONTO!</b> Agora o site deve ler o seu GITHUB_TOKEN corretamente.";
    echo "<br><br><a href='/infopol/sincronizar'>Ir para o Infopol</a>";

} catch (Exception $e) {
    echo "<b style='color:red'>ERRO ao limpar cache:</b> " . $e->getMessage();
}
