<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AgendamentosController.php';

if (!isAdminLoggedIn()) {
    redirect('admin/');
}

$controller = new AgendamentosController();

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $r = $controller->processPost($_POST);
    $mensagem = $r['mensagem'];
    $erro = $r['erro'];
}

$agendamentos = $controller->getList();

$page_title = "Gerenciar Agendamentos - Admin";
include __DIR__ . '/../../app/views/admin/header_admin.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <h2>Gerenciar Agendamentos</h2>
        <div class="admin-stats">
            <span>Total: <strong><?php echo count($agendamentos); ?></strong></span>
        </div>
    </div>
    
    <?php if ($mensagem): ?>
        <div class="alert alert-success"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <?php if ($erro): ?>
        <div class="alert alert-error"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Contato</th>
                    <th>Data/Hora</th>
                    <th>Veículo</th>
                    <th>Placa</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agendamentos as $ag): ?>
                <tr>
                    <td>#<?php echo $ag['id']; ?></td>
                    <td><?php echo htmlspecialchars($ag['cliente_nome']); ?></td>
                    <td><?php echo htmlspecialchars($ag['cliente_telefone']); ?></td>
                    <td>
                        <?php echo formatarData($ag['data_agendamento']); ?><br>
                        <small><?php echo date('H:i', strtotime($ag['hora_agendamento'])); ?></small>
                    </td>
                    <td><?php echo ucfirst($ag['tipo_veiculo']); ?></td>
                    <td><?php echo htmlspecialchars($ag['placa_veiculo'] ?? '-'); ?></td>
                    <td><?php echo formatarMoeda($ag['valor_total']); ?></td>
                    <td>
                        <?php 
                        $pagamento_texto = [
                            'pix' => 'PIX',
                            'dinheiro' => 'Dinheiro',
                            'cartao' => 'Cartão'
                        ];
                        echo $pagamento_texto[$ag['forma_pagamento']] ?? '-';
                        ?>
                    </td>
                    <td>
                        <select onchange="atualizarStatus(<?php echo $ag['id']; ?>, this.value)" class="status-select">
                            <option value="pendente" <?php echo $ag['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="confirmado" <?php echo $ag['status'] == 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                            <option value="em_andamento" <?php echo $ag['status'] == 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="concluido" <?php echo $ag['status'] == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                            <option value="cancelado" <?php echo $ag['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </td>
                    <td>
                        <button onclick="deletarAgendamento(<?php echo $ag['id']; ?>)" class="btn btn-sm btn-danger">Deletar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="POST" action="" id="formStatus" style="display:none;">
    <input type="hidden" name="acao" value="atualizar_status">
    <input type="hidden" name="id" id="status_id">
    <input type="hidden" name="status" id="status_valor">
</form>

<form method="POST" action="" id="formDeletar" style="display:none;">
    <input type="hidden" name="acao" value="deletar">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
async function atualizarStatus(id, novoStatus) {
    const selectElement = event.target;
    const statusAnterior = selectElement.getAttribute('data-status-anterior');
    selectElement.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('agendamento_id', id);
        formData.append('status', novoStatus);
        
        const response = await fetch('mudar_status_agendamento.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.sucesso) {
            selectElement.setAttribute('data-status-anterior', novoStatus);
            mostrarMensagem('Status atualizado com sucesso!', 'success');
        } else {
            selectElement.value = statusAnterior;
            mostrarMensagem('Erro ao atualizar status: ' + result.mensagem, 'error');
        }
    } catch (error) {
        selectElement.value = statusAnterior;
        mostrarMensagem('Erro ao atualizar status. Tente novamente.', 'error');
        console.error('Erro:', error);
    } finally {
        selectElement.disabled = false;
    }
}

function mostrarMensagem(mensagem, tipo) {
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo}`;
    alerta.textContent = mensagem;
    alerta.style.position = 'fixed';
    alerta.style.top = '20px';
    alerta.style.right = '20px';
    alerta.style.zIndex = '9999';
    alerta.style.minWidth = '300px';
    alerta.style.animation = 'slideIn 0.3s ease-out';
    
    document.body.appendChild(alerta);
    
    setTimeout(() => {
        alerta.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => alerta.remove(), 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.setAttribute('data-status-anterior', select.value);
    });
});

function deletarAgendamento(id) {
    if (confirm('Tem certeza que deseja deletar este agendamento?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('formDeletar').submit();
    }
}
</script>

<?php include __DIR__ . '/../../app/views/admin/footer_admin.php'; ?>
