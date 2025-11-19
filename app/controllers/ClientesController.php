<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/BaseController.php';
class ClientesController extends BaseController {
    public function getList() {
        $m = new Cliente($this->db);
        $stmt = $m->readAll();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function processPost(array $post) {
        $mensagem = '';
        $erro = '';
        $acao = $post['acao'] ?? '';
        if ($acao === 'deletar') {
            $m = new Cliente($this->db);
            $m->id = $post['id'] ?? 0;
            if ($m->delete()) {
                $mensagem = 'Cliente deletado com sucesso!';
            } else {
                $erro = 'Erro ao deletar cliente.';
            }
        }
        return ['mensagem' => $mensagem, 'erro' => $erro];
    }
}
?>