<?php
/**
 * Funções de autenticação e helpers gerais.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Exige usuário autenticado. Redireciona para o login se necessário.
 */
function exigirLogin(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /conecta-comunidade/login.php');
        exit;
    }
}

/**
 * Retorna dados do usuário logado ou null.
 */
function usuarioLogado(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id'    => (int) $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
    ];
}

/**
 * Escapa texto para HTML.
 */
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Define mensagem flash (sucesso ou erro).
 */
function setFlash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = [
        'tipo'     => $tipo,
        'mensagem' => $mensagem,
    ];
}

/**
 * Obtém e limpa a mensagem flash.
 */
function getFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * URL base do projeto (ajuste se a pasta mudar).
 */
function url(string $path = ''): string
{
    $base = '/conecta-comunidade';
    $path = ltrim($path, '/');
    return $path === '' ? $base . '/' : $base . '/' . $path;
}

/**
 * Classe CSS do badge de status.
 */
function statusClass(string $status): string
{
    $mapa = [
        'Planejada'     => 'status-planejada',
        'Em andamento'  => 'status-em-andamento',
        'Concluída'     => 'status-concluida',
        'Cancelada'     => 'status-cancelada',
    ];

    return $mapa[$status] ?? 'status-planejada';
}

/**
 * Resume texto longo para cards.
 */
function resumir(?string $texto, int $limite = 120): string
{
    $texto = trim((string) $texto);
    if (mb_strlen($texto) <= $limite) {
        return $texto;
    }
    return rtrim(mb_substr($texto, 0, $limite - 1)) . '…';
}

/**
 * Verifica se o usuário logado é dono da ação.
 */
function ehDonoAcao(array $acao): bool
{
    $usuario = usuarioLogado();
    if (!$usuario) {
        return false;
    }
    return (int) $acao['id_usuario'] === (int) $usuario['id'];
}
