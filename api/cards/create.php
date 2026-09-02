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

[$data, $errors] = validateCardInput($_POST);
if ($errors) {
    jsonResponse(['success' => false, 'error' => 'Preencha os campos obrigatórios.', 'fields' => $errors], 422);
}

$imagePath = null;
if (hasUploadedImage()) {
    try {
        $imagePath = storeUploadedImage();
    } catch (InvalidArgumentException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage(), 'fields' => ['image' => $e->getMessage()]], 422);
    } catch (RuntimeException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

try {
    $stmt = db()->prepare(
        'INSERT INTO cards (name_en, name_pt, card_game, edition_id, edition_name, image_path, rarity)
         VALUES (:name_en, :name_pt, :card_game, :edition_id, :edition_name, :image_path, :rarity)'
    );
    $stmt->execute([
        ':name_en'      => $data['name_en'],
        ':name_pt'      => $data['name_pt'],
        ':card_game'    => $data['card_game'],
        ':edition_id'   => $data['edition_id'],
        ':edition_name' => $data['edition_name'],
        ':image_path'   => $imagePath,
        ':rarity'       => $data['rarity'],
    ]);
    $id = (int) db()->lastInsertId();
} catch (PDOException $e) {
    deleteImageFile($imagePath);
    jsonResponse(['success' => false, 'error' => 'Erro ao salvar a carta.'], 500);
}

jsonResponse(['success' => true, 'card' => findCard($id)], 201);
