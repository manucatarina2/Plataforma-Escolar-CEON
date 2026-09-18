<?php
require 'funcoes.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    foreach (lerDados('usuarios') as $u) {
        if ($u['email'] === $email && $u['senha'] === $senha) {
            $_SESSION['usuario'] = $u;
            header('Location: /M%C3%B3duloA/dashboard.php');
            exit;
        }
    }
    $erro = 'E-mail ou senha inválidos.';
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Login</title>
  <meta name="description" content="Acesso à plataforma escolar CEON da Escola CESI.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">
      <div class="nome">CEON</div>
      <div class="subtitulo">Plataforma Escolar – Escola CESI</div>
    </div>

    <?php if ($erro): ?>
      <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form method="post" id="form-login">
      <div class="form-grupo">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="seu@email.com" required
               value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-grupo">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="••••••" required>
      </div>
      <button type="submit" class="btn btn-primario" id="btn-entrar">Entrar</button>
    </form>

    <div class="login-hint">
      <strong>Acessos de demonstração:</strong><br>
      admin@ceon.com &nbsp;|&nbsp; joao@ceon.com &nbsp;|&nbsp; manu@ceon.com<br>
      Senha: <strong>123</strong>
    </div>
  </div>
</div>
</body>
</html>