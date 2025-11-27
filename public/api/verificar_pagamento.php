<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

$agendamento_id = $_GET['agendamento_id'] ?? 0;

if (!$agendamento_id) {
    echo json_encode(['erro' => 'ID do agendamento não fornecido']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("
    SELECT pagamento_confirmado 
    FROM agendamentos 
    WHERE id = ?
");
$stmt->execute([$agendamento_id]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($resultado) {
    echo json_encode([
        'confirmado' => (bool)$resultado['pagamento_confirmado']
    ]);
} else {
    echo json_encode(['erro' => 'Agendamento não encontrado']);
}
