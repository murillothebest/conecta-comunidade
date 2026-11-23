<?php
/**
 * Cancelar participação em uma ação.
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

$del = $pdo->prepare('DELETE FROM inscricoes WHERE id_usuario = ? AND id_acao = ?');
$del->execute([$idUsuario, $idAcao]);

if ($del->rowCount() > 0) {
    setFlash('sucesso', 'Participação cancelada com sucesso.');
} else {
    setFlash('erro', 'Você não possui inscrição nesta ação.');
}

header('Location: ' . $voltar);
exit;
