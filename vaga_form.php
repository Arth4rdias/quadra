<?php
/* CRIAR e EDITAR vaga (precisa estar logado; só o dono edita). */
require 'config.php';
exigir_login();
$u  = usuario();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$vaga = null;

if ($id) {
    $st = $pdo->prepare('SELECT * FROM vagas WHERE id = ?');
    $st->execute([$id]);
    $vaga = $st->fetch();
    if (!$vaga || $vaga['perfil_id'] != $u['id']) {
        flash('Vaga não encontrada.', 'erro');
        redirecionar('vagas.php');
    }
}

$campos = ['titulo', 'categoria', 'modalidade', 'nivel', 'cidade', 'faixa', 'descricao', 'tags', 'contato'];
$max    = ['titulo' => 120, 'cidade' => 80, 'faixa' => 60, 'descricao' => 400, 'tags' => 200, 'contato' => 120];
if ($vaga) {
    $v = $vaga;
} else {
    $v = array_fill_keys($campos, '');
    $v['categoria'] = in_array($u['categoria'], CATS, true) ? $u['categoria'] : CATS[0];
    $v['modalidade'] = MODS[0]; $v['nivel'] = NIVEIS[0]; $v['cidade'] = $u['cidade']; $v['contato'] = $u['email'];
}
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post();
    foreach ($campos as $c) $v[$c] = mb_substr(trim($_POST[$c] ?? ''), 0, $max[$c] ?? 60);
    if (mb_strlen($v['titulo']) < 3)                    $erros['titulo'] = 'Dê um título com pelo menos 3 caracteres.';
    if (!in_array($v['categoria'], CATS, true))         $erros['categoria'] = 'Escolha uma área.';
    if (!in_array($v['modalidade'], MODS, true))        $v['modalidade'] = MODS[0];
    if (!in_array($v['nivel'], NIVEIS, true))           $v['nivel'] = NIVEIS[0];
    if (!preg_match('/^.{2,},\s*[A-Za-z]{2}$/u', $v['cidade'])) $erros['cidade'] = 'Use o formato: Recife, PE.';
    if (mb_strlen($v['descricao']) < 20)                $erros['descricao'] = 'Escreva ao menos 20 caracteres.';
    if ($v['contato'] === '')                           $erros['contato'] = 'Diga como a pessoa deve se candidatar.';
    $v['tags'] = mb_substr(implode(', ', array_slice(tags_array($v['tags']), 0, 8)), 0, 200);

    if (!$erros) {
        $dados = [$v['titulo'], $v['categoria'], $v['modalidade'], $v['nivel'], $v['cidade'], $v['faixa'], $v['descricao'], $v['tags'], $v['contato']];
        if ($vaga) {
            $dados[] = $id;
            $pdo->prepare('UPDATE vagas SET titulo=?, categoria=?, modalidade=?, nivel=?, cidade=?, faixa=?, descricao=?, tags=?, contato=? WHERE id=?')->execute($dados);
            flash('Vaga atualizada.');
        } else {
            array_unshift($dados, $u['id']);
            $pdo->prepare('INSERT INTO vagas (perfil_id, titulo, categoria, modalidade, nivel, cidade, faixa, descricao, tags, contato) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute($dados);
            flash('Vaga publicada.');
        }
        redirecionar('vagas.php');
    }
}

function cls($erros, $k) { return isset($erros[$k]) ? ' err' : ''; }
function msg($erros, $k) { return '<div class="err-msg">' . e($erros[$k] ?? '') . '</div>'; }

$titulo = $vaga ? 'Editar vaga' : 'Publicar vaga';
$pagina = 'vagas';
include 'inc/topo.php';
?>
<div class="formbox">
  <div class="section-title"><h2><?= e($titulo) ?></h2><span>como <?= e($u['nome']) ?></span></div>
  <form class="card" method="post" action="vaga_form.php<?= $vaga ? '?id=' . $id : '' ?>" novalidate>
    <?= csrf_campo() ?>
    <?php if ($vaga): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="field<?= cls($erros, 'titulo') ?>">
      <label for="titulo">Título da vaga</label>
      <input id="titulo" name="titulo" maxlength="120" value="<?= e($v['titulo']) ?>" placeholder="Ex.: Auxiliar de confeitaria">
      <?= msg($erros, 'titulo') ?>
    </div>

    <div class="row2">
      <div class="field<?= cls($erros, 'categoria') ?>">
        <label for="categoria">Área da vaga</label>
        <select id="categoria" name="categoria"><?= opcoes(CATS, $v['categoria']) ?></select>
        <?= msg($erros, 'categoria') ?>
      </div>
      <div class="field">
        <label for="modalidade">Modalidade</label>
        <select id="modalidade" name="modalidade"><?= opcoes(MODS, $v['modalidade']) ?></select>
      </div>
    </div>

    <div class="row2">
      <div class="field">
        <label for="nivel">Estágio esperado</label>
        <select id="nivel" name="nivel"><?= opcoes(NIVEIS, $v['nivel']) ?></select>
      </div>
      <div class="field<?= cls($erros, 'cidade') ?>">
        <label for="cidade">Cidade</label>
        <input id="cidade" name="cidade" list="lista-cidades" maxlength="80" value="<?= e($v['cidade']) ?>" placeholder="Cidade, UF">
        <datalist id="lista-cidades"><?php foreach (todas_cidades() as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
        <?= msg($erros, 'cidade') ?>
      </div>
    </div>

    <div class="field">
      <label for="faixa">Pagamento <span class="hint">opcional — ex.: R$ 2.800 a R$ 3.600</span></label>
      <input id="faixa" name="faixa" maxlength="60" value="<?= e($v['faixa']) ?>" placeholder="R$ 0,00">
    </div>

    <div class="field<?= cls($erros, 'descricao') ?>">
      <label for="descricao">O que a pessoa vai fazer</label>
      <textarea id="descricao" name="descricao" maxlength="400" placeholder="Descreva a rotina, o horário e o que é indispensável."><?= e($v['descricao']) ?></textarea>
      <?= msg($erros, 'descricao') ?>
    </div>

    <div class="field">
      <label for="tags">Palavras-chave <span class="hint">usadas no cálculo de compatibilidade</span></label>
      <input id="tags" name="tags" maxlength="200" value="<?= e($v['tags']) ?>" placeholder="confeitaria, produção">
    </div>

    <div class="field<?= cls($erros, 'contato') ?>">
      <label for="contato">Como se candidatar</label>
      <input id="contato" name="contato" maxlength="120" value="<?= e($v['contato']) ?>" placeholder="e-mail ou WhatsApp">
      <?= msg($erros, 'contato') ?>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary"><?= $vaga ? 'Salvar alterações' : 'Publicar vaga' ?></button>
      <a class="btn btn-ghost" href="vagas.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include 'inc/rodape.php'; ?>
