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
    $existing = findCard($id);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Erro ao acessar o banco de dados.'], 500);
}
if ($existing === null) {
    jsonResponse(['success' => false, 'error' => 'Carta não encontrada.'], 404);
}

[$data, $errors] = validateCardInput($_POST);
if ($errors) {
    jsonResponse(['success' => false, 'error' => 'Preencha os campos obrigatórios.', 'fields' => $errors], 422);
}

$imagePath = $existing['image_path'];
$newImage  = null;
if (hasUploadedImage()) {
    try {
        $newImage  = storeUploadedImage();
        $imagePath = $newImage;
    } catch (InvalidArgumentException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage(), 'fields' => ['image' => $e->getMessage()]], 422);
    } catch (RuntimeException $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

try {
    $stmt = db()->prepare(
        'UPDATE cards
            SET name_en = :name_en, name_pt = :name_pt, card_game = :card_game, edition_id = :edition_id,
                edition_name = :edition_name, image_path = :image_path, rarity = :rarity
          WHERE id = :id'
    );
    $stmt->execute([
        ':name_en'      => $data['name_en'],
        ':name_pt'      => $data['name_pt'],
        ':card_game'    => $data['card_game'],
        ':edition_id'   => $data['edition_id'],
        ':edition_name' => $data['edition_name'],
        ':image_path'   => $imagePath,
        ':rarity'       => $data['rarity'],
        ':id'           => $id,
    ]);
} catch (PDOException $e) {
    deleteImageFile($newImage);
    jsonResponse(['success' => false, 'error' => 'Erro ao atualizar a carta.'], 500);
}

if ($newImage !== null) {
    deleteImageFile($existing['image_path']);
}

jsonResponse(['success' => true, 'card' => findCard($id)]);
