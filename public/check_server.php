<?php
echo "<h3>Diagnóstico do Servidor Web</h3>";
echo "Versão do PHP: " . PHP_VERSION . "<br>";
echo "curl ativado? " . (function_exists('curl_init') ? "SIM ✅" : "NÃO ❌") . "<br>";
echo "openssl ativado? " . (extension_loaded('openssl') ? "SIM ✅" : "NÃO ❌") . "<br>";
echo "Versão mínima requerida para Laravel 11: 8.2.0<br>";

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    echo "<h4 style='color:red'>ERRO: Seu PHP está desatualizado! Atualize para 8.2+ para rodar o Laravel 11.</h4>";
}

echo "<h4>Extensões carregadas:</h4>";
echo implode(", ", get_loaded_extensions());
