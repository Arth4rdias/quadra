<?php
/* ============================================================
   config.php — conexão com o banco, sessão e funções de apoio.
   Todas as páginas começam com:  require 'config.php';
   ============================================================ */

// ---- ajuste aqui se o seu MySQL tiver outro usuário/senha ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'quadra');
define('DB_USER', 'root');
define('DB_PASS', '');            // no XAMPP a senha do root é vazia

date_default_timezone_set('America/Sao_Paulo');
if (session_status() === PHP_SESSION_NONE) session_start();

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET time_zone = '-03:00'");
} catch (PDOException $ex) {
    die('<p style="font-family:sans-serif;padding:30px;line-height:1.6">Não consegui conectar ao banco.<br>
         Importe o <b>banco.sql</b> no phpMyAdmin e confira usuário/senha no <b>config.php</b>.<br>
         <small>' . htmlspecialchars($ex->getMessage()) . '</small></p>');
}

// ---- listas fixas usadas nos formulários e filtros ----
const CATS         = ['Tecnologia','Design','Marketing','Construção','Beleza','Alimentação','Saúde','Educação','Logística','Jurídico','Contabilidade','Eventos'];
const NIVEIS       = ['Iniciante','Intermediário','Avançado'];
const MODS         = ['Presencial','Híbrido','Remoto'];
const TIPOS_PERFIL = ['Empresa','Prestador'];
const TIPOS_POST   = ['Trabalho','Vaga','Marco'];
const CIDADES      = ['São Paulo, SP','Rio de Janeiro, RJ','Belo Horizonte, MG','Curitiba, PR','Porto Alegre, RS','Recife, PE','Salvador, BA','Fortaleza, CE','Florianópolis, SC','Manaus, AM','Goiânia, GO','Campinas, SP'];

// ---- filtro global de cidade (fica guardado na sessão) ----
if (isset($_GET['cidade'])) $_SESSION['cidade'] = trim($_GET['cidade']);
function cidade_ativa() { return $_SESSION['cidade'] ?? ''; }

// ---- segurança / navegação ----
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function redirecionar($url) { header('Location: ' . $url); exit; }

function flash($msg, $tipo = 'ok') { $_SESSION['flash'][] = [$tipo, $msg]; }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_campo() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }

// Toda ação que altera dados só aceita POST com o token correto.
function exigir_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals(csrf_token(), $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Requisição inválida.');
    }
}

// página atual (arquivo + query) para voltar depois de uma ação
function pagina_atual() {
    return basename($_SERVER['PHP_SELF']) . ($_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
}
function volta_campo() { return '<input type="hidden" name="volta" value="' . e(pagina_atual()) . '">'; }
function volta_seguro($padrao = 'index.php') {
    $v = $_POST['volta'] ?? $_GET['volta'] ?? '';
    return preg_match('/^[a-z_]+\.php(\?[\w=&%.+\-]*)?$/i', $v) ? $v : $padrao;
}

// ---- usuário logado ----
function usuario() {
    global $pdo;
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $st = $pdo->prepare('SELECT * FROM perfis WHERE id = ?');
            $st->execute([$_SESSION['uid']]);
            $u = $st->fetch() ?: null;
        }
    }
    return $u;
}

// ---- administrador logado ----
// A conta de admin usa a mesma tela de login do site, mas mantém uma
// sessão própria para nunca receber permissões administrativas por engano.
function administrador() {
    global $pdo;
    static $a = false;
    if ($a === false) {
        $a = null;
        if (!empty($_SESSION['admin_id'])) {
            try {
                $st = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
                $st->execute([$_SESSION['admin_id']]);
                $a = $st->fetch() ?: null;
            } catch (PDOException $ex) {
                $a = null;
            }
        }
    }
    return $a;
}
function exigir_login($volta = null) {
    if (!usuario()) {
        flash('Entre na sua conta para continuar.', 'erro');
        redirecionar('login.php?volta=' . urlencode($volta ?? pagina_atual()));
    }
}

// ---- apresentação ----
function quando($dt) {
    $d = max(0, time() - strtotime($dt));
    if ($d < 60)     return 'agora';
    if ($d < 3600)   return floor($d / 60) . ' min';
    if ($d < 86400)  return floor($d / 3600) . ' h';
    if ($d < 604800) return floor($d / 86400) . ' d';
    return date('d/m/Y', strtotime($dt));
}
function cidade_curta($c) { return trim(explode(',', $c)[0]); }

function iniciais($nome) {
    $w = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY);
    $r = '';
    foreach (array_slice($w, 0, 2) as $x) $r .= mb_substr($x, 0, 1);
    return mb_strtoupper($r) ?: '?';
}
function cor_de($nome) {
    $h = 0;
    foreach (preg_split('//u', $nome, -1, PREG_SPLIT_NO_EMPTY) as $c) $h = ($h * 31 + mb_ord($c)) % 360;
    return "hsl($h 46% 38%)";
}
function avatar($p, $cls = '') {
    if (!empty($p['foto'])) {
        return '<div class="avatar ' . $cls . '" style="padding:0;overflow:hidden;background:var(--surface-2)"><img src="uploads/'
             . e($p['foto']) . '" alt="" style="width:100%;height:100%;object-fit:cover;display:block"></div>';
    }
    return '<div class="avatar ' . $cls . '" style="background:' . cor_de($p['nome']) . '">' . e(iniciais($p['nome'])) . '</div>';
}
function nivel_idx($n) { $i = array_search($n, NIVEIS, true); return $i === false ? 0 : $i; }
function nivel_html($n) {
    $i = nivel_idx($n);
    $b = '';
    for ($k = 0; $k < 3; $k++) $b .= '<i class="' . ($k <= $i ? 'f' : '') . '"></i>';
    return '<span class="level lv' . ($i + 1) . '"><span class="bars">' . $b . '</span>' . e($n) . '</span>';
}
function opcoes($lista, $sel = '') {
    $o = '';
    foreach ($lista as $x) $o .= '<option' . ($x === $sel ? ' selected' : '') . '>' . e($x) . '</option>';
    return $o;
}
function tags_array($s) { return array_values(array_filter(array_map('trim', explode(',', (string)$s)), 'strlen')); }
function link_externo($url) { return preg_match('#^https?://#i', $url) ? $url : 'https://' . $url; }

// ---- consultas reaproveitadas ----
function contar($tabela, $cid = '') {
    global $pdo;
    $sql = 'SELECT COUNT(*) FROM ' . ($tabela === 'vagas' ? 'vagas' : 'perfis') . ($cid !== '' ? ' WHERE cidade = ?' : '');
    $st = $pdo->prepare($sql);
    $st->execute($cid !== '' ? [$cid] : []);
    return (int)$st->fetchColumn();
}
function cidades_em_uso() {
    global $pdo;
    return $pdo->query('SELECT cidade FROM perfis UNION SELECT cidade FROM vagas ORDER BY cidade')->fetchAll(PDO::FETCH_COLUMN);
}
function todas_cidades() {
    $c = array_unique(array_merge(CIDADES, cidades_em_uso()));
    sort($c);
    return $c;
}

// compatibilidade entre o usuário logado e uma vaga (mesmo cálculo do protótipo)
function compat($v) {
    $a = usuario();
    if (!$a) return 0;
    $s  = 40;
    $ta = array_map('mb_strtolower', tags_array($a['tags']));
    $tv = array_map('mb_strtolower', tags_array($v['tags']));
    $inter = 0;
    foreach ($tv as $t) {
        foreach ($ta as $x) {
            if (str_contains($x, $t) || str_contains($t, $x)) { $inter++; break; }
        }
    }
    $s += min(30, $inter * 12);
    if ($a['cidade'] === $v['cidade'])      $s += 18;
    elseif ($v['modalidade'] === 'Remoto')  $s += 12;
    $d = nivel_idx($a['nivel']) - nivel_idx($v['nivel']);
    $s += $d === 0 ? 12 : (abs($d) === 1 ? 4 : -6);
    foreach ($tv as $t) { if (str_contains(mb_strtolower($a['categoria']), $t)) { $s += 5; break; } }
    if ($a['categoria'] === $v['categoria']) $s += 10;
    return max(8, min(98, (int)round($s)));
}
