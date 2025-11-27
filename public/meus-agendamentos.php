<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Agendamento.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();
$agendamento = new Agendamento($db);
$stmt = $agendamento->readByCliente($_SESSION['cliente_id']);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Meus Agendamentos - " . APP_NAME;
include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="container">
    <h2>Meus Agendamentos</h2>
    <p>Olá, <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>!</p>
    
    <?php if (empty($agendamentos)): ?>
        <div class="alert alert-info">
            Você ainda não tem agendamentos.
            <br><a href="index.php#servicos">Agendar uma lavagem</a>
        </div>
    <?php else: ?>
        <div class="agendamentos-lista">
            <?php foreach ($agendamentos as $ag): ?>
            <div class="agendamento-card status-<?php echo $ag['status']; ?>">
                <div class="agendamento-header">
                    <h3>Agendamento #<?php echo $ag['id']; ?></h3>
                    <span class="status-badge status-<?php echo $ag['status']; ?>">
                        <?php 
                        $status_texto = [
                            'pendente' => 'Pendente',
                            'confirmado' => 'Confirmado',
                            'em_andamento' => 'Em Andamento',
                            'concluido' => 'Concluído',
                            'cancelado' => 'Cancelado'
                        ];
                        echo $status_texto[$ag['status']] ?? $ag['status'];
                        ?>
                    </span>
                </div>
                
                <div class="agendamento-info">
                    <div class="info-item">
                        <strong>Data:</strong>
                        <?php echo formatarData($ag['data_agendamento']); ?>
                    </div>
                    
                    <div class="info-item">
                        <strong>Horário:</strong>
                        <?php echo date('H:i', strtotime($ag['hora_agendamento'])); ?>
                    </div>
                    
                    <div class="info-item">
                        <strong>Tipo de Veículo:</strong>
                        <?php echo ucfirst($ag['tipo_veiculo']); ?>
                    </div>
                    
                    <?php if (!empty($ag['placa_veiculo'])): ?>
                    <div class="info-item">
                        <strong>Placa:</strong>
                        <?php echo htmlspecialchars($ag['placa_veiculo']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($ag['modelo_veiculo'])): ?>
                    <div class="info-item">
                        <strong>Modelo:</strong>
                        <?php echo htmlspecialchars($ag['modelo_veiculo']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="agendamento-footer">
                    <div class="valor-total">
                        <strong>Total:</strong>
                        <?php echo formatarMoeda($ag['valor_total']); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
