<?php
/**
 * Excluir ação comunitária (apenas o dono, com confirmação).
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$pdo = getConnection();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT a.id_acao, a.titulo, a.data, a.status, a.id_usuario, c.nome AS categoria
     FROM acoes a
     INNER JOIN categorias c ON c.id_categoria = a.id_categoria
     WHERE a.id_acao = ?'
);
$stmt->execute([$id]);
$acao = $stmt->fetch();

if (!$acao) {
    setFlash('erro', 'Ação não encontrada.');
    header('Location: ' . url('painel.php'));
    exit;
}

if (!ehDonoAcao($acao)) {
    setFlash('erro', 'Você só pode excluir ações criadas por você.');
    header('Location: ' . url('acoes/visualizar.php?id=' . $id));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $del = $pdo->prepare('DELETE FROM acoes WHERE id_acao = ? AND id_usuario = ?');
    $del->execute([$id, (int) $_SESSION['usuario_id']]);

    setFlash('sucesso', 'Ação excluída com sucesso.');
    header('Location: ' . url('painel.php'));
    exit;
}

$tituloPagina = 'Excluir ação - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Excluir ação</h1>
    <p class="text-muted mb-0">Confirme a exclusão do registro</p>
</div>

<div class="card border-danger shadow-sm">
    <div class="card-body">
        <p class="mb-3">Tem certeza de que deseja excluir a ação abaixo?</p>
        <ul>
            <li><strong>ID:</strong> <?= (int) $acao['id_acao'] ?></li>
            <li><strong>Título:</strong> <?= e($acao['titulo']) ?></li>
            <li><strong>Data:</strong> <?= e(date('d/m/Y', strtotime($acao['data']))) ?></li>
            <li><strong>Categoria:</strong> <?= e($acao['categoria']) ?></li>
            <li><strong>Status:</strong> <?= e($acao['status']) ?></li>
        </ul>
        <p class="small text-muted">Inscrições e comentários vinculados também serão removidos.</p>
        <form method="post" action="" class="d-flex gap-2">
            <input type="hidden" name="id" value="<?= (int) $acao['id_acao'] ?>">
            <button type="submit" class="btn btn-danger">Sim, excluir</button>
            <a href="<?= e(url('acoes/visualizar.php?id=' . $acao['id_acao'])) ?>" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
