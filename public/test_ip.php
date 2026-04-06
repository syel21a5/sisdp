<?php
header('Content-Type: text/plain');
echo "=== TESTE DE CONEXÃO DIRETA (CURL) ===\n\n";

$sites = [
    'Infopol' => 'https://infopol.sds.pe.gov.br/',
    'SEI' => 'https://sei.pe.gov.br/sei/',
    'Google' => 'https://www.google.com'
];

foreach ($sites as $name => $url) {
    echo "Testando $name ($url)...\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    echo "Resultado: HTTP $httpCode\n";
    if ($err) echo "Erro: $err\n";
    echo str_repeat("-", 30) . "\n";
}
?>
