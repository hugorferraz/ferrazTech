<?php
function carregarDashboardIndex() {
    $host = 'localhost';
    $dbname = 'ferraztech_db';
    $username = 'root';
    $password = '';

    $dados = [
        'totalClientes' => 0,
        'totalManutencoes' => 0,
        'totalInstalacoesIrrigacao' => 0,
        'erro_db' => null
    ];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ajuste os nomes das tabelas/condições conforme o seu banco real
        $dados['totalClientes'] = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
        
        // Exemplo para manutenções e instalações baseadas na tabela orcamentos ou servicos
        $dados['totalManutencoes'] = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE tipo_solicitacao LIKE '%Manutenção%'")->fetchColumn();
        
        $dados['totalInstalacoesIrrigacao'] = $pdo->query("SELECT COUNT(*) FROM orcamentos WHERE tipo_solicitacao LIKE '%Irrigação%'")->fetchColumn();

    } catch (Exception $e) {
        $dados['erro_db'] = "Erro ao carregar métricas: " . $e->getMessage();
    }

    return $dados;
}
?>