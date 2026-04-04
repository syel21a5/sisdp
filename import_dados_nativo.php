<?php
$host = '127.0.0.1';
$port = 3308;
$db   = 'sisdp_vweb';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $sql = file_get_contents(__DIR__ . '/outros_backups/inserts_producao.sql');
    
    // Ignore BOM
    $sql = preg_replace('/^[\xef\xbb\xbf]/', '', $sql);
    
    $statements = explode("INSERT INTO", $sql);
    
    $success = 0;
    foreach ($statements as $index => $stmt) {
        $stmt = trim($stmt);
        if ($index === 0) {
            // Executar os SETs iniciais se houver
            if (!empty($stmt)) {
                 $pdo->exec($stmt);
            }
            continue;
        }
        
        if (empty($stmt) || $stmt == 'COMMIT;' || strpos($stmt, 'SET') === 0) {
            continue;
        }
        
        // Remove from the end anything like SET FOREIGN_KEY_CHECKS=1 or COMMIT;
        $stmt = preg_replace('/SET\s+FOREIGN_KEY_CHECKS.*?;/s', '', $stmt);
        $stmt = preg_replace('/COMMIT;/s', '', $stmt);
        $stmt = trim($stmt);
        
        if (empty($stmt)) continue;
        
        $query = "INSERT INTO " . $stmt;
        try {
            $pdo->exec($query);
            $success++;
        } catch (PDOException $e) {
            echo "Erro na importacao:\n";
            echo "Tabela afetada: " . substr($query, 0, 50) . "...\n";
            echo "Motivo: " . $e->getMessage() . "\n\n";
            exit(1);
        }
    }
    
    echo "Importado com sucesso! ($success blocos de INSERT)\n";

} catch (\PDOException $e) {
    echo "Erro fatal: " . $e->getMessage();
}
