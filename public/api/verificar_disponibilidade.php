<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$acao = $_GET['acao'] ?? '';

try {
    if ($acao === 'horarios') {
        $data = $_GET['data'] ?? '';
        
        if (empty($data)) {
            throw new Exception('Data não informada');
        }
        
        $horarios_disponiveis = [
            '08:00:00', '09:00:00', '10:00:00', '11:00:00', 
            '12:00:00', '13:00:00', '14:00:00', '15:00:00', 
            '16:00:00', '17:00:00', '18:00:00'
        ];
        
        $query = "SELECT hora_agendamento 
                  FROM agendamentos 
                  WHERE data_agendamento = :data 
                  AND status NOT IN ('cancelado', 'concluido')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':data', $data);
        $stmt->execute();
        
        $horarios_ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $horarios_liberados = array_filter($horarios_disponiveis, function($hora) use ($horarios_ocupados) {
            return !in_array($hora, $horarios_ocupados);
        });
        
        echo json_encode([
            'sucesso' => true,
            'horarios_disponiveis' => array_values($horarios_liberados)
        ]);
        
    } elseif ($acao === 'dias') {
        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');
        
        $query = "SELECT data_agendamento, COUNT(*) as total 
                  FROM agendamentos 
                  WHERE MONTH(data_agendamento) = :mes 
                  AND YEAR(data_agendamento) = :ano 
                  AND status NOT IN ('cancelado')
                  GROUP BY data_agendamento 
                  HAVING total >= 9";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':ano', $ano);
        $stmt->execute();
        
        $dias_lotados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'sucesso' => true,
            'dias_lotados' => $dias_lotados
        ]);
        
    } else {
        throw new Exception('Ação não especificada');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
?>
