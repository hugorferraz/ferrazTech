<?php
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

$agendamentos = [];
$clientes = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Busca todos os agendamentos cruzando com Clientes e Endereços para exibir os nomes legíveis
    $sql = "SELECT a.*, c.nome as cliente_nome, e.logradouro, e.numero, e.bairro, e.cidade 
            FROM agendamentos a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN enderecos e ON a.endereco_id = e.id
            ORDER BY a.data_agendamento DESC";
    $stmt = $pdo->query($sql);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Busca lista de clientes para o select do modal de cadastro
    $stmtC = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
    $clientes = $stmtC->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $erro_db = "Erro ao carregar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - FerrazTech</title>
    <link rel="stylesheet" href="../css/clientes.css"> <!-- Reaproveita o estilo padrão profissional -->
</head>
<body>

    <!-- Puxa o menu padronizado -->
    <?php include 'menu.php'; ?>

    <div class="container-dashboard">
        <div class="dash-header">
            <div>
                <h2>Gerenciamento de Agendamentos</h2>
                <p>Acompanhe e controle os serviços agendados para os clientes.</p>
            </div>
            <div>
                <button type="button" class="btn-discreto" onclick="abrirModalAgendamento()" style="background-color: #3498db;">
                    + Novo Agendamento
                </button>
            </div>
        </div>

        <!-- Alertas de Status -->
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'sucesso'): ?>
                <div class="alert alert-success">Agendamento cadastrado com sucesso!</div>
            <?php elseif ($_GET['status'] == 'erro_conflito'): ?>
                <div class="alert alert-error"><strong>Conflito de Horário!</strong> Já existe um serviço agendado para este mesmo horário.</div>
            <?php elseif ($_GET['status'] == 'erro_retroativo'): ?>
                <div class="alert alert-error"><strong>Data inválida!</strong> Não é permitido realizar agendamentos para datas ou horários retroativos (passados).</div>
            <?php else: ?>
                <div class="alert alert-error">Ocorreu um erro ao salvar o agendamento. Tente novamente.</div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-error"><?php echo $erro_db; ?></div>
        <?php endif; ?>

        <!-- Tabela de Agendamentos -->
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Data / Hora</th>
                        <th>Cliente</th>
                        <th>Endereço do Serviço</th>
                        <th>Trabalho</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($agendamentos) > 0): ?>
                        <?php foreach ($agendamentos as $ag): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($ag['data_agendamento'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($ag['cliente_nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ag['logradouro']) . ', nº ' . htmlspecialchars($ag['numero']) . ' - ' . htmlspecialchars($ag['bairro']); ?></td>
                                <td><?php echo htmlspecialchars($ag['tipo_trabalho']); ?></td>
                                <td>
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #e2e8f0; color: #334155;">
                                        <?php echo htmlspecialchars($ag['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #777; padding: 20px;">Nenhum agendamento cadastrado até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Novo Agendamento -->
    <div id="modalAgendamento" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModalAgendamento()">&times;</span>
            <h2>Novo Agendamento</h2>
            
            <form action="../backend/salvar_agendamento.php" method="POST">
                
                <div class="form-group">
                    <label for="select_cliente">Selecione o Cliente:</label>
                    <select id="select_cliente" name="cliente_id" class="form-control" onchange="carregarEnderecosCliente(this.value)" required>
                        <option value="">-- Escolha um cliente --</option>
                        <?php foreach ($clientes as $cli): ?>
                            <option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="select_endereco_cliente">Selecione o Endereço do Serviço:</label>
                    <select id="select_endereco_cliente" name="endereco_id" class="form-control" required>
                        <option value="">-- Primeiro selecione um cliente --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_agendamento">Data e Hora:</label>
                    <input type="datetime-local" id="data_agendamento" name="data_agendamento" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                </div>

                <div class="form-group">
                    <label for="tipo_trabalho">Tipo de Trabalho / Serviço:</label>
                    <select id="tipo_trabalho" name="tipo_trabalho" class="form-control" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                        <option value="">-- Selecione o tipo de serviço --</option>
                        <option value="Orçamento">Orçamento</option>
                        <option value="Manutenção">Manutenção</option>
                        <option value="Nova Instalação">Nova Instalação</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descricao">Observações (Opcional):</label>
                    <textarea id="descricao" name="descricao" rows="3" placeholder="Detalhes adicionais..." style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;"></textarea>
                </div>
                
                <button type="submit" class="btn-principal">Salvar Agendamento</button>
            </form>
        </div>
    </div>

    <script>
        const modalAgendamento = document.getElementById("modalAgendamento");

        function abrirModalAgendamento() {
            modalAgendamento.style.display = "flex";
        }

        function fecharModalAgendamento() {
            modalAgendamento.style.display = "none";
        }

        // Função para buscar via AJAX os endereços do cliente selecionado no modal
        function carregarEnderecosCliente(clienteId) {
            const selectEnd = document.getElementById("select_endereco_cliente");
            selectEnd.innerHTML = '<option value="">Carregando endereços...</option>';

            if (!clienteId) {
                selectEnd.innerHTML = '<option value="">-- Primeiro selecione um cliente --</option>';
                return;
            }

            // Faz uma requisição rápida para buscar os endereços daquele cliente
            fetch(`../backend/buscar_enderecos_json.php?cliente_id=${clienteId}`)
                .then(response => response.json())
                .then(data => {
                    selectEnd.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach((end, index) => {
                            let opt = document.createElement("option");
                            opt.value = end.id;
                            opt.text = `Endereço ${index + 1}: ${end.logradouro}, nº ${end.numero} - ${end.bairro} (${end.cidade})`;
                            selectEnd.appendChild(opt);
                        });
                    } else {
                        let opt = document.createElement("option");
                        opt.text = "Este cliente não possui endereços cadastrados";
                        selectEnd.appendChild(opt);
                    }
                })
                .catch(error => {
                    console.error("Erro ao buscar endereços:", error);
                    selectEnd.innerHTML = '<option value="">Erro ao carregar endereços</option>';
                });
        }

        window.onclick = function(event) {
            if (event.target == modalAgendamento) {
                modalAgendamento.style.display = "none";
            }
        }
    </script>
</body>
</html>