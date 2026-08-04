<?php
function carregarDetalhesCliente($cliente_id) {
    $host = 'localhost';
    $dbname = 'ferraztech_db';
    $username = 'root';
    $password = '';

    $dados = [
        'cliente' => null,
        'enderecos' => [],
        'orcamentos' => [],
        'agendamentos' => [],
        'erro_db' => null
    ];

    if (!$cliente_id) {
        $dados['erro_db'] = "ID do cliente não informado.";
        return $dados;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. Dados do Cliente
        $stmtC = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmtC->execute([$cliente_id]);
        $dados['cliente'] = $stmtC->fetch(PDO::FETCH_ASSOC);

        // 2. Endereços do Cliente
        $stmtE = $pdo->prepare("SELECT * FROM enderecos WHERE cliente_id = ?");
        $stmtE->execute([$cliente_id]);
        $dados['enderecos'] = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        // 3. Orçamentos / Solicitações Web deste cliente
        $stmtO = $pdo->prepare("SELECT * FROM orcamentos WHERE cliente_id = ? ORDER BY data_solicitacao DESC");
        $stmtO->execute([$cliente_id]);
        $dados['orcamentos'] = $stmtO->fetchAll(PDO::FETCH_ASSOC);

        // 4. Agendamentos deste cliente com as novas colunas de período
        $stmtA = $pdo->prepare("
            SELECT a.*, e.logradouro, e.numero, e.bairro 
            FROM agendamentos a
            JOIN enderecos e ON a.endereco_id = e.id
            WHERE a.cliente_id = ? 
            ORDER BY a.data_inicio DESC
        ");
        $stmtA->execute([$cliente_id]);
        $dados['agendamentos'] = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $dados['erro_db'] = "Erro ao carregar detalhes: " . $e->getMessage();
    }

    return $dados;
}
?>