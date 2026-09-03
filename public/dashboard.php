<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';

requireAuth();

$username = htmlspecialchars((string) ($_SESSION['username'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cartas · Card Admin Portal</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <header class="topbar">
    <div class="container topbar-inner">
      <h1 class="brand">Card Admin Portal</h1>
      <div class="topbar-actions">
        <span class="muted">Olá, <strong><?= $username ?></strong></span>
        <button type="button" id="logout-btn" class="btn btn-ghost">Sair</button>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="toolbar">
      <div>
        <h2>Cartas cadastradas</h2>
        <p id="cards-count" class="muted"></p>
      </div>
      <button type="button" id="new-card-btn" class="btn btn-primary btn-lg">+ Incluir Carta</button>
    </section>

    <div id="global-feedback" class="feedback" role="status" aria-live="polite" hidden></div>

    <section class="card-panel">
      <div id="cards-loading" class="state-box">Carregando cartas...</div>
      <div id="cards-empty" class="state-box" hidden>
        Nenhuma carta cadastrada ainda. Clique em <strong>Incluir Carta</strong> para começar.
      </div>
      <div class="table-wrap">
        <table id="cards-table" class="table" hidden>
          <thead>
            <tr>
              <th scope="col">Imagem</th>
              <th scope="col">Nome (EN)</th>
              <th scope="col">Nome (PT)</th>
              <th scope="col">Card Game</th>
              <th scope="col">Edição</th>
              <th scope="col">Raridade</th>
              <th scope="col" class="col-actions">Ações</th>
            </tr>
          </thead>
          <tbody id="cards-body"></tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- Modal do formulário de carta -->
  <div id="card-modal" class="modal" hidden>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="card-form-title">
      <div class="modal-header">
        <h2 id="card-form-title">Incluir Carta</h2>
        <button type="button" class="btn-icon" aria-label="Fechar" data-close-modal>&times;</button>
      </div>

      <form id="card-form" class="form" novalidate enctype="multipart/form-data">
        <input type="hidden" name="id" id="card-id">

        <div class="field">
          <label for="name_en">Nome da Carta (inglês) <span class="required">*</span></label>
          <input type="text" id="name_en" name="name_en" required maxlength="255" placeholder="Ex.: Black Lotus">
          <small class="field-error" data-error-for="name_en"></small>
        </div>

        <div class="field">
          <label for="name_pt">Nome em português <span class="optional">(opcional)</span></label>
          <input type="text" id="name_pt" name="name_pt" maxlength="255" placeholder="Ex.: Lótus Negro">
        </div>

        <div class="field-row">
          <div class="field">
            <label for="card_game">Card Game <span class="required">*</span></label>
            <select id="card_game" name="card_game" required>
              <option value="">Selecione o card game</option>
              <option value="magic">Magic: The Gathering</option>
              <option value="pokemon">Pokémon</option>
              <option value="yugioh">Yu-Gi-Oh!</option>
            </select>
            <small class="field-error" data-error-for="card_game"></small>
          </div>

          <div class="field">
            <label for="edition_id">Edição <span class="required">*</span></label>
            <select id="edition_id" name="edition_id" required disabled aria-describedby="edition-hint">
              <option value="">Selecione o card game primeiro</option>
            </select>
            <small id="edition-hint" class="field-hint">A lista de edições depende do card game escolhido.</small>
            <small class="field-error" data-error-for="edition_id"></small>
          </div>
        </div>

        <div class="field">
          <label for="rarity">Raridade da Carta <span class="required">*</span></label>
          <input type="text" id="rarity" name="rarity" required maxlength="100" list="rarity-options" placeholder="Ex.: Rara, Comum, Mítica">
          <datalist id="rarity-options">
            <option value="Comum"></option>
            <option value="Incomum"></option>
            <option value="Rara"></option>
            <option value="Mítica"></option>
            <option value="Ultra Rara"></option>
            <option value="Secreta"></option>
          </datalist>
          <small class="field-error" data-error-for="rarity"></small>
        </div>

        <div class="field">
          <label for="image">Imagem da Carta <span class="optional">(JPG, PNG ou WEBP, até 5 MB)</span></label>
          <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
          <div id="image-preview-wrap" class="image-preview" hidden>
            <img id="image-preview" alt="Pré-visualização da imagem da carta">
            <span id="image-preview-label" class="muted"></span>
          </div>
          <small class="field-error" data-error-for="image"></small>
        </div>

        <div id="form-feedback" class="feedback" role="alert" aria-live="polite" hidden></div>

        <div class="form-actions">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancelar</button>
          <button type="submit" id="card-submit" class="btn btn-primary btn-lg">Salvar carta</button>
        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/card-form.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
