<?php
/* DETALHE de um perfil (READ): dados de contato, vagas e publicações dele. */
require 'config.php';
$u  = usuario();
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM perfis WHERE id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { flash('Perfil não encontrado.', 'erro'); redirecionar('perfis.php'); }

$st = $pdo->prepare('SELECT * FROM vagas WHERE perfil_id = ? ORDER BY criado DESC');
$st->execute([$id]);
$vagas = $st->fetchAll();

$st = $pdo->prepare('SELECT * FROM posts WHERE perfil_id = ? ORDER BY criado DESC LIMIT 5');
$st->execute([$id]);
$posts = $st->fetchAll();

$meu    = $u && $u['id'] == $p['id'];
$titulo = $p['nome'];
$pagina = 'perfis';
include 'inc/topo.php';
?>
<div class="card">
  <div class="detail-top">
    <?= avatar($p, 'lg') ?>
    <div style="flex:1;min-width:220px">
      <h1><?= e($p['nome']) ?> <?php if ($p['verificado']): ?><span class="stamp">conferido</span><?php endif; ?></h1>
      <div class="meta" style="margin:6px 0 8px"><?= e($p['tipo']) ?> · <?= e($p['categoria']) ?> · <?= e($p['cidade']) ?></div>
      <?= nivel_html($p['nivel']) ?>
    </div>
    <?php if ($meu): ?>
    <div class="form-actions" style="margin:0">
      <a class="btn btn-ghost btn-sm" href="perfil_form.php?id=<?= $id ?>">Editar</a>
      <form class="inline" method="post" action="perfil_excluir.php" onsubmit="return confirm('Apagar seu cadastro? Somem também suas vagas e publicações.')">
        <?= csrf_campo() ?><input type="hidden" name="id" value="<?= $id ?>">
        <button class="btn btn-danger btn-sm">Apagar cadastro</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <p style="margin:18px 0 12px;max-width:66ch"><?= e($p['bio']) ?></p>
  <div class="tags" style="padding:0 0 16px"><?php foreach (tags_array($p['tags']) as $t): ?><span class="chip"><?= e($t) ?></span><?php endforeach; ?></div>

  <dl class="kv">
    <dt>E-mail</dt><dd><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></dd>
    <?php if ($p['telefone'] !== ''): ?><dt>Telefone</dt><dd><?= e($p['telefone']) ?></dd><?php endif; ?>
    <?php if ($p['site'] !== ''): ?><dt>Site</dt><dd><a href="<?= e(link_externo($p['site'])) ?>" target="_blank" rel="noopener"><?= e($p['site']) ?></a></dd><?php endif; ?>
    <dt>Cadastrado</dt><dd><?= date('d/m/Y', strtotime($p['criado'])) ?></dd>
  </dl>
</div>

<div class="stack" style="margin-top:18px">
  <div>
    <h3 class="box-title">Vagas abertas (<?= count($vagas) ?>)</h3>
    <?php if (!$vagas): ?><p class="meta">Nenhuma vaga publicada.</p><?php endif; ?>
    <?php foreach ($vagas as $v): ?>
      <article class="vaga">
        <h3><?= e($v['titulo']) ?></h3>
        <div class="meta"><?= e($v['cidade']) ?> · <?= e($v['modalidade']) ?> · <?= e($v['faixa'] ?: 'A combinar') ?></div>
        <div style="margin-top:12px"><a class="btn btn-ghost btn-sm" href="vagas.php?q=<?= urlencode($v['titulo']) ?>">Ver na lista de vagas</a></div>
      </article>
    <?php endforeach; ?>
  </div>

  <div>
    <h3 class="box-title">Últimas publicações</h3>
    <?php if (!$posts): ?><p class="meta">Ainda não publicou nada.</p><?php endif; ?>
    <div class="feed">
    <?php foreach ($posts as $po): ?>
      <article class="post k-servico" style="grid-template-columns:1fr">
        <div class="post-body">
          <div class="meta"><?= e($po['tipo']) ?> · <?= quando($po['criado']) ?></div>
          <p><?= e($po['texto']) ?></p>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include 'inc/rodape.php'; ?>
