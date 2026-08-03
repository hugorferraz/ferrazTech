<?php
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id    = $_POST['cliente_id'];
    $endereco_id   = $_POST['endereco_id'];
    $orcamento_id  = !empty($_POST['orcamento_id']) ? $_POST['orcamento_id'] : null;
    $data_inicio   = $_POST['data_inicio'];
    $data_termino  = $_POST['data_termino'];
    $tipo_trabalho = $_POST['tipo_trabalho']; 
    $descricao     = $_POST['descricao'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // --- 1. VALIDAÇÃO DE DATA RETROATIVA E PERÍODO ---
        $dataAtual = date('Y-m-d H:i:s');
        if ($data_inicio < $dataAtual || $data_termino < $dataAtual) {
            header("Location: ../telas/agendamentos.php?status=erro_retroativo");
            exit;
        }

        if ($data_termino <= $data_inicio) {
            header("Location: ../telas/agendamentos.php?status=erro_periodo");
            exit;
        }

        // --- 2. VALIDAÇÃO DE CONFLITO DE PERÍODO COM EXCEÇÃO INTELIGENTE ---
        // Verifica se há colisão com serviços longos, ignorando caso o horário esteja dentro de uma exceção/janela aberta.
        $stmtConflito = $pdo->prepare("
            SELECT COUNT(*) FROM agendamentos a
            WHERE a.status != 'Cancelado' 
            AND (
                (? BETWEEN a.data_inicio AND a.data_termino) OR 
                (? BETWEEN a.data_inicio AND a.data_termino) OR 
                (a.data_inicio BETWEEN ? AND ?)
            )
            AND NOT EXISTS (
                SELECT 1 FROM agendamento_excecoes e 
                WHERE e.agendamento_id = a.id 
                AND ? >= e.data_inicio_excecao 
                AND ? <= e.data_termino_excecao
            )
        ");
        $stmtConflito->execute([$data_inicio, $data_termino, $data_inicio, $data_termino, $data_inicio, $data_termino]);
        $existeConflito = $stmtConflito->fetchColumn();

        if ($existeConflito > 0) {
            header("Location: ../telas/agendamentos.php?status=erro_conflito");
            exit;
        }

        // Inicia transação
        $pdo->beginTransaction();

        // --- 3. INSERIR O AGENDAMENTO COM INÍCIO E TÉRMINO ---
        $stmt = $pdo->prepare("INSERT INTO agendamentos (cliente_id, endereco_id, orcamento_id, data_inicio, data_termino, tipo_trabalho, descricao, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Agendado')");
        $stmt->execute([$cliente_id, $endereco_id, $orcamento_id, $data_inicio, $data_termino, $tipo_trabalho, $descricao]);

        // --- 4. ATUALIZAR O ORÇAMENTO WEB CORRESPONDENTE ---
        if ($orcamento_id) {
            $stmtUpdateOrc = $pdo->prepare("UPDATE orcamentos SET status = 'Agendado' WHERE id = ?");
            $stmtUpdateOrc->execute([$orcamento_id]);
        }

        $pdo->commit();

        header("Location: ../telas/agendamentos.php?status=sucesso");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../telas/agendamentos.php?status=erro");
        exit;
    }
} else {
    header("Location: ../telas/agendamentos.php");
    exit;
}
?>