<?php
/* Lista de candidatos de uma vaga (só quem publicou a vaga vê). */
require 'config.php';
exigir_login();
$u  = usuario();
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM vagas WHERE id = ? AND perfil_id = ?');
$st->execute([$id, $u['id']]);
$vaga = $st->fetch();
if (!$vaga) { flash('Vaga não encontrada.', 'erro'); redirecionar('vagas.php'); }

$st = $pdo->prepare('SELECT p.*, c.criado AS quando FROM candidaturas c JOIN perfis p ON p.id = c.perfil_id WHERE c.vaga_id = ? ORDER BY c.criado DESC');
$st->execute([$id]);
$cands = $st->fetchAll();

$titulo = 'Candidatos';
$pagina = 'vagas';
include 'inc/topo.php';
?>
<div class="section-title">
  <h2>Candidatos</h2>
  <span><?= count($cands) ?> <?= count($cands) == 1 ? 'pessoa' : 'pessoas' ?></span>
</div>
<div class="card">
  <p class="box-title"><?= e($vaga['titulo']) ?> <span class="meta">· <?= e($vaga['cidade']) ?></span></p>
  <?php if (!$cands): ?><p class="meta">Ninguém se candidatou ainda.</p><?php endif; ?>
  <?php foreach ($cands as $c): ?>
    <div class="cand-row">
      <?= avatar($c, 'sm') ?>
      <div style="min-width:0;flex:1">
        <a href="perfil_ver.php?id=<?= (int)$c['id'] ?>" class="nm2"><?= e($c['nome']) ?></a>
        <small class="meta" style="display:block"><?= e($c['categoria']) ?> · <?= e(cidade_curta($c['cidade'])) ?> · <?= e($c['nivel']) ?> · <?= quando($c['quando']) ?></small>
      </div>
      <a class="btn btn-ghost btn-sm" href="mailto:<?= e($c['email']) ?>">Enviar e-mail</a>
    </div>
  <?php endforeach; ?>
  <p style="margin-top:16px"><a class="btn btn-ghost btn-sm" href="vagas.php">← Voltar às vagas</a></p>
</div>
<?php include 'inc/rodape.php'; ?>
