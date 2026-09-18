<?php
require '../funcoes.php';
exigirTipo('aluno');
$paginaAtual = 'tarefas';

$tarefas     = lerDados('tarefas');
$disciplinas = lerDados('disciplinas');
$notas       = lerDados('notas');
$discMap     = array_column($disciplinas, 'nome', 'id');

$idAluno = (int)$_SESSION['usuario']['id'];
$hoje    = date('Y-m-d');

usort($tarefas, fn($a,$b) => strcmp($a['data_entrega'], $b['data_entrega']));

$minhasNotas = array_filter($notas, fn($n) => $n['id_aluno'] === $idAluno);
?><?php echo '<!DOCTYPE html>'; ?>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Minhas Tarefas</title>
  <meta name="description" content="Tarefas e atividades escolares do aluno na plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include '../inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Minhas Tarefas</h1>

  <div class="stats-grid">
    <?php
      $pendentes = count(array_filter($tarefas, fn($t) => $t['data_entrega'] >= $hoje));
      $atrasadas = count(array_filter($tarefas, fn($t) => $t['data_entrega'] < $hoje));
    ?>
    <div class="stat-card">
      <div class="stat-numero"><?= count($tarefas) ?></div>
      <div class="stat-label">Total de tarefas</div>
    </div>
    <div class="stat-card verde">
      <div class="stat-numero"><?= $pendentes ?></div>
      <div class="stat-label">Dentro do prazo</div>
    </div>
    <div class="stat-card vermelho">
      <div class="stat-numero"><?= $atrasadas ?></div>
      <div class="stat-label">Prazo encerrado</div>
    </div>
  </div>

  <div class="card">
    <h2>Todas as Tarefas (<?= count($tarefas) ?>)</h2>
    <?php if (empty($tarefas)): ?>
      <p class="vazio">Nenhuma tarefa publicada pelos professores.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Título</th>
            <th>Disciplina</th>
            <th>Descrição</th>
            <th>Data de Entrega</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tarefas as $t):
            $atrasada = $t['data_entrega'] < $hoje;
          ?>
            <tr>
              <td><?= e($t['titulo']) ?></td>
              <td><?= e($discMap[$t['id_disciplina']] ?? '—') ?></td>
              <td style="font-size:13px;color:#555"><?= nl2br(e($t['descricao'])) ?></td>
              <td><?= dataBR($t['data_entrega']) ?></td>
              <td>
                <span class="badge <?= $atrasada ? 'badge-vermelho' : 'badge-verde' ?>">
                  <?= $atrasada ? 'Encerrado' : 'Em aberto' ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Minhas Notas</h2>
    <?php if (empty($minhasNotas)): ?>
      <p class="vazio">Nenhuma nota lançada ainda.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Disciplina</th><th>Nota</th><th>Peso</th></tr>
        </thead>
        <tbody>
          <?php foreach ($minhasNotas as $n): ?>
            <tr>
              <td><?= e($discMap[$n['id_disciplina']] ?? '—') ?></td>
              <td>
                <span class="badge <?= $n['valor'] >= 7 ? 'badge-verde' : ($n['valor'] >= 5 ? 'badge-laranja' : 'badge-vermelho') ?>">
                  <?= number_format($n['valor'], 1) ?>
                </span>
              </td>
              <td><?= $n['peso'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
