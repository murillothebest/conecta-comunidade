<?php
/**
 * Listar ações comunitárias (visão em tabela / gerenciamento).
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$pdo = getConnection();
$acoes = $pdo->query(
    'SELECT a.id_acao, a.titulo, a.data, a.local, a.status, a.id_usuario,
            c.nome AS categoria, u.nome AS responsavel,
            (SELECT COUNT(*) FROM inscricoes i WHERE i.id_acao = a.id_acao) AS total_participantes
     FROM acoes a
     INNER JOIN categorias c ON c.id_categoria = a.id_categoria
     INNER JOIN usuarios u ON u.id_usuario = a.id_usuario
     ORDER BY a.data DESC, a.id_acao DESC'
)->fetchAll();

$tituloPagina = 'Ações - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Ações comunitárias</h1>
        <p class="text-muted mb-0">Lista completa — edite ou exclua apenas as suas</p>
    </div>
    <a href="<?= e(url('acoes/cadastrar.php')) ?>" class="btn btn-primary">Nova ação</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Data</th>
                    <th>Local</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th>Participantes</th>
                    <th>Organizador</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($acoes)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">Nenhuma ação cadastrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($acoes as $a): ?>
                    <tr>
                        <td><?= (int) $a['id_acao'] ?></td>
                        <td><?= e($a['titulo']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($a['data']))) ?></td>
                        <td><?= e($a['local']) ?></td>
                        <td><?= e($a['categoria']) ?></td>
                        <td>
                            <span class="badge <?= e(statusClass($a['status'])) ?>">
                                <?= e($a['status']) ?>
                            </span>
                        </td>
                        <td><?= (int) $a['total_participantes'] ?></td>
                        <td><?= e($a['responsavel']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= e(url('acoes/visualizar.php?id=' . $a['id_acao'])) ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                            <?php if (ehDonoAcao($a)): ?>
                                <a href="<?= e(url('acoes/editar.php?id=' . $a['id_acao'])) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                <a href="<?= e(url('acoes/excluir.php?id=' . $a['id_acao'])) ?>" class="btn btn-sm btn-outline-danger">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
