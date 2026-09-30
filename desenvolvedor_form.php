<?php
/* VAGAS — lista de todas as vagas do site, com busca, ver candidatos e apagar. */
require 'config.php';
exigir_admin();
$q = trim($_GET['q'] ?? '');
$onde = ''; $par = [];
if ($q !== '') { $onde = ' WHERE (v.titulo LIKE ? OR p.nome LIKE ?)'; $par = ["%$q%", "%$q%"]; }
$st = $pdo->prepare("SELECT v.*, p.nome AS empresa, p.foto,
        (SELECT COUNT(*) FROM candidaturas c WHERE c.vaga_id = v.id) AS n_cand
      FROM vagas v JOIN perfis p ON p.id = v.perfil_id$onde ORDER BY v.criado DESC");
$st->execute($par);
$lista = $st->fetchAll();

$titulo = 'Vagas';
$paginaAdm = 'vagas';
include 'inc/topo.php';
?>
<div class="section-title"><h2>Vagas</h2><span><?= count($lista) ?> <?= count($lista) == 1 ? 'vaga' : 'vagas' ?></span></div>

<form class="toolbar" method="get" action="vagas.php">
  <label class="search" style="flex:1;min-width:200px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    <input name="q" value="<?= e($q) ?>" placeholder="Título da vaga ou empresa">
  </label>
  <button class="btn btn-primary btn-sm">Buscar</button>
</form>

<div class="tbl-wrap">
<table class="tbl">
  <thead><tr><th>Vaga</th><th>Empresa</th><th>Cidade</th><th>Candidatos</th><th>Publicada</th><th></th></tr></thead>
  <tbody>
  <?php if (!$lista): ?><tr><td colspan="6" class="meta" style="text-align:center;padding:26px">Nenhuma vaga encontrada.</td></tr><?php endif; ?>
  <?php foreach ($lista as $v): ?>
    <tr>
      <td><b style="font-size:13.5px"><?= e($v['titulo']) ?></b></td>
      <td class="meta"><a href="../perfil_ver.php?id=<?= (int)$v['perfil_id'] ?>" target="_blank"><?= e($v['empresa']) ?></a></td>
      <td class="meta"><?= e(cidade_curta($v['cidade'])) ?></td>
      <td class="meta"><?= (int)$v['n_cand'] ?></td>
      <td class="meta"><?= quando($v['criado']) ?></td>
      <td class="acoes">
        <a class="btn btn-ghost btn-sm" href="vaga_candidatos.php?id=<?= (int)$v['id'] ?>">Candidatos</a>
        <form action="vaga_excluir.php" method="post" onsubmit="return confirm('Apagar a vaga &quot;<?= e(addslashes($v['titulo'])) ?>&quot;?')">
          <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
          <button class="btn btn-danger btn-sm">Apagar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php include 'inc/rodape.php'; ?>
