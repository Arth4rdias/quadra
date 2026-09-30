<?php
/* Remove uma candidatura específica sem mexer em mais nada. */
require 'config.php';
exigir_post();
exigir_admin();
$pdo->prepare('DELETE FROM candidaturas WHERE vaga_id = ? AND perfil_id = ?')
    ->execute([(int)($_POST['vaga_id'] ?? 0), (int)($_POST['perfil_id'] ?? 0)]);
flash('Candidatura removida.');
redirecionar('vaga_candidatos.php?id=' . (int)($_POST['vaga_id'] ?? 0));
