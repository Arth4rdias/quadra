<?php
// Compatibilidade: o login administrativo agora usa o mesmo formulário do site.
require __DIR__ . '/../config.php';
if (administrador()) redirecionar('index.php');
redirecionar('../login.php');
