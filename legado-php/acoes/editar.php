<?php
/**
 * Editar ação comunitária.
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

$pdo = getConnection();
$id = (int) ($_GET['id'] ?? 0);
$statusOpcoes = ['Planejada', 'Em andamento', 'Concluída', 'Cancelada'];

$stmt = $pdo->prepare('SELECT * FROM acoes WHERE id_acao = ?');
$stmt->execute([$id]);
$acao = $stmt->fetch();

if (!$acao) {
    setFlash('erro', 'Ação não encontrada.');
    header('Location: ' . url('painel.php'));
    exit;
}

if (!ehDonoAcao($acao)) {
    setFlash('erro', 'Você só pode editar ações criadas por você.');
    header('Location: ' . url('acoes/visualizar.php?id=' . $id));
    exit;
}

$categorias = $pdo->query('SELECT id_categoria, nome FROM categorias ORDER BY nome')->fetchAll();

$erros = [];
$dados = [
    'titulo' => $acao['titulo'],
    'descricao' => $acao['descricao'],
    'data' => $acao['data'],
    'local' => $acao['local'],
    'status' => $acao['status'],
    'id_categoria' => (int) $acao['id_categoria'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['titulo'] = trim($_POST['titulo'] ?? '');
    $dados['descricao'] = trim($_POST['descricao'] ?? '');
    $dados['data'] = trim($_POST['data'] ?? '');
    $dados['local'] = trim($_POST['local'] ?? '');
    $dados['status'] = $_POST['status'] ?? 'Planejada';
    $dados['id_categoria'] = (int) ($_POST['id_categoria'] ?? 0);

    if ($dados['titulo'] === '' || mb_strlen($dados['titulo']) < 3) {
        $erros[] = 'Informe um título com pelo menos 3 caracteres.';
    }
    if ($dados['descricao'] === '') {
        $erros[] = 'Informe a descrição da ação.';
    }
    if ($dados['data'] === '') {
        $erros[] = 'Informe a data da ação.';
    }
    if ($dados['local'] === '') {
        $erros[] = 'Informe o local da ação.';
    }
    if (!in_array($dados['status'], $statusOpcoes, true)) {
        $erros[] = 'Status inválido.';
    }
    if ($dados['id_categoria'] <= 0) {
        $erros[] = 'Selecione uma categoria.';
    }

    if (empty($erros)) {
        $upd = $pdo->prepare(
            'UPDATE acoes
             SET titulo = ?, descricao = ?, data = ?, local = ?, status = ?, id_categoria = ?
             WHERE id_acao = ?'
        );
        $upd->execute([
            $dados['titulo'],
            $dados['descricao'],
            $dados['data'],
            $dados['local'],
            $dados['status'],
            $dados['id_categoria'],
            $id,
        ]);

        setFlash('sucesso', 'Ação alterada com sucesso.');
        header('Location: ' . url('acoes/visualizar.php?id=' . $id));
        exit;
    }
}

$tituloPagina = 'Editar ação - Conecta Comunidade';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Editar ação</h1>
    <p class="text-muted mb-0">Atualize os dados da ação #<?= (int) $id ?></p>
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
        <form method="post" action="" class="row g-3">
            <div class="col-md-8">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= e($dados['titulo']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" value="<?= e($dados['data']) ?>" required>
            </div>
            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="4" required><?= e($dados['descricao']) ?></textarea>
            </div>
            <div class="col-md-4">
                <label for="local" class="form-label">Local</label>
                <input type="text" class="form-control" id="local" name="local" value="<?= e($dados['local']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="id_categoria" class="form-label">Categoria</label>
                <select class="form-select" id="id_categoria" name="id_categoria" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id_categoria'] ?>" <?= (int) $dados['id_categoria'] === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                            <?= e($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <?php foreach ($statusOpcoes as $st): ?>
                        <option value="<?= e($st) ?>" <?= $dados['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
                <a href="<?= e(url('acoes/visualizar.php?id=' . $id)) ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
