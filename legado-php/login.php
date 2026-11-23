<?php
/**
 * Tela de login.
 */
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . url('painel.php'));
    exit;
}

$erro = '';
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } else {
        $pdo = getConnection();
        $stmt = $pdo->prepare('SELECT id_usuario, nome, email, senha FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = (int) $usuario['id_usuario'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            header('Location: ' . url('painel.php'));
            exit;
        }

        $erro = 'E-mail ou senha incorretos.';
    }
}

$tituloPagina = 'Login - Conecta Comunidade';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="login-page">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h1 class="h3 text-primary mb-1">Conecta Comunidade</h1>
                        <p class="text-muted mb-0">Organize e participe de ações comunitárias</p>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?= e($flash['tipo'] === 'erro' ? 'danger' : 'success') ?>">
                            <?= e($flash['mensagem']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger"><?= e($erro) ?></div>
                    <?php endif; ?>

                    <form method="post" action="" autocomplete="off" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>

                    <hr>
                    <p class="text-center small mb-0">
                        Ainda não tem conta?
                        <a href="<?= e(url('cadastro.php')) ?>">Cadastre-se</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
