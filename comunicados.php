<?php
require 'funcoes.php';
exigirLogin();
$paginaAtual = 'comunicados';

$tipo        = $_SESSION['usuario']['tipo'];
$msg         = '';
$erro        = '';
$comunicados = lerDados('comunicados');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tipo === 'administracao') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'salvar') {
        $titulo  = trim($_POST['titulo']   ?? '');
        $mensagem= trim($_POST['mensagem'] ?? '');
        $publi   = $_POST['publico'] ?? 'geral';
        $data    = date('Y-m-d');

        if (!$titulo || !$mensagem) {
            $erro = 'Título e mensagem são obrigatórios.';
        } else {
            if ($acao === 'criar') {
                $comunicados[] = [
                    'id'       => proximoId($comunicados),
                    'titulo'   => $titulo,
                    'mensagem' => $mensagem,
                    'data'     => $data,
                    'publico'  => $publi,
                ];
                $msg = 'Comunicado publicado!';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                foreach ($comunicados as &$c) {
                    if ($c['id'] === $id) {
                        $c['titulo']   = $titulo;
                        $c['mensagem'] = $mensagem;
                        $c['publico']  = $publi;
                    }
                }
                unset($c);
                $msg = 'Comunicado atualizado!';
            }
            salvarDados('comunicados', $comunicados);
        }
    }

    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        $comunicados = array_values(array_filter($comunicados, fn($c) => $c['id'] !== $id));
        salvarDados('comunicados', $comunicados);
        $msg = 'Comunicado excluído.';
    }
}

$editando = null;
if (isset($_GET['editar']) && $tipo === 'administracao') {
    $eid = (int)$_GET['editar'];
    foreach ($comunicados as $c) {
        if ($c['id'] === $eid) { $editando = $c; break; }
    }
}

$comunicadosFiltrados = array_values(array_filter($comunicados, function($c) use ($tipo) {
    return $c['publico'] === 'geral' || $c['publico'] === $tipo;
}));
usort($comunicadosFiltrados, fn($a,$b) => strcmp($b['data'], $a['data']));
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Comunicados</title>
  <meta name="description" content="Mural de comunicados e avisos da plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include 'inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Comunicados e Avisos</h1>

  <?php if ($msg): ?><div class="alerta alerta-ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

  <?php if ($tipo === 'administracao'): ?>
  <div class="card">
    <h2><?= $editando ? 'Editar Comunicado' : 'Novo Comunicado' ?></h2>
    <form method="post" id="form-comunicado">
      <input type="hidden" name="acao" value="<?= $editando ? 'salvar' : 'criar' ?>">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $editando['id'] ?>">
      <?php endif; ?>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="titulo-com">Título *</label>
          <input type="text" id="titulo-com" name="titulo" maxlength="120" required
                 value="<?= e($editando['titulo'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="publico-com">Público-alvo</label>
          <select id="publico-com" name="publico">
            <option value="geral"         <?= ($editando['publico'] ?? 'geral') === 'geral'         ? 'selected' : '' ?>>Geral</option>
            <option value="aluno"         <?= ($editando['publico'] ?? '') === 'aluno'         ? 'selected' : '' ?>>Alunos</option>
            <option value="professor"     <?= ($editando['publico'] ?? '') === 'professor'     ? 'selected' : '' ?>>Professores</option>
            <option value="administracao" <?= ($editando['publico'] ?? '') === 'administracao' ? 'selected' : '' ?>>Administração</option>
          </select>
        </div>
      </div>
      <div class="form-grupo">
        <label for="mensagem-com">Mensagem *</label>
        <textarea id="mensagem-com" name="mensagem" rows="4" required><?= e($editando['mensagem'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn-primario" id="btn-salvar-com">
        <?= $editando ? 'Salvar' : 'Publicar' ?>
      </button>
      <?php if ($editando): ?>
        <a href="comunicados.php" class="btn btn-secundario" style="margin-left:8px">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>
  <?php endif; ?>

  <div class="card">
    <h2>Comunicados (<?= count($comunicadosFiltrados) ?>)</h2>
    <?php if (empty($comunicadosFiltrados)): ?>
      <p class="vazio">Nenhum comunicado disponível.</p>
    <?php else: ?>
      <?php foreach ($comunicadosFiltrados as $c): ?>
        <div class="comunicado-item">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
            <h3><?= e($c['titulo']) ?></h3>
            <div style="display:flex;gap:6px;flex-shrink:0">
              <span class="badge badge-cinza"><?= e($c['publico']) ?></span>
              <?php if ($tipo === 'administracao'): ?>
                <a href="comunicados.php?editar=<?= $c['id'] ?>" class="btn btn-sm btn-secundario">Editar</a>
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Excluir comunicado?')">
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button class="btn btn-sm btn-vermelho" id="btn-excluir-com-<?= $c['id'] ?>">Excluir</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <p><?= nl2br(e($c['mensagem'])) ?></p>
          <div class="meta">Publicado em <?= dataBR($c['data']) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
