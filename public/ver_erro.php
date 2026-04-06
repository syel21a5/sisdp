<?php
$files = glob('/tmp/erro_infopol_*.png');
if (!$files) {
    die("Nenhum print de erro encontrado em /tmp/");
}

// Pega o mais recente
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$latest = $files[0];
$dest = __DIR__ . '/erro_login.png';

if (copy($latest, $dest)) {
    echo "<h1>Sucesso!</h1>";
    echo "<p>O print do erro foi copiado.</p>";
    echo "<a href='erro_login.png' target='_blank' style='font-size:24px; color: blue; font-weight: bold;'>CLIQUE AQUI PARA VER A FOTO DO ERRO</a>";
    echo "<br><br><small>Arquivo original: $latest</small>";
} else {
    echo "Falha ao copiar o arquivo. Verifique permissões.";
}
?>
