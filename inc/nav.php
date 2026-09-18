<?php

$u    = $_SESSION['usuario'];
$tipo = $u['tipo'];
$b    = '/M%C3%B3duloA/'; 

$links = [
    ['url' => $b . 'dashboard.php',        'label' => 'Dashboard',   'id' => 'dashboard',   'acesso' => ['administracao','professor','aluno']],
    ['url' => $b . 'admin/usuarios.php',   'label' => 'Usuários',    'id' => 'usuarios',    'acesso' => ['administracao']],
    ['url' => $b . 'admin/turmas.php',     'label' => 'Turmas',      'id' => 'turmas',      'acesso' => ['administracao']],
    ['url' => $b . 'eventos.php',          'label' => 'Eventos',     'id' => 'eventos',     'acesso' => ['administracao','professor','aluno']],
    ['url' => $b . 'comunicados.php',      'label' => 'Comunicados', 'id' => 'comunicados', 'acesso' => ['administracao','professor','aluno']],
    ['url' => $b . 'professor/tarefas.php','label' => 'Tarefas',     'id' => 'tarefas',     'acesso' => ['professor']],
    ['url' => $b . 'aluno/tarefas.php',    'label' => 'Tarefas',     'id' => 'tarefas',     'acesso' => ['aluno']],
    ['url' => $b . 'cardapio.php',         'label' => 'Cardápio',   'id' => 'cardapio',    'acesso' => ['administracao','professor','aluno']],
    ['url' => $b . 'admin/relatorios.php', 'label' => 'Relatórios',  'id' => 'relatorios',  'acesso' => ['administracao']],
];
?>
<header>
  <div class="brand">CEON <span>Plataforma Escolar CESI</span></div>
  <div class="header-right">
    <span class="usuario-nome"><?= e($u['nome']) ?> &middot; <?= labelTipo($tipo) ?></span>
    <a href="<?= $b ?>logout.php" class="btn-sair">Sair</a>
  </div>
</header>
<nav class="nav-tabs">
<?php foreach ($links as $l): ?>
  <?php if (in_array($tipo, $l['acesso'])): ?>
    <a href="<?= $l['url'] ?>"
       class="<?= ($paginaAtual ?? '') === $l['id'] ? 'ativo' : '' ?>">
      <?= $l['label'] ?>
    </a>
  <?php endif; ?>
<?php endforeach; ?>
</nav>
