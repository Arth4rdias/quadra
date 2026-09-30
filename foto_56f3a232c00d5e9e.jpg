<?php
/* EXCLUIR publicação (só o autor). Aceita apenas POST. */
require 'config.php';
exigir_post();
exigir_login(volta_seguro());
$u = usuario();
$st = $pdo->prepare('DELETE FROM posts WHERE id = ? AND perfil_id = ?');
$st->execute([(int)($_POST['id'] ?? 0), $u['id']]);
flash($st->rowCount() ? 'Publicação apagada.' : 'Não foi possível apagar essa publicação.', $st->rowCount() ? 'ok' : 'erro');
redirecionar(volta_seguro());
