<?php
declare(strict_types=1);

const CARD_GAMES = ['magic', 'pokemon', 'yugioh'];

/**
 * Validates and normalizes card fields from $_POST.
 * Returns [data, errors].
 */
function validateCardInput(array $input): array
{
    $data = [
        'name_en'      => trim((string) ($input['name_en'] ?? '')),
        'name_pt'      => trim((string) ($input['name_pt'] ?? '')),
        'card_game'    => trim((string) ($input['card_game'] ?? '')),
        'edition_id'   => trim((string) ($input['edition_id'] ?? '')),
        'edition_name' => trim((string) ($input['edition_name'] ?? '')),
        'rarity'       => trim((string) ($input['rarity'] ?? '')),
    ];

    $errors = [];
    if ($data['name_en'] === '') {
        $errors['name_en'] = 'Nome em inglês é obrigatório.';
    }
    if (!in_array($data['card_game'], CARD_GAMES, true)) {
        $errors['card_game'] = 'Card game inválido.';
    }
    if ($data['edition_id'] === '') {
        $errors['edition_id'] = 'Edição é obrigatória.';
    }
    if ($data['rarity'] === '') {
        $errors['rarity'] = 'Raridade é obrigatória.';
    }
    foreach (['name_en' => 255, 'name_pt' => 255, 'edition_id' => 50, 'edition_name' => 255, 'rarity' => 100] as $field => $max) {
        if (mb_strlen($data[$field]) > $max) {
            $errors[$field] = "Campo excede $max caracteres.";
        }
    }

    if ($data['name_pt'] === '') {
        $data['name_pt'] = null;
    }

    return [$data, $errors];
}

function hasUploadedImage(): bool
{
    return isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;
}

/**
 * Moves the uploaded image to the uploads dir. Returns the relative path
 * (e.g. "uploads/abc123.png") or throws InvalidArgumentException.
 */
function storeUploadedImage(): string
{
    $file = $_FILES['image'];
    $cfg  = config()['uploads'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Falha no upload da imagem.');
    }
    if ($file['size'] > $cfg['max_bytes']) {
        throw new InvalidArgumentException('Imagem excede o tamanho máximo de ' . ($cfg['max_bytes'] / 1024 / 1024) . ' MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset($cfg['allowed_mimes'][$mime])) {
        throw new InvalidArgumentException('Formato inválido. Envie JPG, PNG ou WEBP.');
    }

    if (!is_dir($cfg['dir']) && !mkdir($cfg['dir'], 0775, true)) {
        throw new RuntimeException('Não foi possível criar a pasta de uploads.');
    }

    $filename = uniqid('card_', true) . '.' . $cfg['allowed_mimes'][$mime];
    $target   = rtrim($cfg['dir'], '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Não foi possível salvar a imagem.');
    }

    return 'uploads/' . $filename;
}

function deleteImageFile(?string $imagePath): void
{
    if ($imagePath === null || $imagePath === '') {
        return;
    }
    $cfg  = config()['uploads'];
    $full = rtrim($cfg['dir'], '/') . '/' . basename($imagePath);
    if (is_file($full)) {
        @unlink($full);
    }
}

function findCard(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM cards WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $card = $stmt->fetch();
    return $card ?: null;
}
