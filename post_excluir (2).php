QUADRA — versão PHP + MySQL (CRUD completo)
===========================================

COMO RODAR (XAMPP / WAMP / Laragon)
1. Copie a pasta "quadra" para  C:\xampp\htdocs\   (ou a pasta "www" do WAMP/Laragon).
2. Ligue o Apache e o MySQL no painel do XAMPP.
3. Abra  http://localhost/phpmyadmin  → aba "Importar" → escolha o arquivo banco.sql → Executar.
   (ele cria o banco "quadra", as tabelas e os dados de exemplo)
4. Acesse  http://localhost/quadra/
5. Se o seu MySQL tiver senha, ajuste as 4 linhas de DB_ no início do config.php.

Requisitos: PHP 8.0 ou superior, extensões pdo_mysql, mbstring e fileinfo (já vêm ligadas no XAMPP).

LOGIN DE TESTE
  e-mail: rafa.quintela@email.com   senha: 1234
  (todas as contas de exemplo usam a senha 1234)

ONDE ESTÁ CADA PARTE DO CRUD
  Tabela       Create               Read                        Update               Delete
  -----------  -------------------  --------------------------  -------------------  -------------------
  perfis       perfil_form.php      perfis.php / perfil_ver.php perfil_form.php?id=  perfil_excluir.php
  vagas        vaga_form.php        vagas.php                   vaga_form.php?id=    vaga_excluir.php
  posts        post_form.php        index.php (Praça)           post_form.php?id=    post_excluir.php
  candidaturas candidatar.php       candidatos.php              (alterna)            candidatar.php
  curtidas     curtir.php           contagem na Praça           (alterna)            curtir.php

OUTROS ARQUIVOS
  config.php      conexão PDO, sessão, CSRF e funções de apoio
  inc/topo.php    cabeçalho + menu    ·  inc/rodape.php  coluna lateral
  login.php / logout.php   autenticação
  painel.php      números e gráficos (COUNT / GROUP BY)
  css/style.css   o mesmo visual do quadra.html
  uploads/        fotos de perfil enviadas pelos usuários

SEGURANÇA JÁ INCLUÍDA
  - PDO com prepared statements (contra SQL injection)
  - htmlspecialchars em toda saída (contra XSS)
  - senhas com password_hash / password_verify
  - token CSRF em todo formulário que altera dados
  - só o dono edita/apaga seu perfil, vagas e publicações
