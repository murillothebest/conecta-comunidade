<?php
/**
 * Excluir usuário (com confirmação).
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$pdo = getConnection();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id_usuario, nome, email FROM usuarios WHERE id_usuario = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    setFlash('erro', 'Usuário não encontrado.');
    header('Location: ' . url('usuarios/listar.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((int) $_SESSION['usuario_id'] === $id) {
        setFlash('erro', 'Você não pode excluir o usuário logado.');
        header('Location: ' . url('usuarios/listar.php'));
        exit;
    }

    // Impede exclusão se o usuário for responsável por ações (FK RESTRICT)
    $checkAcoes = $pdo->prepare('SELECT COUNT(*) FROM acoes WHERE id_usuario = ?');
    $checkAcoes->execute([$id]);
    if ((int) $checkAcoes->fetchColumn() > 0) {
        setFlash('erro', 'Não é possível excluir: este usuário possui ações vinculadas.');
        header('Location: ' . url('usuarios/listar.php'));
        exit;
    }

    $del = $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
    $del->execute([$id]);

    setFlash('sucesso', 'Usuário excluído com sucesso.');
    header('Location: ' . url('usuarios/listar.php'));
    exit;
}

$tituloPagina = 'Excluir usuário - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Excluir usuário</h1>
    <p class="text-muted mb-0">Confirme a exclusão do registro</p>
</div>

<div class="card border-danger shadow-sm">
    <div class="card-body">
        <p class="mb-3">Tem certeza de que deseja excluir o usuário abaixo?</p>
        <ul>
            <li><strong>ID:</strong> <?= (int) $usuario['id_usuario'] ?></li>
            <li><strong>Nome:</strong> <?= e($usuario['nome']) ?></li>
            <li><strong>E-mail:</strong> <?= e($usuario['email']) ?></li>
        </ul>
        <form method="post" action="" class="d-flex gap-2">
            <input type="hidden" name="id" value="<?= (int) $usuario['id_usuario'] ?>">
            <button type="submit" class="btn btn-danger">Sim, excluir</button>
            <a href="<?= e(url('usuarios/listar.php')) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
