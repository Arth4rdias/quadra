<?php
/* LOGIN ÚNICO — usuários comuns e administradores entram pelo mesmo formulário. */
require 'config.php';

if (administrador()) redirecionar('admin/index.php');
if (usuario()) redirecionar('index.php');

$erro  = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // 1) Primeiro verifica se o e-mail pertence a um administrador.
    // Administradores não recebem uma sessão de usuário comum.
    $st = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
    $st->execute([$email]);
    $a = $st->fetch();

    if ($a && password_verify($senha, $a['senha'])) {
        session_regenerate_id(true);
        unset($_SESSION['uid']);
        $_SESSION['admin_id'] = (int)$a['id'];
        flash('Bem-vindo(a) ao painel, ' . $a['nome'] . '.');
        redirecionar('admin/index.php');
    }

    // 2) Se não for admin, tenta autenticar como usuário normal.
    $st = $pdo->prepare('SELECT * FROM perfis WHERE email = ?');
    $st->execute([$email]);
    $p = $st->fetch();

    if ($p && password_verify($senha, $p['senha'])) {
        if (!empty($p['bloqueado'])) {
            $erro = 'Esta conta está suspensa. Entre em contato com a administração.';
        } else {
            session_regenerate_id(true);
            unset($_SESSION['admin_id']);
            $_SESSION['uid'] = (int)$p['id'];

            // Registra o login do usuário para aparecer em "Acessos recentes" no admin.
            try {
                $pdo->prepare('INSERT INTO acessos (perfil_id) VALUES (?)')->execute([(int)$p['id']]);
            } catch (PDOException $ex) {
                // O login continua funcionando mesmo se a tabela de acessos ainda não existir.
            }

            flash('Bem-vindo(a) de volta, ' . $p['nome'] . '.');
            redirecionar(volta_seguro('index.php'));
        }
    } else {
        $erro = $erro ?: 'E-mail ou senha incorretos.';
    }
}

$titulo = 'Entrar';
$pagina = '';
include 'inc/topo.php';
?>
<div class="formbox" style="max-width:460px">
  <div class="section-title"><h2>Entrar</h2></div>
  <form class="card" method="post" action="login.php">
    <?= csrf_campo() ?>
    <input type="hidden" name="volta" value="<?= e(volta_seguro('index.php')) ?>">
    <div class="field <?= $erro ? 'err' : '' ?>">
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" value="<?= e($email) ?>" required autofocus>
    </div>
    <div class="field <?= $erro ? 'err' : '' ?>">
      <label for="senha">Senha</label>
      <input id="senha" name="senha" type="password" required>
      <div class="err-msg"><?= e($erro) ?></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary">Entrar</button>
      <a class="btn btn-ghost" href="perfil_form.php">Criar cadastro</a>
    </div>
    <p class="dica">O mesmo acesso serve para contas comuns e administradores. Se o e-mail for de um administrador, o painel administrativo será aberto automaticamente.</p>
  </form>
</div>
<?php include 'inc/rodape.php'; ?>
