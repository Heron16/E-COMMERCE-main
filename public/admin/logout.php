<?php
require_once __DIR__ . '/../../config/config.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_nome']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_tipo']);

redirect('admin/');
?>
