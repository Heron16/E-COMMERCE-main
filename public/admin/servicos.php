<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ServicosController.php';

if (!isAdminLoggedIn()) {
    redirect('admin/');
}

$controller = new ServicosController();

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $r = $controller->processPost($_POST);
    $mensagem = $r['mensagem'];
    $erro = $r['erro'];
}

$servicos = $controller->getList();

$page_title = "Gerenciar Serviços - Admin";
include __DIR__ . '/../../app/views/admin/header_admin.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <h2>Gerenciar Serviços</h2>
        <button onclick="abrirModal('modalCriar')" class="btn btn-primary">+ Novo Serviço</button>
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
                    <th>Categoria</th>
                    <th>Moto</th>
                    <th>Carro</th>
                    <th>Camioneta</th>
                    <th>Duração</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicos as $servico): ?>
                <tr>
                    <td><?php echo $servico['id']; ?></td>
                    <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                    <td><?php echo htmlspecialchars($servico['categoria_nome']); ?></td>
                    <td><?php echo formatarMoeda($servico['preco_moto']); ?></td>
                    <td><?php echo formatarMoeda($servico['preco_carro']); ?></td>
                    <td><?php echo formatarMoeda($servico['preco_camioneta']); ?></td>
                    <td><?php echo $servico['duracao_minutos']; ?> min</td>
                    <td><?php echo $servico['estoque_disponivel']; ?></td>
                    <td>
                        <span class="badge <?php echo $servico['ativo'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $servico['ativo'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </td>
                    <td>
                        <button onclick='editarServico(<?php echo json_encode($servico); ?>)' class="btn btn-sm">Editar</button>
                        <button onclick="deletarServico(<?php echo $servico['id']; ?>)" class="btn btn-sm btn-danger">Deletar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalCriar" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="fecharModal('modalCriar')">&times;</span>
        <h3>Novo Serviço</h3>
        
        <form method="POST" action="">
            <input type="hidden" name="acao" value="criar">
            
            <div class="form-group">
                <label>Categoria:</label>
                <select name="categoria_id" required>
                    <option value="1">Lavagem Básica</option>
                    <option value="2">Lavagem Completa</option>
                    <option value="3">Polimento</option>
                    <option value="4">Higienização</option>
                    <option value="5">Proteção</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Nome:</label>
                <input type="text" name="nome" required>
            </div>
            
            <div class="form-group">
                <label>Descrição:</label>
                <textarea name="descricao" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Preço Moto:</label>
                    <input type="number" name="preco_moto" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Preço Carro:</label>
                    <input type="number" name="preco_carro" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Preço Camioneta:</label>
                    <input type="number" name="preco_camioneta" step="0.01" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Duração (minutos):</label>
                    <input type="number" name="duracao_minutos" value="60" required>
                </div>
                
                <div class="form-group">
                    <label>Estoque:</label>
                    <input type="number" name="estoque_disponivel" value="999" required>
                </div>
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="ativo">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Criar Serviço</button>
        </form>
    </div>
</div>

<div id="modalEditar" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="fecharModal('modalEditar')">&times;</span>
        <h3>Editar Serviço</h3>
        
        <form method="POST" action="" id="formEditar">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Categoria:</label>
                <select name="categoria_id" id="edit_categoria_id" required>
                    <option value="1">Lavagem Básica</option>
                    <option value="2">Lavagem Completa</option>
                    <option value="3">Polimento</option>
                    <option value="4">Higienização</option>
                    <option value="5">Proteção</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Nome:</label>
                <input type="text" name="nome" id="edit_nome" required>
            </div>
            
            <div class="form-group">
                <label>Descrição:</label>
                <textarea name="descricao" id="edit_descricao" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Preço Moto:</label>
                    <input type="number" name="preco_moto" id="edit_preco_moto" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Preço Carro:</label>
                    <input type="number" name="preco_carro" id="edit_preco_carro" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Preço Camioneta:</label>
                    <input type="number" name="preco_camioneta" id="edit_preco_camioneta" step="0.01" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Duração (minutos):</label>
                    <input type="number" name="duracao_minutos" id="edit_duracao_minutos" required>
                </div>
                
                <div class="form-group">
                    <label>Estoque:</label>
                    <input type="number" name="estoque_disponivel" id="edit_estoque_disponivel" required>
                </div>
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="ativo" id="edit_ativo">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Atualizar Serviço</button>
        </form>
    </div>
</div>

<form method="POST" action="" id="formDeletar" style="display:none;">
    <input type="hidden" name="acao" value="deletar">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function abrirModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editarServico(servico) {
    document.getElementById('edit_id').value = servico.id;
    document.getElementById('edit_categoria_id').value = servico.categoria_id;
    document.getElementById('edit_nome').value = servico.nome;
    document.getElementById('edit_descricao').value = servico.descricao;
    document.getElementById('edit_preco_moto').value = servico.preco_moto;
    document.getElementById('edit_preco_carro').value = servico.preco_carro;
    document.getElementById('edit_preco_camioneta').value = servico.preco_camioneta;
    document.getElementById('edit_duracao_minutos').value = servico.duracao_minutos;
    document.getElementById('edit_estoque_disponivel').value = servico.estoque_disponivel;
    document.getElementById('edit_ativo').value = servico.ativo;
    abrirModal('modalEditar');
}

function deletarServico(id) {
    if (confirm('Tem certeza que deseja deletar este serviço?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('formDeletar').submit();
    }
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../../app/views/admin/footer_admin.php'; ?>
