<?php
require 'funcoes.php';
exigirLogin();
$paginaAtual = 'cardapio';

$tipo    = $_SESSION['usuario']['tipo'];
$msg     = '';
$cardapio = lerDados('cardapio');

// ── POST: admin edita o cardápio ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tipo === 'administracao') {
    $novo = [];
    $dias = ['Segunda','Terça','Quarta','Quinta','Sexta'];
    foreach ($dias as $dia) {
        $chave = 'ref_' . strtolower(iconv('UTF-8','ASCII//TRANSLIT',$dia));
        $refeicao = trim($_POST[$chave] ?? '');
        $novo[] = ['dia' => $dia, 'refeicao' => $refeicao];
    }
    salvarDados('cardapio', $novo);
    $cardapio = $novo;
    $msg = 'Cardápio atualizado com sucesso!';
}

// Mapeia por dia para fácil acesso no formulário
$cardapioMap = [];
foreach ($cardapio as $item) {
    $cardapioMap[$item['dia']] = $item['refeicao'];
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Cardápio</title>
  <meta name="description" content="Cardápio semanal da cantina escolar.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include 'inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Cardápio da Semana</h1>

  <?php if ($msg): ?><div class="alerta alerta-ok"><?= e($msg) ?></div><?php endif; ?>

  <!-- Visualização -->
  <div class="card">
    <h2>Refeições desta semana</h2>
    <div class="cardapio-grid">
      <?php foreach ($cardapio as $item): ?>
        <div class="cardapio-dia">
          <div class="dia-nome"><?= e($item['dia']) ?></div>
          <div class="dia-refeicao"><?= e($item['refeicao']) ?: '—' ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if ($tipo === 'administracao'): ?>
  <!-- Formulário de edição (admin) -->
  <div class="card">
    <h2>Editar Cardápio</h2>
    <form method="post" id="form-cardapio">
      <?php foreach (['Segunda','Terça','Quarta','Quinta','Sexta'] as $dia): ?>
        <?php $chave = 'ref_' . strtolower(iconv('UTF-8','ASCII//TRANSLIT',$dia)); ?>
        <div class="form-grupo">
          <label for="<?= $chave ?>"><?= $dia ?>-feira</label>
          <input type="text" id="<?= $chave ?>" name="<?= $chave ?>" maxlength="150"
                 value="<?= e($cardapioMap[$dia] ?? '') ?>">
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-primario" id="btn-salvar-cardapio">Salvar Cardápio</button>
    </form>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
