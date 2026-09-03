<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/cards.php';

requireAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Método não permitido.'], 405);
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    jsonResponse(['success' => false, 'error' => 'ID inválido.'], 400);
}

try {
    $card = findCard($id);
    if ($card === null) {
        jsonResponse(['success' => false, 'error' => 'Carta não encontrada.'], 404);
    }
    $stmt = db()->prepare('DELETE FROM cards WHERE id = :id');
    $stmt->execute([':id' => $id]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Erro ao excluir a carta.'], 500);
}

deleteImageFile($card['image_path']);

jsonResponse(['success' => true, 'id' => $id]);
