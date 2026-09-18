<?php
require '../funcoes.php';
exigirTipo('professor');
$paginaAtual = 'tarefas';

$msg         = '';
$erro        = '';
$tarefas     = lerDados('tarefas');
$disciplinas = lerDados('disciplinas');
$discMap     = array_column($disciplinas, 'nome', 'id');

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'salvar') {
        $titulo    = trim($_POST['titulo']       ?? '');
        $descricao = trim($_POST['descricao']    ?? '');
        $entrega   = $_POST['data_entrega']      ?? '';
        $id_disc   = (int)($_POST['id_disciplina'] ?? 0);

        if (!$titulo || !$entrega) {
            $erro = 'Título e data de entrega são obrigatórios.';
        } else {
            if ($acao === 'criar') {
                $tarefas[] = [
                    'id'             => proximoId($tarefas),
                    'id_disciplina'  => $id_disc,
                    'titulo'         => $titulo,
                    'descricao'      => $descricao,
                    'data_entrega'   => $entrega,
                ];
                $msg = 'Tarefa publicada com sucesso!';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                foreach ($tarefas as &$t) {
                    if ($t['id'] === $id) {
                        $t['titulo']        = $titulo;
                        $t['descricao']     = $descricao;
                        $t['data_entrega']  = $entrega;
                        $t['id_disciplina'] = $id_disc;
                    }
                }
                unset($t);
                $msg = 'Tarefa atualizada!';
            }
            salvarDados('tarefas', $tarefas);
        }
    }

    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        $tarefas = array_values(array_filter($tarefas, fn($t) => $t['id'] !== $id));
        salvarDados('tarefas', $tarefas);
        $msg = 'Tarefa excluída.';
    }
}

// ── GET: editar ──
$editando = null;
if (isset($_GET['editar'])) {
    $eid = (int)$_GET['editar'];
    foreach ($tarefas as $t) {
        if ($t['id'] === $eid) { $editando = $t; break; }
    }
}

usort($tarefas, fn($a,$b) => strcmp($a['data_entrega'], $b['data_entrega']));
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Tarefas (Professor)</title>
  <meta name="description" content="Gerenciamento de tarefas e materiais para alunos.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include '../inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Gerenciar Tarefas</h1>

  <?php if ($msg): ?><div class="alerta alerta-ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

  <!-- Formulário -->
  <div class="card">
    <h2><?= $editando ? 'Editar Tarefa' : 'Nova Tarefa' ?></h2>
    <form method="post" id="form-tarefa">
      <input type="hidden" name="acao" value="<?= $editando ? 'salvar' : 'criar' ?>">
      <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="titulo-tar">Título da Tarefa *</label>
          <input type="text" id="titulo-tar" name="titulo" maxlength="120" required
                 value="<?= e($editando['titulo'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="disc-tar">Disciplina</label>
          <select id="disc-tar" name="id_disciplina">
            <option value="0">— selecione —</option>
            <?php foreach ($disciplinas as $d): ?>
              <option value="<?= $d['id'] ?>"
                <?= ($editando['id_disciplina'] ?? 0) == $d['id'] ? 'selected' : '' ?>>
                <?= e($d['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-grupo">
        <label for="desc-tar">Descrição / Instruções</label>
        <textarea id="desc-tar" name="descricao" rows="3"><?= e($editando['descricao'] ?? '') ?></textarea>
      </div>
      <div class="form-grupo" style="max-width:220px">
        <label for="entrega-tar">Data de Entrega *</label>
        <input type="date" id="entrega-tar" name="data_entrega" required
               value="<?= e($editando['data_entrega'] ?? '') ?>">
      </div>
      <button type="submit" class="btn btn-primario" id="btn-salvar-tar">
        <?= $editando ? 'Salvar' : 'Publicar Tarefa' ?>
      </button>
      <?php if ($editando): ?>
        <a href="tarefas.php" class="btn btn-secundario" style="margin-left:8px">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Lista de tarefas -->
  <div class="card">
    <h2>Tarefas Publicadas (<?= count($tarefas) ?>)</h2>
    <?php if (empty($tarefas)): ?>
      <p class="vazio">Nenhuma tarefa publicada ainda.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Título</th>
            <th>Disciplina</th>
            <th>Descrição</th>
            <th>Entrega</th>
            <th style="width:110px">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $hoje = date('Y-m-d');
            foreach ($tarefas as $t):
              $atrasada = $t['data_entrega'] < $hoje;
          ?>
            <tr>
              <td><?= e($t['titulo']) ?></td>
              <td><?= e($discMap[$t['id_disciplina']] ?? '—') ?></td>
              <td style="font-size:13px;color:#666"><?= e(mb_substr($t['descricao'],0,60)) ?><?= strlen($t['descricao'])>60?'…':'' ?></td>
              <td>
                <span class="badge <?= $atrasada ? 'badge-vermelho' : 'badge-verde' ?>">
                  <?= dataBR($t['data_entrega']) ?>
                </span>
              </td>
              <td>
                <a href="tarefas.php?editar=<?= $t['id'] ?>" class="btn btn-sm btn-secundario">Editar</a>
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Excluir tarefa?')">
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="id" value="<?= $t['id'] ?>">
                  <button class="btn btn-sm btn-vermelho" id="btn-del-tar-<?= $t['id'] ?>">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
