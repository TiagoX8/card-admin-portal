<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';

if (isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar · Card Admin Portal</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="page-login">
  <main class="login-card" aria-labelledby="login-title">
    <h1 id="login-title" class="brand">Card Admin Portal</h1>
    <p class="muted">Acesse com seu usuário e senha para gerenciar as cartas.</p>

    <form id="login-form" class="form" novalidate>
      <div class="field">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="username" autocomplete="username" required autofocus>
      </div>

      <div class="field">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>

      <div id="login-feedback" class="feedback" role="alert" aria-live="polite" hidden></div>

      <button type="submit" id="login-submit" class="btn btn-primary btn-block">Entrar</button>
    </form>
  </main>

  <script src="assets/js/login.js"></script>
</body>
</html>
