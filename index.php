<?php
/* PRAÇA — feed de publicações (READ + botão de criar).
   Criar/editar: post_form.php · Apagar: post_excluir.php · Curtir: curtir.php */
require 'config.php';
$titulo = 'Praça';
$pagina = 'praca';
$u   = usuario();
$cid = cidade_ativa();
$q   = trim($_GET['q'] ?? '');

// filtros (cidade + busca)
$onde = []; $par = [];
if ($cid !== '') { $onde[] = 'pf.cidade = ?'; $par[] = $cid; }
if ($q !== '')   { $onde[] = '(po.texto LIKE ? OR pf.nome LIKE ?)'; $par[] = "%$q%"; $par[] = "%$q%"; }
$W = $onde ? ' WHERE ' . implode(' AND ', $onde) : '';

// paginação
$por = 6;
$st = $pdo->prepare('SELECT COUNT(*) FROM posts po JOIN perfis pf ON pf.id = po.perfil_id' . $W);
$st->execute($par);
$total   = (int)$st->fetchColumn();
$paginas = max(1, (int)ceil($total / $por));
$pg      = min(max(1, (int)($_GET['p'] ?? 1)), $paginas);
$off     = ($pg - 1) * $por;

$st = $pdo->prepare("SELECT po.*, pf.nome, pf.foto, pf.cidade, pf.tipo AS ptipo, pf.categoria, pf.verificado,
        po.curtidas_base + (SELECT COUNT(*) FROM curtidas c WHERE c.post_id = po.id) AS curtidas,
        (SELECT COUNT(*) FROM curtidas c WHERE c.post_id = po.id AND c.perfil_id = ?) AS eu_curti
    FROM posts po JOIN perfis pf ON pf.id = po.perfil_id $W
    ORDER BY po.criado DESC LIMIT $por OFFSET $off");
$st->execute(array_merge([$u['id'] ?? 0], $par));
$posts = $st->fetchAll();

$nomeCidade = $cid === '' ? 'Brasil inteiro' : cidade_curta($cid);
$nPerfis = contar('perfis', $cid);
$nVagas  = contar('vagas', $cid);

include 'inc/topo.php';
?>
<section class="hero">
  <div class="hero-in">
    <h1><em>Acontecendo em</em><?= e($nomeCidade) ?></h1>
    <div class="hero-stats">
      <div class="stat t"><b><?= $nPerfis ?></b><span><?= $nPerfis == 1 ? 'perfil cadastrado' : 'perfis cadastrados' ?></span></div>
      <div class="stat h"><b><?= $nVagas ?></b><span><?= $nVagas == 1 ? 'vaga aberta' : 'vagas abertas' ?></span></div>
      <div class="stat a"><b><?= $total ?></b><span><?= $total == 1 ? 'publicação' : 'publicações' ?></span></div>
    </div>
  </div>
</section>

<?php if ($u): ?>
<form class="composer" method="post" action="post_form.php">
  <?= csrf_campo() ?>
  <div class="composer-head">
    <?= avatar($u, 'sm') ?>
    <div style="font-size:13px;color:var(--ink-2)">Publicando como <b style="color:var(--ink)"><?= e($u['nome']) ?></b></div>
  </div>
  <textarea name="texto" maxlength="420" required placeholder="O que sua empresa fez essa semana?"></textarea>
  <div class="composer-foot">
    <div class="segmented">
      <?php foreach (TIPOS_POST as $i => $t): ?>
        <label><input type="radio" name="tipo" value="<?= $t ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= $t ?></span></label>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-primary btn-sm" style="margin-left:auto">Publicar</button>
  </div>
</form>
<?php else: ?>
<div class="card" style="margin-bottom:18px;text-align:center;padding:22px">
  <p style="margin:0 0 12px;color:var(--ink-2)">Entre com seu cadastro para publicar na praça.</p>
  <a class="btn btn-primary btn-sm" href="login.php">Entrar</a>
  <a class="btn btn-ghost btn-sm" href="perfil_form.php">Criar cadastro</a>
</div>
<?php endif; ?>

<div class="feed">
<?php if (!$posts): ?>
  <div class="empty">
    <h3><?= $q !== '' ? 'Nada com esse termo por aqui' : 'A praça está vazia nesta cidade' ?></h3>
    <p><?= $q !== '' ? 'Tente outra palavra ou troque a cidade no topo.' : 'Seja o primeiro a publicar. Todo mundo começa com zero.' ?></p>
  </div>
<?php endif; ?>

<?php foreach ($posts as $p):
    $cl = $p['tipo'] === 'Vaga' ? 'k-vaga' : ($p['tipo'] === 'Marco' ? 'k-conquista' : 'k-servico');
    $meu = $u && $u['id'] == $p['perfil_id'];
?>
  <article class="post <?= $cl ?>">
    <a href="perfil_ver.php?id=<?= (int)$p['perfil_id'] ?>" aria-label="Ver <?= e($p['nome']) ?>"><?= avatar($p) ?></a>
    <div class="post-body">
      <div class="who">
        <b><?= e($p['nome']) ?></b>
        <?php if ($p['verificado']): ?><span class="stamp">conferido</span><?php endif; ?>
        <span class="meta"><?= e(cidade_curta($p['cidade'])) ?> · <?= quando($p['criado']) ?> · <?= e($p['tipo']) ?></span>
      </div>
      <div class="meta"><?= e($p['ptipo']) ?> · <?= e($p['categoria']) ?></div>
      <p><?= e($p['texto']) ?></p>
      <div class="post-actions">
        <form class="inline" method="post" action="curtir.php">
          <?= csrf_campo() ?><?= volta_campo() ?>
          <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">
          <button class="act <?= $p['eu_curti'] ? 'on' : '' ?>" title="Curtir">
            <svg viewBox="0 0 24 24" fill="<?= $p['eu_curti'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M12 20s-7-4.5-7-9a4 4 0 017-2.6A4 4 0 0119 11c0 4.5-7 9-7 9z"/></svg>
            <?= (int)$p['curtidas'] ?>
          </button>
        </form>
        <a class="act" href="perfil_ver.php?id=<?= (int)$p['perfil_id'] ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h16v14H4z"/><path d="M4 6l8 6 8-6"/></svg>Falar
        </a>
        <?php if ($meu): ?>
          <a class="act" href="post_form.php?id=<?= (int)$p['id'] ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16z"/></svg>Editar
          </a>
          <form class="inline" method="post" action="post_excluir.php" onsubmit="return confirm('Apagar esta publicação? Não dá para desfazer.')">
            <?= csrf_campo() ?><?= volta_campo() ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="act">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/></svg>Apagar
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </article>
<?php endforeach; ?>
</div>

<?php if ($paginas > 1): ?>
<div class="pager">
  <?php if ($pg > 1): ?><a class="btn btn-ghost btn-sm" href="?<?= e(http_build_query(['p' => $pg - 1, 'q' => $q])) ?>">← Anterior</a><?php endif; ?>
  <span>Página <?= $pg ?> de <?= $paginas ?></span>
  <?php if ($pg < $paginas): ?><a class="btn btn-ghost btn-sm" href="?<?= e(http_build_query(['p' => $pg + 1, 'q' => $q])) ?>">Próxima →</a><?php endif; ?>
</div>
<?php endif; ?>

<?php include 'inc/rodape.php'; ?>
