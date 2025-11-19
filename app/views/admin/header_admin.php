<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin - ' . APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>🚗 Admin Painel</h2>
            </div>
            
<nav class="admin-nav">
    <ul>
        <?php if (isset($_SESSION['admin_tipo']) && $_SESSION['admin_tipo'] == 'admin'): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                Dashboard
            </a>
        </li>
        <?php endif; ?>
                    
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/agendamentos.php" class="nav-link">
                            <span class="nav-icon">📅</span>
                            Agendamentos
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/servicos.php" class="nav-link">
                            <span class="nav-icon">🧼</span>
                            Serviços
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/clientes.php" class="nav-link">
                            <span class="nav-icon">👥</span>
                            Clientes
                        </a>
                    </li>
                    
                    
        <?php if (isset($_SESSION['admin_tipo']) && $_SESSION['admin_tipo'] == 'admin'): ?>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/usuarios.php" class="nav-link">
                <span class="nav-icon">👤</span>
                Usuários
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
            
            <div class="admin-user">
                <p><strong><?php echo htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin'); ?></strong></p>
                <p class="user-role"><?php echo ucfirst($_SESSION['admin_tipo'] ?? 'admin'); ?></p>
                <a href="<?php echo BASE_URL; ?>admin/logout.php" class="btn btn-secondary btn-sm">Sair</a>
            </div>
        </aside>
        
        <main class="admin-main">
