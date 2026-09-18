<?php
// Conexao com o banco de dados MySQL - CEON
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // padrao XAMPP: sem senha
define('DB_NAME', 'ceon');
define('DB_CHARSET', 'utf8mb4');

function getConn(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['erro' => 'Falha na conexao com o banco de dados: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
