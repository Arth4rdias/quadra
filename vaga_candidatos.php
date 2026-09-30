<?php
/* Apaga uma vaga (candidaturas dela somem junto). */
require 'config.php';
exigir_post();
exigir_admin();
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare('DELETE FROM vagas WHERE id = ?');
$st->execute([$id]);
flash($st->rowCount() ? 'Vaga apagada.' : 'Vaga não encontrada.', $st->rowCount() ? 'ok' : 'erro');
redirecionar('vagas.php');
