<?php
function carregarDadosAgendamentos() {
    $host = 'localhost';
    $dbname = 'ferraztech_db';
    $username = 'root';
    $password = '';

    $dados = [
        'agendamentos' => [],
        'clientes' => [],
        'erro_db' => null
    ];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. Busca todos os agendamentos cruzando com Clientes e Endereços
        $sql = "SELECT a.*, c.nome as cliente_nome, e.logradouro, e.numero, e.bairro, e.cidade 
                FROM agendamentos a
                JOIN clientes c ON a.cliente_id = c.id
                JOIN enderecos e ON a.endereco_id = e.id
                ORDER BY a.data_inicio DESC";
        $stmt = $pdo->query($sql);
        $dados['agendamentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Busca lista de clientes que possuem orçamentos pendentes
        $stmtC = $pdo->query("SELECT DISTINCT c.id, c.nome 
                              FROM clientes c 
                              JOIN orcamentos o ON c.id = o.cliente_id 
                              WHERE o.status = 'Pendente' 
                              ORDER BY c.nome ASC");
        $dados['clientes'] = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $dados['erro_db'] = "Erro ao carregar dados: " . $e->getMessage();
    }

    return $dados;
}
?>