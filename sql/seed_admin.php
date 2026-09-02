<?php
declare(strict_types=1);

/**
 * Cria/atualiza o usuário admin com um hash gerado por password_hash().
 *
 * Uso:
 *   php sql/seed_admin.php                       -> admin / admin123
 *   php sql/seed_admin.php meuuser minhasenha    -> usuário e senha customizados
 */

require_once __DIR__ . '/../src/db.php';

$username = $argv[1] ?? 'admin';
$password = $argv[2] ?? 'admin123';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = db()->prepare(
    'INSERT INTO users (username, password_hash) VALUES (:username, :hash)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$stmt->execute([':username' => $username, ':hash' => $hash]);

echo "Usuário '{$username}' pronto. Senha: {$password}\n";
