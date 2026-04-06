<?php
echo "--- DIAGNÓSTICO INDEPENDENTE GITHUB ---\n\n";

// Tenta ler o .env manualmente para não dar erro de versão
$envContent = file_exists('.env') ? file_get_contents('.env') : '';
preg_match('/GITHUB_TOKEN=(.*)/', $envContent, $matchesToken);
preg_match('/GITHUB_REPO=(.*)/', $envContent, $matchesRepo);

$token = trim($matchesToken[1] ?? '');
$repo = trim($matchesRepo[1] ?? 'syel21a5/sisdp');

if (!$token) {
    echo "ERRO: GITHUB_TOKEN não encontrado no .env\n";
    echo "Conteúdo do .env lido: " . ($envContent ? "SIM" : "NÃO (Arquivo não encontrado)") . "\n";
    die();
}

echo "Token: " . substr($token, 0, 7) . "...\n";
echo "Repo: " . $repo . "\n\n";

function github_api($url, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'User-Agent: PHP-Diagnostic-Script'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

echo "1. Testando conexão com Repo...\n";
$res = github_api("https://api.github.com/repos/$repo", $token);
echo "Status: " . $res['code'] . "\n";
if ($res['code'] == 200) {
    echo "SUCESSO: Acesso ao repositório OK!\n";
} else {
    echo "FALHA: " . $res['body'] . "\n";
}

echo "\n2. Testando listagem de Workflows...\n";
$res = github_api("https://api.github.com/repos/$repo/actions/workflows", $token);
echo "Status: " . $res['code'] . "\n";
if ($res['code'] == 200) {
    $data = json_decode($res['body'], true);
    echo "Workflows encontrados: " . ($data['total_count'] ?? 0) . "\n";
} else {
    echo "FALHA ao listar workflows.\n";
}
echo "\n--- FIM ---\n";
