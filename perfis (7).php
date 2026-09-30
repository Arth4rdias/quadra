<?php
/* Topo comum do painel do admin. Cada página define antes: $titulo e $paginaAdm. */
$adm  = admin_usuario();
$pend = $adm ? admin_notificacoes($adm) : 0;
$navAdm = [
    ['geral',  'index.php',            'Acesso geral',  '<path d="M4 20V11M10 20V4M16 20v-6M22 20H2"/>', $pend],
    ['perfis', 'perfis.php',           'Cadastros',     '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M17 11h4M19 9v4"/>', 0],
    ['vagas',  'vagas.php',            'Vagas',         '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5.5A1.5 1.5 0 0110.5 4h3A1.5 1.5 0 0115 5.5V7"/>', 0],
    ['posts',  'posts.php',            'Publicações',   '<path d="M4 6h16M4 12h16M4 18h10"/>', 0],
    ['equipe', 'desenvolvedores.php',  'Equipe do site','<path d="M12 3l1.9 4.6L19 9l-4.2 2.9L15.6 17 12 14.4 8.4 17l.8-5.1L5 9l5.1-1.4z"/>', 0],
];
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Painel') ?> — admin · Quadra</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&family=Inter+Tight:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="topbar">
  <div class="topbar-in">
    <a class="brand" href="index.php">
      <div class="mark" style="background:var(--hot)"><span style="border-color:var(--paper)"></span></div>
      <b>Quadra <span style="font-weight:500;color:var(--ink-3);font-size:13px">/ admin</span></b>
    </a>
    <span style="flex:1"></span>
    <a class="btn btn-ghost btn-sm" href="../index.php" target="_blank" rel="noopener">Ver o site</a>
    <div class="auth-wrap">
      <span class="auth-pill"><?= avatar(['nome' => $adm['nome'], 'foto' => '']) ?><span><?= e($adm['nome']) ?></span></span>
      <a class="btn btn-ghost btn-sm" href="logout.php">Sair</a>
    </div>
  </div>
</header>

<div class="adm-shell">
  <nav class="adm-nav" aria-label="Painel do administrador">
    <?php foreach ($navAdm as [$chave, $href, $rot, $svg, $bd]): ?>
      <a href="<?= $href ?>" <?= ($paginaAdm ?? '') === $chave ? 'aria-current="true"' : '' ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $svg ?></svg>
        <?= $rot ?>
        <?php if ($bd): ?><span class="badge-dot"><?= $bd > 99 ? '99+' : $bd ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
    <hr>
    <a href="../index.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7 7-7M3 12h18"/></svg>
      Voltar ao site
    </a>
  </nav>

  <main>
    <?php if (!empty($_SESSION['flash'])): foreach ($_SESSION['flash'] as [$fTipo, $fMsg]): ?>
      <div class="flash <?= e($fTipo) ?>" role="status"><?= e($fMsg) ?></div>
    <?php endforeach; unset($_SESSION['flash']); endif; ?>
