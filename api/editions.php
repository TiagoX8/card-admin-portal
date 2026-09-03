<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';

requireAuth(true);

const EDITIONS = [
    'magic' => [
        ['id' => 'dom', 'name' => 'Dominaria'],
        ['id' => 'war', 'name' => 'War of the Spark'],
        ['id' => 'eld', 'name' => 'Throne of Eldraine'],
        ['id' => 'hob', 'name' => 'The Hobbit'],
        ['id' => 'msh', 'name' => 'Marvel Super Heroes'],
    ],
    'pokemon' => [
        ['id' => 'base1', 'name' => 'Base Set'],
        ['id' => 'swsh1', 'name' => 'Sword & Shield'],
        ['id' => 'sv1', 'name' => 'Scarlet & Violet'],
        ['id' => '30c', 'name' => '30th Celebration'],
        ['id' => 'cri', 'name' => 'Chaos Rising'],
    ],
    'yugioh' => [
        ['id' => 'lob', 'name' => 'Legend of Blue Eyes White Dragon'],
        ['id' => 'mrd', 'name' => 'Metal Raiders'],
        ['id' => 'sdy', 'name' => 'Starter Deck: Yugi'],
        ['id' => 'rotd', 'name' => 'Rise of the Duelist'],
        ['id' => 'blzd', 'name' => 'Blazing Dominion'],
    ],
];

$game = (string) ($_GET['game'] ?? '');

if (!array_key_exists($game, EDITIONS)) {
    jsonResponse(['success' => false, 'error' => 'Card game inválido. Use: magic, pokemon ou yugioh.'], 400);
}

jsonResponse(['success' => true, 'game' => $game, 'editions' => EDITIONS[$game]]);
