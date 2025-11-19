<?php
/**
 * Página de Agendamento
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Cliente.php';
require_once __DIR__ . '/../app/models/Servico.php';
require_once __DIR__ . '/../app/models/Agendamento.php';

// Verificar se está logado
if (!isLoggedIn()) {
    redirect('login.php');
}

$erro = '';
$sucesso = '';

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_agendamento = $_POST['data_agendamento'] ?? '';
    $hora_agendamento = $_POST['hora_agendamento'] ?? '';
    $tipo_veiculo = $_POST['tipo_veiculo'] ?? '';
    $placa_veiculo = $_POST['placa_veiculo'] ?? '';
    $modelo_veiculo = $_POST['modelo_veiculo'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    $servicos_selecionados = json_decode($_POST['servicos_json'] ?? '[]', true);
    
    // Validações
    if (empty($data_agendamento) || empty($hora_agendamento) || empty($tipo_veiculo)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (empty($servicos_selecionados)) {
        $erro = 'Por favor, selecione pelo menos um serviço.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Calcular valor total
            $valor_total = 0;
            $servico_model = new Servico($db);
            
            foreach ($servicos_selecionados as $servico_sel) {
                $servico_model->id = $servico_sel['id'];
                $valor = $servico_model->calcularValor($tipo_veiculo, 1);
                $valor_total += $valor;
                
                // Verificar disponibilidade
                $disponibilidade = $servico_model->verificarDisponibilidade(1);
                if ($disponibilidade != 'DISPONIVEL') {
                    throw new Exception('Serviço ' . $servico_sel['nome'] . ' não está disponível no momento.');
                }
            }
            
            // Criar agendamento
            $agendamento = new Agendamento($db);
            $agendamento->cliente_id = $_SESSION['cliente_id'];
            $agendamento->data_agendamento = $data_agendamento;
            $agendamento->hora_agendamento = $hora_agendamento;
            $agendamento->tipo_veiculo = $tipo_veiculo;
            $agendamento->placa_veiculo = $placa_veiculo;
            $agendamento->modelo_veiculo = $modelo_veiculo;
            $agendamento->observacoes = $observacoes;
            $agendamento->valor_total = $valor_total;
            $agendamento->status = 'pendente';
            
            if ($agendamento->create()) {
                // Adicionar itens do agendamento
                foreach ($servicos_selecionados as $servico_sel) {
                    $servico_model->id = $servico_sel['id'];
                    $valor_unitario = $servico_model->calcularValor($tipo_veiculo, 1);
                    
                    $agendamento->adicionarItem(
                        $servico_sel['id'],
                        1,
                        $valor_unitario,
                        $valor_unitario
                    );
                }
                
                $db->commit();
                
                // Limpar carrinho
                echo '<script>localStorage.removeItem("carrinho");</script>';
                
                $sucesso = 'Agendamento realizado com sucesso!';
            } else {
                throw new Exception('Erro ao criar agendamento.');
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $erro = $e->getMessage();
        }
    }
}

$page_title = "Agendar Lavagem - " . APP_NAME;
include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="agendamento-container">
    <h2>Agendar Lavagem</h2>
    
    <?php if ($erro): ?>
        <div class="alert alert-error"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="alert alert-success">
            <?php echo $sucesso; ?>
            <br><a href="meus-agendamentos.php">Ver meus agendamentos</a>
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
                </ul>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
// Carregar serviços do carrinho
let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
let diasLotados = [];

document.addEventListener('DOMContentLoaded', function() {
    atualizarResumo();
    carregarDiasLotados();
    
    // Carregar horários para a data inicial se houver
    const dataInput = document.getElementById('data_agendamento');
    if (dataInput.value) {
        carregarHorariosDisponiveis();
    }
});

// Carregar dias lotados do mês atual
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

// Aplicar estilo visual aos dias lotados no input de data
function aplicarEstiloDiasLotados() {
    const dataInput = document.getElementById('data_agendamento');
    const avisoData = document.getElementById('aviso-data');
    
    // Verificar se a data selecionada está lotada
    dataInput.addEventListener('input', function() {
        if (diasLotados.includes(this.value)) {
            avisoData.style.display = 'block';
            avisoData.style.color = '#ff6b6b';
            avisoData.textContent = 'Atenção: Este dia está com capacidade máxima!';
        } else {
            avisoData.style.display = 'none';
        }
    });
}

// Carregar horários disponíveis para a data selecionada
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
    
    // Mostrar loading
    loadingMsg.style.display = 'block';
    horaSelect.disabled = true;
    horaSelect.innerHTML = '<option value="">Carregando...</option>';
    
    try {
        const response = await fetch(`api/verificar_disponibilidade.php?acao=horarios&data=${data}`);
        const result = await response.json();
        
        if (result.sucesso) {
            const horarios = result.horarios;
            
            if (horarios.length === 0) {
                horaSelect.innerHTML = '<option value="">Nenhum horário disponível nesta data</option>';
            } else {
                horaSelect.innerHTML = '<option value="">Selecione um horário...</option>';
                
                horarios.forEach(hora => {
                    const option = document.createElement('option');
                    option.value = hora;
                    // Formatar hora para exibição (HH:MM)
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
        
        // Atualizar preços no carrinho
        // (implementação simplificada - na prática, buscar preços do servidor)
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

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
