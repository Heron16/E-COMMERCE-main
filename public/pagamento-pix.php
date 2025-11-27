<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Agendamento.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$agendamento_id = $_GET['id'] ?? 0;

if (!$agendamento_id) {
    redirect('meus-agendamentos.php');
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("\n    SELECT a.*, c.nome as cliente_nome, c.email as cliente_email\n    FROM agendamentos a\n    INNER JOIN clientes c ON a.cliente_id = c.id\n    WHERE a.id = ? AND a.cliente_id = ?\n");
$stmt->execute([$agendamento_id, $_SESSION['cliente_id']]);
$agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agendamento) {
    redirect('meus-agendamentos.php');
}
$stmt = $db->prepare("
    SELECT * FROM pagamentos_pix 
    WHERE agendamento_id = ?
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute([$agendamento_id]);
$pagamento_pix = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pagamento_pix) {
    $erro = 'Informações de pagamento PIX não encontradas.';
}

$page_title = "Pagamento PIX - " . APP_NAME;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .pix-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .pix-logo {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .valor-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .valor-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .valor-total {
            color: #28a745;
            font-size: 36px;
            font-weight: bold;
        }
        
        .qr-code-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            margin: 30px 0;
            display: inline-block;
        }
        
        .qr-code {
            width: 250px;
            height: 250px;
            background: white;
        }
        
        .chave-pix-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .chave-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .chave-pix {
            background: white;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color: #333;
            word-break: break-all;
            margin-bottom: 15px;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .instrucoes {
            text-align: left;
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .instrucoes h3 {
            color: #2196F3;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .instrucoes ol {
            margin-left: 20px;
        }
        
        .instrucoes li {
            margin: 10px 0;
            color: #555;
            line-height: 1.6;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .status-pendente {
            background: #ffc107;
            color: #000;
        }
        
        .status-confirmado {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="pix-container">
        <div class="pix-logo">🔷</div>
        <h1>Pagamento via PIX</h1>
        <p style="color: #666; margin-bottom: 20px;">Agendamento #<?php echo $agendamento_id; ?></p>
        
        <?php if (isset($erro)): ?>
            <div class="alert alert-warning"><?php echo $erro; ?></div>
        <?php else: ?>
            
            <div class="valor-container">
                <div class="valor-label">Valor a pagar</div>
                <div class="valor-total">R$ <?php echo number_format($agendamento['valor_total'], 2, ',', '.'); ?></div>
                <span class="status-badge status-<?php echo $pagamento_pix['status']; ?>">
                    <?php echo ucfirst($pagamento_pix['status']); ?>
                </span>
            </div>
            
            <div class="qr-code-container">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($pagamento_pix['chave_pix']); ?>" 
                     alt="QR Code PIX" 
                     class="qr-code">
            </div>
            
            <p style="color: #666; margin-bottom: 20px;">Escaneie o QR Code com o app do seu banco</p>
            
            <div class="chave-pix-container">
                <div class="chave-label">Ou copie a chave PIX:</div>
                <div class="chave-pix" id="chave-pix"><?php echo $pagamento_pix['chave_pix']; ?></div>
                <button class="btn" onclick="copiarChavePix()">📋 Copiar Chave PIX</button>
            </div>
            
            <div class="instrucoes">
                <h3>📱 Como pagar</h3>
                <ol>
                    <li>Abra o aplicativo do seu banco</li>
                    <li>Escolha a opção Pix</li>
                    <li>Selecione "Ler QR Code" ou "Pix Copia e Cola"</li>
                    <li>Escaneie o código ou cole a chave PIX</li>
                    <li>Confirme o pagamento</li>
                </ol>
            </div>
            
            <div class="alert alert-warning">
                ⏰ O pagamento pode levar alguns minutos para ser confirmado.
            </div>
            
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="meus-agendamentos.php" class="btn btn-secondary">Voltar para Agendamentos</a>
            <button class="btn" onclick="window.close()">Fechar</button>
        </div>
    </div>
    
    <script>
        function copiarChavePix() {
            const chavePix = document.getElementById('chave-pix').textContent;
            
            const textarea = document.createElement('textarea');
            textarea.value = chavePix;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Chave PIX copiada para a área de transferência!');
        }
        
        setInterval(function() {
            fetch('api/verificar_pagamento.php?agendamento_id=<?php echo $agendamento_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.confirmado) {
                        alert('Pagamento confirmado! Obrigado.');
                        window.location.href = 'meus-agendamentos.php';
                    }
                })
                .catch(error => console.error('Erro ao verificar pagamento:', error));
        }, 10000);
    </script>
</body>
</html>
