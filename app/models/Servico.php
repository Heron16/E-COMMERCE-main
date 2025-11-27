<?php
require_once __DIR__ . '/../../config/database.php';

class Servico {
    private $conn;
    private $table_name = "servicos";

    public $id;
    public $categoria_id;
    public $nome;
    public $descricao;
    public $preco_moto;
    public $preco_carro;
    public $preco_camioneta;
    public $duracao_minutos;
    public $estoque_disponivel;
    public $ativo;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET categoria_id=:categoria_id, nome=:nome, descricao=:descricao,
                      preco_moto=:preco_moto, preco_carro=:preco_carro, 
                      preco_camioneta=:preco_camioneta, duracao_minutos=:duracao_minutos,
                      estoque_disponivel=:estoque_disponivel, ativo=:ativo";

        $stmt = $this->conn->prepare($query);
        
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));

        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":preco_moto", $this->preco_moto);
        $stmt->bindParam(":preco_carro", $this->preco_carro);
        $stmt->bindParam(":preco_camioneta", $this->preco_camioneta);
        $stmt->bindParam(":duracao_minutos", $this->duracao_minutos);
        $stmt->bindParam(":estoque_disponivel", $this->estoque_disponivel);
        $stmt->bindParam(":ativo", $this->ativo);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT s.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " s
                  LEFT JOIN categorias_servicos c ON s.categoria_id = c.id
                  WHERE s.ativo = 1
                  ORDER BY c.nome, s.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readAllAdmin() {
        $query = "SELECT s.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " s
                  LEFT JOIN categorias_servicos c ON s.categoria_id = c.id
                  ORDER BY c.nome, s.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT s.*, c.nome as categoria_nome 
                  FROM " . $this->table_name . " s
                  LEFT JOIN categorias_servicos c ON s.categoria_id = c.id
                  WHERE s.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->categoria_id = $row['categoria_id'];
            $this->nome = $row['nome'];
            $this->descricao = $row['descricao'];
            $this->preco_moto = $row['preco_moto'];
            $this->preco_carro = $row['preco_carro'];
            $this->preco_camioneta = $row['preco_camioneta'];
            $this->duracao_minutos = $row['duracao_minutos'];
            $this->estoque_disponivel = $row['estoque_disponivel'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET categoria_id=:categoria_id, nome=:nome, descricao=:descricao,
                      preco_moto=:preco_moto, preco_carro=:preco_carro, 
                      preco_camioneta=:preco_camioneta, duracao_minutos=:duracao_minutos,
                      estoque_disponivel=:estoque_disponivel, ativo=:ativo
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));

        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":preco_moto", $this->preco_moto);
        $stmt->bindParam(":preco_carro", $this->preco_carro);
        $stmt->bindParam(":preco_camioneta", $this->preco_camioneta);
        $stmt->bindParam(":duracao_minutos", $this->duracao_minutos);
        $stmt->bindParam(":estoque_disponivel", $this->estoque_disponivel);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function verificarDisponibilidade($quantidade = 1) {
        $query = "SELECT fn_verificar_disponibilidade(:servico_id, :quantidade) as status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":servico_id", $this->id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['status'];
    }

    public function calcularValor($tipo_veiculo, $quantidade = 1) {
        $query = "SELECT fn_calcular_valor_servico(:servico_id, :tipo_veiculo, :quantidade) as valor";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":servico_id", $this->id);
        $stmt->bindParam(":tipo_veiculo", $tipo_veiculo);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['valor'];
    }

    public function maissolicitados($limit = 5) {
        $query = "
            SELECT 
                s.nome AS servico,
                COALESCE(SUM(ai.quantidade), 0) AS quantidade_vendida,
                COALESCE(SUM(CASE WHEN a.status IN ('confirmado','concluido') THEN ai.valor_total ELSE 0 END), 0) AS receita_total
            FROM servicos s
            LEFT JOIN agendamento_itens ai ON ai.servico_id = s.id
            LEFT JOIN agendamentos a ON a.id = ai.agendamento_id
            GROUP BY s.id, s.nome
            ORDER BY quantidade_vendida DESC, receita_total DESC
            LIMIT :limit
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
?>
