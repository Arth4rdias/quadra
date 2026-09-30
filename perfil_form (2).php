<?php
/* Fecha o <main> e monta a coluna lateral direita. */
$u   = usuario();
$cid = cidade_ativa();
$sql = 'SELECT * FROM perfis WHERE id <> ?' . ($cid !== '' ? ' AND cidade = ?' : '') . ' ORDER BY criado DESC LIMIT 4';
$st  = $pdo->prepare($sql);
$st->execute($cid !== '' ? [$u['id'] ?? 0, $cid] : [$u['id'] ?? 0]);
$perto = $st->fetchAll();
$topCidades = $pdo->query("SELECT cidade, COUNT(*) AS n FROM perfis GROUP BY cidade ORDER BY n DESC, cidade LIMIT 5")->fetchAll();
?>
  </main>

  <aside class="side">
    <?php if ($u): ?>
    <div class="card">
      <div style="display:flex;gap:12px;align-items:center">
        <?= avatar($u) ?>
        <div style="min-width:0">
          <div style="font-weight:600;font-size:14.5px"><?= e($u['nome']) ?></div>
          <div class="meta"><?= e(cidade_curta($u['cidade'])) ?> · <?= e($u['categoria']) ?></div>
        </div>
      </div>
      <div style="margin-top:12px"><?= nivel_html($u['nivel']) ?></div>
      <a class="btn btn-ghost btn-sm" style="width:100%;margin-top:12px" href="perfil_form.php?id=<?= (int)$u['id'] ?>">Editar meu perfil</a>
    </div>
    <?php endif; ?>

    <div class="card">
      <h3>Perto de você</h3>
      <?php if ($perto): foreach ($perto as $p): ?>
        <a class="sug" href="perfil_ver.php?id=<?= (int)$p['id'] ?>">
          <?= avatar($p, 'sm') ?>
          <span style="min-width:0"><span class="nm2"><?= e($p['nome']) ?></span><small><?= e($p['categoria']) ?> · <?= e(cidade_curta($p['cidade'])) ?></small></span>
        </a>
      <?php endforeach; else: ?>
        <p class="meta">Ninguém mais nesta cidade ainda. Convide alguém.</p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3>Onde a quadra está cheia</h3>
      <?php foreach ($topCidades as $c): ?>
        <a class="sug" href="index.php?cidade=<?= urlencode($c['cidade']) ?>" style="padding:6px 0">
          <span style="min-width:0"><span class="nm2"><?= e($c['cidade']) ?></span><small><?= (int)$c['n'] ?> <?= $c['n'] == 1 ? 'cadastro' : 'cadastros' ?></small></span>
        </a>
      <?php endforeach; ?>
    </div>

    <p class="meta" style="padding:0 4px">Quadra — projeto escolar em PHP + MySQL.</p>
  </aside>
</div>
</body>
</html>
