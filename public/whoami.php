<?php
header('Content-Type: text/plain');
echo "USUÁRIO: " . posix_getpwuid(posix_geteuid())['name'] . "\n";
echo "UID: " . posix_geteuid() . "\n";
echo "LIMITE MEMÓRIA: " . ini_get('memory_limit') . "\n";
?>
