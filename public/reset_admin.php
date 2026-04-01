<?php
// Arquivo temporário para resetar senha do admin
// APAGAR APÓS USO!

// Credenciais do banco do servidor
$host = '127.0.0.1';
$db   = 'sisdp';
$user = 'sisdp';
$pass = 'jNJj6rY2dLPdXMee';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gerar hash bcrypt para a senha '123456'
    $novaSenha = password_hash('123456', PASSWORD_BCRYPT);

    // Atualizar a senha do admin
    $stmt = $pdo->prepare("UPDATE usuario SET password = ?, ativo = 1 WHERE username = 'admin'");
    $stmt->execute([$novaSenha]);

    if ($stmt->rowCount() > 0) {
        echo "<h1 style='color:green'>SUCESSO!</h1>";
        echo "<p>Senha do admin resetada para: <strong>123456</strong></p>";
        echo "<p>Hash gerado: " . $novaSenha . "</p>";
        echo "<p><strong>APAGUE ESTE ARQUIVO DO SERVIDOR IMEDIATAMENTE!</strong></p>";
        echo "<p><a href='/login'>Ir para o Login</a></p>";
    } else {
        echo "<h1 style='color:orange'>AVISO</h1>";
        echo "<p>Nenhuma linha foi alterada. Verificando se o usuario admin existe...</p>";

        $check = $pdo->query("SELECT id, username, ativo FROM usuario LIMIT 10");
        echo "<h3>Usuarios encontrados no banco:</h3>";
        echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Ativo</th></tr>";
        while ($row = $check->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['ativo']}</td></tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<h1 style='color:red'>ERRO de conexao!</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
