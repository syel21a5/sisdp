<?php
header('Content-Type: text/plain');
echo "=== TESTE DE SINTAXE PYTHON ===\n";
$script = realpath(__DIR__ . '/../scripts/python/baixar_boes.py');
echo "Script: $script\n";

$cmd = "python3 -m py_compile $script 2>&1";
echo "Executando: $cmd\n";
echo shell_exec($cmd) ?: "Sintaxe OK!\n";

echo "\n=== TESTE DE EXECUÇÃO MÍNIMA ===\n";
$cmd2 = "sudo /usr/local/bin/run_playwright.sh $script --help 2>&1";
echo "Executando: $cmd2\n";
echo shell_exec($cmd2);
?>
