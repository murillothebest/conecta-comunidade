<?php
/**
 * Painel inicial: cards das ações da comunidade.
 */
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$pdo = getConnection();
$idUsuario = (int) $_SESSION['usuario_id'];

$totalAcoes = (int) $pdo->query('SELECT COUNT(*) FROM acoes')->fetchColumn();
$minhasInscricoes = $pdo->prepare('SELECT COUNT(*) FROM inscricoes WHERE id_usuario = ?');
$minhasInscricoes->execute([$idUsuario]);
$totalMinhasInscricoes = (int) $minhasInscricoes->fetchColumn();

$acoes = $pdo->query(
    'SELECT a.id_acao, a.titulo, a.descricao, a.data, a.local, a.status, a.id_usuario,
            c.nome AS categoria,
            (SELECT COUNT(*) FROM inscricoes i WHERE i.id_acao = a.id_acao) AS total_participantes
     FROM acoes a
     INNER JOIN categorias c ON c.id_categoria = a.id_categoria
     ORDER BY a.data ASC, a.id_acao DESC'
)->fetchAll();

// Inscrições do usuário logado (para botão Participar / Cancelar)
$inscricoesUsuario = $pdo->prepare('SELECT id_acao FROM inscricoes WHERE id_usuario = ?');
$inscricoesUsuario->execute([$idUsuario]);
$acoesInscrito = array_map('intval', $inscricoesUsuario->fetchAll(PDO::FETCH_COLUMN));

$tituloPagina = 'Painel - Conecta Comunidade';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Ações da comunidade</h1>
        <p class="text-muted mb-0">Veja, participe e organize mutirões, campanhas e atividades</p>
    </div>
    <a href="<?= e(url('acoes/cadastrar.php')) ?>" class="btn btn-primary">Criar ação</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="text-muted small">Ações disponíveis</div>
                <div class="display-6"><?= $totalAcoes ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="text-muted small">Minhas participações</div>
                <div class="display-6"><?= $totalMinhasInscricoes ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($acoes)): ?>
    <div class="alert alert-info">
        Nenhuma ação cadastrada ainda. Seja o primeiro a
        <a href="<?= e(url('acoes/cadastrar.php')) ?>">criar uma ação</a>.
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($acoes as $acao): ?>
            <?php
            $jaInscrito = in_array((int) $acao['id_acao'], $acoesInscrito, true);
            $souDono = ehDonoAcao($acao);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card action-card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                            <span class="badge text-bg-light border"><?= e($acao['categoria']) ?></span>
                            <span class="badge <?= e(statusClass($acao['status'])) ?>"><?= e($acao['status']) ?></span>
                        </div>
                        <h2 class="h5 card-title"><?= e($acao['titulo']) ?></h2>
                        <p class="card-text text-muted flex-grow-1"><?= e(resumir($acao['descricao'])) ?></p>
                        <ul class="list-unstyled small text-muted mb-3">
                            <li><strong>Data:</strong> <?= e(date('d/m/Y', strtotime($acao['data']))) ?></li>
                            <li><strong>Local:</strong> <?= e($acao['local']) ?></li>
                            <li><strong>Participantes:</strong> <?= (int) $acao['total_participantes'] ?></li>
                        </ul>
                        <div class="d-flex flex-wrap gap-2 mt-auto">
                            <a href="<?= e(url('acoes/visualizar.php?id=' . $acao['id_acao'])) ?>" class="btn btn-outline-secondary btn-sm">Ver detalhes</a>
                            <?php if ($jaInscrito): ?>
                                <form method="post" action="<?= e(url('acoes/cancelar_participacao.php')) ?>" class="d-inline">
                                    <input type="hidden" name="id_acao" value="<?= (int) $acao['id_acao'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar participação</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= e(url('acoes/participar.php')) ?>" class="d-inline">
                                    <input type="hidden" name="id_acao" value="<?= (int) $acao['id_acao'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Participar</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($souDono): ?>
                                <a href="<?= e(url('acoes/editar.php?id=' . $acao['id_acao'])) ?>" class="btn btn-link btn-sm px-1">Editar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
