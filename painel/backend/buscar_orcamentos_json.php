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
    // Busca apenas orçamentos pendentes deste cliente específico
    $stmt = $pdo->prepare("SELECT id, tipo_solicitacao, data_solicitacao FROM orcamentos WHERE cliente_id = ? AND status = 'Pendente' ORDER BY data_solicitacao DESC");
    $stmt->execute([$cliente_id]);
    $orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orcamentos);
} catch (Exception $e) {
    echo json_encode([]);
}