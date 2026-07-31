<?php
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_POST['cliente_id'];
    $nome       = $_POST['nome'];
    $telefone   = $_POST['telefone'];
    $email      = $_POST['email'];
    $cpf        = $_POST['cpf'];

    // Dados do endereço selecionado
    $endereco_id = $_POST['endereco_id'] ?? null;
    $cep         = $_POST['cep'] ?? '';
    $logradouro  = $_POST['logradouro'] ?? '';
    $numero      = $_POST['numero'] ?? '';
    $bairro      = $_POST['bairro'] ?? '';
    $cidade      = $_POST['cidade'] ?? '';
    $estado      = $_POST['estado'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        // 1. Atualiza dados do cliente
        $stmtCliente = $pdo->prepare("UPDATE clientes SET nome = ?, telefone = ?, email = ?, cpf = ? WHERE id = ?");
        $stmtCliente->execute([$nome, $telefone, $email, $cpf, $cliente_id]);

        // 2. Atualiza o endereço específico selecionado (se houver)
        if ($endereco_id) {
            $stmtEnd = $pdo->prepare("UPDATE enderecos SET cep = ?, logradouro = ?, numero = ?, bairro = ?, cidade = ?, estado = ? WHERE id = ? AND cliente_id = ?");
            $stmtEnd->execute([$cep, $logradouro, $numero, $bairro, $cidade, $estado, $endereco_id, $cliente_id]);
        }

        $pdo->commit();

        header("Location: ../telas/clientes.php?status=atualizado");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../telas/clientes.php?status=erro_atualizacao");
        exit;
    }
} else {
    header("Location: ../telas/clientes.php");
    exit;
}
?>