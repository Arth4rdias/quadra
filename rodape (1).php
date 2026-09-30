<?php
/* SAIR — encerra a sessão. */
require 'config.php';
unset($_SESSION['uid'], $_SESSION['admin_id']);
session_regenerate_id(true);
flash('Você saiu da sua conta.');
redirecionar('index.php');
