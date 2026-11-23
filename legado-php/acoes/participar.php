<?php
/**
 * Participar de uma ação (inscrição).
 */
require_once __DIR__ . '/../includes/auth.php';
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('painel.php'));
    exit;
}

$pdo = getConnection();
$idAcao = (int) ($_POST['id_acao'] ?? 0);
$idUsuario = (int) $_SESSION['usuario_id'];
$voltar = url('acoes/visualizar.php?id=' . $idAcao);

$stmt = $pdo->prepare('SELECT id_acao FROM acoes WHERE id_acao = ?');
$stmt->execute([$idAcao]);
if (!$stmt->fetch()) {
    setFlash('erro', 'Ação não encontrada.');
    header('Location: ' . url('painel.php'));
    exit;
}

$check = $pdo->prepare('SELECT id_inscricao FROM inscricoes WHERE id_usuario = ? AND id_acao = ?');
$check->execute([$idUsuario, $idAcao]);
if ($check->fetch()) {
    setFlash('erro', 'Você já está inscrito nesta ação.');
    header('Location: ' . $voltar);
    exit;
}

try {
    $ins = $pdo->prepare('INSERT INTO inscricoes (id_usuario, id_acao, data_inscricao) VALUES (?, ?, NOW())');
    $ins->execute([$idUsuario, $idAcao]);
    setFlash('sucesso', 'Inscrição realizada com sucesso!');
} catch (PDOException $e) {
    // UNIQUE (id_usuario, id_acao) impede duplicidade
    setFlash('erro', 'Não foi possível concluir a inscrição. Você já pode estar inscrito.');
}

header('Location: ' . $voltar);
exit;
