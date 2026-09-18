<?php
require 'funcoes.php';
exigirLogin();
$paginaAtual = 'eventos';

$tipo    = $_SESSION['usuario']['tipo'];
$msg     = '';
$erro    = '';
$eventos = lerDados('eventos');

// ── POST: ações de admin/professor ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($tipo, ['administracao','professor'])) {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'salvar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $data   = $_POST['data']   ?? '';
        $hora   = $_POST['hora']   ?? '';
        $local  = trim($_POST['local']  ?? '');
        $desc   = trim($_POST['descricao'] ?? '');
        $publi  = $_POST['publico'] ?? 'geral';

        if (!$titulo || !$data || !$hora) {
            $erro = 'Preencha os campos obrigatórios: título, data e hora.';
        } else {
            if ($acao === 'criar') {
                $eventos[] = [
                    'id'        => proximoId($eventos),
                    'titulo'    => $titulo,
                    'data'      => $data,
                    'hora'      => $hora,
                    'local'     => $local,
                    'descricao' => $desc,
                    'publico'   => $publi,
                ];
                $msg = 'Evento criado com sucesso!';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                foreach ($eventos as &$ev) {
                    if ($ev['id'] === $id) {
                        $ev['titulo']    = $titulo;
                        $ev['data']      = $data;
                        $ev['hora']      = $hora;
                        $ev['local']     = $local;
                        $ev['descricao'] = $desc;
                        $ev['publico']   = $publi;
                    }
                }
                unset($ev);
                $msg = 'Evento atualizado com sucesso!';
            }
            salvarDados('eventos', $eventos);
        }
    }

    if ($acao === 'excluir' && $tipo === 'administracao') {
        $id = (int)($_POST['id'] ?? 0);
        $eventos = array_values(array_filter($eventos, fn($e) => $e['id'] !== $id));
        salvarDados('eventos', $eventos);
        $msg = 'Evento excluído.';
    }
}

// ── GET: editar ──
$editando = null;
if (isset($_GET['editar']) && in_array($tipo, ['administracao','professor'])) {
    $eid = (int)$_GET['editar'];
    foreach ($eventos as $ev) {
        if ($ev['id'] === $eid) { $editando = $ev; break; }
    }
}

// Filtra por público para alunos
if ($tipo === 'aluno') {
    $eventos = array_values(array_filter($eventos, fn($e) => in_array($e['publico'], ['geral','aluno'])));
}
usort($eventos, fn($a,$b) => strcmp($a['data'], $b['data']));

$meses = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Eventos</title>
  <meta name="description" content="Calendário de eventos escolares da plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include 'inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Eventos Escolares</h1>

  <?php if ($msg): ?><div class="alerta alerta-ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

  <?php if (in_array($tipo, ['administracao','professor'])): ?>
  <!-- Formulário de criação / edição -->
  <div class="card">
    <h2><?= $editando ? 'Editar Evento' : 'Novo Evento' ?></h2>
    <form method="post" id="form-evento">
      <input type="hidden" name="acao" value="<?= $editando ? 'salvar' : 'criar' ?>">
      <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $editando['id'] ?>">
      <?php endif; ?>

      <div class="form-linha">
        <div class="form-grupo">
          <label for="titulo">Título *</label>
          <input type="text" id="titulo" name="titulo" maxlength="120" required
                 value="<?= e($editando['titulo'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="local">Local</label>
          <input type="text" id="local" name="local" maxlength="80"
                 value="<?= e($editando['local'] ?? '') ?>">
        </div>
      </div>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="data">Data *</label>
          <input type="date" id="data" name="data" required
                 value="<?= e($editando['data'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="hora">Hora *</label>
          <input type="time" id="hora" name="hora" required
                 value="<?= e($editando['hora'] ?? '') ?>">
        </div>
      </div>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="publico">Público-alvo</label>
          <select id="publico" name="publico">
            <option value="geral"      <?= ($editando['publico'] ?? '') === 'geral'      ? 'selected' : '' ?>>Geral</option>
            <option value="aluno"      <?= ($editando['publico'] ?? '') === 'aluno'      ? 'selected' : '' ?>>Alunos</option>
            <option value="professor"  <?= ($editando['publico'] ?? '') === 'professor'  ? 'selected' : '' ?>>Professores</option>
            <option value="administracao" <?= ($editando['publico'] ?? '') === 'administracao' ? 'selected' : '' ?>>Administração</option>
          </select>
        </div>
        <div class="form-grupo">
          <label for="descricao">Descrição</label>
          <input type="text" id="descricao" name="descricao" maxlength="200"
                 value="<?= e($editando['descricao'] ?? '') ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primario" id="btn-salvar-evento">
        <?= $editando ? 'Salvar Alterações' : 'Criar Evento' ?>
      </button>
      <?php if ($editando): ?>
        <a href="eventos.php" class="btn btn-secundario" style="margin-left:8px">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>
  <?php endif; ?>

  <!-- Lista de eventos -->
  <div class="card">
    <h2>Lista de Eventos (<?= count($eventos) ?>)</h2>
    <?php if (empty($eventos)): ?>
      <p class="vazio">Nenhum evento cadastrado.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th style="width:70px">Data</th>
            <th>Título</th>
            <th>Hora</th>
            <th>Local</th>
            <th>Público</th>
            <?php if (in_array($tipo, ['administracao','professor'])): ?><th style="width:110px">Ações</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($eventos as $ev): ?>
            <tr>
              <td><?= dataBR($ev['data']) ?></td>
              <td>
                <?= e($ev['titulo']) ?>
                <?php if (!empty($ev['descricao'])): ?>
                  <div style="font-size:12px;color:#888"><?= e($ev['descricao']) ?></div>
                <?php endif; ?>
              </td>
              <td><?= e($ev['hora']) ?></td>
              <td><?= e($ev['local']) ?></td>
              <td><span class="badge badge-azul"><?= e($ev['publico']) ?></span></td>
              <?php if (in_array($tipo, ['administracao','professor'])): ?>
              <td>
                <a href="eventos.php?editar=<?= $ev['id'] ?>" class="btn btn-sm btn-secundario">Editar</a>
                <?php if ($tipo === 'administracao'): ?>
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Excluir este evento?')">
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-vermelho" id="btn-excluir-ev-<?= $ev['id'] ?>">Excluir</button>
                </form>
                <?php endif; ?>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
