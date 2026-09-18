<?php
require '../funcoes.php';
exigirTipo('administracao');
$paginaAtual = 'turmas';

$msg    = '';
$erro   = '';
$turmas = lerDados('turmas');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'salvar') {
        $nome = trim($_POST['nome'] ?? '');
        $ano  = trim($_POST['ano_letivo'] ?? date('Y'));

        if (!$nome) {
            $erro = 'Nome da turma é obrigatório.';
        } else {
            if ($acao === 'criar') {
                $turmas[] = [
                    'id'         => proximoId($turmas),
                    'nome'       => $nome,
                    'ano_letivo' => $ano,
                ];
                $msg = 'Turma criada!';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                foreach ($turmas as &$t) {
                    if ($t['id'] === $id) {
                        $t['nome']       = $nome;
                        $t['ano_letivo'] = $ano;
                    }
                }
                unset($t);
                $msg = 'Turma atualizada!';
            }
            salvarDados('turmas', $turmas);
        }
    }

    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        $turmas = array_values(array_filter($turmas, fn($t) => $t['id'] !== $id));
        salvarDados('turmas', $turmas);
        $msg = 'Turma excluída.';
    }
}

$editando = null;
if (isset($_GET['editar'])) {
    $eid = (int)$_GET['editar'];
    foreach ($turmas as $t) {
        if ($t['id'] === $eid) { $editando = $t; break; }
    }
}

$usuarios = lerDados('usuarios');
$alunosPorTurma = [];
foreach ($usuarios as $u) {
    if ($u['tipo'] === 'aluno' && isset($u['id_turma'])) {
        $alunosPorTurma[$u['id_turma']] = ($alunosPorTurma[$u['id_turma']] ?? 0) + 1;
    }
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Turmas</title>
  <meta name="description" content="Gerenciamento de turmas escolares na plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include '../inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Gerenciar Turmas</h1>

  <?php if ($msg): ?><div class="alerta alerta-ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

  <div class="card">
    <h2><?= $editando ? 'Editar Turma' : 'Nova Turma' ?></h2>
    <form method="post" id="form-turma">
      <input type="hidden" name="acao" value="<?= $editando ? 'salvar' : 'criar' ?>">
      <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="nome-turma">Nome da Turma *</label>
          <input type="text" id="nome-turma" name="nome" maxlength="60" required
                 placeholder="Ex: 1º Ano A"
                 value="<?= e($editando['nome'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="ano-turma">Ano Letivo</label>
          <input type="number" id="ano-turma" name="ano_letivo" min="2020" max="2035"
                 value="<?= e($editando['ano_letivo'] ?? date('Y')) ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primario" id="btn-salvar-turma">
        <?= $editando ? 'Salvar' : 'Criar Turma' ?>
      </button>
      <?php if ($editando): ?>
        <a href="turmas.php" class="btn btn-secundario" style="margin-left:8px">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="card">
    <h2>Turmas Cadastradas (<?= count($turmas) ?>)</h2>
    <?php if (empty($turmas)): ?>
      <p class="vazio">Nenhuma turma cadastrada.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Nome da Turma</th>
            <th>Ano Letivo</th>
            <th style="width:110px">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($turmas as $t): ?>
            <tr>
              <td><?= $t['id'] ?></td>
              <td><?= e($t['nome']) ?></td>
              <td><?= e($t['ano_letivo']) ?></td>
              <td>
                <a href="turmas.php?editar=<?= $t['id'] ?>" class="btn btn-sm btn-secundario">Editar</a>
                <form method="post" style="display:inline"
                      onsubmit="return confirm('Excluir turma <?= e($t['nome']) ?>?')">
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="id" value="<?= $t['id'] ?>">
                  <button class="btn btn-sm btn-vermelho" id="btn-del-turma-<?= $t['id'] ?>">Excluir</button>
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
