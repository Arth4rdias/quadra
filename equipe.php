<?php
/* EQUIPE — página pública que mostra quem desenvolveu o site.
   Os cartões são cadastrados pelo admin em admin/desenvolvedores.php. */
require 'config.php';
$titulo = 'Equipe';
$pagina = 'equipe';
$lista = $pdo->query('SELECT * FROM desenvolvedores ORDER BY ordem ASC, criado ASC')->fetchAll();
include 'inc/topo.php';
?>
<style>
/* estilo só desta página — não mexe no css/style.css compartilhado */
.dev-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.dev-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-l);padding:24px 20px;text-align:center}
.dev-card .avatar{margin:0 auto 14px;width:72px;height:72px;font-size:26px;border-radius:20px}
.dev-card h3{font-size:17px;margin-bottom:3px}
.dev-card .papel{font-size:12.5px;color:var(--teal);font-weight:600;margin-bottom:10px}
.dev-card p{font-size:13.5px;color:var(--ink-2);margin:0 0 12px}
</style>

<div class="section-title">
  <h2>Quem fez a Quadra</h2>
  <span><?= count($lista) ?> <?= count($lista) == 1 ? 'pessoa' : 'pessoas' ?></span>
</div>

<?php if (!$lista): ?>
  <div class="empty"><h3>Equipe ainda não cadastrada</h3><p>Assim que o administrador adicionar a equipe, ela aparece aqui.</p></div>
<?php else: ?>
<div class="dev-grid">
  <?php foreach ($lista as $d): ?>
    <div class="dev-card">
      <?= avatar(['nome' => $d['nome'], 'foto' => $d['foto']]) ?>
      <h3><?= e($d['nome']) ?></h3>
      <div class="papel"><?= e($d['papel'] ?: 'Desenvolvedor(a)') ?></div>
      <?php if ($d['bio'] !== ''): ?><p><?= e($d['bio']) ?></p><?php endif; ?>
      <?php if ($d['link'] !== ''): ?><a class="btn btn-ghost btn-sm" href="<?= e(link_externo($d['link'])) ?>" target="_blank" rel="noopener">Ver perfil</a><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include 'inc/rodape.php'; ?>
