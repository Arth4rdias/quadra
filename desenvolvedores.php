<?php
/* Apaga qualquer publicação do site (moderação). */
require 'config.php';
exigir_post();
exigir_admin();
$st = $pdo->prepare('DELETE FROM posts WHERE id = ?');
$st->execute([(int)($_POST['id'] ?? 0)]);
flash($st->rowCount() ? 'Publicação apagada.' : 'Publicação não encontrada.', $st->rowCount() ? 'ok' : 'erro');
redirecionar('posts.php');
