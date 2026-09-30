<?php

$u      = usuario();
$adm    = administrador();
$cid    = cidade_ativa();
$script = basename($_SERVER['PHP_SELF']);
$acao   = in_array($script, ['index.php', 'perfis.php', 'vagas.php']) ? $script : 'index.php';
$nav = [
    ['praca',   'index.php',  'Praça',    '<path d="M4 6h16M4 12h16M4 18h10"/>'],
    ['perfis',  'perfis.php', 'Quem faz', '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M17 11h4M19 9v4"/>'],
    ['vagas',   'vagas.php',  'Vagas',    '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5.5A1.5 1.5 0 0110.5 4h3A1.5 1.5 0 0115 5.5V7"/>'],
    ['painel',  'painel.php', 'Pulso',    '<path d="M4 20V11M10 20V4M16 20v-6M22 20H2"/>'],
];
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Quadra') ?> — Quadra</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&family=Inter+Tight:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-in">
    <a class="brand" href="index.php"><div class="mark"><span></span></div><b>Quadra</b></a>

    <form method="get" action="<?= e($acao) ?>" style="display:contents">
      <div class="tag-city">
        <select name="cidade" onchange="this.form.submit()" aria-label="Cidade em foco">
          <option value="">Todas as cidades</option>
          <?php foreach (cidades_em_uso() as $cOp): ?>
            <option <?= $cOp === $cid ? 'selected' : '' ?>><?= e($cOp) ?></option>
          <?php endforeach; ?>
        </select>
        <svg class="chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg>
      </div>
      <label class="search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar pessoa, empresa, serviço ou vaga" aria-label="Buscar">
      </label>
    </form>

    <div class="auth-wrap">
      <?php if ($adm): ?>
        <a class="btn btn-primary btn-sm" href="admin/index.php">Painel admin</a>
        <a class="auth-pill" href="admin/index.php" title="Painel do administrador"><?= avatar(['nome' => $adm['nome'], 'foto' => '']) ?><span><?= e($adm['nome']) ?></span></a>
        <a class="btn btn-ghost btn-sm" href="logout.php">Sair</a>
      <?php elseif ($u): ?>
        <a class="auth-pill" href="perfil_ver.php?id=<?= (int)$u['id'] ?>" title="Meu perfil"><?= avatar($u, 'sm') ?><span><?= e($u['nome']) ?></span></a>
        <a class="btn btn-ghost btn-sm" href="logout.php">Sair</a>
      <?php else: ?>
        <a class="btn btn-primary btn-sm" href="login.php">Entrar</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="shell">
  <nav class="rail" aria-label="Seções">
    <?php foreach ($nav as [$chave, $href, $rotulo, $svg]): ?>
      <a class="rail-btn" href="<?= $href ?>" <?= ($pagina ?? '') === $chave ? 'aria-current="true"' : '' ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $svg ?></svg><?= $rotulo ?>
      </a>
    <?php endforeach; ?>
    <hr>
    <a class="cta" href="perfil_form.php">
      <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>
      Cadastrar
    </a>
  </nav>

  <main class="main">
    <?php if (!empty($_SESSION['flash'])): foreach ($_SESSION['flash'] as [$fTipo, $fMsg]): ?>
      <div class="flash <?= e($fTipo) ?>" role="status"><?= e($fMsg) ?></div>
    <?php endforeach; unset($_SESSION['flash']); endif; ?>
