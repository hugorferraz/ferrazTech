<?php
header('Content-Type: application/json');
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

$cliente_id = $_GET['cliente_id'] ?? null;
if (!$cliente_id) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Seleciona todas as colunas (incluindo endereco_id) dos orçamentos pendentes deste cliente
    $stmt = $pdo->prepare("SELECT * FROM orcamentos WHERE cliente_id = ? AND status = 'Pendente' ORDER BY data_solicitacao DESC");
    $stmt->execute([$cliente_id]);
    $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($orcamentos);
} catch (Exception $e) {
    echo json_encode([]);
}
?>