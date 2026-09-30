<?php
/* PULSO — painel com números e gráficos de barras (consultas com COUNT/GROUP BY). */
require 'config.php';
$titulo = 'Pulso';
$pagina = 'painel';

$t = $pdo->query("SELECT (SELECT COUNT(*) FROM perfis) AS perfis, (SELECT COUNT(*) FROM vagas) AS vagas,
                         (SELECT COUNT(*) FROM posts) AS posts, (SELECT COUNT(*) FROM candidaturas) AS cands,
                         (SELECT COUNT(DISTINCT cidade) FROM perfis) AS cidades")->fetch();
$cidades = $pdo->query("SELECT SUBSTRING_INDEX(cidade, ',', 1) AS k, COUNT(*) AS n FROM perfis GROUP BY k ORDER BY n DESC, k LIMIT 7")->fetchAll();
$areas   = $pdo->query("SELECT categoria AS k, COUNT(*) AS n FROM perfis GROUP BY categoria ORDER BY n DESC, k LIMIT 7")->fetchAll();
$niveis  = array_column($pdo->query("SELECT nivel AS k, COUNT(*) AS n FROM perfis GROUP BY nivel")->fetchAll(), 'n', 'k');
$totalP  = max(1, (int)$t['perfis']);

function barras($linhas, $cor) {
    $max = $linhas ? max(array_column($linhas, 'n')) : 1;
    foreach ($linhas as $l) {
        echo '<div class="pulse-row"><span class="nm">' . e($l['k']) . '</span><span class="bar"><i style="width:'
           . round($l['n'] / $max * 100) . '%;background:' . $cor . '"></i></span><span class="vl">' . (int)$l['n'] . '</span></div>';
    }
    if (!$linhas) echo '<p class="meta">Sem dados ainda.</p>';
}

include 'inc/topo.php';
?>
<div class="section-title"><h2>Pulso da quadra</h2><span>atualizado agora</span></div>

<div class="hero" style="margin-bottom:18px">
  <div class="hero-in">
    <div class="hero-stats" style="margin-top:0">
      <div class="stat t"><b><?= (int)$t['perfis'] ?></b><span>cadastros</span></div>
      <div class="stat h"><b><?= (int)$t['vagas'] ?></b><span>vagas</span></div>
      <div class="stat a"><b><?= (int)$t['posts'] ?></b><span>publicações</span></div>
      <div class="stat"><b><?= (int)$t['cands'] ?></b><span>candidaturas</span></div>
      <div class="stat"><b><?= (int)$t['cidades'] ?></b><span>cidades ativas</span></div>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(290px,1fr))">
  <div class="card">
    <h3 class="box-title" style="margin-bottom:14px">Cidades com mais gente</h3>
    <?php barras($cidades, 'var(--teal)'); ?>
  </div>
  <div class="card">
    <h3 class="box-title" style="margin-bottom:14px">Áreas mais procuradas</h3>
    <?php barras($areas, 'var(--hot)'); ?>
  </div>
  <div class="card">
    <h3 class="box-title" style="margin-bottom:14px">Em que estágio estão</h3>
    <?php foreach (NIVEIS as $i => $n): $q = (int)($niveis[$n] ?? 0); ?>
      <div class="pulse-row"><span class="nm"><?= e($n) ?></span>
        <span class="bar"><i style="width:<?= round($q / $totalP * 100) ?>%;background:<?= ['var(--teal)', 'var(--amber)', 'var(--hot)'][$i] ?>"></i></span>
        <span class="vl"><?= $q ?></span></div>
    <?php endforeach; ?>
    <p class="meta" style="margin-top:14px">Quem está começando tem lugar aqui: <?= (int)($niveis['Iniciante'] ?? 0) ?> dos <?= (int)$t['perfis'] ?> cadastros.</p>
  </div>
</div>
<?php include 'inc/rodape.php'; ?>
