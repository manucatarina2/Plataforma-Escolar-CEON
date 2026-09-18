<?php
session_start();

function lerDados($nome) {
    $caminho = __DIR__ . '/dados/' . $nome . '.php';
    if (!file_exists($caminho)) return [];
    return include $caminho;
}

function salvarDados($nome, $dados) {
    $caminho = __DIR__ . '/dados/' . $nome . '.php';
    $conteudo = "<?php\nreturn " . var_export($dados, true) . ";\n";
    file_put_contents($caminho, $conteudo);
}

function proximoId($lista) {
    if (empty($lista)) return 1;
    return max(array_column($lista, 'id')) + 1;
}

function exigirLogin() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: ' . base() . 'login.php');
        exit;
    }
}

function exigirTipo($tipo) {
    exigirLogin();
    if ($_SESSION['usuario']['tipo'] !== $tipo) {
        header('Location: ' . base() . 'dashboard.php');
        exit;
    }
}

function ehTipo($tipo) {
    return isset($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] === $tipo;
}

function base() {
    return '/MóduloA/';
}

function e($t) {
    return htmlspecialchars($t ?? '', ENT_QUOTES, 'UTF-8');
}

function dataBR($d) {
    if (!$d) return '';
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : $d;
}

function labelTipo($tipo) {
    $map = [
        'administracao' => 'Administração',
        'professor'     => 'Professor',
        'aluno'         => 'Aluno',
    ];
    return $map[$tipo] ?? ucfirst($tipo);
}
?>