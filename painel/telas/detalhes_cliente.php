<?php
// Conexão com o Banco de Dados
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

$clienteId = $_GET['id'] ?? null;
$cliente = null;
$enderecos = [];

if (!$clienteId) {
    header("Location: clientes.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Busca os dados do cliente
    $stmtC = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmtC->execute([$clienteId]);
    $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        header("Location: clientes.php");
        exit;
    }

    // 2. Busca todos os endereços vinculados a este cliente
    $stmtE = $pdo->prepare("SELECT * FROM enderecos WHERE cliente_id = ?");
    $stmtE->execute([$clienteId]);
    $enderecos = $stmtE->fetchAll(PDO::FETCH_ASSOC);

    // 3. Busca agendamentos do cliente (caso a tabela exista no banco)
    $agendamentos = [];
    try {
        $stmtA = $pdo->prepare("SELECT * FROM agendamentos WHERE cliente_id = ?");
        $stmtA->execute([$clienteId]);
        $agendamentos = $stmtA->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        // Ignora caso a tabela ainda não esteja populada ou criada
    }

    // 4. Busca produtos/orçamentos vinculados ao cliente
    $produtos = [];
    try {
        $stmtP = $pdo->prepare("SELECT * FROM orcamentos WHERE cliente_id = ?");
        $stmtP->execute([$clienteId]);
        $produtos = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        // Ignora caso não haja registros
    }

} catch (Exception $e) {
    $erro_db = "Erro ao carregar detalhes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente - FerrazTech</title>
    <link rel="stylesheet" href="../css/clientes.css"> <!-- Reaproveita o estilo limpo da tela de clientes -->
</head>
<body>

    <!-- Puxa o menu padronizado -->
    <?php include 'menu.php'; ?>

    <div class="container-dashboard">
        <div class="dash-header">
            <div>
                <h2>Detalhes do Cliente</h2>
                <p>Informações completas e vínculos do cliente selecionado.</p>
            </div>
            <div>
                <a href="clientes.php" class="btn-voltar">← Voltar para Clientes</a>
            </div>
        </div>

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-error"><?php echo $erro_db; ?></div>
        <?php endif; ?>

        <!-- Card com Informações Pessoais -->
        <div class="card-metric" style="margin-bottom: 25px; background: #fff;">
            <h3 style="color: #2c3e50; font-size: 16px; border-bottom: 2px solid #3498db; padding-bottom: 8px; margin-bottom: 15px;">Dados Pessoais</h3>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome']); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($cliente['telefone']); ?></p>
        </div>

        <!-- Seção de Endereços Vinculados -->
        <h3 style="color: #2c3e50; margin-bottom: 15px;">Endereços Vinculados (<?php echo count($enderecos); ?>)</h3>
        
        <?php if (count($enderecos) > 0): ?>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>CEP</th>
                            <th>Logradouro</th>
                            <th>Número</th>
                            <th>Bairro</th>
                            <th>Cidade/UF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enderecos as $index => $end): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($end['cep']); ?></td>
                                <td><strong><?php echo htmlspecialchars($end['logradouro']); ?></strong></td>
                                <td><?php echo htmlspecialchars($end['numero']); ?></td>
                                <td><?php echo htmlspecialchars($end['bairro']); ?></td>
                                <td><?php echo htmlspecialchars($end['cidade']) . ' / ' . htmlspecialchars($end['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: #777; background: #f8fafc; padding: 15px; border-radius: 6px;">Nenhum endereço cadastrado para este cliente.</p>
        <?php endif; ?>
        
        <!-- Seção de Agendamentos -->
        <h3 style="color: #2c3e50; margin-top: 30px; margin-bottom: 15px;">Agendamentos (<?php echo count($agendamentos); ?>)</h3>
        <?php if (count($agendamentos) > 0): ?>
            <!-- Tabela de agendamentos aqui -->
        <?php else: ?>
            <p style="color: #777; background: #f8fafc; padding: 15px; border-radius: 6px;">Nenhum agendamento registrado para este cliente.</p>
        <?php endif; ?>

        <!-- Seção de Produtos / Orçamentos -->
        <h3 style="color: #2c3e50; margin-top: 30px; margin-bottom: 15px;">Produtos / Orçamentos (<?php echo count($produtos); ?>)</h3>
        <?php if (count($produtos) > 0): ?>
            <!-- Tabela de produtos aqui -->
        <?php else: ?>
            <p style="color: #777; background: #f8fafc; padding: 15px; border-radius: 6px;">Nenhum produto ou orçamento registrado para este cliente.</p>
        <?php endif; ?>
        
    </div>

</body>
</html>