<?php

$host = '127.0.0.1';
$port = '3308';
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
     $password = 'Admin@2026';
     $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
     
     $stmt = $pdo->prepare("UPDATE usuario SET password = ? WHERE username = 'admin'");
     $stmt->execute([$hash]);
     
     echo "Password updated successfully for user 'admin'.\n";
     echo "New hash: $hash\n";
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
