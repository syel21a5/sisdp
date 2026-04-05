<?php
// Script de comparação de schema: localhost vs backup do servidor
$backupFile = 'i:/PHP/sisdp.statsfut.com/outros_backups/backup zip servidor/backup_sisdp_vweb_2026-04-05_114109.sql';
$backupSql = file_get_contents($backupFile);

// Extrair CREATE TABLEs do backup
preg_match_all('/CREATE TABLE `(\w+)` \((.*?)\) ENGINE=/s', $backupSql, $matches, PREG_SET_ORDER);

$serverTables = [];
foreach ($matches as $m) {
    $tableName = $m[1];
    // Extrair colunas
    preg_match_all('/^\s+`(\w+)`\s+(.+?)(?:,?\s*$)/m', $m[2], $cols, PREG_SET_ORDER);
    $columns = [];
    foreach ($cols as $c) {
        $columns[$c[1]] = trim($c[2], ', ');
    }
    $serverTables[$tableName] = $columns;
}

// Conectar ao localhost
$pdo = new PDO('mysql:host=127.0.0.1;port=3308;dbname=sisdp_vweb', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$localTables = [];
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    $columns = [];
    foreach ($cols as $col) {
        $columns[$col['Field']] = $col['Type'];
    }
    $localTables[$table] = $columns;
}

echo "========================================\n";
echo "   COMPARAÇÃO DE SCHEMA\n";
echo "   Local (Laragon) vs Servidor (Backup)\n";
echo "========================================\n\n";

// Comparar tabelas
$allTables = array_unique(array_merge(array_keys($localTables), array_keys($serverTables)));
sort($allTables);

$diferencas = 0;

foreach ($allTables as $table) {
    $inLocal = isset($localTables[$table]);
    $inServer = isset($serverTables[$table]);
    
    if ($inLocal && !$inServer) {
        echo "❌ TABELA '{$table}' existe APENAS no LOCAL (falta no servidor)\n";
        $diferencas++;
        continue;
    }
    if (!$inLocal && $inServer) {
        echo "❌ TABELA '{$table}' existe APENAS no SERVIDOR (falta no local)\n";
        $diferencas++;
        continue;
    }
    
    // Comparar colunas
    $localCols = $localTables[$table];
    $serverCols = $serverTables[$table];
    
    $allCols = array_unique(array_merge(array_keys($localCols), array_keys($serverCols)));
    sort($allCols);
    
    $tableDiffs = [];
    foreach ($allCols as $col) {
        $inLocalCol = isset($localCols[$col]);
        $inServerCol = isset($serverCols[$col]);
        
        if ($inLocalCol && !$inServerCol) {
            $tableDiffs[] = "   ➕ Coluna '{$col}' existe APENAS no LOCAL → Tipo: {$localCols[$col]}";
        } elseif (!$inLocalCol && $inServerCol) {
            $tableDiffs[] = "   ➖ Coluna '{$col}' existe APENAS no SERVIDOR";
        }
    }
    
    if (!empty($tableDiffs)) {
        echo "⚠️  TABELA '{$table}' — diferenças encontradas:\n";
        foreach ($tableDiffs as $diff) {
            echo $diff . "\n";
        }
        echo "\n";
        $diferencas += count($tableDiffs);
    }
}

if ($diferencas === 0) {
    echo "✅ TUDO SINCRONIZADO! Nenhuma diferença encontrada entre as tabelas.\n";
} else {
    echo "\n🔎 Total de diferenças: {$diferencas}\n";
}
