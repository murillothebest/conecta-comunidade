<?php
/**
 * Visualizar ação comunitária + participar / cancelar.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$pdo = getConnection();
$id = (int) ($_GET['id'] ?? 0);
$idUsuario = (int) $_SESSION['usuario_id'];

$stmt = $pdo->prepare(
    'SELECT a.*, c.nome AS categoria, u.nome AS responsavel, u.email AS email_responsavel
     FROM acoes a
     INNER JOIN categorias c ON c.id_categoria = a.id_categoria
     INNER JOIN usuarios u ON u.id_usuario = a.id_usuario
     WHERE a.id_acao = ?'
);
$stmt->execute([$id]);
$acao = $stmt->fetch();

if (!$acao) {
    setFlash('erro', 'Ação não encontrada.');
    header('Location: ' . url('painel.php'));
    exit;
}

$inscricoes = $pdo->prepare(
    'SELECT i.data_inscricao, u.nome, u.email
     FROM inscricoes i
     INNER JOIN usuarios u ON u.id_usuario = i.id_usuario
     WHERE i.id_acao = ?
     ORDER BY i.data_inscricao DESC'
);
$inscricoes->execute([$id]);
$listaInscricoes = $inscricoes->fetchAll();
$totalParticipantes = count($listaInscricoes);

$checkInscricao = $pdo->prepare('SELECT id_inscricao FROM inscricoes WHERE id_usuario = ? AND id_acao = ?');
$checkInscricao->execute([$idUsuario, $id]);
$jaInscrito = (bool) $checkInscricao->fetch();
$souDono = ehDonoAcao($acao);

$comentarios = $pdo->prepare(
    'SELECT c.comentario, c.data_comentario, u.nome
     FROM comentarios c
     INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
     WHERE c.id_acao = ?
     ORDER BY c.data_comentario DESC'
);
$comentarios->execute([$id]);
$listaComentarios = $comentarios->fetchAll();

$tituloPagina = 'Visualizar ação - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= e($acao['titulo']) ?></h1>
        <p class="text-muted mb-0">Detalhes da ação comunitária</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($jaInscrito): ?>
            <form method="post" action="<?= e(url('acoes/cancelar_participacao.php')) ?>">
                <input type="hidden" name="id_acao" value="<?= (int) $acao['id_acao'] ?>">
                <button type="submit" class="btn btn-outline-danger">Cancelar participação</button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= e(url('acoes/participar.php')) ?>">
                <input type="hidden" name="id_acao" value="<?= (int) $acao['id_acao'] ?>">
                <button type="submit" class="btn btn-primary">Participar da ação</button>
            </form>
        <?php endif; ?>

        <?php if ($souDono): ?>
            <a href="<?= e(url('acoes/editar.php?id=' . $acao['id_acao'])) ?>" class="btn btn-outline-primary">Editar</a>
            <a href="<?= e(url('acoes/excluir.php?id=' . $acao['id_acao'])) ?>" class="btn btn-outline-danger">Excluir</a>
        <?php endif; ?>
        <a href="<?= e(url('painel.php')) ?>" class="btn btn-outline-secondary">Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h5">Descrição</h2>
                <p class="mb-0"><?= nl2br(e($acao['descricao'])) ?></p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><strong>Comentários</strong></div>
            <div class="card-body">
                <?php if (empty($listaComentarios)): ?>
                    <p class="text-muted mb-0">Nenhum comentário registrado.</p>
                <?php else: ?>
                    <?php foreach ($listaComentarios as $c): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-semibold"><?= e($c['nome']) ?></div>
                            <div class="small text-muted mb-1"><?= e(date('d/m/Y H:i', strtotime($c['data_comentario']))) ?></div>
                            <div><?= e($c['comentario']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Status</dt>
                    <dd><span class="badge <?= e(statusClass($acao['status'])) ?>"><?= e($acao['status']) ?></span></dd>
                    <dt>Data</dt>
                    <dd><?= e(date('d/m/Y', strtotime($acao['data']))) ?></dd>
                    <dt>Local</dt>
                    <dd><?= e($acao['local']) ?></dd>
                    <dt>Categoria</dt>
                    <dd><?= e($acao['categoria']) ?></dd>
                    <dt>Organizador</dt>
                    <dd><?= e($acao['responsavel']) ?> <br><span class="small text-muted"><?= e($acao['email_responsavel']) ?></span></dd>
                    <dt>Participantes</dt>
                    <dd class="fs-4 fw-semibold text-primary mb-0"><?= $totalParticipantes ?></dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Lista de participantes (<?= $totalParticipantes ?>)</strong></div>
            <ul class="list-group list-group-flush">
                <?php if (empty($listaInscricoes)): ?>
                    <li class="list-group-item text-muted">Nenhum participante ainda. Seja o primeiro!</li>
                <?php else: ?>
                    <?php foreach ($listaInscricoes as $i): ?>
                        <li class="list-group-item">
                            <div class="fw-semibold"><?= e($i['nome']) ?></div>
                            <div class="small text-muted"><?= e($i['email']) ?> &middot; <?= e(date('d/m/Y H:i', strtotime($i['data_inscricao']))) ?></div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
