<?php
/* admin/config.php — autenticação e funções do painel do administrador.
   Reaproveita tudo do config.php da raiz (conexão PDO, csrf, e(), flash,
   avatar, quando, etc.) — nada ali precisou ser alterado. */
require __DIR__ . '/../config.php';

function admin_usuario() {
    return administrador();
}

function exigir_admin() {
    if (!admin_usuario()) {
        flash('Entre com a conta de administrador para continuar.', 'erro');
        redirecionar('../login.php');
    }
}

// quantos acessos de usuários aconteceram desde a última vez que o
// admin abriu o painel — é o número do sininho no menu lateral.
function admin_notificacoes($admin) {
    global $pdo;
    $desde = $admin['visto_em'] ?? null;
    $st = $pdo->prepare('SELECT COUNT(*) FROM acessos' . ($desde ? ' WHERE criado > ?' : ''));
    $st->execute($desde ? [$desde] : []);
    return (int)$st->fetchColumn();
}

// marca o painel como "visto agora" (some a contagem de notificações)
function admin_marcar_visto($id) {
    global $pdo;
    $pdo->prepare('UPDATE admins SET visto_em = NOW() WHERE id = ?')->execute([$id]);
}
