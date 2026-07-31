<?php
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id       = $_POST['cliente_id'];
    $endereco_id      = $_POST['endereco_id'];
    $data_agendamento = $_POST['data_agendamento'];
    $tipo_trabalho    = $_POST['tipo_trabalho'];
    $descricao        = $_POST['descricao'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // --- 1. VALIDAÇÃO DE DATA RETROATIVA ---
        // Compara a data informada com a data/hora atual do servidor
        $dataAtual = date('Y-m-d H:i:s');
        if ($data_agendamento < $dataAtual) {
            header("Location: ../telas/agendamentos.php?status=erro_retroativo");
            exit;
        }

        // --- 2. VALIDAÇÃO DE CONFLITO DE HORÁRIO ---
        $stmtConflito = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = ? AND status != 'Cancelado'");
        $stmtConflito->execute([$data_agendamento]);
        $existeConflito = $stmtConflito->fetchColumn();

        if ($existeConflito > 0) {
            header("Location: ../telas/agendamentos.php?status=erro_conflito");
            exit;
        }

        // Se passar pelas validações, insere normalmente
        $stmt = $pdo->prepare("INSERT INTO agendamentos (cliente_id, endereco_id, data_agendamento, tipo_trabalho, descricao, status) VALUES (?, ?, ?, ?, ?, 'Pendente')");
        $stmt->execute([$cliente_id, $endereco_id, $data_agendamento, $tipo_trabalho, $descricao]);

        header("Location: ../telas/agendamentos.php?status=sucesso");
        exit;

    } catch (Exception $e) {
        header("Location: ../telas/agendamentos.php?status=erro");
        exit;
    }
} else {
    header("Location: ../telas/agendamentos.php");
    exit;
}
?>