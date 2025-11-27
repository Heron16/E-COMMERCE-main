<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Cliente.php';

$erro = '';

if (isLoggedIn()) {
    redirect('meus-agendamentos.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha) || empty($telefone) || 
        empty($endereco) || empty($cidade) || empty($estado) || empty($cep)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não conferem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $cliente = new Cliente($db);
        
        if ($cliente->emailExiste($email)) {
            $erro = 'Este email já está cadastrado.';
        } else {
            $cliente->nome = $nome;
            $cliente->email = $email;
            $cliente->senha = $senha;
            $cliente->telefone = $telefone;
            $cliente->endereco = $endereco;
            $cliente->cidade = $cidade;
            $cliente->estado = $estado;
            $cliente->cep = $cep;
            
            if ($cliente->create()) {
                redirect('login.php?cadastro=sucesso');
            } else {
                $erro = 'Erro ao criar cadastro. Tente novamente.';
            }
        }
    }
}

$page_title = "Cadastro - " . APP_NAME;
include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="auth-container">
    <div class="auth-box auth-box-large">
        <h2>Cadastro de Cliente</h2>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome Completo: *</label>
                    <input type="text" id="nome" name="nome" required 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email: *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="senha">Senha: *</label>
                    <input type="password" id="senha" name="senha" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha: *</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telefone">Telefone: *</label>
                    <input type="tel" id="telefone" name="telefone" required 
                           placeholder="(11) 98765-4321"
                           value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="cep">CEP: *</label>
                    <input type="text" id="cep" name="cep" required 
                           placeholder="01234-567"
                           value="<?php echo isset($_POST['cep']) ? htmlspecialchars($_POST['cep']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="endereco">Endereço Completo: *</label>
                <input type="text" id="endereco" name="endereco" required 
                       placeholder="Rua, número, complemento"
                       value="<?php echo isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : ''; ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cidade">Cidade: *</label>
                    <input type="text" id="cidade" name="cidade" required 
                           value="<?php echo isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado: *</label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecione...</option>
                        <option value="AC">AC</option>
                        <option value="AL">AL</option>
                        <option value="AP">AP</option>
                        <option value="AM">AM</option>
                        <option value="BA">BA</option>
                        <option value="CE">CE</option>
                        <option value="DF">DF</option>
                        <option value="ES">ES</option>
                        <option value="GO">GO</option>
                        <option value="MA">MA</option>
                        <option value="MT">MT</option>
                        <option value="MS">MS</option>
                        <option value="MG">MG</option>
                        <option value="PA">PA</option>
                        <option value="PB">PB</option>
                        <option value="PR">PR</option>
                        <option value="PE">PE</option>
                        <option value="PI">PI</option>
                        <option value="RJ">RJ</option>
                        <option value="RN">RN</option>
                        <option value="RS">RS</option>
                        <option value="RO">RO</option>
                        <option value="RR">RR</option>
                        <option value="SC">SC</option>
                        <option value="SP" selected>SP</option>
                        <option value="SE">SE</option>
                        <option value="TO">TO</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
        </form>
        
        <p class="auth-link">
            Já tem uma conta? <a href="login.php">Faça login aqui</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
