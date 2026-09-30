<?php
/* PUBLICAÇÕES — todos os posts da Praça (o "comentário"/publicação do
   protótipo), com busca e apagar. */
require 'config.php';
exigir_admin();
$q = trim($_GET['q'] ?? '');
$onde = ''; $par = [];
if ($q !== '') { $onde = ' WHERE (po.texto LIKE ? OR pf.nome LIKE ?)'; $par = ["%$q%", "%$q%"]; }
$st = $pdo->prepare("SELECT po.*, pf.nome, pf.foto FROM posts po JOIN perfis pf ON pf.id = po.perfil_id$onde ORDER BY po.criado DESC LIMIT 200");
$st->execute($par);
$lista = $st->fetchAll();

$titulo = 'Publicações';
$paginaAdm = 'posts';
include 'inc/topo.php';
?>
<div class="section-title"><h2>Publicações</h2><span><?= count($lista) ?> mostradas (máx. 200)</span></div>

<form class="toolbar" method="get" action="posts.php">
  <label class="search" style="flex:1;min-width:200px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    <input name="q" value="<?= e($q) ?>" placeholder="Texto da publicação ou autor">
  </label>
  <button class="btn btn-primary btn-sm">Buscar</button>
</form>

<div class="feed">
<?php if (!$lista): ?><div class="empty"><h3>Nada por aqui</h3><p>Nenhuma publicação encontrada.</p></div><?php endif; ?>
<?php foreach ($lista as $p): $cl = $p['tipo'] === 'Vaga' ? 'k-vaga' : ($p['tipo'] === 'Marco' ? 'k-conquista' : 'k-servico'); ?>
  <article class="post <?= $cl ?>">
    <a href="../perfil_ver.php?id=<?= (int)$p['perfil_id'] ?>" target="_blank"><?= avatar($p) ?></a>
    <div class="post-body">
      <div class="who"><b><?= e($p['nome']) ?></b><span class="meta"><?= quando($p['criado']) ?> · <?= e($p['tipo']) ?></span></div>
      <p><?= e($p['texto']) ?></p>
      <div class="post-actions">
        <form class="inline" method="post" action="post_excluir.php" onsubmit="return confirm('Apagar esta publicação?')">
          <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="act">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/></svg>Apagar
          </button>
        </form>
      </div>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php include 'inc/rodape.php'; ?>
