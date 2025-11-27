<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/pagamento.php';
require_once __DIR__ . '/../app/models/Cliente.php';
require_once __DIR__ . '/../app/models/Servico.php';
require_once __DIR__ . '/../app/models/Agendamento.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$erro = '';
$sucesso = '';
$agendamento_id = null;
$forma_pagamento = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_agendamento = $_POST['data_agendamento'] ?? '';
    $hora_agendamento = $_POST['hora_agendamento'] ?? '';
    $tipo_veiculo = $_POST['tipo_veiculo'] ?? '';
    $placa_veiculo = $_POST['placa_veiculo'] ?? '';
    $modelo_veiculo = $_POST['modelo_veiculo'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';
    $servicos_selecionados = json_decode($_POST['servicos_json'] ?? '[]', true);
    
    if (empty($data_agendamento) || empty($hora_agendamento) || empty($tipo_veiculo)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (empty($servicos_selecionados)) {
        $erro = 'Por favor, selecione pelo menos um serviço.';
    } elseif (empty($forma_pagamento)) {
        $erro = 'Por favor, selecione uma forma de pagamento.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();
            
            $valor_total = 0;
            $servico_model = new Servico($db);
            
            foreach ($servicos_selecionados as $servico_sel) {
                $servico_model->id = $servico_sel['id'];
                $valor = $servico_model->calcularValor($tipo_veiculo, 1);
                $valor_total += $valor;
                
                $disponibilidade = $servico_model->verificarDisponibilidade(1);
                if ($disponibilidade != 'DISPONIVEL') {
                    throw new Exception('Serviço ' . $servico_sel['nome'] . ' não está disponível no momento.');
                }
            }
            
            $agendamento = new Agendamento($db);
            $agendamento->cliente_id = $_SESSION['cliente_id'];
            $agendamento->data_agendamento = $data_agendamento;
            $agendamento->hora_agendamento = $hora_agendamento;
            $agendamento->tipo_veiculo = $tipo_veiculo;
            $agendamento->placa_veiculo = $placa_veiculo;
            $agendamento->modelo_veiculo = $modelo_veiculo;
            $agendamento->observacoes = $observacoes;
            $agendamento->valor_total = $valor_total;
            $agendamento->forma_pagamento = $forma_pagamento;
            $agendamento->status = 'pendente';
            
            if ($agendamento->create()) {
                $agendamento_id = $agendamento->id;
                
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
                
                if ($forma_pagamento === 'pix') {
                    $chave_pix = PIX_CHAVE;
                    $descricao = 'Agendamento #' . $agendamento_id . ' - Lavagem de veículo';
                    $qr_code_texto = gerarPayloadPix($chave_pix, $valor_total, $descricao);
                    
                    $stmt = $db->prepare("INSERT INTO pagamentos_pix (agendamento_id, chave_pix, qr_code_texto, valor, status) VALUES (?, ?, ?, ?, 'pendente')");
                    $stmt->execute([$agendamento_id, $chave_pix, $qr_code_texto, $valor_total]);
                }
                
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

include __DIR__ . '/../app/views/agendamento.php';
