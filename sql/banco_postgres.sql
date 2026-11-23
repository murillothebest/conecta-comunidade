-- Conecta Comunidade - PostgreSQL (Filess.io)
-- Opcional: a API também cria as tabelas automaticamente (EnsureCreated).
-- Use este script se preferir criar o banco manualmente no painel do Filess.io.

CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  data_cadastro TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS categorias (
  id_categoria SERIAL PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE,
  descricao VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS acoes (
  id_acao SERIAL PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  descricao TEXT NOT NULL,
  data DATE NOT NULL,
  local VARCHAR(150) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Planejada',
  id_categoria INT NOT NULL REFERENCES categorias(id_categoria),
  id_usuario INT NOT NULL REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS inscricoes (
  id_inscricao SERIAL PRIMARY KEY,
  id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  id_acao INT NOT NULL REFERENCES acoes(id_acao) ON DELETE CASCADE,
  data_inscricao TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CONSTRAINT uk_inscricao_usuario_acao UNIQUE (id_usuario, id_acao)
);

CREATE TABLE IF NOT EXISTS comentarios (
  id_comentario SERIAL PRIMARY KEY,
  id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  id_acao INT NOT NULL REFERENCES acoes(id_acao) ON DELETE CASCADE,
  comentario TEXT NOT NULL,
  data_comentario TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_acoes_status ON acoes(status);
CREATE INDEX IF NOT EXISTS idx_acoes_data ON acoes(data);

INSERT INTO categorias (nome, descricao) VALUES
('Arrecadação', 'Campanhas de doação e arrecadação'),
('Mutirão', 'Trabalhos coletivos voluntários'),
('Reunião', 'Encontros da comunidade'),
('Atividade Social', 'Convivência e apoio social'),
('Evento Cultural', 'Feiras e manifestações culturais'),
('Ação Ambiental', 'Limpeza e preservação'),
('Educação', 'Oficinas e reforço escolar')
ON CONFLICT (nome) DO NOTHING;
