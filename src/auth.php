<?php
declare(strict_types=1);

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']);
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Pages: redirect to login. APIs: 401 JSON.
 */
function requireAuth(bool $isApi = false): void
{
    startSession();
    if (isAuthenticated()) {
        return;
    }
    if ($isApi) {
        jsonResponse(['success' => false, 'error' => 'Não autenticado.'], 401);
    }
    header('Location: login.php');
    exit;
}

function loginUser(int $id, string $username): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']  = $id;
    $_SESSION['username'] = $username;
}

function logoutUser(): void
{
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
