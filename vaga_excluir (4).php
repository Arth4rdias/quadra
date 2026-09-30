-- ============================================================
--  QUADRA — banco de dados (MySQL / MariaDB)
--  Importe este arquivo no phpMyAdmin (aba "Importar")
--  ou rode:  mysql -u root < banco.sql
--  Senha de todas as contas de exemplo: 1234
-- ============================================================
CREATE DATABASE IF NOT EXISTS quadra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quadra;
SET time_zone = '-03:00';

DROP TABLE IF EXISTS curtidas;
DROP TABLE IF EXISTS candidaturas;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS vagas;
DROP TABLE IF EXISTS perfis;

CREATE TABLE perfis (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(120) NOT NULL,
  tipo       ENUM('Empresa','Prestador') NOT NULL DEFAULT 'Empresa',
  categoria  VARCHAR(40)  NOT NULL,
  nivel      ENUM('Iniciante','Intermediário','Avançado') NOT NULL DEFAULT 'Iniciante',
  cidade     VARCHAR(80)  NOT NULL,
  bio        VARCHAR(260) NOT NULL,
  tags       VARCHAR(200) NOT NULL DEFAULT '',
  email      VARCHAR(120) NOT NULL UNIQUE,
  telefone   VARCHAR(30)  NOT NULL DEFAULT '',
  site       VARCHAR(120) NOT NULL DEFAULT '',
  foto       VARCHAR(120) NOT NULL DEFAULT '',
  senha      VARCHAR(255) NOT NULL,
  verificado TINYINT(1)   NOT NULL DEFAULT 0,
  criado     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vagas (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  perfil_id  INT NOT NULL,
  titulo     VARCHAR(120) NOT NULL,
  categoria  VARCHAR(40)  NOT NULL,
  modalidade ENUM('Presencial','Híbrido','Remoto') NOT NULL DEFAULT 'Presencial',
  nivel      ENUM('Iniciante','Intermediário','Avançado') NOT NULL DEFAULT 'Iniciante',
  cidade     VARCHAR(80)  NOT NULL,
  faixa      VARCHAR(60)  NOT NULL DEFAULT '',
  descricao  VARCHAR(400) NOT NULL,
  tags       VARCHAR(200) NOT NULL DEFAULT '',
  contato    VARCHAR(120) NOT NULL DEFAULT '',
  criado     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE posts (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  perfil_id     INT NOT NULL,
  tipo          ENUM('Trabalho','Vaga','Marco') NOT NULL DEFAULT 'Trabalho',
  texto         VARCHAR(420) NOT NULL,
  curtidas_base INT NOT NULL DEFAULT 0,
  criado        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE candidaturas (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  vaga_id   INT NOT NULL,
  perfil_id INT NOT NULL,
  criado    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cand (vaga_id, perfil_id),
  FOREIGN KEY (vaga_id)   REFERENCES vagas(id)   ON DELETE CASCADE,
  FOREIGN KEY (perfil_id) REFERENCES perfis(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE curtidas (
  post_id   INT NOT NULL,
  perfil_id INT NOT NULL,
  PRIMARY KEY (post_id, perfil_id),
  FOREIGN KEY (post_id)   REFERENCES posts(id)   ON DELETE CASCADE,
  FOREIGN KEY (perfil_id) REFERENCES perfis(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------- dados de exemplo -------------------------
INSERT INTO perfis (nome,tipo,categoria,nivel,cidade,bio,tags,email,telefone,site,senha,verificado,criado) VALUES
('Oficina Mandacaru','Empresa','Design','Avançado','Recife, PE','Estúdio de identidade visual para negócios de bairro. Trabalhamos com quem está começando e não tem verba de agência grande.','identidade visual, embalagem, sinalização','contato@mandacaru.co','(81) 98812-4410','mandacaru.co','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',1,DATE_SUB(NOW(), INTERVAL 40978 MINUTE)),
('Rafa Quintela','Prestador','Tecnologia','Intermediário','São Paulo, SP','Desenvolvo sites e sistemas simples para pequenos comércios. Entrego em 3 semanas, com treinamento pra equipe usar sozinha.','site, javascript, automação','rafa.quintela@email.com','(11) 99741-2208','rafaq.dev','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 54013 MINUTE)),
('Doce Ofício','Empresa','Alimentação','Iniciante','Belo Horizonte, MG','Confeitaria de bairro há 8 meses. Bolos de festa, doces finos e encomendas corporativas.','confeitaria, festas, encomendas','pedidos@doceoficio.com','(31) 98444-1120','','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 50557 MINUTE)),
('Construtora Lajedo','Empresa','Construção','Avançado','Fortaleza, CE','Reformas residenciais e comerciais. Obra com cronograma público: o cliente acompanha cada etapa por link.','reforma, alvenaria, elétrica','obras@lajedo.com.br','(85) 3266-0900','lajedo.com.br','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',1,DATE_SUB(NOW(), INTERVAL 18664 MINUTE)),
('Bruna Sayuri','Prestador','Marketing','Intermediário','Curitiba, PR','Cuido de redes sociais para clínicas e consultórios. Foco em agendamento, não em curtida.','social media, tráfego pago, copy','bruna@sayuri.mkt','(41) 99120-7781','','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 40547 MINUTE)),
('Nó Cego Marcenaria','Empresa','Construção','Intermediário','Porto Alegre, RS','Móveis sob medida em madeira maciça. Atendemos arquitetos e clientes finais.','marcenaria, móvel planejado, restauro','orcamento@nocego.com','(51) 98330-2255','nocego.com','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',1,DATE_SUB(NOW(), INTERVAL 52739 MINUTE)),
('Clínica Passo Firme','Empresa','Saúde','Avançado','São Paulo, SP','Fisioterapia esportiva e reabilitação. Convênios e atendimento domiciliar na zona oeste.','fisioterapia, pilates, domiciliar','recepcao@passofirme.com.br','(11) 3872-1140','passofirme.com.br','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',1,DATE_SUB(NOW(), INTERVAL 9604 MINUTE)),
('Téo Albuquerque','Prestador','Jurídico','Iniciante','Salvador, BA','Advogado recém-formado, foco em contratos para MEIs e pequenos prestadores. Primeira consulta sem custo.','contratos, MEI, consultoria','teo.adv@email.com','(71) 99655-3311','','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 24697 MINUTE)),
('Matiz Escola Livre','Empresa','Educação','Intermediário','Florianópolis, SC','Cursos curtos de ofício: costura, panificação, elétrica residencial. Turmas de 12 pessoas.','cursos, oficinas, presencial','matiz@escolalivre.org','(48) 3025-8800','matizescola.org','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 30786 MINUTE)),
('Ana Corrêa','Prestador','Contabilidade','Avançado','Goiânia, GO','Contabilidade para prestadores de serviço. Abro seu CNPJ em 5 dias e cuido do mensal.','MEI, imposto de renda, folha','ana@correacontabil.com','(62) 98155-4477','correacontabil.com','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',1,DATE_SUB(NOW(), INTERVAL 36843 MINUTE)),
('Vidro & Vento Eventos','Empresa','Eventos','Iniciante','Campinas, SP','Montagem de eventos ao ar livre. Tendas, som e iluminação para casamentos e feiras.','tendas, som, iluminação','vidrovento@email.com','(19) 99801-2244','','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 51437 MINUTE)),
('Kauê Nogueira','Prestador','Logística','Intermediário','Manaus, AM','Entregas urbanas com van própria. Rotas fixas para lojas do centro, com rastreio por mensagem.','entrega, van, rotas','kaue.log@email.com','(92) 99400-1877','','$2b$12$Wk1VmE6NUkAP6bT5QWrXxeJCEjbgQwnyQiEnymg6WKwWClnuGCBHW',0,DATE_SUB(NOW(), INTERVAL 30962 MINUTE));

INSERT INTO vagas (perfil_id,titulo,categoria,modalidade,nivel,cidade,faixa,descricao,tags,contato,criado) VALUES
(4,'Mestre de obras','Construção','Presencial','Avançado','Fortaleza, CE','R$ 5.500 a R$ 7.000','Coordenar cinco obras comerciais simultâneas, gerir equipe de 18 pessoas e responder pelo cronograma público de cada obra.','obra, gestão, reforma','obras@lajedo.com.br',DATE_SUB(NOW(), INTERVAL 240 MINUTE)),
(1,'Desenvolvedor front-end júnior','Tecnologia','Híbrido','Iniciante','Recife, PE','R$ 2.800 a R$ 3.600','Transformar layouts do estúdio em sites leves. Não exigimos experiência formal, mas queremos ver algo que você tenha feito.','javascript, site, design','contato@mandacaru.co',DATE_SUB(NOW(), INTERVAL 660 MINUTE)),
(9,'Instrutor de elétrica residencial','Educação','Presencial','Intermediário','Florianópolis, SC','R$ 420 por turma','Duas turmas por semana, 12 alunos cada. Exigimos NR-10 válida e prática de campo.','elétrica, ensino, NR-10','matiz@escolalivre.org',DATE_SUB(NOW(), INTERVAL 1560 MINUTE)),
(7,'Social media para clínicas','Marketing','Remoto','Intermediário','São Paulo, SP','R$ 3.200','Cuidar de dois perfis com meta de agendamento, não de alcance. Relatório mensal simples.','social media, saúde, tráfego pago','recepcao@passofirme.com.br',DATE_SUB(NOW(), INTERVAL 2280 MINUTE)),
(3,'Auxiliar de confeitaria','Alimentação','Presencial','Iniciante','Belo Horizonte, MG','R$ 1.800','Apoio na produção de encomendas, das 6h às 14h. Ensinamos tudo, queremos alguém pontual e limpo.','confeitaria, produção','pedidos@doceoficio.com',DATE_SUB(NOW(), INTERVAL 3000 MINUTE)),
(12,'Motorista entregador com van','Logística','Presencial','Intermediário','Manaus, AM','R$ 2.400 + ajuda de custo','Rota fixa no centro, dez paradas, saída às 7h. Precisa ter CNH D e conhecer bem o centro.','entrega, van, rotas','kaue.log@email.com',DATE_SUB(NOW(), INTERVAL 3840 MINUTE));

INSERT INTO posts (perfil_id,tipo,texto,curtidas_base,criado) VALUES
(1,'Trabalho','Entregamos a nova identidade do Mercado da Encruzilhada. Três meses conversando com 40 feirantes antes de desenhar a primeira letra. A placa de cada banca virou peça do sistema.',47,DATE_SUB(NOW(), INTERVAL 120 MINUTE)),
(4,'Vaga','Procuramos mestre de obras com experiência em reforma comercial. Cinco obras simultâneas no Aldeota, carteira assinada desde o primeiro dia. Chama no direct que eu respondo hoje.',23,DATE_SUB(NOW(), INTERVAL 300 MINUTE)),
(3,'Marco','Primeiro casamento de 200 convidados fechado. Há oito meses eu fazia bolo na cozinha de casa pra vizinha. Obrigada a quem indicou.',189,DATE_SUB(NOW(), INTERVAL 540 MINUTE)),
(2,'Trabalho','Dica pra quem vende pelo Instagram e ainda não tem site: um catálogo simples com botão de WhatsApp resolve 80% do problema e custa menos que um mês de anúncio. Não precisa de app.',96,DATE_SUB(NOW(), INTERVAL 840 MINUTE)),
(10,'Trabalho','Lembrete de março: quem faturou acima do limite do MEI ano passado precisa migrar antes que a multa entre. Levo 20 minutos pra checar seu caso, sem cobrar.',71,DATE_SUB(NOW(), INTERVAL 1200 MINUTE)),
(6,'Trabalho','Mesa de jantar em nogueira, 2,40m, uma peça só de tampo. Nove dias de trabalho e três de secagem. Quem quiser ver o processo, tem vídeo no site.',134,DATE_SUB(NOW(), INTERVAL 1680 MINUTE)),
(8,'Marco','Primeiro contrato assinado como advogado. Era um contrato de prestação de serviço de R$ 1.200 pra uma manicure do bairro. Começou.',215,DATE_SUB(NOW(), INTERVAL 2040 MINUTE)),
(9,'Vaga','Abrimos duas turmas novas de elétrica residencial e precisamos de instrutor com NR-10 válida. Aulas terça e quinta à noite, pagamento por turma.',18,DATE_SUB(NOW(), INTERVAL 2400 MINUTE)),
(5,'Trabalho','Fechei o trimestre com quatro clínicas e uma conclusão desconfortável: quase ninguém mede quantos agendamentos vêm da rede social. Se você não mede, está pagando no escuro.',88,DATE_SUB(NOW(), INTERVAL 2880 MINUTE)),
(12,'Trabalho','Rota nova no centro de Manaus, saída 7h, dez paradas fixas. Ainda tenho três vagas de carga. Loja pequena também cabe.',29,DATE_SUB(NOW(), INTERVAL 3360 MINUTE)),
(7,'Marco','Dez anos de clínica hoje. Começamos numa sala alugada com uma maca emprestada.',302,DATE_SUB(NOW(), INTERVAL 4200 MINUTE)),
(11,'Trabalho','Montagem de feira em dia de chuva ensina mais que qualquer curso. Levem lona sobrando, sempre.',42,DATE_SUB(NOW(), INTERVAL 4800 MINUTE));
