<?php
header('Content-Type: text/plain');
echo "=== TESTE DE EXECUÇÃO SUDO ===\n";

$cmd = "sudo -n /usr/local/bin/run_playwright.sh -c 'import resource; print(\"SUCESSO: Limites elevados!\", resource.getrlimit(resource.RLIMIT_AS))' 2>&1";
echo "Executando: $cmd\n";

$output = shell_exec($cmd);
echo "Retorno:\n" . ($output ?: "(Nada retornado - possível bloqueio de execução)") . "\n";

echo "\n=== TESTE DE VERSÃO PYTHON ===\n";
echo "Executando via wrapper:\n";
echo shell_exec("sudo -n /usr/local/bin/run_playwright.sh --version 2>&1");
?>
