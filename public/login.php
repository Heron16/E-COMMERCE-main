<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Cliente.php';

$erro = '';
$sucesso = '';

if (isLoggedIn()) {
    redirect('meus-agendamentos.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $cliente = new Cliente($db);
        
        $resultado = $cliente->login($email, $senha);
        
        if ($resultado) {
            $_SESSION['cliente_id'] = $resultado['id'];
            $_SESSION['cliente_nome'] = $resultado['nome'];
            $_SESSION['cliente_email'] = $resultado['email'];
            
            redirect('agendamento.php');
        } else {
            $erro = 'Email ou senha incorretos.';
        }
    }
}

if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso') {
    $sucesso = 'Cadastro realizado com sucesso! Faça login para continuar.';
}

$page_title = "Login - " . APP_NAME;
include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login de Cliente</h2>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        
        <p class="auth-link">
            Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
