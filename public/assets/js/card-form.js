/**
 * Lógica do formulário de carta: modal, campo de edição dependente (fetch +
 * loading + reset), pré-preenchimento na edição e envio via FormData.
 *
 * Expõe window.CardForm = { open(card?), close(), onSaved(callback) }.
 */
(function () {
  'use strict';

  var modal = document.getElementById('card-modal');
  var form = document.getElementById('card-form');
  var title = document.getElementById('card-form-title');
  var submitBtn = document.getElementById('card-submit');
  var feedback = document.getElementById('form-feedback');

  var idInput = document.getElementById('card-id');
  var nameEn = document.getElementById('name_en');
  var namePt = document.getElementById('name_pt');
  var gameSelect = document.getElementById('card_game');
  var editionSelect = document.getElementById('edition_id');
  var rarityInput = document.getElementById('rarity');
  var imageInput = document.getElementById('image');
  var previewWrap = document.getElementById('image-preview-wrap');
  var previewImg = document.getElementById('image-preview');
  var previewLabel = document.getElementById('image-preview-label');

  var savedCallback = null;
  var currentRequest = 0; // evita que uma resposta antiga sobrescreva a atual

  /* ---------- feedback / erros ---------- */

  function showFeedback(message, type) {
    feedback.textContent = message;
    feedback.className = 'feedback feedback-' + type;
    feedback.hidden = false;
  }

  function hideFeedback() {
    feedback.hidden = true;
    feedback.textContent = '';
  }

  function clearFieldErrors() {
    var nodes = form.querySelectorAll('.field-error');
    for (var i = 0; i < nodes.length; i++) {
      nodes[i].textContent = '';
    }
    var invalid = form.querySelectorAll('.is-invalid');
    for (var j = 0; j < invalid.length; j++) {
      invalid[j].classList.remove('is-invalid');
    }
  }

  function setFieldError(field, message) {
    var node = form.querySelector('[data-error-for="' + field + '"]');
    if (node) {
      node.textContent = message;
    }
    var input = form.elements[field];
    if (input) {
      input.classList.add('is-invalid');
    }
  }

  /* ---------- campo de edição (dependente) ---------- */

  function setEditionPlaceholder(text, disabled) {
    editionSelect.innerHTML = '';
    var opt = document.createElement('option');
    opt.value = '';
    opt.textContent = text;
    editionSelect.appendChild(opt);
    editionSelect.disabled = disabled;
    editionSelect.classList.toggle('is-loading', text === 'Carregando...');
  }

  function resetEditions() {
    setEditionPlaceholder('Selecione o card game primeiro', true);
  }

  /**
   * Busca as edições do jogo informado e popula o select.
   * @param {string} game
   * @param {string|null} preselectId edição a pré-selecionar (modo edição)
   * @returns {Promise}
   */
  function loadEditions(game, preselectId) {
    if (!game) {
      resetEditions();
      return Promise.resolve();
    }

    var requestId = ++currentRequest;
    setEditionPlaceholder('Carregando...', true);

    return fetch('api/editions.php?game=' + encodeURIComponent(game), { credentials: 'same-origin' })
      .then(function (res) {
        return res.json().then(function (data) { return { ok: res.ok, data: data }; });
      })
      .then(function (result) {
        if (requestId !== currentRequest) {
          return; // o usuário trocou de jogo enquanto carregava
        }
        if (!result.ok || !result.data.success) {
          setEditionPlaceholder('Erro ao carregar. Selecione o jogo novamente.', true);
          showFeedback(result.data.error || 'Não foi possível carregar as edições.', 'error');
          return;
        }

        editionSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione a edição';
        editionSelect.appendChild(placeholder);

        result.data.editions.forEach(function (ed) {
          var opt = document.createElement('option');
          opt.value = ed.id;
          opt.textContent = ed.name;
          editionSelect.appendChild(opt);
        });

        editionSelect.disabled = false;
        editionSelect.classList.remove('is-loading');
        editionSelect.value = preselectId || '';
      })
      .catch(function () {
        if (requestId !== currentRequest) {
          return;
        }
        setEditionPlaceholder('Erro de conexão. Selecione o jogo novamente.', true);
        showFeedback('Erro de conexão ao buscar edições.', 'error');
      });
  }

  gameSelect.addEventListener('change', function () {
    hideFeedback();
    // Ao trocar o jogo, a seleção anterior de edição deixa de ser válida.
    loadEditions(gameSelect.value, null);
  });

  /* ---------- pré-visualização da imagem ---------- */

  function showPreview(src, label) {
    if (!src) {
      previewWrap.hidden = true;
      previewImg.removeAttribute('src');
      previewLabel.textContent = '';
      return;
    }
    previewImg.src = src;
    previewLabel.textContent = label || '';
    previewWrap.hidden = false;
  }

  imageInput.addEventListener('change', function () {
    var file = imageInput.files && imageInput.files[0];
    if (!file) {
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      showPreview(e.target.result, 'Nova imagem: ' + file.name);
    };
    reader.readAsDataURL(file);
  });

  /* ---------- abrir / fechar ---------- */

  function resetForm() {
    form.reset();
    idInput.value = '';
    clearFieldErrors();
    hideFeedback();
    resetEditions();
    showPreview(null);
    submitBtn.disabled = false;
    submitBtn.textContent = 'Salvar carta';
  }

  function open(card) {
    resetForm();

    if (card) {
      title.textContent = 'Editar Carta';
      idInput.value = card.id;
      nameEn.value = card.name_en || '';
      namePt.value = card.name_pt || '';
      rarityInput.value = card.rarity || '';
      gameSelect.value = card.card_game || '';
      if (card.image_path) {
        showPreview(card.image_path, 'Imagem atual (envie outra para substituir)');
      }
      loadEditions(card.card_game, card.edition_id);
    } else {
      title.textContent = 'Incluir Carta';
    }

    modal.hidden = false;
    document.body.classList.add('modal-open');
    nameEn.focus();
  }

  function close() {
    modal.hidden = true;
    document.body.classList.remove('modal-open');
  }

  var closers = modal.querySelectorAll('[data-close-modal]');
  for (var c = 0; c < closers.length; c++) {
    closers[c].addEventListener('click', close);
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
      close();
    }
  });

  /* ---------- envio ---------- */

  function validateClient() {
    var ok = true;
    clearFieldErrors();

    if (!nameEn.value.trim()) {
      setFieldError('name_en', 'Informe o nome da carta em inglês.');
      ok = false;
    }
    if (!gameSelect.value) {
      setFieldError('card_game', 'Selecione o card game.');
      ok = false;
    }
    if (!editionSelect.value) {
      setFieldError('edition_id', 'Selecione a edição.');
      ok = false;
    }
    if (!rarityInput.value.trim()) {
      setFieldError('rarity', 'Informe a raridade.');
      ok = false;
    }
    return ok;
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    hideFeedback();

    if (!validateClient()) {
      showFeedback('Revise os campos destacados.', 'error');
      return;
    }

    var isEdit = idInput.value !== '';
    var data = new FormData(form);
    var selected = editionSelect.options[editionSelect.selectedIndex];
    data.set('edition_name', selected ? selected.textContent : '');
    if (!isEdit) {
      data.delete('id');
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Salvando...';
    showFeedback('Salvando carta...', 'loading');

    fetch(isEdit ? 'api/cards/update.php' : 'api/cards/create.php', {
      method: 'POST',
      body: data,
      credentials: 'same-origin'
    })
      .then(function (res) {
        return res.json().then(function (json) { return { ok: res.ok, status: res.status, data: json }; });
      })
      .then(function (result) {
        if (result.status === 401) {
          window.location.href = 'login.php';
          return;
        }
        if (!result.ok || !result.data.success) {
          if (result.data.fields) {
            Object.keys(result.data.fields).forEach(function (f) {
              setFieldError(f, result.data.fields[f]);
            });
          }
          showFeedback(result.data.error || 'Não foi possível salvar a carta.', 'error');
          return;
        }
        close();
        if (typeof savedCallback === 'function') {
          savedCallback(result.data.card, isEdit);
        }
      })
      .catch(function () {
        showFeedback('Erro de conexão. Tente novamente.', 'error');
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Salvar carta';
      });
  });

  window.CardForm = {
    open: open,
    close: close,
    onSaved: function (cb) { savedCallback = cb; }
  };
})();
