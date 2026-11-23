<?php
/**
 * Cabeçalho compartilhado (Bootstrap + menu).
 * Variáveis esperadas: $tituloPagina (string)
 */

$usuario = usuarioLogado();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina ?? 'Conecta Comunidade') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<?php if ($usuario): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(url('painel.php')) ?>">Conecta Comunidade</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(url('painel.php')) ?>">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(url('acoes/listar.php')) ?>">Ações</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(url('usuarios/listar.php')) ?>">Usuários</a>
                </li>
            </ul>
            <span class="navbar-text text-white me-3">
                Olá, <?= e($usuario['nome']) ?>
            </span>
            <a class="btn btn-outline-light btn-sm" href="<?= e(url('logout.php')) ?>">Sair</a>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="container py-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipo'] === 'erro' ? 'danger' : 'success') ?> alert-dismissible fade show" role="alert">
            <?= e($flash['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
