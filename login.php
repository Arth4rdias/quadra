<?php
/* LOGIN ÚNICO — usuários e administradores entram pela mesma tela. */
require 'config.php';

// Se já houver uma sessão válida, manda cada tipo para o seu ambiente.
if (admin_usuario()) redirecionar('admin/index.php');
if (usuario()) redirecionar('index.php');

$erro  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // 1) Primeiro verifica se a conta é de administrador.
    try {
        $stAdmin = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stAdmin->execute([$email]);
        $admin = $stAdmin->fetch();
    } catch (PDOException $ex) {
        $admin = null;
    }

    if ($admin && password_verify($senha, $admin['senha'])) {
        session_regenerate_id(true);
        unset($_SESSION['uid']);
        $_SESSION['admin_id'] = (int)$admin['id'];
        flash('Bem-vindo(a) ao painel, ' . $admin['nome'] . '.');
        redirecionar('admin/index.php');
    }

    // 2) Caso não seja admin, verifica a conta normal.
    $st = $pdo->prepare('SELECT * FROM perfis WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $p = $st->fetch();

    if ($p && password_verify($senha, $p['senha'])) {
        if (!empty($p['bloqueado'])) {
            $erro = 'Esta conta está temporariamente suspensa.';
        } else {
            session_regenerate_id(true);
            unset($_SESSION['admin_id']);
            $_SESSION['uid'] = (int)$p['id'];

            // Registra o acesso do usuário para o painel administrativo.
            try {
                $pdo->prepare('INSERT INTO acessos (perfil_id) VALUES (?)')->execute([(int)$p['id']]);
            } catch (PDOException $ex) {
                // O login continua funcionando mesmo se a tabela de acessos não existir.
            }

            flash('Bem-vindo(a) de volta, ' . $p['nome'] . '.');
            redirecionar(volta_seguro('index.php'));
        }
    } else if ($erro === '') {
        $erro = 'E-mail ou senha incorretos.';
    }
}

$titulo = 'Entrar';
$pagina = '';
include 'inc/topo.php';
?>
<div class="formbox" style="max-width:460px">
  <div class="section-title">
    <h2>Entrar</h2>
    <span>usuário ou administrador</span>
  </div>
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
    <p class="dica">A mesma tela identifica automaticamente se a conta é de usuário ou administrador.</p>
  </form>
</div>
<?php include 'inc/rodape.php'; ?>
