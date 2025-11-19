<?php
require_once __DIR__ . '/../models/Servico.php';
require_once __DIR__ . '/BaseController.php';
class ServicosController extends BaseController {
    public function getList() {
        $m = new Servico($this->db);
        $stmt = $m->readAllAdmin();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function processPost(array $post) {
        $mensagem = '';
        $erro = '';
        $acao = $post['acao'] ?? '';
        $m = new Servico($this->db);
        if ($acao === 'criar') {
            $m->categoria_id = $post['categoria_id'] ?? 1;
            $m->nome = $post['nome'] ?? '';
            $m->descricao = $post['descricao'] ?? '';
            $m->preco_moto = $post['preco_moto'] ?? 0;
            $m->preco_carro = $post['preco_carro'] ?? 0;
            $m->preco_camioneta = $post['preco_camioneta'] ?? 0;
            $m->duracao_minutos = $post['duracao_minutos'] ?? 60;
            $m->estoque_disponivel = $post['estoque_disponivel'] ?? 999;
            $m->ativo = $post['ativo'] ?? 1;
            if ($m->create()) {
                $mensagem = 'Serviço criado com sucesso!';
            } else {
                $erro = 'Erro ao criar serviço.';
            }
        } elseif ($acao === 'editar') {
            $m->id = $post['id'] ?? 0;
            $m->categoria_id = $post['categoria_id'] ?? 1;
            $m->nome = $post['nome'] ?? '';
            $m->descricao = $post['descricao'] ?? '';
            $m->preco_moto = $post['preco_moto'] ?? 0;
            $m->preco_carro = $post['preco_carro'] ?? 0;
            $m->preco_camioneta = $post['preco_camioneta'] ?? 0;
            $m->duracao_minutos = $post['duracao_minutos'] ?? 60;
            $m->estoque_disponivel = $post['estoque_disponivel'] ?? 999;
            $m->ativo = $post['ativo'] ?? 1;
            if ($m->update()) {
                $mensagem = 'Serviço atualizado com sucesso!';
            } else {
                $erro = 'Erro ao atualizar serviço.';
            }
        } elseif ($acao === 'deletar') {
            $m->id = $post['id'] ?? 0;
            if ($m->delete()) {
                $mensagem = 'Serviço deletado com sucesso!';
            } else {
                $erro = 'Erro ao deletar serviço.';
            }
        }
        return ['mensagem' => $mensagem, 'erro' => $erro];
    }
}
?>