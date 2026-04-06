<?php
/**
 * Script de Diagnóstico Ultra-Rápido para Playwright/Linux
 * Acesse via: https://sisdp.syel.com.br/probe.php
 */

header('Content-Type: text/plain; charset=utf-8');

function run($cmd) {
    echo "Executando: $cmd\n";
    $descriptorspec = array(
       0 => array("pipe", "r"),
       1 => array("pipe", "w"),
       2 => array("pipe", "w")
    );
    $process = proc_open($cmd, $descriptorspec, $pipes);
    if (is_resource($process)) {
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return ["out" => trim($out), "err" => trim($err)];
    }
    return ["out" => "", "err" => "Falha ao abrir processo."];
}

echo "=== DIAGNÓSTICO DO AMBIENTE ===\n";
echo "Usuário PHP: " . get_current_user() . " (UID: " . posix_getuid() . ")\n";
echo "OS: " . PHP_OS . "\n";

$python = run("python3 --version");
echo "Python: " . $python['out'] . " " . $python['err'] . "\n";

$playwright = run("python3 -m playwright --version");
echo "Playwright: " . $playwright['out'] . " " . $playwright['err'] . "\n";

$chrome_dir = "/home/www/.cache/ms-playwright/chromium_headless_shell-1208/chrome-headless-shell-linux64";
$chrome_path = "$chrome_dir/chrome-headless-shell";

echo "\n=== CHECANDO BINÁRIO DO CHROME ===\n";
if (file_exists($chrome_path)) {
    echo "Binário localizado em: $chrome_path\n";
    echo "Permissões: " . substr(sprintf('%o', fileperms($chrome_path)), -4) . "\n";
    
    echo "\n=== TESTANDO CHROME DIRETO ===\n";
    $test_chrome = run("$chrome_path --headless --disable-gpu --no-sandbox --dump-dom https://example.com");
    echo "Saída do Chrome Direto:\n";
    echo "STDOUT: " . substr($test_chrome['out'], 0, 500) . "...\n";
    echo "STDERR: " . $test_chrome['err'] . "\n";

    echo "\n=== TESTANDO DEPENDÊNCIAS (ldd) ===\n";
    $ldd = run("ldd $chrome_path");
    
    $missing = [];
    if (preg_match_all('/([a-z0-9_\-\.]+) => not found/i', $ldd['out'], $m)) {
        $missing = array_unique($m[1]);
        echo "\n⚠️ ERRO: Faltam " . count($missing) . " bibliotecas: " . implode(", ", $missing) . "\n";
    } else {
        echo "\n✅ Nenhuma biblioteca dada como 'not found' pelo ldd.\n";
    }
} else {
    echo "ERRO: Binário não encontrado em $chrome_path\n";
}


echo "\n=== TESTE DE LANÇAMENTO REAL (Playwright) ===\n";
$py_code = "from playwright.sync_api import sync_playwright;
import sys
try:
    with sync_playwright() as p:
        print('Iniciando...', flush=True)
        browser = p.chromium.launch(headless=True, args=['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'])
        print('🚀 SUCESSO NO LANÇAMENTO!', flush=True)
        browser.close()
except Exception as e:
    print(f'FALHA: {e}', file=sys.stderr, flush=True)";

$tmp_py = "/tmp/test_pw.py";
file_put_contents($tmp_py, $py_code);

// Define ambiente idêntico ao do site
$env = "export HOME=/home/www; export PLAYWRIGHT_BROWSERS_PATH=/home/www/.cache/ms-playwright; ";
$test = run("$env python3 $tmp_py");
echo "Saída STDOUT: " . $test['out'] . "\n";
echo "Saída STDERR: " . $test['err'] . "\n";

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
