-- =====================================================
-- Conecta Comunidade - Script do Banco de Dados
-- Compatível com MySQL / MariaDB (XAMPP)
-- =====================================================
-- Observação:
-- O sistema NÃO depende de usuário fixo para funcionar.
-- Cadastre-se pela tela "Cadastre-se" e faça login.
-- O usuário seed abaixo existe apenas para vincular
-- as 3 ações de demonstração (desenvolvimento).
-- =====================================================

CREATE DATABASE IF NOT EXISTS conecta_comunidade
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE conecta_comunidade;

-- -----------------------------------------------------
-- Tabela: usuarios
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uk_usuarios_email (email),
  KEY idx_usuarios_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: categorias
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(80) NOT NULL,
  descricao VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_categoria),
  UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: acoes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS acoes (
  id_acao INT UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(150) NOT NULL,
  descricao TEXT NOT NULL,
  data DATE NOT NULL,
  local VARCHAR(150) NOT NULL,
  status ENUM('Planejada', 'Em andamento', 'Concluída', 'Cancelada') NOT NULL DEFAULT 'Planejada',
  id_categoria INT UNSIGNED NOT NULL,
  id_usuario INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_acao),
  KEY idx_acoes_status (status),
  KEY idx_acoes_data (data),
  KEY idx_acoes_categoria (id_categoria),
  KEY idx_acoes_usuario (id_usuario),
  CONSTRAINT fk_acoes_categoria
    FOREIGN KEY (id_categoria) REFERENCES categorias (id_categoria)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_acoes_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: inscricoes
-- UNIQUE evita inscrição duplicada do mesmo usuário na mesma ação
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS inscricoes (
  id_inscricao INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_usuario INT UNSIGNED NOT NULL,
  id_acao INT UNSIGNED NOT NULL,
  data_inscricao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_inscricao),
  UNIQUE KEY uk_inscricao_usuario_acao (id_usuario, id_acao),
  KEY idx_inscricoes_acao (id_acao),
  CONSTRAINT fk_inscricoes_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_inscricoes_acao
    FOREIGN KEY (id_acao) REFERENCES acoes (id_acao)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: comentarios
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS comentarios (
  id_comentario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_usuario INT UNSIGNED NOT NULL,
  id_acao INT UNSIGNED NOT NULL,
  comentario TEXT NOT NULL,
  data_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_comentario),
  KEY idx_comentarios_acao (id_acao),
  KEY idx_comentarios_usuario (id_usuario),
  CONSTRAINT fk_comentarios_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_comentarios_acao
    FOREIGN KEY (id_acao) REFERENCES acoes (id_acao)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Categorias de exemplo
-- -----------------------------------------------------
INSERT INTO categorias (nome, descricao) VALUES
('Arrecadação', 'Campanhas de doação e arrecadação de recursos'),
('Mutirão', 'Trabalhos coletivos voluntários'),
('Reunião', 'Encontros e assembleias da comunidade'),
('Atividade Social', 'Ações de convivência e apoio social'),
('Evento Cultural', 'Feiras, apresentações e manifestações culturais'),
('Ação Ambiental', 'Limpeza, plantio e preservação do meio ambiente'),
('Educação', 'Oficinas, reforço escolar e atividades educacionais');

-- -----------------------------------------------------
-- Usuário seed (somente para vincular ações de demonstração)
-- NÃO use este usuário na apresentação: crie sua conta em Cadastre-se.
-- -----------------------------------------------------
INSERT INTO usuarios (nome, email, senha, data_cadastro) VALUES
(
  'Organizador Seed',
  'seed@conecta.local',
  '$2y$10$F2W/8LygO0DZ5DUH83Yf2uck4rjrCdbE5iALghY3ykhR0rCeiU/rq',
  NOW()
);

-- -----------------------------------------------------
-- 3 ações de demonstração
-- -----------------------------------------------------
INSERT INTO acoes (titulo, descricao, data, local, status, id_categoria, id_usuario) VALUES
(
  'Mutirão de Limpeza da Praça',
  'Encontro coletivo para limpeza, organização e revitalização da praça do bairro. Traga luvas e disposição para cuidar do espaço comum.',
  '2026-10-05',
  'Praça Central',
  'Planejada',
  6,
  1
),
(
  'Campanha de Arrecadação de Alimentos',
  'Campanha solidária para arrecadar alimentos não perecíveis destinados a famílias da comunidade em situação de vulnerabilidade.',
  '2026-10-12',
  'Salão Comunitário',
  'Em andamento',
  1,
  1
),
(
  'Oficina de Reforço Escolar',
  'Oficina gratuita de reforço em matemática e português para estudantes do ensino fundamental, com voluntários da comunidade.',
  '2026-10-20',
  'Centro Comunitário - Sala 2',
  'Planejada',
  7,
  1
);
