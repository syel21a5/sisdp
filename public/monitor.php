<?php
// Script para monitorar se o GitHub está conseguindo enviar dados de volta
$jobId = $_GET['jobId'] ?? '';
$logDir = __DIR__ . '/storage/app/public/jobs';

echo "<h3>Monitor de Conexão GitHub -> Servidor</h3>";
echo "Diretório de Logs: $logDir <br>";

if (!is_dir($logDir)) {
    echo "❌ Diretório de logs ainda não foi criado (Nenhum job rodou ainda).<br>";
} else {
    echo "✅ Diretório de logs existe.<br>";
    $files = scandir($logDir);
    echo "<h4>Jobs detectados:</h4><ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $outputFile = "$logDir/$file/output.log";
            $time = file_exists($outputFile) ? date("H:i:s", filemtime($outputFile)) : 'sem log';
            echo "<li>ID: $file - Última atualização: $time</li>";
        }
    }
    echo "</ul>";
}

echo "<h4>Últimos erros do Laravel (storage/logs/laravel.log):</h4>";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = explode("\n", file_get_contents($logFile));
    $lastLines = array_slice($lines, -15);
    echo "<pre style='background:#000; color:#0f0; padding:10px;'>" . implode("\n", $lastLines) . "</pre>";
} else {
    echo "Arquivo de log não encontrado.";
}
