<?php
require '../funcoes.php';
exigirTipo('administracao');
$paginaAtual = 'usuarios';

$msg      = '';
$erro     = '';
$usuarios = lerDados('usuarios');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'salvar') {
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $tipo  = $_POST['tipo'] ?? 'aluno';

        if (!$nome || !$email) {
            $erro = 'Nome e e-mail são obrigatórios.';
        } else {
            if ($acao === 'criar') {
                foreach ($usuarios as $u) {
                    if ($u['email'] === $email) { $erro = 'E-mail já cadastrado.'; break; }
                }
                if (!$erro) {
                    $usuarios[] = [
                        'id'    => proximoId($usuarios),
                        'nome'  => $nome,
                        'email' => $email,
                        'senha' => $senha ?: '123',
                        'tipo'  => $tipo,
                    ];
                    salvarDados('usuarios', $usuarios);
                    $msg = 'Usuário criado com sucesso!';
                }
            } else {
                $id = (int)($_POST['id'] ?? 0);
                foreach ($usuarios as &$u) {
                    if ($u['id'] === $id) {
                        $u['nome']  = $nome;
                        $u['email'] = $email;
                        $u['tipo']  = $tipo;
                        if ($senha) $u['senha'] = $senha;
                    }
                }
                unset($u);
                salvarDados('usuarios', $usuarios);
                $msg = 'Usuário atualizado!';
            }
        }
    }

    if ($acao === 'excluir') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$_SESSION['usuario']['id']) {
            $erro = 'Você não pode excluir seu próprio usuário.';
        } else {
            $usuarios = array_values(array_filter($usuarios, fn($u) => $u['id'] !== $id));
            salvarDados('usuarios', $usuarios);
            $msg = 'Usuário excluído.';
        }
    }
}

$editando = null;
if (isset($_GET['editar'])) {
    $eid = (int)$_GET['editar'];
    foreach ($usuarios as $u) {
        if ($u['id'] === $eid) { $editando = $u; break; }
    }
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CEON – Usuários</title>
  <meta name="description" content="Gerenciamento de usuários da plataforma CEON.">
  <link rel="stylesheet" href="/M%C3%B3duloA/css/estilo.css">
</head>
<body>
<?php include '../inc/nav.php'; ?>
<div class="container">
  <h1 class="page-title">Gerenciar Usuários</h1>

  <?php if ($msg): ?><div class="alerta alerta-ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

  <div class="card">
    <h2><?= $editando ? 'Editar Usuário' : 'Novo Usuário' ?></h2>
    <form method="post" id="form-usuario">
      <input type="hidden" name="acao" value="<?= $editando ? 'salvar' : 'criar' ?>">
      <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="nome-usr">Nome completo *</label>
          <input type="text" id="nome-usr" name="nome" maxlength="100" required
                 value="<?= e($editando['nome'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="email-usr">E-mail *</label>
          <input type="email" id="email-usr" name="email" maxlength="100" required
                 value="<?= e($editando['email'] ?? '') ?>">
        </div>
      </div>
      <div class="form-linha">
        <div class="form-grupo">
          <label for="senha-usr">Senha <?= $editando ? '(deixe em branco para manter)' : '*' ?></label>
          <input type="text" id="senha-usr" name="senha" maxlength="50"
                 placeholder="<?= $editando ? 'manter atual' : 'mínimo 3 caracteres' ?>">
        </div>
        <div class="form-grupo">
          <label for="tipo-usr">Tipo de usuário</label>
          <select id="tipo-usr" name="tipo">
            <option value="aluno"         <?= ($editando['tipo'] ?? 'aluno') === 'aluno'         ? 'selected' : '' ?>>Aluno</option>
            <option value="professor"     <?= ($editando['tipo'] ?? '') === 'professor'     ? 'selected' : '' ?>>Professor</option>
            <option value="administracao" <?= ($editando['tipo'] ?? '') === 'administracao' ? 'selected' : '' ?>>Administração</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primario" id="btn-salvar-usr">
        <?= $editando ? 'Salvar' : 'Criar Usuário' ?>
      </button>
      <?php if ($editando): ?>
        <a href="usuarios.php" class="btn btn-secundario" style="margin-left:8px">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="card">
    <h2>Usuários Cadastrados (<?= count($usuarios) ?>)</h2>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Tipo</th>
          <th style="width:110px">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= e($u['nome']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td>
              <?php
                $badgeClass = match($u['tipo']) {
                    'administracao' => 'badge-vermelho',
                    'professor'     => 'badge-verde',
                    default         => 'badge-azul',
                };
              ?>
              <span class="badge <?= $badgeClass ?>"><?= labelTipo($u['tipo']) ?></span>
            </td>
            <td>
              <a href="usuarios.php?editar=<?= $u['id'] ?>" class="btn btn-sm btn-secundario">Editar</a>
              <?php if ($u['id'] !== (int)$_SESSION['usuario']['id']): ?>
              <form method="post" style="display:inline"
                    onsubmit="return confirm('Excluir <?= e($u['nome']) ?>?')">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button class="btn btn-sm btn-vermelho" id="btn-del-usr-<?= $u['id'] ?>">Excluir</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
