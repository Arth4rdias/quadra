<?php
/* CRIAR e EDITAR publicação.
   Sem ?id  → cria (o formulário da Praça envia para cá)
   Com ?id  → edita (só o autor pode) */
require 'config.php';
exigir_login();
$u  = usuario();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$post = null;

if ($id) {
    $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch();
    if (!$post || $post['perfil_id'] != $u['id']) {
        flash('Publicação não encontrada.', 'erro');
        redirecionar('index.php');
    }
}

$v = ['texto' => $post['texto'] ?? '', 'tipo' => $post['tipo'] ?? TIPOS_POST[0]];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post();
    $v['texto'] = trim($_POST['texto'] ?? '');
    $v['tipo']  = in_array($_POST['tipo'] ?? '', TIPOS_POST, true) ? $_POST['tipo'] : TIPOS_POST[0];
    $len = mb_strlen($v['texto']);
    if ($len < 1)        $erro = 'Escreva alguma coisa antes de publicar.';
    elseif ($len > 420)  $erro = 'A publicação passa de 420 caracteres.';

    if ($erro === '') {
        if ($post) {
            $pdo->prepare('UPDATE posts SET texto = ?, tipo = ? WHERE id = ?')->execute([$v['texto'], $v['tipo'], $id]);
            flash('Publicação atualizada.');
        } else {
            $pdo->prepare('INSERT INTO posts (perfil_id, tipo, texto) VALUES (?, ?, ?)')->execute([$u['id'], $v['tipo'], $v['texto']]);
            flash('Publicado na praça.');
        }
        redirecionar('index.php');
    }
}

$titulo = $post ? 'Editar publicação' : 'Nova publicação';
$pagina = 'praca';
include 'inc/topo.php';
?>
<div class="formbox">
  <div class="section-title"><h2><?= e($titulo) ?></h2></div>
  <form class="card" method="post" action="post_form.php<?= $post ? '?id=' . $id : '' ?>">
    <?= csrf_campo() ?>
    <?php if ($post): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
    <div class="field <?= $erro ? 'err' : '' ?>">
      <label for="texto">O que aconteceu?</label>
      <textarea id="texto" name="texto" maxlength="420" required><?= e($v['texto']) ?></textarea>
      <div class="err-msg"><?= e($erro) ?></div>
    </div>
    <div class="field">
      <label>Tipo</label>
      <div class="segmented" style="display:inline-flex">
        <?php foreach (TIPOS_POST as $t): ?>
          <label><input type="radio" name="tipo" value="<?= $t ?>" <?= $v['tipo'] === $t ? 'checked' : '' ?>><span><?= $t ?></span></label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary"><?= $post ? 'Salvar alterações' : 'Publicar' ?></button>
      <a class="btn btn-ghost" href="index.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include 'inc/rodape.php'; ?>
