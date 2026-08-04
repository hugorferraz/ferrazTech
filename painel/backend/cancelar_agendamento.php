<?php
// Exibe erros do PHP na tela para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

if (isset($_GET['id'])) {
    $agendamento_id = $_GET['id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        // 1. Verifica se a coluna orcamento_id existe na tabela agendamentos
        $orcamento_id = null;
        $stmtCheckCol = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'orcamento_id'");
        
        if ($stmtCheckCol->rowCount() > 0) {
            $stmtBusca = $pdo->prepare("SELECT orcamento_id FROM agendamentos WHERE id = ?");
            $stmtBusca->execute([$agendamento_id]);
            $res = $stmtBusca->fetch(PDO::FETCH_ASSOC);
            if ($res && !empty($res['orcamento_id'])) {
                $orcamento_id = $res['orcamento_id'];
            }
        }

        // 2. Se houver orçamento vinculado, reverte o status dele para 'Pendente' na tabela orcamentos
        if ($orcamento_id) {
            $stmtReverte = $pdo->prepare("UPDATE orcamentos SET status = 'Pendente' WHERE id = ?");
            $stmtReverte->execute([$orcamento_id]);
        }

        // 3. Atualiza o status do agendamento para 'Cancelado'
        if ($orcamento_id !== null) {
            $stmtCancela = $pdo->prepare("UPDATE agendamentos SET status = 'Cancelado', orcamento_id = NULL WHERE id = ?");
            $stmtCancela->execute([$agendamento_id]);
        } else {
            $stmtCancela = $pdo->prepare("UPDATE agendamentos SET status = 'Cancelado' WHERE id = ?");
            $stmtCancela->execute([$agendamento_id]);
        }

        $pdo->commit();

        // Sucesso real: Redireciona para a tela
        header("Location: ../telas/agendamentos.php?status=sucesso");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Exibe o erro exato na tela para entendermos o que falhou
        echo "<div style='padding: 30px; font-family: sans-serif; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; margin: 20px; border-radius: 8px;'>";
        echo "<h2>❌ Erro Técnico Detalhado:</h2>";
        echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
        echo "<br><a href='../telas/agendamentos.php' style='padding: 10px 15px; background: #991b1b; color: #fff; text-decoration: none; border-radius: 5px;'>Voltar para Agendamentos</a>";
        echo "</div>";
        exit;
    }
} else {
    header("Location: ../telas/agendamentos.php?status=cancelado_sucesso");
    exit;
}
?>