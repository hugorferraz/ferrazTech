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
    $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE cliente_id = ?");
    $stmt->execute([$cliente_id]);
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($enderecos);
} catch (Exception $e) {
    echo json_encode([]);
}