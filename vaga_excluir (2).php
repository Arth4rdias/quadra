<?php
/* CRIAR (cadastro) e EDITAR perfil.
   Sem ?id → cadastro novo (qualquer visitante) · Com ?id → edição (só o dono). */
require 'config.php';
$u  = usuario();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$p  = null;

if ($id) {
    exigir_login();
    $st = $pdo->prepare('SELECT * FROM perfis WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p || $p['id'] != $u['id']) {
        flash('Você só pode editar o seu próprio cadastro.', 'erro');
        redirecionar('perfis.php');
    }
}

$campos = ['nome', 'tipo', 'categoria', 'nivel', 'cidade', 'bio', 'tags', 'email', 'telefone', 'site'];
$max    = ['nome' => 120, 'cidade' => 80, 'bio' => 260, 'tags' => 200, 'email' => 120, 'telefone' => 30, 'site' => 120];
if ($p) {
    $v = $p;
} else {
    $v = array_fill_keys($campos, '');
    $v['tipo'] = TIPOS_PERFIL[0]; $v['categoria'] = CATS[0]; $v['nivel'] = NIVEIS[0]; $v['verificado'] = 0; $v['foto'] = '';
}
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post();
    foreach ($campos as $c) $v[$c] = mb_substr(trim($_POST[$c] ?? ''), 0, $max[$c] ?? 60);
    $v['verificado'] = isset($_POST['verificado']) ? 1 : 0;
    $senha = $_POST['senha'] ?? '';

    if (mb_strlen($v['nome']) < 2)                     $erros['nome'] = 'Escreva pelo menos 2 caracteres.';
    if (!in_array($v['tipo'], TIPOS_PERFIL, true))     $v['tipo'] = TIPOS_PERFIL[0];
    if (!in_array($v['categoria'], CATS, true))        $erros['categoria'] = 'Escolha uma área.';
    if (!in_array($v['nivel'], NIVEIS, true))          $v['nivel'] = NIVEIS[0];
    if (!preg_match('/^.{2,},\s*[A-Za-z]{2}$/u', $v['cidade'])) $erros['cidade'] = 'Use o formato: Recife, PE.';
    if (mb_strlen($v['bio']) < 15)                     $erros['bio'] = 'Escreva ao menos 15 caracteres.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Esse e-mail não parece válido.';
    } else {
        $st = $pdo->prepare('SELECT id FROM perfis WHERE email = ? AND id <> ?');
        $st->execute([$v['email'], $id]);
        if ($st->fetch()) $erros['email'] = 'Esse e-mail já está cadastrado.';
    }
    if ((!$p || $senha !== '') && strlen($senha) < 4)  $erros['senha'] = 'A senha precisa de ao menos 4 caracteres.';
    $v['tags'] = mb_substr(implode(', ', array_slice(tags_array($v['tags']), 0, 8)), 0, 200);

    // foto (opcional)
    $extFoto = null;
    if (!empty($_FILES['foto']['name'])) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK)        $erros['foto'] = 'Não consegui enviar a imagem.';
        elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024)     $erros['foto'] = 'A imagem passa de 2 MB.';
        else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['foto']['tmp_name']);
            $extFoto = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
            if (!$extFoto) $erros['foto'] = 'Use uma imagem JPG, PNG, WEBP ou GIF.';
        }
    }

    if (!$erros) {
        $foto = $p['foto'] ?? '';
        $dir  = __DIR__ . '/uploads';
        if ($extFoto) {
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $novo = 'foto_' . bin2hex(random_bytes(8)) . '.' . $extFoto;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], "$dir/$novo")) {
                if ($foto !== '') @unlink("$dir/$foto");
                $foto = $novo;
            }
        } elseif (isset($_POST['remover_foto']) && $foto !== '') {
            @unlink("$dir/$foto");
            $foto = '';
        }

        $dados = [$v['nome'], $v['tipo'], $v['categoria'], $v['nivel'], $v['cidade'], $v['bio'], $v['tags'],
                  $v['email'], $v['telefone'], $v['site'], $foto, $v['verificado']];
        if ($p) {
            $sql = 'UPDATE perfis SET nome=?, tipo=?, categoria=?, nivel=?, cidade=?, bio=?, tags=?, email=?, telefone=?, site=?, foto=?, verificado=?'
                 . ($senha !== '' ? ', senha=?' : '') . ' WHERE id=?';
            if ($senha !== '') $dados[] = password_hash($senha, PASSWORD_DEFAULT);
            $dados[] = $id;
            $pdo->prepare($sql)->execute($dados);
            flash('Cadastro atualizado.');
            redirecionar('perfil_ver.php?id=' . $id);
        }
        $dados[] = password_hash($senha, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO perfis (nome, tipo, categoria, nivel, cidade, bio, tags, email, telefone, site, foto, verificado, senha)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($dados);
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$pdo->lastInsertId();
        flash('Cadastro criado! Você já está dentro da conta.');
        redirecionar('perfil_ver.php?id=' . $_SESSION['uid']);
    }
}

function cls($erros, $k) { return isset($erros[$k]) ? ' err' : ''; }
function msg($erros, $k) { return '<div class="err-msg">' . e($erros[$k] ?? '') . '</div>'; }

$titulo = $p ? 'Editar cadastro' : 'Novo cadastro';
$pagina = $p ? 'perfis' : '';
include 'inc/topo.php';
?>
<div class="formbox">
  <div class="section-title"><h2><?= e($titulo) ?></h2></div>
  <form class="card" method="post" enctype="multipart/form-data" action="perfil_form.php<?= $p ? '?id=' . $id : '' ?>" novalidate>
    <?= csrf_campo() ?>
    <?php if ($p): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="field<?= cls($erros, 'foto') ?>">
      <label>Foto de perfil <span class="hint">opcional — JPG, PNG, WEBP ou GIF até 2 MB</span></label>
      <div class="upload-row">
        <?= avatar(['nome' => $v['nome'] ?: 'Novo', 'foto' => $v['foto'] ?? '']) ?>
        <input type="file" name="foto" accept="image/*" style="flex:1;min-width:160px">
      </div>
      <?php if (!empty($v['foto'])): ?>
        <label class="switch" style="margin-top:8px"><input type="checkbox" name="remover_foto"><span class="track"></span>Remover a foto atual</label>
      <?php endif; ?>
      <?= msg($erros, 'foto') ?>
    </div>

    <div class="field<?= cls($erros, 'nome') ?>">
      <label for="nome">Nome da empresa ou seu nome</label>
      <input id="nome" name="nome" maxlength="120" value="<?= e($v['nome']) ?>" placeholder="Ex.: Marcenaria Nó Cego">
      <?= msg($erros, 'nome') ?>
    </div>

    <div class="row2">
      <div class="field">
        <label for="tipo">Você é</label>
        <select id="tipo" name="tipo"><?= opcoes(TIPOS_PERFIL, $v['tipo']) ?></select>
      </div>
      <div class="field<?= cls($erros, 'categoria') ?>">
        <label for="categoria">Área</label>
        <select id="categoria" name="categoria"><?= opcoes(CATS, $v['categoria']) ?></select>
        <?= msg($erros, 'categoria') ?>
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label for="nivel">Estágio <span class="hint">honestidade ajuda quem procura</span></label>
        <select id="nivel" name="nivel"><?= opcoes(NIVEIS, $v['nivel']) ?></select>
      </div>
      <div class="field<?= cls($erros, 'cidade') ?>">
        <label for="cidade">Cidade</label>
        <input id="cidade" name="cidade" list="lista-cidades" maxlength="80" value="<?= e($v['cidade']) ?>" placeholder="Cidade, UF">
        <datalist id="lista-cidades"><?php foreach (todas_cidades() as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
        <?= msg($erros, 'cidade') ?>
      </div>
    </div>

    <div class="field<?= cls($erros, 'bio') ?>">
      <label for="bio">O que você faz</label>
      <textarea id="bio" name="bio" maxlength="260" placeholder="Reformas residenciais no bairro X, com cronograma que o cliente acompanha."><?= e($v['bio']) ?></textarea>
      <?= msg($erros, 'bio') ?>
    </div>

    <div class="field">
      <label for="tags">Palavras-chave <span class="hint">separadas por vírgula</span></label>
      <input id="tags" name="tags" maxlength="200" value="<?= e($v['tags']) ?>" placeholder="marcenaria, móvel planejado, restauro">
    </div>

    <div class="row2">
      <div class="field<?= cls($erros, 'email') ?>">
        <label for="email">E-mail <span class="hint">usado para entrar</span></label>
        <input id="email" name="email" type="email" maxlength="120" value="<?= e($v['email']) ?>" placeholder="contato@empresa.com">
        <?= msg($erros, 'email') ?>
      </div>
      <div class="field">
        <label for="telefone">Telefone ou WhatsApp</label>
        <input id="telefone" name="telefone" maxlength="30" value="<?= e($v['telefone']) ?>" placeholder="(11) 90000-0000">
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label for="site">Site ou rede social <span class="hint">opcional</span></label>
        <input id="site" name="site" maxlength="120" value="<?= e($v['site']) ?>" placeholder="minhaempresa.com.br">
      </div>
      <div class="field<?= cls($erros, 'senha') ?>">
        <label for="senha">Senha <?php if ($p): ?><span class="hint">deixe em branco para manter</span><?php endif; ?></label>
        <input id="senha" name="senha" type="password" autocomplete="new-password" placeholder="mínimo 4 caracteres">
        <?= msg($erros, 'senha') ?>
      </div>
    </div>

    <label class="switch" style="margin-bottom:6px">
      <input type="checkbox" name="verificado" <?= !empty($v['verificado']) ? 'checked' : '' ?>><span class="track"></span>
      Selo “conferido” <span class="hint">(no protótipo é autodeclarado)</span>
    </label>

    <div class="form-actions">
      <button class="btn btn-primary"><?= $p ? 'Salvar alterações' : 'Criar cadastro' ?></button>
      <a class="btn btn-ghost" href="<?= $p ? 'perfil_ver.php?id=' . $id : 'perfis.php' ?>">Cancelar</a>
    </div>
  </form>
</div>
<?php include 'inc/rodape.php'; ?>
