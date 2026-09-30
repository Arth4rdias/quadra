<?php
/* CURTIR / descurtir uma publicação (alterna). Aceita apenas POST. */
require 'config.php';
exigir_post();
exigir_login(volta_seguro());
$u   = usuario();
$pid = (int)($_POST['post_id'] ?? 0);

$st = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
$st->execute([$pid]);
if ($st->fetch()) {
    $st = $pdo->prepare('DELETE FROM curtidas WHERE post_id = ? AND perfil_id = ?');
    $st->execute([$pid, $u['id']]);
    if ($st->rowCount() === 0) {
        $pdo->prepare('INSERT INTO curtidas (post_id, perfil_id) VALUES (?, ?)')->execute([$pid, $u['id']]);
    }
}
redirecionar(volta_seguro());
