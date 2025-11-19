<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Agendamento.php';

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação (admin ou funcionário logado no painel)
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso não autorizado']);
    exit;
}

// Verificar se os dados foram enviados
if (!isset($_POST['agendamento_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
    exit;
}

$agendamento_id = (int)$_POST['agendamento_id'];
$novo_status = $_POST['status'];

// Validar status
$status_validos = ['pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado'];
if (!in_array($novo_status, $status_validos)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Status inválido']);
    exit;
}

try {
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();
    
    // Usar o modelo Agendamento que já tem o log implementado
    $agendamento = new Agendamento($db);
    $agendamento->id = $agendamento_id;
    
    // Atualizar status (isso já registra o log automaticamente)
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
