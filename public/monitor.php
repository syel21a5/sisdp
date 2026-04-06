<?php
// Script para monitorar se o GitHub está conseguindo enviar dados de volta
$jobId = $_GET['jobId'] ?? '';
$baseDir = dirname(__DIR__); 
$logDir = $baseDir . '/storage/app/public/jobs';

echo "<h3>Monitor de Conexão GitHub -> Servidor</h3>";
echo "Horário do Servidor: " . date("H:i:s") . "<br>";
echo "Diretório de Logs: $logDir <br>";

$testFile = $baseDir . '/storage/logs/write_test.txt';
if (@file_put_contents($testFile, "teste " . date("H:i:s"))) {
    echo "✅ Escrita em storage/logs: OK!<br>";
} else {
    echo "❌ <b style='color:red'>ERRO DE PERMISSÃO:</b> Não consigo escrever em storage/logs. Peça ao suporte para dar permissão de escrita (775) na pasta storage, ou use o Gerenciador de Arquivos do cPanel/Painel para dar permissão 775 na pasta 'storage' e todas as subpastas.<br>";
}

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

echo "<h4>Rastreador de Emergência (public/emergency_debug.txt):</h4>";
$emergencyFile = $baseDir . '/public/emergency_debug.txt';
if (file_exists($emergencyFile)) {
    echo "<pre style='background:#f0f0f0; color:#c00; padding:10px;'>" . htmlspecialchars(file_get_contents($emergencyFile)) . "</pre>";
} else {
    echo "Rastreador ainda não registrou nada.";
}

echo "<h4>Últimos erros do Laravel (storage/logs/laravel.log):</h4>";
$logFile = $baseDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = explode("\n", file_get_contents($logFile));
    $lastLines = array_slice($lines, -15);
    echo "<pre style='background:#000; color:#0f0; padding:10px;'>" . implode("\n", $lastLines) . "</pre>";
} else {
    echo "Arquivo de log não encontrado.";
}
