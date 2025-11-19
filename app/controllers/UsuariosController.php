<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/BaseController.php';
class UsuariosController extends BaseController {
    public function getList() {
        $m = new Usuario($this->db);
        $stmt = $m->readAll();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function processPost(array $post) {
        $mensagem = '';
        $erro = '';
        $acao = $post['acao'] ?? '';
        $m = new Usuario($this->db);
        if ($acao === 'criar') {
            $m->nome = $post['nome'] ?? '';
            $m->email = $post['email'] ?? '';
            $m->senha = $post['senha'] ?? '';
            $m->tipo = $post['tipo'] ?? 'funcionario';
            $m->ativo = $post['ativo'] ?? 1;
            if ($m->emailExiste($m->email)) {
                $erro = 'Este email já está cadastrado.';
            } elseif ($m->create()) {
                $mensagem = 'Usuário criado com sucesso!';
            } else {
                $erro = 'Erro ao criar usuário.';
            }
        } elseif ($acao === 'editar') {
            $m->id = $post['id'] ?? 0;
            $m->nome = $post['nome'] ?? '';
            $m->email = $post['email'] ?? '';
            $m->tipo = $post['tipo'] ?? 'funcionario';
            $m->ativo = $post['ativo'] ?? 1;
            if ($m->update()) {
                $mensagem = 'Usuário atualizado com sucesso!';
            } else {
                $erro = 'Erro ao atualizar usuário.';
            }
        } elseif ($acao === 'deletar') {
            $m->id = $post['id'] ?? 0;
            if ($m->delete()) {
                $mensagem = 'Usuário deletado com sucesso!';
            } else {
                $erro = 'Erro ao deletar usuário.';
            }
        }
        return ['mensagem' => $mensagem, 'erro' => $erro];
    }
}
?>