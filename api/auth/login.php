<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Método não permitido.'], 405);
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    jsonResponse(['success' => false, 'error' => 'Informe usuário e senha.'], 400);
}

try {
    $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Erro ao acessar o banco de dados.'], 500);
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['success' => false, 'error' => 'Usuário ou senha inválidos.'], 401);
}

loginUser((int) $user['id'], $user['username']);

jsonResponse(['success' => true, 'user' => ['id' => (int) $user['id'], 'username' => $user['username']]]);
