<?php
declare(strict_types=1);

/**
 * Router para o servidor embutido do PHP (php -S ... -t public public/router.php).
 * Encaminha /api/* para a pasta ../api (fora do docroot); o restante é servido normalmente.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

if (str_starts_with($path, '/api/')) {
    $apiRoot = realpath(__DIR__ . '/../api');
    $target  = realpath($apiRoot . substr($path, 4));

    if ($target !== false && str_starts_with($target, $apiRoot . DIRECTORY_SEPARATOR) && is_file($target)) {
        chdir(dirname($target));
        require $target;
        return true;
    }

    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Endpoint não encontrado.']);
    return true;
}

return false;
