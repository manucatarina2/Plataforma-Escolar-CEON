<?php
require '../funcoes.php';
exigirTipo('administracao');
$paginaAtual = 'relatorios';

$usuarios    = lerDados('usuarios');
$turmas      = lerDados('turmas');
$eventos     = lerDados('eventos');
$comunicados = lerDados('comunicados');
$tarefas     = lerDados('tarefas');
$notas       = lerDados('notas');
$disciplinas = lerDados('disciplinas');

$qtdAlunos  = count(array_filter($usuarios, fn($u) => $u['tipo'] === 'aluno'));
$qtdProfs   = count(array_filter($usuarios, fn($u) => $u['tipo'] === 'professor'));
$qtdAdmins  = count(array_filter($usuarios, fn($u) => $u['tipo'] === 'administracao'));

$evPublico = [];
foreach ($eventos as $ev) {
    $evPublico[$ev['publico']] = ($evPublico[$ev['publico']] ?? 0) + 1;
}

$comPublico = [];
foreach ($comunicados as $c) {
    $comPublico[$c['publico']] = ($comPublico[$c['publico']] ?? 0) + 1;
}

$discMap    = array_column($disciplinas, 'nome', 'id');
$notasPorDisc = [];
foreach ($notas as $n) {
    $disc = $discMap[$n['id_disciplina']] ?? 'Desconhecida';
    if (!isset($notasPorDisc[$disc])) $notasPorDisc[$disc] = [];
    $notasPorDisc[$disc][] = $n['valor'];
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Relatórios</title>
  <meta name="description" content="Dashboard de relatórios e estatísticas da plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include '../inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title"> Relatórios e Estatísticas</h1>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-numero"><?= count($usuarios) ?></div>
      <div class="stat-label">Total de usuários</div>
    </div>
    <div class="stat-card verde">
      <div class="stat-numero"><?= $qtdAlunos ?></div>
      <div class="stat-label">Alunos</div>
    </div>
    <div class="stat-card laranja">
      <div class="stat-numero"><?= $qtdProfs ?></div>
      <div class="stat-label">Professores</div>
    </div>
    <div class="stat-card vermelho">
      <div class="stat-numero"><?= $qtdAdmins ?></div>
      <div class="stat-label">Administradores</div>
    </div>
    <div class="stat-card">
      <div class="stat-numero"><?= count($turmas) ?></div>
      <div class="stat-label">Turmas ativas</div>
    </div>
    <div class="stat-card verde">
      <div class="stat-numero"><?= count($eventos) ?></div>
      <div class="stat-label">Eventos cadastrados</div>
    </div>
    <div class="stat-card laranja">
      <div class="stat-numero"><?= count($comunicados) ?></div>
      <div class="stat-label">Comunicados</div>
    </div>
    <div class="stat-card vermelho">
      <div class="stat-numero"><?= count($tarefas) ?></div>
      <div class="stat-label">Tarefas publicadas</div>
    </div>
  </div>

  <div class="grid-2">
    <div class="card">
      <h2> Usuários por Perfil</h2>
      <table>
        <thead><tr><th>Perfil</th><th>Quantidade</th><th>% do total</th></tr></thead>
        <tbody>
          <?php
            $perfis = [
                'Aluno'         => $qtdAlunos,
                'Professor'     => $qtdProfs,
                'Administração' => $qtdAdmins,
            ];
            $total = count($usuarios);
            foreach ($perfis as $label => $qtd):
          ?>
            <tr>
              <td><?= $label ?></td>
              <td><?= $qtd ?></td>
              <td><?= $total > 0 ? round($qtd/$total*100) : 0 ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h2> Eventos por Público-Alvo</h2>
      <?php if (empty($eventos)): ?>
        <p class="vazio">Nenhum evento cadastrado.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Público</th><th>Qtd. Eventos</th></tr></thead>
          <tbody>
            <?php foreach ($evPublico as $pub => $qtd): ?>
              <tr><td><?= e($pub) ?></td><td><?= $qtd ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2> Comunicados por Público</h2>
      <?php if (empty($comunicados)): ?>
        <p class="vazio">Nenhum comunicado.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Público</th><th>Qtd. Comunicados</th></tr></thead>
          <tbody>
            <?php foreach ($comPublico as $pub => $qtd): ?>
              <tr><td><?= e($pub) ?></td><td><?= $qtd ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2> Média de Notas por Disciplina</h2>
      <?php if (empty($notas)): ?>
        <p class="vazio">Nenhuma nota lançada.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Disciplina</th><th>Nº Notas</th><th>Média</th></tr></thead>
          <tbody>
            <?php foreach ($notasPorDisc as $disc => $vals): ?>
              <?php $media = array_sum($vals) / count($vals); ?>
              <tr>
                <td><?= e($disc) ?></td>
                <td><?= count($vals) ?></td>
                <td>
                  <span class="badge <?= $media >= 7 ? 'badge-verde' : ($media >= 5 ? 'badge-laranja' : 'badge-vermelho') ?>">
                    <?= number_format($media, 1) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h2> Lista Completa de Usuários</h2>
    <table>
      <thead>
        <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Perfil</th></tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= e($u['nome']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td>
              <?php
                $bc = match($u['tipo']) {
                    'administracao' => 'badge-vermelho',
                    'professor'     => 'badge-verde',
                    default         => 'badge-azul',
                };
              ?>
              <span class="badge <?= $bc ?>"><?= labelTipo($u['tipo']) ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
