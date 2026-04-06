<?php
/**
 * Diagnóstico FINAL v4 - Com fix de ulimit via resource.setrlimit
 */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);

function run($cmd) {
    echo "CMD: $cmd\n";
    $descriptorspec = [
       0 => ["pipe", "r"],
       1 => ["pipe", "w"],
       2 => ["pipe", "w"]
    ];
    $env = [
        'HOME' => '/home/www',
        'PLAYWRIGHT_BROWSERS_PATH' => '/home/www/.cache/ms-playwright',
        'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        'PYTHONUNBUFFERED' => '1',
        'PYTHONIOENCODING' => 'UTF-8',
    ];
    $process = proc_open($cmd, $descriptorspec, $pipes, null, $env);
    if (is_resource($process)) {
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        return ["out" => trim($out), "err" => trim($err), "exit" => $exit];
    }
    return ["out" => "", "err" => "proc_open falhou", "exit" => -1];
}

echo "========================================\n";
echo "  DIAGNÓSTICO v4 - COM FIX DE MEMÓRIA\n";
echo "========================================\n\n";

// 1. Limites atuais
echo "=== LIMITES ATUAIS ===\n";
$r = run("ulimit -v");
echo "Virtual Memory Limit (ulimit -v): " . $r['out'] . "\n";
$r = run("ulimit -v -H");
echo "Virtual Memory HARD Limit: " . $r['out'] . "\n";
$r = run("free -m | head -3");
echo "RAM:\n" . $r['out'] . "\n";

// 2. Teste SEM fix (deve falhar)
echo "\n=== TESTE 1: Playwright SEM fix (original) ===\n";
$py_sem_fix = <<<'PYTHON'
import json, sys
from playwright.sync_api import sync_playwright
try:
    import resource
    soft, hard = resource.getrlimit(resource.RLIMIT_AS)
    print(f"RLIMIT_AS antes: soft={soft}, hard={hard}", flush=True)
except: pass

try:
    with sync_playwright() as p:
        print('Lançando browser SEM fix...', flush=True)
        browser = p.chromium.launch(
            headless=True,
            args=['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
        )
        page = browser.new_page()
        page.goto('about:blank')
        print('SUCESSO SEM FIX!', flush=True)
        browser.close()
except Exception as e:
    print(f'FALHA SEM FIX: {e}', file=sys.stderr, flush=True)
PYTHON;

file_put_contents("/tmp/test_sem_fix.py", $py_sem_fix);
$r = run("timeout 30 python3 /tmp/test_sem_fix.py");
echo "Exit: " . $r['exit'] . "\n";
echo "OUT: " . substr($r['out'], 0, 300) . "\n";
echo "ERR: " . substr($r['err'], 0, 500) . "\n";

// 3. Teste COM fix (deve funcionar!)
echo "\n=== TESTE 2: Playwright COM fix (resource.setrlimit) ===\n";
$py_com_fix = <<<'PYTHON'
import json, sys, platform
from playwright.sync_api import sync_playwright

# === O FIX ===
if platform.system() == 'Linux':
    try:
        import resource
        soft, hard = resource.getrlimit(resource.RLIMIT_AS)
        print(f"ANTES: RLIMIT_AS soft={soft}, hard={hard}", flush=True)
        resource.setrlimit(resource.RLIMIT_AS, (resource.RLIM_INFINITY, resource.RLIM_INFINITY))
        soft2, hard2 = resource.getrlimit(resource.RLIMIT_AS)
        print(f"DEPOIS: RLIMIT_AS soft={soft2}, hard={hard2}", flush=True)
    except (ValueError, OSError) as e:
        print(f"Aviso setrlimit(INFINITY) falhou: {e}, tentando soft=hard...", flush=True)
        try:
            import resource
            soft, hard = resource.getrlimit(resource.RLIMIT_AS)
            resource.setrlimit(resource.RLIMIT_AS, (hard, hard))
            soft2, hard2 = resource.getrlimit(resource.RLIMIT_AS)
            print(f"FALLBACK: RLIMIT_AS soft={soft2}, hard={hard2}", flush=True)
        except Exception as e2:
            print(f"Fallback tambem falhou: {e2}", flush=True)
# === FIM DO FIX ===

try:
    with sync_playwright() as p:
        print('Lançando browser COM fix...', flush=True)
        browser = p.chromium.launch(
            headless=True,
            args=['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
        )
        print('Browser abriu! Criando pagina...', flush=True)
        page = browser.new_page()
        print('Navegando para example.com...', flush=True)
        page.goto('https://example.com', timeout=15000)
        title = page.title()
        print(f'SUCESSO COM FIX! Titulo: {title}', flush=True)
        browser.close()
        print('Browser fechado.', flush=True)
except Exception as e:
    print(f'FALHA COM FIX: {e}', file=sys.stderr, flush=True)
PYTHON;

file_put_contents("/tmp/test_com_fix.py", $py_com_fix);
$r = run("timeout 30 python3 /tmp/test_com_fix.py");
echo "Exit: " . $r['exit'] . "\n";
echo "OUT: " . $r['out'] . "\n";
echo "ERR: " . substr($r['err'], 0, 500) . "\n";

if (strpos($r['out'], 'SUCESSO COM FIX') !== false) {
    echo "\n✅✅✅ O FIX FUNCIONA! O baixar_boes.py já foi atualizado com esse fix.\n";
} else {
    echo "\n❌ O fix não funcionou. Verificando se o hard limit é o problema...\n";
    
    // Teste 3: Verificar se é o hard limit
    echo "\n=== TESTE 3: Verificar hard limit ===\n";
    $py_check = <<<'PYTHON'
import resource, sys
soft, hard = resource.getrlimit(resource.RLIMIT_AS)
print(f"soft={soft} hard={hard}", flush=True)
if hard != resource.RLIM_INFINITY:
    print(f"PROBLEMA: Hard limit nao eh infinito ({hard} bytes = {hard/1024/1024:.0f} MB)", flush=True)
    print("SOLUCAO: Precisa alterar o LiteSpeed config ou executar 'ulimit -v unlimited' como root", flush=True)
else:
    print("Hard limit eh infinito - o fix deveria ter funcionado", flush=True)
PYTHON;
    file_put_contents("/tmp/test_hardlimit.py", $py_check);
    $r = run("timeout 10 python3 /tmp/test_hardlimit.py");
    echo "OUT: " . $r['out'] . "\n";
}

echo "\n========================================\n";
echo "  FIM DO DIAGNÓSTICO v4\n";
echo "========================================\n";
