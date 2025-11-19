<?php
/**
 * Configurações Gerais do Sistema
 */

// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Definir constantes
define('BASE_URL', 'http://localhost/E-COMMERCE-main/public/');
define('APP_NAME', 'Sistema de Lavagem de Veículos');

// Função para autoload de classes
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

// Função auxiliar para redirecionar
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Função auxiliar para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['cliente_id']);
}

// Função auxiliar para verificar se admin está logado
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Função para formatar moeda
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

// Função para formatar data e hora
function formatarDataHora($data) {
    return date('d/m/Y H:i', strtotime($data));
}
?>
