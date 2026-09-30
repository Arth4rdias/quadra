-- ============================================================
--  QUADRA — extensão de administração (login admin, bloqueio de
--  contas, registro de acessos e equipe de desenvolvedores)
--
--  Importe ESTE arquivo depois do banco.sql (aba "Importar" do
--  phpMyAdmin, ou: mysql -u root quadra < admin.sql).
--  Ele só ACRESCENTA coisas — não apaga nem muda o que já existe.
--
--  Login do admin:  admin@quadra.com   senha: admin123
--  (troque essa senha depois de importar — veja o painel, aba
--  "Cadastros" não tem isso, é só entrar e nunca usar senha de teste em produção)
-- ============================================================
USE quadra;

-- permite ao admin suspender uma conta sem apagar os dados dela
ALTER TABLE perfis ADD COLUMN bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER verificado;

-- login separado do administrador (não é um "perfil" comum)
CREATE TABLE admins (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  nome     VARCHAR(120) NOT NULL,
  email    VARCHAR(120) NOT NULL UNIQUE,
  senha    VARCHAR(255) NOT NULL,
  visto_em DATETIME NULL,
  criado   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- um registro por login bem-sucedido, para o admin ver quem entrou e quando
CREATE TABLE acessos (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  perfil_id INT NOT NULL,
  criado    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- equipe de desenvolvedores (mostrada em equipe.php, gerenciada em admin/desenvolvedores.php)
CREATE TABLE desenvolvedores (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nome   VARCHAR(120) NOT NULL,
  papel  VARCHAR(120) NOT NULL DEFAULT '',
  bio    VARCHAR(300) NOT NULL DEFAULT '',
  foto   VARCHAR(120) NOT NULL DEFAULT '',
  link   VARCHAR(160) NOT NULL DEFAULT '',
  ordem  INT NOT NULL DEFAULT 0,
  criado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------- dados iniciais -------------------------
INSERT INTO admins (nome, email, senha) VALUES
('Administrador', 'admin@quadra.com', '$2b$12$ZUJDwAe8L2ubI4h3CwRYmuJF97HKutC23SWERw.MdjoBDPB3XSuia');

INSERT INTO desenvolvedores (nome, papel, bio, link, ordem) VALUES
('Seu nome aqui',   'Desenvolvedor(a)', 'Troque este texto de exemplo por uma frase sobre o que você fez no projeto.', '', 1),
('Nome do colega',  'Desenvolvedor(a)', 'Edite ou apague este cartão de exemplo em Painel do admin → Equipe do site.', '', 2);
