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

    // 1. Busca todos os agendamentos cruzando com Clientes e Endereços ordenando pelo início
    $sql = "SELECT a.*, c.nome as cliente_nome, e.logradouro, e.numero, e.bairro, e.cidade 
            FROM agendamentos a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN enderecos e ON a.endereco_id = e.id
            ORDER BY a.data_inicio DESC";
    $stmt = $pdo->query($sql);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Busca lista de clientes que possuem solicitações/orçamentos pendentes
    $stmtC = $pdo->query("SELECT DISTINCT c.id, c.nome 
                          FROM clientes c 
                          JOIN orcamentos o ON c.id = o.cliente_id 
                          WHERE o.status = 'Pendente' 
                          ORDER BY c.nome ASC");
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
    <link rel="stylesheet" href="../css/clientes.css">
</head>
<body>

    <!-- Puxa o menu padronizado -->
    <?php include 'menu.php'; ?>

    <div class="container-dashboard">
        <div class="dash-header">
            <div>
                <h2>Gerenciamento de Agendamentos</h2>
                <p>Acompanhe e controle os serviços de longa duração e janelas da equipe.</p>
            </div>
            <div>
                <button type="button" onclick="abrirModalAgendamento()" style="background-color: #3b82f6; color: white; border: none; padding: 10px 18px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3); transition: background 0.2s, transform 0.1s;">
                    + Novo Agendamento
                </button>
            </div>
        </div>

        <!-- Alertas de Status -->
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'sucesso'): ?>
                <div class="alert alert-success">Agendamento cadastrado com sucesso!</div>
            <?php elseif ($_GET['status'] == 'excecao_sucesso'): ?>
                <div class="alert alert-success">Janela de exceção aberta com sucesso! Horário liberado para encaixes.</div>
            <?php elseif ($_GET['status'] == 'erro_conflito'): ?>
                <div class="alert alert-error"><strong>Conflito de Horário!</strong> Já existe um serviço alocado neste período.</div>
            <?php elseif ($_GET['status'] == 'erro_retroativo'): ?>
                <div class="alert alert-error"><strong>Data inválida!</strong> Não é permitido agendar para datas ou horários passados.</div>
            <?php elseif ($_GET['status'] == 'erro_periodo'): ?>
                <div class="alert alert-error"><strong>Período inválido!</strong> A data de término deve ser posterior à data de início.</div>
            <?php else: ?>
                <div class="alert alert-error">Ocorreu um erro na operação. Tente novamente.</div>
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
                        <th>Período do Serviço</th>
                        <th>Cliente</th>
                        <th>Endereço do Serviço</th>
                        <th>Trabalho</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($agendamentos) > 0): ?>
                        <?php foreach ($agendamentos as $ag): ?>
                            <tr>
                                <td>
                                    <strong>Início:</strong> <?php echo date('d/m/Y H:i', strtotime($ag['data_inicio'])); ?><br>
                                    <strong>Término:</strong> <?php echo date('d/m/Y H:i', strtotime($ag['data_termino'])); ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($ag['cliente_nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ag['logradouro']) . ', nº ' . htmlspecialchars($ag['numero']) . ' - ' . htmlspecialchars($ag['bairro']); ?></td>
                                <td><?php echo htmlspecialchars($ag['tipo_trabalho']); ?></td>
                                <td>
                                    <?php 
                                        $statusAgendamento = trim($ag['status']);
                                        if ($statusAgendamento == 'Agendado') {
                                            $estiloStatus = 'background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;';
                                        } elseif ($statusAgendamento == 'Concluído') {
                                            $estiloStatus = 'background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;';
                                        } elseif ($statusAgendamento == 'Cancelado') {
                                            $estiloStatus = 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;';
                                        } else {
                                            $estiloStatus = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;';
                                        }
                                    ?>
                                    <span style="padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; <?php echo $estiloStatus; ?>">
                                        <?php echo htmlspecialchars($ag['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Botão para abrir brecha/exceção durante o serviço longo -->
                                    <button type="button" onclick="abrirModalExcecao(<?php echo $ag['id']; ?>)" style="background: #f59e0b; color: white; border: none; padding: 6px 10px; font-size: 12px; font-weight: bold; border-radius: 6px; cursor: pointer; transition: background 0.2s;" title="Abrir janela de horário livre para orçamentos">
                                        ⚡ Abrir Janela
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #777; padding: 20px;">Nenhum agendamento cadastrado até o momento.</td>
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
                <input type="hidden" id="orcamento_id" name="orcamento_id" value="">

                <div class="form-group">
                    <label for="select_cliente">Selecione o Cliente:</label>
                    <select id="select_cliente" name="cliente_id" class="form-control" onchange="carregarDadosCliente(this.value)" required>
                        <option value="">-- Escolha um cliente com pendência --</option>
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
                    <label for="data_inicio">Data e Hora de Início:</label>
                    <input type="datetime-local" id="data_inicio" name="data_inicio" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                </div>

                <div class="form-group">
                    <label for="data_termino">Data e Hora de Previsão de Término:</label>
                    <input type="datetime-local" id="data_termino" name="data_termino" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                </div>

                <div class="form-group">
                    <label for="tipo_trabalho">Tipo de Trabalho / Serviço:</label>
                    <select id="tipo_trabalho" name="tipo_trabalho" class="form-control" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                        <option value="">-- Selecione o cliente primeiro --</option>
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

    <!-- Modal para Abrir Janela de Exceção -->
    <div id="modalExcecao" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModalExcecao()">&times;</span>
            <h2>⚡ Abrir Janela / Pausa</h2>
            <p>Libere um horário específico durante este serviço para encaixar orçamentos.</p>
            
            <form action="../backend/salvar_excecao.php" method="POST" style="margin-top: 15px;">
                <input type="hidden" id="excecao_agendamento_id" name="agendamento_id" value="">

                <div class="form-group">
                    <label for="data_inicio_excecao">Início da Liberação:</label>
                    <input type="datetime-local" id="data_inicio_excecao" name="data_inicio_excecao" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                </div>

                <div class="form-group">
                    <label for="data_termino_excecao">Término da Liberação:</label>
                    <input type="datetime-local" id="data_termino_excecao" name="data_termino_excecao" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                </div>

                <div class="form-group">
                    <label for="motivo">Motivo / Descrição:</label>
                    <input type="text" id="motivo" name="motivo" value="Janela livre para Orçamento Rápido" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc;">
                </div>
                
                <button type="submit" class="btn-principal" style="margin-top: 10px; background-color: #f59e0b;">Salvar Janela</button>
            </form>
        </div>
    </div>

    <script>
        const modalAgendamento = document.getElementById("modalAgendamento");
        const modalExcecao = document.getElementById("modalExcecao");

        function abrirModalAgendamento() {
            modalAgendamento.style.display = "flex";
        }

        function fecharModalAgendamento() {
            modalAgendamento.style.display = "none";
        }

        function abrirModalExcecao(agendamentoId) {
            document.getElementById("excecao_agendamento_id").value = agendamentoId;
            modalExcecao.style.display = "flex";
        }

        function fecharModalExcecao() {
            modalExcecao.style.display = "none";
        }

        // Função para buscar via AJAX os endereços e orçamentos do cliente selecionado no modal
        function carregarDadosCliente(clienteId) {
            const selectEnd = document.getElementById("select_endereco_cliente");
            const selectServico = document.getElementById("tipo_trabalho");
            const inputOrcamentoId = document.getElementById("orcamento_id"); 

            selectEnd.innerHTML = '<option value="">Carregando endereços...</option>';
            selectServico.innerHTML = '<option value="">Carregando solicitações...</option>';
            inputOrcamentoId.value = "";

            if (!clienteId) {
                selectEnd.innerHTML = '<option value="">-- Primeiro selecione um cliente --</option>';
                selectServico.innerHTML = '<option value="">-- Selecione o tipo de serviço --</option>';
                return;
            }

            // 1. Carrega Endereços
            fetch(`../backend/buscar_enderecos_json.php?cliente_id=${clienteId}`)
                .then(res => res.json())
                .then(data => {
                    selectEnd.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach((end, index) => {
                            let opt = document.createElement("option");
                            opt.value = end.id;
                            opt.text = `Endereço ${index + 1}: ${end.logradouro}, nº ${end.numero} - ${end.bairro}`;
                            selectEnd.appendChild(opt);
                        });
                    } else {
                        selectEnd.innerHTML = '<option value="">Nenhum endereço cadastrado</option>';
                    }
                });

            // 2. Carrega Solicitações Pendentes da Web
            fetch(`../backend/buscar_orcamentos_json.php?cliente_id=${clienteId}`)
                .then(res => res.json())
                .then(data => {
                    selectServico.innerHTML = '<option value="">-- Selecione a solicitação da web --</option>';
                    if (data.length > 0) {
                        data.forEach(orc => {
                            let opt = document.createElement("option");
                            opt.value = orc.tipo_solicitacao;
                            opt.setAttribute('data-orc-id', orc.id);
                            opt.text = `${orc.tipo_solicitacao} (Solicitado em: ${new Date(orc.data_solicitacao).toLocaleDateString()})`;
                            selectServico.appendChild(opt);
                        });
                        
                        let optOrc = document.createElement("option");
                        optOrc.value = "Orçamento";
                        optOrc.text = "Orçamento Geral";
                        selectServico.appendChild(optOrc);
                    } else {
                        let opt = document.createElement("option");
                        opt.value = "Orçamento";
                        opt.text = "Orçamento Geral (Sem pendências web)";
                        selectServico.appendChild(opt);
                    }
                });
        }

        // Evento para capturar o ID do orçamento selecionado e injetar num input hidden
        document.getElementById("tipo_trabalho").addEventListener("change", function() {
            let selectedOption = this.options[this.selectedIndex];
            let orcId = selectedOption.getAttribute('data-orc-id');
            document.getElementById("orcamento_id").value = orcId || "";
        });

        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            if (event.target == modalAgendamento) {
                modalAgendamento.style.display = "none";
            }
            if (event.target == modalExcecao) {
                modalExcecao.style.display = "none";
            }
        }
    </script>
</body>
</html>