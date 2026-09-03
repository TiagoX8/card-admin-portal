(function () {
  'use strict';

  var form = document.getElementById('login-form');
  var feedback = document.getElementById('login-feedback');
  var submit = document.getElementById('login-submit');

  function showFeedback(message, type) {
    feedback.textContent = message;
    feedback.className = 'feedback feedback-' + type;
    feedback.hidden = false;
  }

  function hideFeedback() {
    feedback.hidden = true;
    feedback.textContent = '';
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    hideFeedback();

    var username = form.username.value.trim();
    var password = form.password.value;

    if (!username || !password) {
      showFeedback('Informe usuário e senha.', 'error');
      return;
    }

    submit.disabled = true;
    submit.textContent = 'Entrando...';

    var body = new FormData();
    body.append('username', username);
    body.append('password', password);

    fetch('api/auth/login.php', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (res) {
        return res.json().then(function (data) { return { ok: res.ok, data: data }; });
      })
      .then(function (result) {
        if (result.ok && result.data.success) {
          window.location.href = 'dashboard.php';
          return;
        }
        showFeedback(result.data.error || 'Não foi possível entrar.', 'error');
      })
      .catch(function () {
        showFeedback('Erro de conexão. Tente novamente.', 'error');
      })
      .finally(function () {
        submit.disabled = false;
        submit.textContent = 'Entrar';
      });
  });
})();
