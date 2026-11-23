<?php
/**
 * Cadastrar usuário.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

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
            $erros[] = 'Já existe um usuário com este e-mail.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha, data_cadastro) VALUES (?, ?, ?, NOW())'
            );
            $stmt->execute([$nome, $email, $hash]);

            setFlash('sucesso', 'Usuário cadastrado com sucesso.');
            header('Location: ' . url('usuarios/listar.php'));
            exit;
        }
    }
}

$tituloPagina = 'Cadastrar usuário - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Cadastrar usuário</h1>
    <p class="text-muted mb-0">Preencha os dados do novo usuário</p>
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

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="" class="row g-3" autocomplete="off">
            <div class="col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= e($nome) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="col-md-6">
                <label for="senha_confirmacao" class="form-label">Confirmar senha</label>
                <input type="password" class="form-control" id="senha_confirmacao" name="senha_confirmacao" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="<?= e(url('usuarios/listar.php')) ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
