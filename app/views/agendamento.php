<?php
$page_title = "Agendar Lavagem - " . APP_NAME;
include __DIR__ . '/layouts/header.php';
?>

<div class="agendamento-container">
    <h2>Agendar Lavagem</h2>
    
    <?php if ($erro): ?>
        <div class="alert alert-error"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="alert alert-success">
            <?php echo $sucesso; ?>
            <br><br>
            <?php if ($agendamento_id && $forma_pagamento === 'pix'): ?>
                <a href="pagamento-pix.php?id=<?php echo $agendamento_id; ?>" class="btn btn-primary" target="_blank">
                    Ver QR Code do PIX
                </a>
                <br><br>
            <?php endif; ?>
            <a href="meus-agendamentos.php">Ver meus agendamentos</a>
        </div>
    <?php else: ?>
    
    <div class="agendamento-grid">
        <div class="agendamento-form-section">
            <form method="POST" action="" id="form-agendamento" class="agendamento-form">
                <h3>Dados do Agendamento</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_agendamento">Data: *</label>
                        <input type="date" id="data_agendamento" name="data_agendamento" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="carregarHorariosDisponiveis()"
                               value="<?php echo isset($_POST['data_agendamento']) ? $_POST['data_agendamento'] : date('Y-m-d'); ?>">
                        <small id="aviso-data" style="color: #666; display: none;">Este dia está com capacidade limitada</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_agendamento">Horário: *</label>
                        <select id="hora_agendamento" name="hora_agendamento" required disabled>
                            <option value="">Selecione uma data primeiro...</option>
                        </select>
                        <small id="loading-horarios" style="color: #666; display: none;">Carregando horários...</small>
                    </div>
                </div>
                
                <h3>Dados do Veículo</h3>
                
                <div class="form-group">
                    <label for="tipo_veiculo">Tipo de Veículo: *</label>
                    <select id="tipo_veiculo" name="tipo_veiculo" required onchange="atualizarPrecos()">
                        <option value="">Selecione...</option>
                        <option value="moto">Moto</option>
                        <option value="carro" selected>Carro</option>
                        <option value="camioneta">Camioneta</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="placa_veiculo">Placa:</label>
                        <input type="text" id="placa_veiculo" name="placa_veiculo" 
                               placeholder="ABC-1234"
                               value="<?php echo isset($_POST['placa_veiculo']) ? htmlspecialchars($_POST['placa_veiculo']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="modelo_veiculo">Modelo:</label>
                        <input type="text" id="modelo_veiculo" name="modelo_veiculo" 
                               placeholder="Ex: Honda Civic"
                               value="<?php echo isset($_POST['modelo_veiculo']) ? htmlspecialchars($_POST['modelo_veiculo']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3" 
                              placeholder="Alguma informação adicional sobre seu veículo ou preferências..."><?php echo isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : ''; ?></textarea>
                </div>
                
                <h3>Forma de Pagamento *</h3>
                
                <div class="form-group">
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="forma_pagamento" value="pix" required>
                            <span class="payment-label">
                                <i class="icon-pix">💳</i>
                                <strong>PIX</strong>
                                <small>Pagamento instantâneo</small>
                            </span>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="forma_pagamento" value="dinheiro" required>
                            <span class="payment-label">
                                <i class="icon-money">💵</i>
                                <strong>Dinheiro</strong>
                                <small>Pagar no local</small>
                            </span>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="forma_pagamento" value="cartao" required>
                            <span class="payment-label">
                                <i class="icon-card">💳</i>
                                <strong>Cartão</strong>
                                <small>Débito ou crédito no local</small>
                            </span>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" id="servicos_json" name="servicos_json">
                
                <button type="submit" class="btn btn-primary btn-block">Confirmar Agendamento</button>
            </form>
        </div>
        
        <div class="agendamento-resumo-section">
            <div class="resumo-box">
                <h3>Resumo do Agendamento</h3>
                
                <div id="resumo-servicos">
                    <p class="texto-vazio">Nenhum serviço selecionado</p>
                </div>
                
                <div class="resumo-total">
                    <strong>Total:</strong>
                    <span id="resumo-total">R$ 0,00</span>
                </div>
                
                <a href="index.php#servicos" class="btn btn-secondary btn-block">Adicionar Mais Serviços</a>
            </div>
            
            <div class="info-box">
                <h4>Informações Importantes</h4>
                <ul>
                    <li>Chegue com 10 minutos de antecedência</li>
                    <li>Traga seu veículo limpo de objetos pessoais</li>
                    <li>Cancelamentos devem ser feitos com 24h de antecedência</li>
                    <li>Para pagamento via PIX, o QR Code será exibido após confirmar o agendamento</li>
                </ul>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
let diasLotados = [];

document.addEventListener('DOMContentLoaded', function() {
    atualizarResumo();
    carregarDiasLotados();
    
    const dataInput = document.getElementById('data_agendamento');
    if (dataInput.value) {
        carregarHorariosDisponiveis();
    }
});

async function carregarDiasLotados() {
    const dataAtual = new Date();
    const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
    const ano = dataAtual.getFullYear();
    
    try {
        const response = await fetch(`api/verificar_disponibilidade.php?acao=dias&mes=${mes}&ano=${ano}`);
        const data = await response.json();
        
        if (data.sucesso) {
            diasLotados = data.dias_lotados;
            aplicarEstiloDiasLotados();
        }
    } catch (error) {
        console.error('Erro ao carregar dias lotados:', error);
    }
}

function aplicarEstiloDiasLotados() {
    const dataInput = document.getElementById('data_agendamento');
    const avisoData = document.getElementById('aviso-data');
    
    dataInput.addEventListener('change', function() {
        if (diasLotados.includes(this.value)) {
            avisoData.style.display = 'block';
        } else {
            avisoData.style.display = 'none';
        }
    });
}

async function carregarHorariosDisponiveis() {
    const dataInput = document.getElementById('data_agendamento');
    const horaSelect = document.getElementById('hora_agendamento');
    const loadingMsg = document.getElementById('loading-horarios');
    
    const data = dataInput.value;
    
    if (!data) {
        horaSelect.disabled = true;
        horaSelect.innerHTML = '<option value="">Selecione uma data primeiro...</option>';
        return;
    }
    
    horaSelect.disabled = true;
    horaSelect.innerHTML = '<option value="">Carregando...</option>';
    loadingMsg.style.display = 'block';
    
    try {
        const response = await fetch(`api/verificar_disponibilidade.php?acao=horarios&data=${data}`);
        const result = await response.json();
        
        if (result.sucesso) {
            const horarios = result.horarios_disponiveis;
            
            if (horarios.length === 0) {
                horaSelect.innerHTML = '<option value="">Nenhum horário disponível nesta data</option>';
            } else {
                horaSelect.innerHTML = '<option value="">Selecione um horário...</option>';
                
                horarios.forEach(hora => {
                    const option = document.createElement('option');
                    option.value = hora;
                    option.textContent = hora.substring(0, 5);
                    horaSelect.appendChild(option);
                });
                
                horaSelect.disabled = false;
            }
        } else {
            horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
        }
    } catch (error) {
        console.error('Erro ao carregar horários:', error);
        horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
    } finally {
        loadingMsg.style.display = 'none';
    }
}

function atualizarPrecos() {
    const tipoVeiculo = document.getElementById('tipo_veiculo').value;
    
    if (tipoVeiculo) {
        localStorage.setItem('tipo_veiculo', tipoVeiculo);
        atualizarResumo();
    }
}

function atualizarResumo() {
    const resumoServicos = document.getElementById('resumo-servicos');
    const resumoTotal = document.getElementById('resumo-total');
    const servicosJson = document.getElementById('servicos_json');
    
    if (carrinho.length === 0) {
        resumoServicos.innerHTML = '<p class="texto-vazio">Nenhum serviço selecionado</p>';
        resumoTotal.textContent = 'R$ 0,00';
        servicosJson.value = '[]';
        return;
    }
    
    let html = '<ul class="resumo-lista">';
    let total = 0;
    
    carrinho.forEach(item => {
        total += item.preco;
        html += `
            <li class="resumo-item">
                <span class="item-nome">${item.nome}</span>
                <span class="item-preco">R$ ${item.preco.toFixed(2).replace('.', ',')}</span>
            </li>
        `;
    });
    
    html += '</ul>';
    
    resumoServicos.innerHTML = html;
    resumoTotal.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    servicosJson.value = JSON.stringify(carrinho);
}
</script>

<style>
.payment-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.payment-option {
    position: relative;
    cursor: pointer;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    background: white;
    transition: all 0.3s ease;
    text-align: center;
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: #007bff;
    background-color: #e8f4ff;
}

.payment-label i {
    font-size: 2em;
    margin-bottom: 10px;
}

.payment-label strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.payment-label small {
    color: #666;
    font-size: 0.85em;
}

.payment-option:hover .payment-label {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?php include __DIR__ . '/layouts/footer.php'; ?>
