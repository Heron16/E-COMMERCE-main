<?php
/**
 * Login Administrativo
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Usuario.php';

$erro = '';

// Se já está logado como admin, redirecionar para dashboard
if (isAdminLoggedIn()) {
    redirect('admin/dashboard.php');
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $usuario = new Usuario($db);
        
        $resultado = $usuario->login($email, $senha);
        
        if ($resultado) {
            $_SESSION['admin_id'] = $resultado['id'];
            $_SESSION['admin_nome'] = $resultado['nome'];
            $_SESSION['admin_email'] = $resultado['email'];
            $_SESSION['admin_tipo'] = $resultado['tipo'];
            
            redirect('admin/dashboard.php');
        } else {
            $erro = 'Email ou senha incorretos, ou usuário inativo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body class="admin-body">
    <div class="admin-login-container">
        <div class="admin-login-box">
            <h2>🔐 Acesso Administrativo</h2>
            
            <?php if ($erro): ?>
                <div class="alert alert-error"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="admin-login-form">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
            
            <p class="admin-login-footer">
                <a href="<?php echo BASE_URL; ?>">← Voltar ao site</a>
            </p>
        </div>
    </div>
</body>
</html>
