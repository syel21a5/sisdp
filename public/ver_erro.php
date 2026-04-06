<?php
$files_infopol = glob('/tmp/erro_infopol_*.png');
$files_sei = glob('/tmp/erro_sei_*.png');
$files = array_merge($files_infopol, $files_sei);

if (!$files) {
    die("<h1>Nenhum print de erro encontrado.</h1><p>Tente conectar novamente no site para gerar um novo print.</p>");
}

// Ordena por data (mais recente primeiro)
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

echo "<h1>Prints de Erro Recentes</h1>";
echo "<ul>";
foreach ($files as $f) {
    $name = basename($f);
    $dest = __DIR__ . '/' . $name;
    copy($f, $dest);
    $time = date("H:i:s", filemtime($f));
    echo "<li>[$time] <a href='$name' target='_blank' style='font-size:20px;'>Ver Foto: $name</a></li>";
}
echo "</ul>";
?>
