/**
 * Lógica geral do dashboard: lista de cartas, excluir, logout.
 */
(function () {
  'use strict';

  var GAME_LABELS = {
    magic: 'Magic: The Gathering',
    pokemon: 'Pokémon',
    yugioh: 'Yu-Gi-Oh!'
  };

  var table = document.getElementById('cards-table');
  var body = document.getElementById('cards-body');
  var loading = document.getElementById('cards-loading');
  var empty = document.getElementById('cards-empty');
  var count = document.getElementById('cards-count');
  var feedback = document.getElementById('global-feedback');
  var newBtn = document.getElementById('new-card-btn');
  var logoutBtn = document.getElementById('logout-btn');

  var cards = [];
  var feedbackTimer = null;

  function showFeedback(message, type) {
    clearTimeout(feedbackTimer);
    feedback.textContent = message;
    feedback.className = 'feedback feedback-' + type;
    feedback.hidden = false;
    if (type === 'success') {
      feedbackTimer = setTimeout(function () { feedback.hidden = true; }, 4000);
    }
  }

  function handleUnauthorized(status) {
    if (status === 401) {
      window.location.href = 'login.php';
      return true;
    }
    return false;
  }

  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) {
      node.className = className;
    }
    if (text !== undefined && text !== null) {
      node.textContent = text;
    }
    return node;
  }

  function renderRow(card) {
    var tr = el('tr');
    tr.dataset.id = card.id;

    var tdImg = el('td', 'col-image');
    if (card.image_path) {
      var img = el('img', 'thumb');
      img.src = card.image_path;
      img.alt = 'Imagem de ' + card.name_en;
      img.loading = 'lazy';
      tdImg.appendChild(img);
    } else {
      tdImg.appendChild(el('span', 'thumb thumb-empty', 'Sem imagem'));
    }
    tr.appendChild(tdImg);

    tr.appendChild(el('td', 'col-name', card.name_en));
    tr.appendChild(el('td', 'muted', card.name_pt || '—'));
    tr.appendChild(el('td', null, GAME_LABELS[card.card_game] || card.card_game));
    tr.appendChild(el('td', null, card.edition_name || card.edition_id));

    var tdRarity = el('td');
    tdRarity.appendChild(el('span', 'badge', card.rarity));
    tr.appendChild(tdRarity);

    var tdActions = el('td', 'col-actions');
    var editBtn = el('button', 'btn btn-sm', 'Editar');
    editBtn.type = 'button';
    editBtn.addEventListener('click', function () { window.CardForm.open(card); });
    var delBtn = el('button', 'btn btn-sm btn-danger', 'Excluir');
    delBtn.type = 'button';
    delBtn.addEventListener('click', function () { deleteCard(card, delBtn); });
    tdActions.appendChild(editBtn);
    tdActions.appendChild(delBtn);
    tr.appendChild(tdActions);

    return tr;
  }

  function render() {
    body.innerHTML = '';
    loading.hidden = true;

    if (cards.length === 0) {
      table.hidden = true;
      empty.hidden = false;
      count.textContent = '';
      return;
    }

    empty.hidden = true;
    table.hidden = false;
    count.textContent = cards.length + (cards.length === 1 ? ' carta cadastrada' : ' cartas cadastradas');
    cards.forEach(function (card) { body.appendChild(renderRow(card)); });
  }

  function loadCards() {
    loading.hidden = false;
    table.hidden = true;
    empty.hidden = true;

    fetch('api/cards/list.php', { credentials: 'same-origin' })
      .then(function (res) {
        if (handleUnauthorized(res.status)) { return null; }
        return res.json().then(function (data) { return { ok: res.ok, data: data }; });
      })
      .then(function (result) {
        if (!result) { return; }
        if (!result.ok || !result.data.success) {
          loading.hidden = true;
          showFeedback(result.data.error || 'Erro ao carregar as cartas.', 'error');
          return;
        }
        cards = result.data.cards;
        render();
      })
      .catch(function () {
        loading.hidden = true;
        showFeedback('Erro de conexão ao carregar as cartas.', 'error');
      });
  }

  function deleteCard(card, button) {
    var ok = window.confirm('Excluir a carta "' + card.name_en + '"?\n\nEsta ação não pode ser desfeita.');
    if (!ok) { return; }

    button.disabled = true;
    button.textContent = 'Excluindo...';

    var data = new FormData();
    data.append('id', card.id);

    fetch('api/cards/delete.php', { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (res) {
        if (handleUnauthorized(res.status)) { return null; }
        return res.json().then(function (json) { return { ok: res.ok, data: json }; });
      })
      .then(function (result) {
        if (!result) { return; }
        if (!result.ok || !result.data.success) {
          showFeedback(result.data.error || 'Não foi possível excluir a carta.', 'error');
          button.disabled = false;
          button.textContent = 'Excluir';
          return;
        }
        cards = cards.filter(function (c) { return String(c.id) !== String(card.id); });
        render();
        showFeedback('Carta "' + card.name_en + '" excluída com sucesso.', 'success');
      })
      .catch(function () {
        showFeedback('Erro de conexão ao excluir a carta.', 'error');
        button.disabled = false;
        button.textContent = 'Excluir';
      });
  }

  newBtn.addEventListener('click', function () { window.CardForm.open(null); });

  window.CardForm.onSaved(function (card, isEdit) {
    if (isEdit) {
      cards = cards.map(function (c) { return String(c.id) === String(card.id) ? card : c; });
    } else {
      cards.unshift(card);
    }
    render();
    showFeedback(isEdit ? 'Carta atualizada com sucesso.' : 'Carta incluída com sucesso.', 'success');
  });

  logoutBtn.addEventListener('click', function () {
    logoutBtn.disabled = true;
    fetch('api/auth/logout.php', { method: 'POST', credentials: 'same-origin' })
      .finally(function () { window.location.href = 'login.php'; });
  });

  loadCards();
})();
