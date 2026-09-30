<?php
/* EXCLUIR o próprio cadastro. As vagas, publicações, candidaturas e curtidas
   dele somem junto (ON DELETE CASCADE no banco). Aceita apenas POST. */
require 'config.php';
exigir_post();
exigir_login('index.php');
$u = usuario();
if ((int)($_POST['id'] ?? 0) !== (int)$u['id']) {
    flash('Você só pode apagar o seu próprio cadastro.', 'erro');
    redirecionar('perfis.php');
}
if ($u['foto'] !== '') @unlink(__DIR__ . '/uploads/' . $u['foto']);
$pdo->prepare('DELETE FROM perfis WHERE id = ?')->execute([$u['id']]);
unset($_SESSION['uid']);
session_regenerate_id(true);
flash('Cadastro apagado.');
redirecionar('index.php');
