<?php
/* CANDIDATAR-SE a uma vaga / cancelar a candidatura (alterna). Aceita apenas POST. */
require 'config.php';
exigir_post();
exigir_login(volta_seguro('vagas.php'));
$u  = usuario();
$vid = (int)($_POST['vaga_id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM vagas WHERE id = ?');
$st->execute([$vid]);
$vaga = $st->fetch();

if (!$vaga) {
    flash('Vaga não encontrada.', 'erro');
} elseif ($vaga['perfil_id'] == $u['id']) {
    flash('Você não pode se candidatar à sua própria vaga.', 'erro');
} else {
    $st = $pdo->prepare('DELETE FROM candidaturas WHERE vaga_id = ? AND perfil_id = ?');
    $st->execute([$vid, $u['id']]);
    if ($st->rowCount()) {
        flash('Candidatura cancelada.');
    } else {
        $pdo->prepare('INSERT INTO candidaturas (vaga_id, perfil_id) VALUES (?, ?)')->execute([$vid, $u['id']]);
        flash('Candidatura registrada! A empresa já vê o seu perfil na lista.');
    }
}
redirecionar(volta_seguro('vagas.php'));
