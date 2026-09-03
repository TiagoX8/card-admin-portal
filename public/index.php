<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';

header('Location: ' . (isAuthenticated() ? 'dashboard.php' : 'login.php'));
exit;
