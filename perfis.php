<?php
/* QUEM FAZ O QUÊ — lista de perfis com filtros e busca (READ). */
require 'config.php';
$titulo = 'Quem faz o quê';
$pagina = 'perfis';
$u   = usuario();
$cid = cidade_ativa();
$q   = trim($_GET['q'] ?? '');
$f_tipo = in_array($_GET['tipo'] ?? '', TIPOS_PERFIL, true) ? $_GET['tipo'] : '';
$f_cat  = in_array($_GET['categoria'] ?? '', CATS, true)    ? $_GET['categoria'] : '';
$f_niv  = in_array($_GET['nivel'] ?? '', NIVEIS, true)      ? $_GET['nivel'] : '';
$ordem  = $_GET['ordem'] ?? 'recentes';

$onde = []; $par = [];
if ($cid !== '')    { $onde[] = 'cidade = ?';    $par[] = $cid; }
if ($f_tipo !== '') { $onde[] = 'tipo = ?';      $par[] = $f_tipo; }
if ($f_cat !== '')  { $onde[] = 'categoria = ?'; $par[] = $f_cat; }
if ($f_niv !== '')  { $onde[] = 'nivel = ?';     $par[] = $f_niv; }
if ($q !== '') {
    $onde[] = '(nome LIKE ? OR bio LIKE ? OR categoria LIKE ? OR tags LIKE ?)';
    array_push($par, "%$q%", "%$q%", "%$q%", "%$q%");
}
$ordens = [
    'nome'     => 'nome ASC',
    'nivel'    => "FIELD(nivel, 'Avançado', 'Intermediário', 'Iniciante'), nome",
    'recentes' => 'criado DESC',
];
$sql = 'SELECT * FROM perfis' . ($onde ? ' WHERE ' . implode(' AND ', $onde) : '') . ' ORDER BY ' . ($ordens[$ordem] ?? $ordens['recentes']);
$st = $pdo->prepare($sql);
$st->execute($par);
$lista = $st->fetchAll();

include 'inc/topo.php';
?>
<div class="section-title">
  <h2>Quem faz o quê</h2>
  <span><?= count($lista) ?> <?= count($lista) == 1 ? 'resultado' : 'resultados' ?></span>
</div>

<form class="toolbar" method="get" action="perfis.php">
  <input type="hidden" name="q" value="<?= e($q) ?>">
  <select class="sel" name="tipo" onchange="this.form.submit()">
    <option value="">Empresas e pessoas</option>
    <option value="Empresa"   <?= $f_tipo === 'Empresa' ? 'selected' : '' ?>>Só empresas</option>
    <option value="Prestador" <?= $f_tipo === 'Prestador' ? 'selected' : '' ?>>Só prestadores</option>
  </select>
  <select class="sel" name="categoria" onchange="this.form.submit()">
    <option value="">Todas as áreas</option><?= opcoes(CATS, $f_cat) ?>
  </select>
  <select class="sel" name="nivel" onchange="this.form.submit()">
    <option value="">Qualquer estágio</option><?= opcoes(NIVEIS, $f_niv) ?>
  </select>
  <select class="sel" name="ordem" onchange="this.form.submit()">
    <option value="recentes">Mais recentes</option>
    <option value="nome"  <?= $ordem === 'nome' ? 'selected' : '' ?>>Nome A–Z</option>
    <option value="nivel" <?= $ordem === 'nivel' ? 'selected' : '' ?>>Mais experientes</option>
  </select>
  <?php if ($f_tipo || $f_cat || $f_niv): ?><a class="btn btn-ghost btn-sm" href="perfis.php?q=<?= urlencode($q) ?>">Limpar filtros</a><?php endif; ?>
  <noscript><button class="btn btn-primary btn-sm">Filtrar</button></noscript>
</form>

<?php if ($lista): ?>
<div class="grid">
  <?php foreach ($lista as $p): ?>
  <div class="pcard">
    <a class="pcard-top" href="perfil_ver.php?id=<?= (int)$p['id'] ?>" style="width:100%">
      <?= avatar($p) ?>
      <div style="min-width:0">
        <h3><?= e($p['nome']) ?> <?php if ($p['verificado']): ?><span class="stamp">conferido</span><?php endif; ?></h3>
        <div class="meta"><?= e($p['categoria']) ?> · <?= e(cidade_curta($p['cidade'])) ?></div>
        <div style="margin-top:6px"><?= nivel_html($p['nivel']) ?></div>
      </div>
    </a>
    <p class="bio"><?= e($p['bio']) ?></p>
    <div class="tags"><?php foreach (array_slice(tags_array($p['tags']), 0, 3) as $t): ?><span class="chip"><?= e($t) ?></span><?php endforeach; ?></div>
    <div class="pcard-foot">
      <span class="chip <?= $p['tipo'] === 'Empresa' ? 'teal' : 'amber' ?>"><?= e($p['tipo']) ?></span>
      <span style="margin-left:auto"></span>
      <?php if ($u && $u['id'] == $p['id']): ?><a class="btn btn-ghost btn-sm" href="perfil_form.php?id=<?= (int)$p['id'] ?>">Editar</a><?php endif; ?>
      <a class="btn btn-primary btn-sm" href="perfil_ver.php?id=<?= (int)$p['id'] ?>">Ver perfil</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty">
  <h3>Ninguém aqui ainda</h3>
  <p>Nenhum cadastro bate com esses filtros nesta cidade.</p>
  <a class="btn btn-primary" href="perfil_form.php">Cadastrar agora</a>
</div>
<?php endif; ?>
<?php include 'inc/rodape.php'; ?>
