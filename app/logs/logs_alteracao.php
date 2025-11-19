<?php
/**
 * app/logs/logs_alteracao.php
 *
 * Define a função registrarLog() que será usada para inserir
 * registros na tabela 'logs_alteracoes'.
 */

/**
 * Registra uma alteração no banco de dados.
 *
 * @param PDO $pdo A conexão PDO com o banco de dados (vinda da sua classe Database).
 * @param string $acao Ação realizada (ex: 'UPDATE', 'INSERT', 'LOGIN_FALHOU').
 * @param string $tabela_afetada O nome da tabela que sofreu a alteração.
 * @param int|null $registro_id O ID do registro que foi alterado (null se não aplicável).
 * @param mixed $dados_antigos Dados antes da alteração (array, objeto ou null).
 * @param mixed $dados_novos Dados depois da alteração (array, objeto ou null).
 * @return bool True se o log foi inserido com sucesso, false caso contrário.
 */
function registrarLog(PDO $pdo, string $acao, string $tabela_afetada, ?int $registro_id, $dados_antigos, $dados_novos): bool {
    
    // 1. Obter dados automáticos (usuário e IP)
    
    // Garante que a sessão está iniciada para pegar o ID do usuário
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuario_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : (isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null);
    $ip_usuario = $_SERVER['REMOTE_ADDR'] ?? 'CLI'; 

    // 2. Preparar os dados JSON
    $json_antigos = json_encode($dados_antigos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $json_novos = json_encode($dados_novos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // 3. Montar e executar o SQL
    $sql = "INSERT INTO logs_alteracoes 
                (usuario_id, ip_usuario, acao, tabela_afetada, registro_id, dados_antigos, dados_novos, data_hora) 
            VALUES 
                (:usuario_id, :ip_usuario, :acao, :tabela_afetada, :registro_id, :dados_antigos, :dados_novos, CURRENT_TIMESTAMP)";
    
    try {
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':ip_usuario' => $ip_usuario,
            ':acao' => $acao,
            ':tabela_afetada' => $tabela_afetada,
            ':registro_id' => $registro_id,
            ':dados_antigos' => ($json_antigos === 'null' || $json_antigos === false) ? null : $json_antigos,
            ':dados_novos' => ($json_novos === 'null' || $json_novos === false) ? null : $json_novos
        ]);

    } catch (PDOException $e) {
        // Se o log falhar, não queremos quebrar a aplicação principal.
        error_log('ALERTA: Falha ao registrar log no banco de dados: ' . $e->getMessage());
        return false;
    }
}
?>

