<?php
require 'config.php';
unset($_SESSION['uid'], $_SESSION['admin_id']);
flash('Você saiu da conta.');
redirecionar('index.php');
