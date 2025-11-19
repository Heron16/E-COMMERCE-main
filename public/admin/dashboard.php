<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Agendamento.php';
require_once __DIR__ . '/../../app/models/Servico.php';
require_once __DIR__ . '/../../app/models/Cliente.php';

if (!isAdminLoggedIn()) {
    redirect('admin/');
}

$database = new Database();
$db = $database->getConnection();

$agendamento_model = new Agendamento($db);
$servico_model = new Servico($db);
$cliente_model = new Cliente($db);

$total_agendamentos_mes = $agendamento_model->totalAgendamentosMes();
$total_lavagens_mes = $agendamento_model->totalLavagensMes();
$total_lavagens_semana = $agendamento_model->totalLavagensSemana();
$receita_mensal = $agendamento_model->receitaMensal();
$receita_semanal = $agendamento_model->receitaSemanal();
$total_clientes = $cliente_model->count();

$stmt_servicos = $servico_model->maissolicitados(5);
$servicos_mais_solicitados = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

$stmt_dashboard = $agendamento_model->getDashboardSemanal();
$dashboard_semanal = $stmt_dashboard->fetchAll(PDO::FETCH_ASSOC);

$stmt_agendamentos = $agendamento_model->readAll();
$agendamentos_recentes = $stmt_agendamentos->fetchAll(PDO::FETCH_ASSOC);
$agendamentos_recentes = array_slice($agendamentos_recentes, 0, 10);

$page_title = "Dashboard - Admin";
include __DIR__ . '/../../app/views/admin/header_admin.php';
?>

<div class="admin-content">
    <h2>Dashboard de Indicadores</h2>
    
    <div class="dashboard-cards">
        <div class="dashboard-card card-blue">
            <div class="card-icon">📅</div>
            <div class="card-content">
                <h3>Agendamentos do Mês</h3>
                <p class="card-value"><?php echo $total_agendamentos_mes; ?></p>
            </div>
        </div>
        
        <div class="dashboard-card card-green">
            <div class="card-icon">✓</div>
            <div class="card-content">
                <h3>Lavagens do Mês</h3>
                <p class="card-value"><?php echo $total_lavagens_mes; ?></p>
            </div>
        </div>
        
        <div class="dashboard-card card-purple">
            <div class="card-icon">📊</div>
            <div class="card-content">
                <h3>Lavagens da Semana</h3>
                <p class="card-value"><?php echo $total_lavagens_semana; ?></p>
            </div>
        </div>
        
        <div class="dashboard-card card-orange">
            <div class="card-icon">💰</div>
            <div class="card-content">
                <h3>Receita Mensal</h3>
                <p class="card-value"><?php echo formatarMoeda($receita_mensal); ?></p>
            </div>
        </div>
        
        <div class="dashboard-card card-teal">
            <div class="card-icon">💵</div>
            <div class="card-content">
                <h3>Receita Semanal</h3>
                <p class="card-value"><?php echo formatarMoeda($receita_semanal); ?></p>
            </div>
        </div>
        
        <div class="dashboard-card card-pink">
            <div class="card-icon">👥</div>
            <div class="card-content">
                <h3>Total de Clientes</h3>
                <p class="card-value"><?php echo $total_clientes; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Gráficos e Tabelas -->
    <div class="dashboard-grid">
        <div class="dashboard-widget">
            <h3>Serviços Mais Solicitados</h3>
            <?php if (!empty($servicos_mais_solicitados)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Quantidade</th>
                            <th>Receita</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos_mais_solicitados as $servico): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($servico['servico']); ?></td>
                            <td><?php echo $servico['quantidade_vendida']; ?></td>
                            <td><?php echo formatarMoeda($servico['receita_total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-widget">
            <h3>Desempenho Semanal</h3>
            <?php if (!empty($dashboard_semanal)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Agendamentos</th>
                            <th>Lavagens</th>
                            <th>Receita</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard_semanal as $dia): ?>
                        <tr>
                            <td><?php echo formatarData($dia['data']); ?></td>
                            <td><?php echo $dia['total_agendamentos']; ?></td>
                            <td><?php echo $dia['lavagens_concluidas']; ?></td>
                            <td><?php echo formatarMoeda($dia['receita']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum dado disponível para a última semana</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-widget">
        <h3>Agendamentos Recentes</h3>
        <?php if (!empty($agendamentos_recentes)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data/Hora</th>
                        <th>Veículo</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos_recentes as $ag): ?>
                    <tr>
                        <td>#<?php echo $ag['id']; ?></td>
                        <td><?php echo htmlspecialchars($ag['cliente_nome']); ?></td>
                        <td>
                            <?php echo formatarData($ag['data_agendamento']); ?>
                            <?php echo date('H:i', strtotime($ag['hora_agendamento'])); ?>
                        </td>
                        <td><?php echo ucfirst($ag['tipo_veiculo']); ?></td>
                        <td><?php echo formatarMoeda($ag['valor_total']); ?></td>
                        <td>
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
                        </td>
                        <td>
                            <a href="agendamentos.php?id=<?php echo $ag['id']; ?>" class="btn btn-sm">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="widget-footer">
                <a href="agendamentos.php" class="btn btn-primary">Ver Todos os Agendamentos</a>
            </div>
        <?php else: ?>
            <p>Nenhum agendamento encontrado</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../app/views/admin/footer_admin.php'; ?>
