<?php
/* Candidatos de UMA vaga, vistos pelo admin — pode remover uma candidatura
   isolada sem apagar a vaga nem o cadastro de ninguém. */
require 'config.php';
exigir_admin();
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT v.*, p.nome AS empresa FROM vagas v JOIN perfis p ON p.id = v.perfil_id WHERE v.id = ?');
$st->execute([$id]);
$vaga = $st->fetch();
if (!$vaga) { flash('Vaga não encontrada.', 'erro'); redirecionar('vagas.php'); }

$st = $pdo->prepare('SELECT p.*, c.criado AS quando FROM candidaturas c JOIN perfis p ON p.id = c.perfil_id WHERE c.vaga_id = ? ORDER BY c.criado DESC');
$st->execute([$id]);
$cands = $st->fetchAll();

$titulo = 'Candidatos';
$paginaAdm = 'vagas';
include 'inc/topo.php';
?>
<div class="section-title"><h2>Candidatos</h2><span><?= count($cands) ?> <?= count($cands) == 1 ? 'pessoa' : 'pessoas' ?></span></div>
<div class="card">
  <p class="box-title"><?= e($vaga['titulo']) ?> <span class="meta">· <?= e($vaga['empresa']) ?> · <?= e($vaga['cidade']) ?></span></p>
  <?php if (!$cands): ?><p class="meta">Ninguém se candidatou ainda.</p><?php endif; ?>
  <?php foreach ($cands as $c): ?>
    <div class="cand-row">
      <?= avatar($c, 'sm') ?>
      <div style="min-width:0;flex:1">
        <a href="../perfil_ver.php?id=<?= (int)$c['id'] ?>" class="nm2" target="_blank"><?= e($c['nome']) ?></a>
        <small class="meta" style="display:block"><?= e($c['categoria']) ?> · <?= e(cidade_curta($c['cidade'])) ?> · <?= quando($c['quando']) ?></small>
      </div>
      <form method="post" action="candidatura_excluir.php" onsubmit="return confirm('Remover esta candidatura?')">
        <?= csrf_campo() ?>
        <input type="hidden" name="vaga_id" value="<?= $id ?>"><input type="hidden" name="perfil_id" value="<?= (int)$c['id'] ?>">
        <button class="btn btn-ghost btn-sm">Remover</button>
      </form>
    </div>
  <?php endforeach; ?>
  <p style="margin-top:16px"><a class="btn btn-ghost btn-sm" href="vagas.php">← Voltar às vagas</a></p>
</div>
<?php include 'inc/rodape.php'; ?>
