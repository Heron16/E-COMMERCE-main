<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Agendamento.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso não autorizado']);
    exit;
}

if (!isset($_POST['agendamento_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
    exit;
}

$agendamento_id = (int)$_POST['agendamento_id'];
$novo_status = $_POST['status'];
$status_validos = ['pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado'];
if (!in_array($novo_status, $status_validos)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Status inválido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $agendamento = new Agendamento($db);
    $agendamento->id = $agendamento_id;

    if ($agendamento->updateStatus($novo_status)) {
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Status atualizado com sucesso'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar status'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
