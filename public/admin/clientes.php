<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ClientesController.php';

if (!isAdminLoggedIn()) {
    redirect('admin/');
}

$controller = new ClientesController();

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $r = $controller->processPost($_POST);
    $mensagem = $r['mensagem'];
    $erro = $r['erro'];
}

$clientes = $controller->getList();

$page_title = "Gerenciar Clientes - Admin";
include __DIR__ . '/../../app/views/admin/header_admin.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <h2>Gerenciar Clientes</h2>
        <div class="admin-stats">
            <span>Total de Clientes: <strong><?php echo count($clientes); ?></strong></span>
        </div>
    </div>
    
    <?php if ($mensagem): ?>
        <div class="alert alert-success"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <?php if ($erro): ?>
        <div class="alert alert-error"><?php echo $erro; ?></div>
    <?php endif; ?>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Cidade/Estado</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><?php echo $cliente['id']; ?></td>
                    <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['cidade']) . '/' . htmlspecialchars($cliente['estado']); ?></td>
                    <td><?php echo formatarData($cliente['data_cadastro']); ?></td>
                    <td>
                        <button onclick="deletarCliente(<?php echo $cliente['id']; ?>)" class="btn btn-sm btn-danger">Deletar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="POST" action="" id="formDeletar" style="display:none;">
    <input type="hidden" name="acao" value="deletar">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function deletarCliente(id) {
    if (confirm('Tem certeza que deseja deletar este cliente?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('formDeletar').submit();
    }
}
</script>

<?php include __DIR__ . '/../../app/views/admin/footer_admin.php'; ?>
