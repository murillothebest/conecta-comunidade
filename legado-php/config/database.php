<?php
/**
 * Conecta Comunidade - Configuração do banco de dados (PDO)
 * Ajuste host, nome do banco, usuário e senha conforme o XAMPP.
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'conecta_comunidade');
define('DB_USER', 'root');
define('DB_PASS', ''); // Padrão do XAMPP: senha vazia
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma conexão PDO única (singleton simples).
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die('Erro ao conectar ao banco de dados. Verifique config/database.php e se o MySQL está ativo no XAMPP.');
    }

    return $pdo;
}
