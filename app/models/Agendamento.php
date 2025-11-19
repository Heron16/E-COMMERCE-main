<?php
/**
 * Model Agendamento - CRUD completo
 */

require_once __DIR__ . '/../../config/database.php';

class Agendamento {
    private $conn;
    private $table_name = "agendamentos";

    public $id;
    public $cliente_id;
    public $data_agendamento;
    public $hora_agendamento;
    public $tipo_veiculo;
    public $placa_veiculo;
    public $modelo_veiculo;
    public $observacoes;
    public $valor_total;
    public $status;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // CREATE - Criar novo agendamento
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET cliente_id=:cliente_id, data_agendamento=:data_agendamento,
                      hora_agendamento=:hora_agendamento, tipo_veiculo=:tipo_veiculo,
                      placa_veiculo=:placa_veiculo, modelo_veiculo=:modelo_veiculo,
                      observacoes=:observacoes, valor_total=:valor_total, status=:status";

        $stmt = $this->conn->prepare($query);

        // Limpar dados
        $this->placa_veiculo = htmlspecialchars(strip_tags($this->placa_veiculo));
        $this->modelo_veiculo = htmlspecialchars(strip_tags($this->modelo_veiculo));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos valores
        $stmt->bindParam(":cliente_id", $this->cliente_id);
        $stmt->bindParam(":data_agendamento", $this->data_agendamento);
        $stmt->bindParam(":hora_agendamento", $this->hora_agendamento);
        $stmt->bindParam(":tipo_veiculo", $this->tipo_veiculo);
        $stmt->bindParam(":placa_veiculo", $this->placa_veiculo);
        $stmt->bindParam(":modelo_veiculo", $this->modelo_veiculo);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":valor_total", $this->valor_total);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // CREATE - Adicionar item ao agendamento
    public function adicionarItem($servico_id, $quantidade, $valor_unitario, $valor_total) {
        $query = "INSERT INTO agendamento_itens 
                  SET agendamento_id=:agendamento_id, servico_id=:servico_id,
                      quantidade=:quantidade, valor_unitario=:valor_unitario,
                      valor_total=:valor_total";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":agendamento_id", $this->id);
        $stmt->bindParam(":servico_id", $servico_id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":valor_unitario", $valor_unitario);
        $stmt->bindParam(":valor_total", $valor_total);

        return $stmt->execute();
    }

    // READ - Listar todos os agendamentos
    public function readAll() {
        $query = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone
                  FROM " . $this->table_name . " a
                  INNER JOIN clientes c ON a.cliente_id = c.id
                  ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ - Listar agendamentos de um cliente
    public function readByCliente($cliente_id) {
        $query = "SELECT a.*, c.nome as cliente_nome
                  FROM " . $this->table_name . " a
                  INNER JOIN clientes c ON a.cliente_id = c.id
                  WHERE a.cliente_id = :cliente_id
                  ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->execute();
        return $stmt;
    }

    // READ - Buscar agendamento por ID
    public function readOne() {
        $query = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone,
                         c.email as cliente_email
                  FROM " . $this->table_name . " a
                  INNER JOIN clientes c ON a.cliente_id = c.id
                  WHERE a.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->cliente_id = $row['cliente_id'];
            $this->data_agendamento = $row['data_agendamento'];
            $this->hora_agendamento = $row['hora_agendamento'];
            $this->tipo_veiculo = $row['tipo_veiculo'];
            $this->placa_veiculo = $row['placa_veiculo'];
            $this->modelo_veiculo = $row['modelo_veiculo'];
            $this->observacoes = $row['observacoes'];
            $this->valor_total = $row['valor_total'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // READ - Buscar itens do agendamento
    public function getItens() {
        $query = "SELECT ai.*, s.nome as servico_nome
                  FROM agendamento_itens ai
                  INNER JOIN servicos s ON ai.servico_id = s.id
                  WHERE ai.agendamento_id = :agendamento_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":agendamento_id", $this->id);
        $stmt->execute();
        return $stmt;
    }

    // UPDATE - Atualizar agendamento
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET data_agendamento=:data_agendamento, hora_agendamento=:hora_agendamento,
                      tipo_veiculo=:tipo_veiculo, placa_veiculo=:placa_veiculo,
                      modelo_veiculo=:modelo_veiculo, observacoes=:observacoes,
                      valor_total=:valor_total, status=:status
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Limpar dados
        $this->placa_veiculo = htmlspecialchars(strip_tags($this->placa_veiculo));
        $this->modelo_veiculo = htmlspecialchars(strip_tags($this->modelo_veiculo));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos valores
        $stmt->bindParam(":data_agendamento", $this->data_agendamento);
        $stmt->bindParam(":hora_agendamento", $this->hora_agendamento);
        $stmt->bindParam(":tipo_veiculo", $this->tipo_veiculo);
        $stmt->bindParam(":placa_veiculo", $this->placa_veiculo);
        $stmt->bindParam(":modelo_veiculo", $this->modelo_veiculo);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":valor_total", $this->valor_total);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // UPDATE - Atualizar apenas status (COM LOG)
    public function updateStatus($novo_status) {
        // Incluir a função de log
        require_once __DIR__ . '/../logs/logs_alteracao.php';
        
        try {
            // Iniciar transação
            $this->conn->beginTransaction();
            
            // 1. Buscar dados antigos
            $query_old = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt_old = $this->conn->prepare($query_old);
            $stmt_old->bindParam(":id", $this->id);
            $stmt_old->execute();
            $dados_antigos = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            if (!$dados_antigos) {
                throw new Exception('Agendamento não encontrado');
            }
            
            // Se o status já for o mesmo, não faz nada
            if ($dados_antigos['status'] === $novo_status) {
                $this->conn->rollBack();
                return true;
            }
            
            // 2. Atualizar o status
            $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $novo_status);
            $stmt->bindParam(":id", $this->id);
            
            if (!$stmt->execute()) {
                throw new Exception('Falha ao atualizar status');
            }
            
            // 3. Preparar dados novos para o log
            $dados_novos = $dados_antigos;
            $dados_novos['status'] = $novo_status;
            
            // 4. Commit da transação
            $this->conn->commit();
            
            // 5. Registrar o log (fora da transação principal)
            registrarLog(
                $this->conn,
                'UPDATE_STATUS_AGENDAMENTO',
                $this->table_name,
                (int)$this->id,
                $dados_antigos,
                $dados_novos
            );
            
            return true;
            
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            return false;
        }
    }

    // DELETE - Deletar agendamento
    public function delete() {
        // Primeiro deletar itens (cascade já faz isso, mas por garantia)
        $query = "DELETE FROM agendamento_itens WHERE agendamento_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        // Depois deletar agendamento
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DASHBOARD - Indicadores
    public function getDashboardSemanal() {
        $query = "
            SELECT 
                a.data_agendamento AS data,
                COUNT(*) AS total_agendamentos,
                SUM(CASE WHEN a.status = 'concluido' THEN 1 ELSE 0 END) AS lavagens_concluidas,
                COALESCE(SUM(CASE WHEN a.status IN ('confirmado','concluido') THEN a.valor_total ELSE 0 END), 0) AS receita
            FROM agendamentos a
            WHERE a.data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY a.data_agendamento
            ORDER BY data DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // DASHBOARD - Total de agendamentos do mês
    public function totalAgendamentosMes() {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE MONTH(data_agendamento) = MONTH(CURDATE())
                  AND YEAR(data_agendamento) = YEAR(CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // DASHBOARD - Total de lavagens do mês
    public function totalLavagensMes() {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE status = 'concluido'
                  AND MONTH(data_agendamento) = MONTH(CURDATE())
                  AND YEAR(data_agendamento) = YEAR(CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // DASHBOARD - Total de lavagens da semana
    public function totalLavagensSemana() {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE status = 'concluido'
                  AND data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // DASHBOARD - Receita mensal
    public function receitaMensal() {
        $query = "SELECT COALESCE(SUM(valor_total), 0) as total 
                  FROM " . $this->table_name . "
                  WHERE status IN ('concluido', 'confirmado')
                  AND MONTH(data_agendamento) = MONTH(CURDATE())
                  AND YEAR(data_agendamento) = YEAR(CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // DASHBOARD - Receita semanal
    public function receitaSemanal() {
        $query = "SELECT COALESCE(SUM(valor_total), 0) as total 
                  FROM " . $this->table_name . "
                  WHERE status IN ('concluido', 'confirmado')
                  AND data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
