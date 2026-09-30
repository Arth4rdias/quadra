<?php
/* CADASTROS — lista de todos os perfis do site, com busca e filtro,
   e ações de moderação: suspender/reativar login e apagar cadastro. */
require 'config.php';
exigir_admin();
$q      = trim($_GET['q'] ?? '');
$f_tipo = in_array($_GET['tipo'] ?? '', TIPOS_PERFIL, true) ? $_GET['tipo'] : '';
$f_st   = $_GET['status'] ?? '';

$onde = []; $par = [];
if ($q !== '')      { $onde[] = '(nome LIKE ? OR email LIKE ? OR cidade LIKE ?)'; array_push($par, "%$q%", "%$q%", "%$q%"); }
if ($f_tipo !== '')  { $onde[] = 'tipo = ?'; $par[] = $f_tipo; }
if ($f_st === 'bloqueado')   $onde[] = 'bloqueado = 1';
if ($f_st === 'verificado')  $onde[] = 'verificado = 1';
$sql = 'SELECT * FROM perfis' . ($onde ? ' WHERE ' . implode(' AND ', $onde) : '') . ' ORDER BY criado DESC';
$st = $pdo->prepare($sql);
$st->execute($par);
$lista = $st->fetchAll();

$titulo = 'Cadastros';
$paginaAdm = 'perfis';
include 'inc/topo.php';
?>
<div class="section-title"><h2>Cadastros</h2><span><?= count($lista) ?> <?= count($lista) == 1 ? 'perfil' : 'perfis' ?></span></div>

<form class="toolbar" method="get" action="perfis.php">
  <label class="search" style="flex:1;min-width:200px">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    <input name="q" value="<?= e($q) ?>" placeholder="Nome, e-mail ou cidade">
  </label>
  <select class="sel" name="tipo" onchange="this.form.submit()">
    <option value="">Empresas e pessoas</option>
    <option value="Empresa" <?= $f_tipo === 'Empresa' ? 'selected' : '' ?>>Só empresas</option>
    <option value="Prestador" <?= $f_tipo === 'Prestador' ? 'selected' : '' ?>>Só prestadores</option>
  </select>
  <select class="sel" name="status" onchange="this.form.submit()">
    <option value="">Qualquer status</option>
    <option value="bloqueado" <?= $f_st === 'bloqueado' ? 'selected' : '' ?>>Suspensos</option>
    <option value="verificado" <?= $f_st === 'verificado' ? 'selected' : '' ?>>Conferidos</option>
  </select>
  <button class="btn btn-primary btn-sm">Filtrar</button>
</form>

<div class="tbl-wrap">
<table class="tbl">
  <thead><tr><th>Cadastro</th><th>Cidade</th><th>Tipo</th><th>Cadastrado</th><th></th></tr></thead>
  <tbody>
  <?php if (!$lista): ?><tr><td colspan="5" class="meta" style="text-align:center;padding:26px">Nenhum cadastro encontrado.</td></tr><?php endif; ?>
  <?php foreach ($lista as $p): ?>
    <tr>
      <td>
        <a class="row-flex" href="../perfil_ver.php?id=<?= (int)$p['id'] ?>" target="_blank" style="text-decoration:none">
          <?= avatar($p, 'sm') ?><span class="nm2" style="color:var(--ink)"><?= e($p['nome']) ?></span>
        </a>
        <?php if ($p['bloqueado']): ?><span class="bloq-tag">suspenso</span><?php endif; ?>
        <?php if ($p['verificado']): ?><span class="stamp" style="margin-left:6px">conferido</span><?php endif; ?>
      </td>
      <td class="meta"><?= e(cidade_curta($p['cidade'])) ?></td>
      <td class="meta"><?= e($p['tipo']) ?> · <?= e($p['categoria']) ?></td>
      <td class="meta"><?= quando($p['criado']) ?></td>
      <td class="acoes">
        <form action="perfil_bloquear.php" method="post">
          <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn <?= $p['bloqueado'] ? 'btn-ghost' : 'btn-danger' ?> btn-sm"><?= $p['bloqueado'] ? 'Reativar' : 'Suspender' ?></button>
        </form>
        <form action="perfil_excluir.php" method="post" onsubmit="return confirm('Apagar o cadastro de <?= e(addslashes($p['nome'])) ?>? Vagas e publicações dele(a) somem junto. Não dá para desfazer.')">
          <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn btn-danger btn-sm">Apagar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php include 'inc/rodape.php'; ?>
