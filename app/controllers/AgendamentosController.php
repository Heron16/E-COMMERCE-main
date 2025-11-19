<?php
require_once __DIR__ . '/../models/Agendamento.php';
require_once __DIR__ . '/BaseController.php';
class AgendamentosController extends BaseController {
    public function getList() {
        $m = new Agendamento($this->db);
        $stmt = $m->readAll();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function processPost(array $post) {
        $mensagem = '';
        $erro = '';
        $acao = $post['acao'] ?? '';
        $m = new Agendamento($this->db);
        if ($acao === 'atualizar_status') {
            $m->id = $post['id'] ?? 0;
            $novo_status = $post['status'] ?? '';
            if ($m->updateStatus($novo_status)) {
                $mensagem = 'Status atualizado com sucesso!';
            } else {
                $erro = 'Erro ao atualizar status.';
            }
        } elseif ($acao === 'deletar') {
            $m->id = $post['id'] ?? 0;
            if ($m->delete()) {
                $mensagem = 'Agendamento deletado com sucesso!';
            } else {
                $erro = 'Erro ao deletar agendamento.';
            }
        }
        return ['mensagem' => $mensagem, 'erro' => $erro];
    }
}
?>