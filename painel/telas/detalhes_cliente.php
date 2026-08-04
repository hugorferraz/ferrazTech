<?php
require_once '../controllers/controller_detalhes_cliente.php';
$cliente_id = $_GET['id'] ?? null;
$resultado = carregarDetalhesCliente($cliente_id);

$cliente = $resultado['cliente'];
$enderecos = $resultado['enderecos'];
$orcamentos = $resultado['orcamentos'];
$agendamentos = $resultado['agendamentos'];
$erro_db = $resultado['erro_db'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente - FerrazTech</title>
    <link rel="stylesheet" href="../css/clientes.css">
</head>
<body>

    <!-- Puxa o menu padronizado -->
    <?php include 'menu.php'; ?>

    <div class="container-dashboard">
        
        <!-- Cabeçalho -->
        <div class="dash-header">
            <div>
                <h2>Detalhes do Cliente</h2>
                <p>Visão completa de cadastros, endereços, agendamentos e orçamentos.</p>
            </div>
            <div>
                <a href="clientes.php" class="btn-voltar">← Voltar para Clientes</a>
            </div>
        </div>

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-error"><?php echo $erro_db; ?></div>
        <?php endif; ?>

        <!-- Card de Dados Pessoais (Layout em Grid Elegante) -->
        <div class="card-metric" style="margin-bottom: 30px; background: #fff; padding: 25px;">
            <h3 style="color: #2c3e50; font-size: 16px; border-bottom: 2px solid #3498db; padding-bottom: 8px; margin-bottom: 20px; text-transform: none; letter-spacing: normal;">
                👤 Informações Pessoais
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Nome Completo</span>
                    <p style="font-size: 15px; font-weight: bold; color: #1e293b; margin-top: 4px;"><?php echo htmlspecialchars($cliente['nome']); ?></p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">CPF</span>
                    <p style="font-size: 15px; color: #334155; margin-top: 4px;"><?php echo htmlspecialchars($cliente['cpf']); ?></p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">E-mail</span>
                    <p style="font-size: 15px; color: #334155; margin-top: 4px;"><?php echo htmlspecialchars($cliente['email']); ?></p>
                </div>
                <div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Telefone</span>
                    <p style="font-size: 15px; color: #334155; margin-top: 4px;"><?php echo htmlspecialchars($cliente['telefone']); ?></p>
                </div>
            </div>
        </div>

        <!-- Seção de Endereços -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #2c3e50; margin-bottom: 12px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                📍 Endereços Vinculados <span style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 12px; font-size: 12px;"><?php echo count($enderecos); ?></span>
            </h3>
            
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
                <p style="color: #777; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">Nenhum endereço cadastrado para este cliente.</p>
            <?php endif; ?>
        </div>

        <!-- Seção de Agendamentos -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #2c3e50; margin-bottom: 12px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                📅 Agendamentos <span style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 12px; font-size: 12px;"><?php echo count($agendamentos); ?></span>
            </h3>

            <?php if (count($agendamentos) > 0): ?>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Data / Hora</th>
                                <th>Serviço</th>
                                <th>Local (Endereço)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendamentos as $ag): ?>
                                <tr>
                                    <td>
                                        <strong>Início:</strong> <?php echo date('d/m/Y H:i', strtotime($ag['data_inicio'])); ?><br>
                                        <strong>Término:</strong> <?php echo date('d/m/Y H:i', strtotime($ag['data_termino'])); ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($ag['tipo_trabalho']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ag['logradouro']) . ', nº ' . htmlspecialchars($ag['numero']); ?></td>
                                    <td>
                                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #dbeafe; color: #1e40af;">
                                            <?php echo htmlspecialchars($ag['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #777; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">Nenhum agendamento registrado para este cliente.</p>
            <?php endif; ?>
        </div>

        <!-- Seção de Produtos / Orçamentos -->
        <div style="margin-bottom: 10px;">
            <h3 style="color: #2c3e50; margin-bottom: 12px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                📋 Solicitações Web <span style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 12px; font-size: 12px;"><?php echo count($orcamentos); ?></span>
            </h3>

            <?php if (count($orcamentos) > 0): ?>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Data Solicitação</th>
                                <th>Tipo de Residência</th>
                                <th>Tipo de Solicitação</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orcamentos as $orc): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($orc['data_solicitacao'])); ?></td>
                                    <td><?php echo htmlspecialchars($orc['tipo_residencia']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($orc['tipo_solicitacao']); ?></strong></td>
                                    <td>
                                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #fef3c7; color: #92400e;">
                                            <?php echo htmlspecialchars($orc['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #777; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1;">Nenhum orçamento registrado para este cliente.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>