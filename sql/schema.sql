CREATE DATABASE IF NOT EXISTS card_admin
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE card_admin;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cards (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name_en      VARCHAR(255) NOT NULL,
  name_pt      VARCHAR(255) NULL,
  card_game    ENUM('magic', 'pokemon', 'yugioh') NOT NULL,
  edition_id   VARCHAR(50) NOT NULL,
  edition_name VARCHAR(255) NOT NULL DEFAULT '',
  image_path   VARCHAR(255) NULL,
  rarity       VARCHAR(100) NOT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cards_card_game (card_game),
  KEY idx_cards_edition (card_game, edition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuário admin (senha: admin123).
-- O hash abaixo é um placeholder; rode `php sql/seed_admin.php` para gerar um hash
-- válido com password_hash() e inserir/atualizar o usuário admin.
INSERT INTO users (username, password_hash)
VALUES ('admin', 'RUN_sql/seed_admin.php_TO_SET_PASSWORD')
ON DUPLICATE KEY UPDATE username = username;
