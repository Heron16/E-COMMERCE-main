<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>">
                        <h1>🚗 Lavagem Premium</h1>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">Início</a></li>
                        <li><a href="<?php echo BASE_URL; ?>index.php#servicos">Serviços</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>meus-agendamentos.php">Meus Agendamentos</a></li>
                            <li><a href="<?php echo BASE_URL; ?>logout.php">Sair</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                            <li><a href="<?php echo BASE_URL; ?>cadastro.php">Cadastrar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="admin-link">
                    <a href="<?php echo BASE_URL; ?>admin/" class="btn-admin">Admin</a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
