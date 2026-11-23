<?php
/**
 * Listar usuários.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$pdo = getConnection();
$usuarios = $pdo->query(
    'SELECT id_usuario, nome, email, data_cadastro
     FROM usuarios
     ORDER BY nome ASC'
)->fetchAll();

$tituloPagina = 'Usuários - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Usuários</h1>
        <p class="text-muted mb-0">Cadastro e gerenciamento de usuários</p>
    </div>
    <a href="<?= e(url('usuarios/cadastrar.php')) ?>" class="btn btn-primary">Novo usuário</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Data de cadastro</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Nenhum usuário encontrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= (int) $u['id_usuario'] ?></td>
                        <td><?= e($u['nome']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($u['data_cadastro']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= e(url('usuarios/editar.php?id=' . $u['id_usuario'])) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="<?= e(url('usuarios/excluir.php?id=' . $u['id_usuario'])) ?>" class="btn btn-sm btn-outline-danger">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
