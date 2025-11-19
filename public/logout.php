<?php
/**
 * Página de Logout
 */

require_once __DIR__ . '/../config/config.php';

// Destruir sessão
session_destroy();

// Redirecionar para home
redirect('index.php');
?>
