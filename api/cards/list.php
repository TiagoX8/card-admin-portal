<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/auth.php';

requireAuth(true);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Método não permitido.'], 405);
}

try {
    $stmt  = db()->query('SELECT id, name_en, name_pt, card_game, edition_id, edition_name, image_path, rarity, created_at, updated_at FROM cards ORDER BY created_at DESC, id DESC');
    $cards = $stmt->fetchAll();
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Erro ao listar cartas.'], 500);
}

jsonResponse(['success' => true, 'cards' => $cards]);
