<?php
function carregarDadosClientes() {
    $host = 'localhost';
    $dbname = 'ferraztech_db';
    $username = 'root';
    $password = '';

    $dados = [
        'clientes_com_enderecos' => [],
        'termoBusca' => '',
        'erro_db' => null
    ];

    $termoBusca = $_GET['busca'] ?? '';
    $dados['termoBusca'] = $termoBusca;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!empty($termoBusca)) {
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome LIKE ? OR email LIKE ? OR telefone LIKE ? ORDER BY nome ASC");
            $like = "%$termoBusca%";
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC");
        }
        
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($clientes as $c) {
            $stmtEnd = $pdo->prepare("SELECT * FROM enderecos WHERE cliente_id = ?");
            $stmtEnd->execute([$c['id']]);
            $enderecos = $stmtEnd->fetchAll(PDO::FETCH_ASSOC);

            $dados['clientes_com_enderecos'][] = [
                'cliente' => $c,
                'enderecos' => $enderecos
            ];
        }

    } catch (Exception $e) {
        $dados['erro_db'] = "Erro ao carregar clientes: " . $e->getMessage();
    }

    return $dados;
}
?>