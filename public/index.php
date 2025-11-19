<?php
/**
 * Página Principal do Site
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Servico.php';

// Buscar todos os serviços ativos
$database = new Database();
$db = $database->getConnection();
$servico = new Servico($db);
$stmt = $servico->readAll();
$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar serviços por categoria
$servicos_por_categoria = [];
foreach ($servicos as $s) {
    $categoria = $s['categoria_nome'] ?? 'Outros';
    if (!isset($servicos_por_categoria[$categoria])) {
        $servicos_por_categoria[$categoria] = [];
    }
    $servicos_por_categoria[$categoria][] = $s;
}

$page_title = "Lavagem de Veículos - Serviços de Qualidade";
include __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Lavagem de Veículos Profissional</h1>
        <p>Serviços de qualidade com os melhores preços da região</p>
        <a href="#servicos" class="btn btn-primary">Ver Serviços</a>
    </div>
</section>

<!-- Serviços Section -->
<section id="servicos" class="servicos-section">
    <div class="container">
        <h2>Nossos Serviços</h2>
        <p class="subtitle">Escolha os serviços e agende sua lavagem online</p>
        
        <div id="carrinho-resumo" class="carrinho-resumo" style="display:none;">
            <h3>Carrinho de Serviços</h3>
            <div id="carrinho-itens"></div>
            <div class="carrinho-total">
                <strong>Total:</strong> 
                <span id="carrinho-total">R$ 0,00</span>
            </div>
            <div class="carrinho-actions">
                <button onclick="limparCarrinho()" class="btn btn-secondary">Limpar</button>
                <a href="agendamento.php" class="btn btn-primary">Agendar Agora</a>
            </div>
        </div>

        <?php foreach ($servicos_por_categoria as $categoria => $servicos_cat): ?>
        <div class="categoria-section">
            <h3 class="categoria-titulo"><?php echo htmlspecialchars($categoria); ?></h3>
            
            <div class="servicos-grid">
                <?php foreach ($servicos_cat as $servico): ?>
                <div class="servico-card" data-servico-id="<?php echo $servico['id']; ?>">
                    <div class="servico-header">
                        <h4><?php echo htmlspecialchars($servico['nome']); ?></h4>
                        <span class="servico-duracao"><?php echo $servico['duracao_minutos']; ?> min</span>
                    </div>
                    
                    <p class="servico-descricao"><?php echo htmlspecialchars($servico['descricao']); ?></p>
                    
                    <div class="servico-precos">
                        <div class="preco-item">
                            <span class="veiculo-tipo">Moto:</span>
                            <span class="preco" data-tipo="moto" data-valor="<?php echo $servico['preco_moto']; ?>">
                                <?php echo formatarMoeda($servico['preco_moto']); ?>
                            </span>
                        </div>
                        <div class="preco-item">
                            <span class="veiculo-tipo">Carro:</span>
                            <span class="preco" data-tipo="carro" data-valor="<?php echo $servico['preco_carro']; ?>">
                                <?php echo formatarMoeda($servico['preco_carro']); ?>
                            </span>
                        </div>
                        <div class="preco-item">
                            <span class="veiculo-tipo">Camioneta:</span>
                            <span class="preco" data-tipo="camioneta" data-valor="<?php echo $servico['preco_camioneta']; ?>">
                                <?php echo formatarMoeda($servico['preco_camioneta']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <button onclick="adicionarAoCarrinho(<?php echo $servico['id']; ?>, '<?php echo htmlspecialchars($servico['nome']); ?>')" 
                            class="btn btn-primary btn-adicionar">
                        Adicionar ao Carrinho
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Sobre Section -->
<section class="sobre-section">
    <div class="container">
        <h2>Por que nos escolher?</h2>
        <div class="vantagens-grid">
            <div class="vantagem-item">
                <div class="vantagem-icon">✓</div>
                <h4>Profissionais Qualificados</h4>
                <p>Equipe treinada e experiente</p>
            </div>
            <div class="vantagem-item">
                <div class="vantagem-icon">✓</div>
                <h4>Produtos de Qualidade</h4>
                <p>Utilizamos os melhores produtos</p>
            </div>
            <div class="vantagem-item">
                <div class="vantagem-icon">✓</div>
                <h4>Agendamento Online</h4>
                <p>Agende pelo site de forma rápida</p>
            </div>
            <div class="vantagem-item">
                <div class="vantagem-icon">✓</div>
                <h4>Preços Justos</h4>
                <p>Melhor custo-benefício</p>
            </div>
        </div>
    </div>
</section>

<div id="modal-tipo-veiculo" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <button class="modal-close" onclick="fecharModal()">&times;</button>
        
        <h3>Selecione o tipo de veículo</h3>
        <p>Para o serviço: <strong id="modal-servico-nome"></strong></p>
        
        <div class="modal-opcoes">
            <button id="modal-btn-moto" class="btn btn-primary">Moto</button>
            <button id="modal-btn-carro" class="btn btn-primary">Carro</button>
            <button id="modal-btn-camioneta" class="btn btn-primary">Camioneta</button>
        </div>
    </div>
</div>
<script>
// Sistema de Carrinho
let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];

// Atualizar carrinho ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    atualizarCarrinho();
});

// NOVA FUNÇÃO: Apenas fecha o modal
function fecharModal() {
    document.getElementById('modal-tipo-veiculo').style.display = 'none';
}

/**
 * FUNÇÃO ATUALIZADA:
 * Agora, ela não adiciona ao carrinho.
 * Ela abre o modal e configura os botões dele.
 */
function adicionarAoCarrinho(servicoId, servicoNome) {
    // 1. Verificar se já existe no carrinho (para não abrir o modal à toa)
    const existe = carrinho.find(item => item.id === servicoId);
    if (existe) {
        alert('Este serviço já está no carrinho!');
        return;
    }
    
    // 2. Buscar os elementos do modal
    const modal = document.getElementById('modal-tipo-veiculo');
    const modalNome = document.getElementById('modal-servico-nome');
    const btnMoto = document.getElementById('modal-btn-moto');
    const btnCarro = document.getElementById('modal-btn-carro');
    const btnCamioneta = document.getElementById('modal-btn-camioneta');
    
    // 3. Buscar o card do serviço que foi clicado
    const servicoCard = document.querySelector(`[data-servico-id="${servicoId}"]`);
    
    // 4. Pegar os PREÇOS de CADA tipo de veículo
    const precoMoto = parseFloat(servicoCard.querySelector('.preco[data-tipo="moto"]').dataset.valor);
    const precoCarro = parseFloat(servicoCard.querySelector('.preco[data-tipo="carro"]').dataset.valor);
    const precoCamioneta = parseFloat(servicoCard.querySelector('.preco[data-tipo="camioneta"]').dataset.valor);
    
    // 5. Atualizar o modal com os dados do serviço
    modalNome.textContent = servicoNome;
    
    // 6. Configurar o clique de CADA botão do modal
    // Usamos arrow functions (=>) para passar os parâmetros corretamente
    btnMoto.onclick = () => {
        confirmarAdicao(servicoId, servicoNome, 'moto', precoMoto);
    };
    
    btnCarro.onclick = () => {
        confirmarAdicao(servicoId, servicoNome, 'carro', precoCarro);
    };
    
    btnCamioneta.onclick = () => {
        confirmarAdicao(servicoId, servicoNome, 'camioneta', precoCamioneta);
    };
    
    // 7. Mostrar o modal
    modal.style.display = 'flex'; // Usamos 'flex' por causa do CSS de centralização
}

/**
 * NOVA FUNÇÃO:
 * Esta função é chamada pelos botões do modal (Moto, Carro, Camioneta).
 * Ela contém a lógica que ANTES estava em 'adicionarAoCarrinho'.
 */
function confirmarAdicao(servicoId, servicoNome, tipoVeiculo, preco) {
    // Adicionar ao carrinho
    carrinho.push({
        id: servicoId,
        nome: servicoNome,
        tipo_veiculo: tipoVeiculo,
        preco: preco
    });
    
    // Salvar no localStorage
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    
    // Atualizar interface
    atualizarCarrinho();
    
    // Fechar o modal
    fecharModal();
    
    // Feedback visual
    alert('Serviço adicionado ao carrinho!');
}


// --- Funções que permanecem iguais ---

function removerDoCarrinho(servicoId) {
    carrinho = carrinho.filter(item => item.id !== servicoId);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    atualizarCarrinho();
}

function limparCarrinho() {
    if (confirm('Deseja limpar todos os serviços do carrinho?')) {
        carrinho = [];
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        atualizarCarrinho();
    }
}

function atualizarCarrinho() {
    const carrinhoResumo = document.getElementById('carrinho-resumo');
    const carrinhoItens = document.getElementById('carrinho-itens');
    const carrinhoTotal = document.getElementById('carrinho-total');
    
    if (carrinho.length === 0) {
        carrinhoResumo.style.display = 'none';
        return;
    }
    
    carrinhoResumo.style.display = 'block';
    
    // Montar lista de itens
    let html = '<ul class="carrinho-lista">';
    let total = 0;
    
    carrinho.forEach(item => {
        total += item.preco;
        // Adicionei um span para mostrar o tipo de veículo no resumo
        html += `
            <li class="carrinho-item">
                <span class="item-nome">${item.nome} <small>(${item.tipo_veiculo})</small></span>
                <span class="item-preco">R$ ${item.preco.toFixed(2).replace('.', ',')}</span>
                <button onclick="removerDoCarrinho(${item.id})" class="btn-remover">×</button>
            </li>
        `;
    });
    
    html += '</ul>';
    
    carrinhoItens.innerHTML = html;
    carrinhoTotal.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
