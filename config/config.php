<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

define('BASE_URL', 'http://localhost/E-COMMERCE-main/public/');
define('APP_NAME', 'Sistema de Lavagem de Veículos');

spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/../app/models/' . $class_name . '.php',
        __DIR__ . '/../app/controllers/' . $class_name . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['cliente_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

function formatarDataHora($data) {
    return date('d/m/Y H:i', strtotime($data));
}
?>
