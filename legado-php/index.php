<?php
/**
 * Redireciona para o login ou painel.
 */
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . url('painel.php'));
} else {
    header('Location: ' . url('login.php'));
}
exit;
