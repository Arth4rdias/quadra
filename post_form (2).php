<?php
/* EXCLUIR vaga (só o dono). Aceita apenas POST. */
require 'config.php';
exigir_post();
exigir_login(volta_seguro('vagas.php'));
$u  = usuario();
$st = $pdo->prepare('DELETE FROM vagas WHERE id = ? AND perfil_id = ?');
$st->execute([(int)($_POST['id'] ?? 0), $u['id']]);
flash($st->rowCount() ? 'Vaga apagada.' : 'Não foi possível apagar essa vaga.', $st->rowCount() ? 'ok' : 'erro');
redirecionar(volta_seguro('vagas.php'));
