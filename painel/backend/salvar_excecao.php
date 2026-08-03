<?php
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $agendamento_id       = $_POST['agendamento_id'];
    $data_inicio_excecao  = $_POST['data_inicio_excecao'];
    $data_termino_excecao = $_POST['data_termino_excecao'];
    $motivo               = $_POST['motivo'] ?? 'Liberação para Orçamento';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Valida se o término da exceção é posterior ao início
        if ($data_termino_excecao <= $data_inicio_excecao) {
            header("Location: ../telas/agendamentos.php?status=erro_periodo_excecao");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO agendamento_excecoes (agendamento_id, data_inicio_excecao, data_termino_excecao, motivo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$agendamento_id, $data_inicio_excecao, $data_termino_excecao, $motivo]);

        header("Location: ../telas/agendamentos.php?status=excecao_sucesso");
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