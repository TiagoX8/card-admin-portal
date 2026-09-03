# Card Admin Portal

Portal administrativo para gestão de cartas de card games (Magic: The Gathering, Pokémon e Yu-Gi-Oh!).

- **Back-end:** PHP puro (sem frameworks), MySQL via PDO com prepared statements.
- **Front-end:** apenas HTML5, CSS3 e JavaScript vanilla — nenhuma dependência externa de JS ou CSS.

Funcionalidades: login com sessão, listagem de cartas, inclusão/edição/exclusão com upload de imagem, e campo de Edição dependente do Card Game (carregado via API).

## Estrutura

```
card-admin-portal/
  public/                 docroot
    index.php             redireciona para login ou dashboard conforme sessão
    login.php             página de login
    dashboard.php         lista de cartas + modal do formulário
    router.php            roteador do servidor embutido (encaminha /api/* para ../api)
    assets/css/styles.css
    assets/js/login.js    lógica do login
    assets/js/app.js      lista, excluir, logout
    assets/js/card-form.js formulário (edições dependentes, loading, reset, salvar)
    uploads/              imagens enviadas
  api/
    auth/login.php, auth/logout.php
    editions.php          edições por card game
    cards/list.php, create.php, update.php, delete.php
  src/
    config.php            credenciais via getenv() com fallback local
    db.php                PDO (ERRMODE_EXCEPTION, utf8mb4)
    auth.php              session helpers: requireAuth(), etc.
    cards.php             validação e upload de imagens
  sql/
    schema.sql            database + tabelas + seed do admin
    seed_admin.php        gera o hash do admin com password_hash()
```

## Requisitos

- PHP 8.0+ com extensões `pdo_mysql`, `fileinfo` e `mbstring`
- MySQL 5.7+ / MariaDB 10.3+

## Como rodar

1. **Banco de dados**

   ```bash
   mysql -u root -p < sql/schema.sql
   ```

2. **Configuração** (opcional — os padrões são `127.0.0.1:3306`, db `card_admin`, user `root`, senha vazia):

   ```bash
   export DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=card_admin DB_USER=root DB_PASSWORD=secret
   ```

3. **Usuário admin** (gera o hash com `password_hash()`):

   ```bash
   php sql/seed_admin.php            # cria admin / admin123
   php sql/seed_admin.php user senha # usuário/senha customizados
   ```

4. **Servidor**

   ```bash
   php -S localhost:8000 -t public public/router.php
   ```

   Acesse <http://localhost:8000>. O `router.php` faz o servidor embutido responder às URLs `/api/...`
   com os arquivos da pasta `api/` (que fica fora do docroot). Em Apache/Nginx, configure o docroot em
   `public/` e um alias de `/api/` para a pasta `api/`.

5. Garanta permissão de escrita em `public/uploads/`.

### Credenciais padrão

| Usuário | Senha      |
| ------- | ---------- |
| `admin` | `admin123` |

Altere a senha em produção rodando `php sql/seed_admin.php admin <nova-senha>`.

## API

Todas as respostas são JSON (`{ "success": true|false, ... }`). Os endpoints de cartas e edições exigem sessão autenticada (401 caso contrário).

| Método | Endpoint                          | Descrição                                                                     |
| ------ | --------------------------------- | ----------------------------------------------------------------------------- |
| POST   | `/api/auth/login.php`             | `username`, `password` → cria sessão                                          |
| POST   | `/api/auth/logout.php`            | destrói a sessão                                                              |
| GET    | `/api/editions.php?game=<g>`      | edições de `magic`, `pokemon` ou `yugioh` (400 se inválido)                   |
| GET    | `/api/cards/list.php`             | lista todas as cartas                                                         |
| POST   | `/api/cards/create.php`           | `name_en`*, `name_pt`, `card_game`*, `edition_id`*, `edition_name`, `rarity`*, `image` (jpg/png/webp, até 5 MB) |
| POST   | `/api/cards/update.php`           | mesmos campos + `id`*; nova imagem substitui e apaga a anterior               |
| POST   | `/api/cards/delete.php`           | `id`* — apaga o registro e a imagem                                           |

## Decisões de experiência

1. **Campo de Edição desabilitado até escolher o Card Game, com estado de loading e reset ao trocar de jogo.**
   As edições só fazem sentido dentro de um jogo; deixar o campo bloqueado até a escolha evita combinações inválidas
   e torna a ordem de preenchimento óbvia para quem tem pouca familiaridade. Durante a requisição o select mostra
   "Carregando..." e permanece desabilitado, e ao trocar de jogo a seleção anterior é descartada (respostas
   atrasadas de um jogo anterior também são ignoradas), para que nunca fique uma edição de outro jogo salva por engano.

2. **Confirmação antes de excluir e feedback explícito em toda ação (sucesso, erro, carregando).**
   Excluir uma carta apaga também a imagem e não tem desfazer, então pedimos confirmação com o nome da carta.
   Cada operação (login, salvar, excluir, buscar edições) mostra uma mensagem visível e desabilita o botão enquanto
   processa, com erros de validação destacados campo a campo. Isso dá segurança ao usuário e reduz cliques duplicados
   e erros irreversíveis.
