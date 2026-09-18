<?php
require 'funcoes.php';
exigirLogin();
$paginaAtual = 'dashboard';

$tipo      = $_SESSION['usuario']['tipo'];
$nome      = $_SESSION['usuario']['nome'];
$usuarios  = lerDados('usuarios');
$eventos   = lerDados('eventos');
$comunicados = lerDados('comunicados');
$turmas    = lerDados('turmas');
$tarefas   = lerDados('tarefas');
$cardapio  = lerDados('cardapio');

$hoje = date('Y-m-d');
$eventosProximos = array_filter($eventos, fn($e) => $e['data'] >= $hoje);
usort($eventosProximos, fn($a,$b) => strcmp($a['data'], $b['data']));
$eventosProximos = array_slice($eventosProximos, 0, 5);

$comunicadosFiltrados = array_filter($comunicados, function($c) use ($tipo) {
    return $c['publico'] === 'geral' || $c['publico'] === $tipo;
});
usort($comunicadosFiltrados, fn($a,$b) => strcmp($b['data'], $a['data']));
$comunicadosFiltrados = array_slice($comunicadosFiltrados, 0, 3);

$meses = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Dashboard</title>
  <meta name="description" content="Painel principal da plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include 'inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Olá, <?= e($nome) ?>! </h1>

  <?php if ($tipo === 'administracao'): ?>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-numero"><?= count($usuarios) ?></div>
      <div class="stat-label">Usuários cadastrados</div>
    </div>
    <div class="stat-card verde">
      <div class="stat-numero"><?= count($turmas) ?></div>
      <div class="stat-label">Turmas ativas</div>
    </div>
    <div class="stat-card laranja">
      <div class="stat-numero"><?= count($eventos) ?></div>
      <div class="stat-label">Eventos cadastrados</div>
    </div>
    <div class="stat-card vermelho">
      <div class="stat-numero"><?= count($comunicados) ?></div>
      <div class="stat-label">Comunicados enviados</div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card">
      <h2> Próximos Eventos</h2>
      <?php if (empty($eventosProximos)): ?>
        <p class="vazio">Nenhum evento futuro cadastrado.</p>
      <?php else: ?>
        <?php foreach ($eventosProximos as $ev): ?>
          <?php $ts = strtotime($ev['data']); ?>
          <div class="evento-item">
            <div class="evento-data">
              <div class="dia"><?= date('d', $ts) ?></div>
              <div class="mes"><?= $meses[(int)date('m', $ts)] ?></div>
            </div>
            <div class="evento-info">
              <h3><?= e($ev['titulo']) ?></h3>
              <div class="meta"><?= e($ev['hora']) ?> · <?= e($ev['local']) ?>
                <span class="badge badge-azul" style="margin-left:4px"><?= e($ev['publico']) ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2> Comunicados Recentes</h2>
      <?php if (empty($comunicadosFiltrados)): ?>
        <p class="vazio">Nenhum comunicado disponível.</p>
      <?php else: ?>
        <?php foreach ($comunicadosFiltrados as $c): ?>
          <div class="comunicado-item">
            <h3><?= e($c['titulo']) ?></h3>
            <p><?= e($c['mensagem']) ?></p>
            <div class="meta"><?= dataBR($c['data']) ?> &middot;
              <span class="badge badge-cinza"><?= e($c['publico']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h2>Cardápio da Semana</h2>
    <div class="cardapio-grid">
      <?php foreach ($cardapio as $item): ?>
        <div class="cardapio-dia">
          <div class="dia-nome"><?= e($item['dia']) ?></div>
          <div class="dia-refeicao"><?= e($item['refeicao']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php elseif ($tipo === 'professor'): ?>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-numero"><?= count($eventosProximos) ?></div>
      <div class="stat-label">Eventos futuros</div>
    </div>
    <div class="stat-card verde">
      <div class="stat-numero"><?= count($tarefas) ?></div>
      <div class="stat-label">Tarefas publicadas</div>
    </div>
    <div class="stat-card laranja">
      <div class="stat-numero"><?= count($comunicadosFiltrados) ?></div>
      <div class="stat-label">Comunicados</div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card">
      <h2>Próximos Eventos</h2>
      <?php if (empty($eventosProximos)): ?>
        <p class="vazio">Nenhum evento futuro.</p>
      <?php else: ?>
        <?php foreach ($eventosProximos as $ev): ?>
          <?php $ts = strtotime($ev['data']); ?>
          <div class="evento-item">
            <div class="evento-data">
              <div class="dia"><?= date('d', $ts) ?></div>
              <div class="mes"><?= $meses[(int)date('m', $ts)] ?></div>
            </div>
            <div class="evento-info">
              <h3><?= e($ev['titulo']) ?></h3>
              <div class="meta"><?= e($ev['hora']) ?> · <?= e($ev['local']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="card">
      <h2>Comunicados</h2>
      <?php if (empty($comunicadosFiltrados)): ?>
        <p class="vazio">Nenhum comunicado.</p>
      <?php else: ?>
        <?php foreach ($comunicadosFiltrados as $c): ?>
          <div class="comunicado-item">
            <h3><?= e($c['titulo']) ?></h3>
            <p><?= e($c['mensagem']) ?></p>
            <div class="meta"><?= dataBR($c['data']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php else: ?>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-numero"><?= count($eventosProximos) ?></div>
      <div class="stat-label">Eventos futuros</div>
    </div>
    <div class="stat-card verde">
      <div class="stat-numero"><?= count($tarefas) ?></div>
      <div class="stat-label">Tarefas abertas</div>
    </div>
    <div class="stat-card laranja">
      <div class="stat-numero"><?= count($comunicadosFiltrados) ?></div>
      <div class="stat-label">Comunicados</div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card">
      <h2>Próximos Eventos</h2>
      <?php if (empty($eventosProximos)): ?>
        <p class="vazio">Nenhum evento futuro.</p>
      <?php else: ?>
        <?php foreach ($eventosProximos as $ev): ?>
          <?php $ts = strtotime($ev['data']); ?>
          <div class="evento-item">
            <div class="evento-data">
              <div class="dia"><?= date('d', $ts) ?></div>
              <div class="mes"><?= $meses[(int)date('m', $ts)] ?></div>
            </div>
            <div class="evento-info">
              <h3><?= e($ev['titulo']) ?></h3>
              <div class="meta"><?= e($ev['hora']) ?> · <?= e($ev['local']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="card">
      <h2>Próximas Tarefas</h2>
      <?php
        $tarefasProximas = array_filter($tarefas, fn($t) => $t['data_entrega'] >= $hoje);
        usort($tarefasProximas, fn($a,$b) => strcmp($a['data_entrega'],$b['data_entrega']));
        $tarefasProximas = array_slice($tarefasProximas, 0, 4);
        $disciplinas = lerDados('disciplinas');
        $discMap = array_column($disciplinas, 'nome', 'id');
      ?>
      <?php if (empty($tarefasProximas)): ?>
        <p class="vazio">Nenhuma tarefa pendente.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($tarefasProximas as $t): ?>
            <li>
              <div>
                <div class="li-titulo"><?= e($t['titulo']) ?></div>
                <div class="li-detalhe"><?= e($discMap[$t['id_disciplina']] ?? '') ?></div>
              </div>
              <span class="badge badge-laranja">Entrega: <?= dataBR($t['data_entrega']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h2>Cardápio da Semana</h2>
    <div class="cardapio-grid">
      <?php foreach ($cardapio as $item): ?>
        <div class="cardapio-dia">
          <div class="dia-nome"><?= e($item['dia']) ?></div>
          <div class="dia-refeicao"><?= e($item['refeicao']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
