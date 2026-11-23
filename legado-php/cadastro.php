<?php
/**
 * Cadastro de nova conta (público).
 * Fluxo: Cadastre-se → INSERT com password_hash → redireciona ao login.
 */
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . url('painel.php'));
    exit;
}

$erros = [];
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha_confirmacao'] ?? '';

    if ($nome === '' || mb_strlen($nome) < 3) {
        $erros[] = 'Informe um nome com pelo menos 3 caracteres.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 6) {
        $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
    }
    if ($senha !== $senha2) {
        $erros[] = 'A confirmação de senha não confere.';
    }

    if (empty($erros)) {
        $pdo = getConnection();
        $check = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
        $check->execute([$email]);

        if ((int) $check->fetchColumn() > 0) {
            $erros[] = 'Já existe uma conta com este e-mail.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha, data_cadastro) VALUES (?, ?, ?, NOW())'
            );
            $stmt->execute([$nome, $email, $hash]);

            setFlash('sucesso', 'Conta criada com sucesso! Faça login para continuar.');
            header('Location: ' . url('login.php'));
            exit;
        }
    }
}

$tituloPagina = 'Cadastre-se - Conecta Comunidade';
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
    <div class="row justify-content-center align-items-center min-vh-100 py-4">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h1 class="h3 text-primary mb-1">Cadastre-se</h1>
                        <p class="text-muted mb-0">Crie sua conta para participar da comunidade</p>
                    </div>

                    <?php if (!empty($erros)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= e($erro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="" autocomplete="off" novalidate>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome"
                                   value="<?= e($nome) ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= e($email) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                            <div class="form-text">Mínimo de 6 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label for="senha_confirmacao" class="form-label">Confirmar senha</label>
                            <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Criar conta</button>
                    </form>

                    <hr>
                    <p class="text-center small mb-0">
                        Já tem conta?
                        <a href="<?= e(url('login.php')) ?>">Entrar</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
