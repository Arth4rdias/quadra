<?php
/* VAGAS — lista com filtros, compatibilidade e candidatura (READ).
   Criar/editar: vaga_form.php · Apagar: vaga_excluir.php · Candidatar: candidatar.php */
require 'config.php';
$titulo = 'Vagas';
$pagina = 'vagas';
$u   = usuario();
$cid = cidade_ativa();
$q   = trim($_GET['q'] ?? '');
$f_cat = in_array($_GET['categoria'] ?? '', CATS, true)   ? $_GET['categoria'] : '';
$f_niv = in_array($_GET['nivel'] ?? '', NIVEIS, true)     ? $_GET['nivel'] : '';
$f_mod = in_array($_GET['modalidade'] ?? '', MODS, true)  ? $_GET['modalidade'] : '';

$onde = []; $par = [];
if ($cid !== '')   { $onde[] = 'v.cidade = ?';     $par[] = $cid; }
if ($f_cat !== '') { $onde[] = 'v.categoria = ?';  $par[] = $f_cat; }
if ($f_niv !== '') { $onde[] = 'v.nivel = ?';      $par[] = $f_niv; }
if ($f_mod !== '') { $onde[] = 'v.modalidade = ?'; $par[] = $f_mod; }
if ($q !== '') {
    $onde[] = '(v.titulo LIKE ? OR v.descricao LIKE ? OR v.tags LIKE ? OR p.nome LIKE ?)';
    array_push($par, "%$q%", "%$q%", "%$q%", "%$q%");
}
$sql = 'SELECT v.*, p.nome AS empresa, p.foto,
          (SELECT COUNT(*) FROM candidaturas c WHERE c.vaga_id = v.id) AS n_cand,
          (SELECT COUNT(*) FROM candidaturas c WHERE c.vaga_id = v.id AND c.perfil_id = ?) AS eu_cand
        FROM vagas v JOIN perfis p ON p.id = v.perfil_id'
     . ($onde ? ' WHERE ' . implode(' AND ', $onde) : '') . ' ORDER BY v.criado DESC';
$st = $pdo->prepare($sql);
$st->execute(array_merge([$u['id'] ?? 0], $par));
$lista = $st->fetchAll();

include 'inc/topo.php';
?>
<div class="section-title">
  <h2>Vagas abertas</h2>
  <span><?= count($lista) ?> <?= count($lista) == 1 ? 'vaga' : 'vagas' ?></span>
</div>

<div class="card" style="margin-bottom:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
  <div style="flex:1;min-width:200px">
    <?php if ($u): ?>
      <b style="font-size:14px">Compatibilidade calculada para <?= e($u['nome']) ?></b>
      <div class="meta">Comparamos área, cidade, estágio e o que você sabe fazer.</div>
    <?php else: ?>
      <b style="font-size:14px">Entre para ver sua compatibilidade com cada vaga</b>
      <div class="meta">Ela é calculada a partir do seu perfil.</div>
    <?php endif; ?>
  </div>
  <a class="btn btn-primary btn-sm" href="vaga_form.php">Publicar vaga</a>
</div>

<form class="toolbar" method="get" action="vagas.php">
  <input type="hidden" name="q" value="<?= e($q) ?>">
  <select class="sel" name="categoria" onchange="this.form.submit()"><option value="">Todas as áreas</option><?= opcoes(CATS, $f_cat) ?></select>
  <select class="sel" name="nivel" onchange="this.form.submit()"><option value="">Qualquer estágio</option><?= opcoes(NIVEIS, $f_niv) ?></select>
  <select class="sel" name="modalidade" onchange="this.form.submit()"><option value="">Qualquer modalidade</option><?= opcoes(MODS, $f_mod) ?></select>
  <?php if ($f_cat || $f_niv || $f_mod): ?><a class="btn btn-ghost btn-sm" href="vagas.php?q=<?= urlencode($q) ?>">Limpar filtros</a><?php endif; ?>
  <noscript><button class="btn btn-primary btn-sm">Filtrar</button></noscript>
</form>

<?php if (!$lista): ?>
<div class="empty">
  <h3>Nenhuma vaga por aqui</h3>
  <p>Nenhuma vaga bate com esses filtros nesta cidade.</p>
  <a class="btn btn-primary" href="vaga_form.php">Publicar a primeira</a>
</div>
<?php endif; ?>

<?php foreach ($lista as $v):
    $m   = compat($v);
    $dono = $u && $u['id'] == $v['perfil_id'];
?>
<article class="vaga">
  <div class="vaga-head">
    <div style="min-width:0;display:flex;gap:12px;align-items:flex-start">
      <a href="perfil_ver.php?id=<?= (int)$v['perfil_id'] ?>" style="flex-shrink:0"><?= avatar(['nome' => $v['empresa'], 'foto' => $v['foto']], 'sm') ?></a>
      <div style="min-width:0">
        <h3><?= e($v['titulo']) ?></h3>
        <div class="meta"><?= e($v['empresa']) ?> · <?= e($v['cidade']) ?> · <?= e($v['modalidade']) ?></div>
      </div>
    </div>
    <?php if ($u): ?><div class="ring" style="--p:<?= $m ?>" data-p="<?= $m ?>" title="Compatibilidade <?= $m ?>%"></div><?php endif; ?>
  </div>
  <p style="margin:10px 0 0;color:var(--ink-2);max-width:68ch"><?= e($v['descricao']) ?></p>
  <div class="tags" style="padding:12px 0 0">
    <span class="chip hot"><?= e($v['faixa'] ?: 'A combinar') ?></span>
    <span class="chip"><?= e($v['nivel']) ?></span>
    <span class="chip teal"><?= e($v['categoria']) ?></span>
    <?php foreach (tags_array($v['tags']) as $t): ?><span class="chip"><?= e($t) ?></span><?php endforeach; ?>
  </div>
  <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
    <?php if ($dono): ?>
      <a class="btn btn-primary btn-sm" href="candidatos.php?id=<?= (int)$v['id'] ?>">Ver candidatos (<?= (int)$v['n_cand'] ?>)</a>
      <a class="btn btn-ghost btn-sm" href="vaga_form.php?id=<?= (int)$v['id'] ?>">Editar</a>
      <form class="inline" method="post" action="vaga_excluir.php" onsubmit="return confirm('Apagar esta vaga? Os candidatos também saem da lista.')">
        <?= csrf_campo() ?><?= volta_campo() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
        <button class="btn btn-danger btn-sm">Apagar</button>
      </form>
    <?php else: ?>
      <form class="inline" method="post" action="candidatar.php">
        <?= csrf_campo() ?><?= volta_campo() ?><input type="hidden" name="vaga_id" value="<?= (int)$v['id'] ?>">
        <button class="btn <?= $v['eu_cand'] ? 'btn-ghost' : 'btn-primary' ?> btn-sm"><?= $v['eu_cand'] ? 'Candidatura enviada · cancelar' : 'Quero me candidatar' ?></button>
      </form>
      <?php if ($v['contato'] !== ''): ?><span class="meta" style="align-self:center">Contato: <?= e($v['contato']) ?></span><?php endif; ?>
    <?php endif; ?>
  </div>
</article>
<?php endforeach; ?>
<?php include 'inc/rodape.php'; ?>
