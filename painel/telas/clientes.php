<?php
// Conexão com o Banco de Dados
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

$clientes = [];
$termoBusca = $_GET['busca'] ?? ''; // Pega o que foi digitado na busca

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se houver termo de busca, filtra por nome (usando LIKE). Senão, traz todos.
    if (!empty($termoBusca)) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome LIKE ? ORDER BY id DESC");
        $stmt->execute(["%$termoBusca%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");
    }
    
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $erro_db = "Erro ao carregar clientes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - FerrazTech</title>
    <link rel="stylesheet" href="../css/clientes.css"> <!-- Vamos criar este CSS específico ou usar o index.css -->
</head>
<body>

    <!-- Puxa o menu padronizado -->
    <?php include 'menu.php'; ?>

    <div class="container-dashboard">
        <div class="dash-header">
            <div>
                <h2>Gerenciamento de Clientes</h2>
                <p>Lista completa de clientes cadastrados no sistema.</p>
            </div>
        </div>

        <!-- Formulário de Pesquisa por Nome -->
        <form method="GET" action="clientes.php" class="form-busca">
            <input type="text" name="busca" placeholder="Pesquisar cliente pelo nome..." value="<?php echo htmlspecialchars($termoBusca); ?>">
            <button type="submit" class="btn-buscar">🔍 Buscar</button>
            <?php if (!empty($termoBusca)): ?>
                <a href="clientes.php" class="btn-limpar">Limpar Filtro</a>
            <?php endif; ?>
        </form>

        <!-- Tabela de Clientes -->
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clientes) > 0): ?>
                        <?php foreach ($clientes as $c): ?>
                            <?php
                                // Busca todos os endereços vinculados a este cliente específico
                                $stmtEnd = $pdo->prepare("SELECT * FROM enderecos WHERE cliente_id = ?");
                                $stmtEnd->execute([$c['id']]);
                                $enderecosCliente = $stmtEnd->fetchAll(PDO::FETCH_ASSOC);

                                // Agrupa os dados do cliente e seus respectivos endereços em um array único
                                $dadosClienteCompleto = [
                                    'cliente' => $c,
                                    'enderecos' => $enderecosCliente
                                ];
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['cpf']); ?></td>
                                <td class="acoes-btns">
                                    <!-- Botão Editar (Envia os dados do cliente e seus múltiplos endereços para o JavaScript) -->
                                    <button type="button" class="btn-acao btn-editar" title="Editar Cliente e Endereços" onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($dadosClienteCompleto), ENT_QUOTES, 'UTF-8'); ?>)">✏️</button>
                                    
                                    <!-- Botão Ver Detalhes / Agendamentos / Histórico / Produtos -->
                                    <a href="detalhes_cliente.php?id=<?php echo $c['id']; ?>">
                                        <button type="button" class="btn-acao btn-info" title="Ver Detalhes, Agendamentos e Produtos">👁️</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #777; padding: 20px;">Nenhum cliente cadastrado até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Edição de Cliente -->
    <!-- Modal de Edição de Cliente e Endereço -->
    <div id="modalEditar" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModalEditar()">&times;</span>
            <h2>Editar Cliente e Endereço</h2>
            
            <form action="../backend/atualizar_cliente.php" method="POST">
                <input type="hidden" id="edit_cliente_id" name="cliente_id">
                
                <div class="modal-grid">
                    
                    <!-- Coluna da Esquerda: Dados Pessoais -->
                    <div class="modal-coluna">
                        <h3>Dados Pessoais</h3>
                        
                        <div class="form-group">
                            <label for="edit_nome">Nome:</label>
                            <input type="text" id="edit_nome" name="nome" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_telefone">Telefone:</label>
                            <input type="text" id="edit_telefone" name="telefone" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_email">E-mail:</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_cpf">CPF:</label>
                            <input type="text" id="edit_cpf" name="cpf" required>
                        </div>
                    </div>

                    <!-- Coluna da Direita: Endereço -->
                    <div class="modal-coluna">
                        <h3>Endereço</h3>
                        
                        <div class="form-group">
                            <label for="select_endereco">Selecione qual endereço editar:</label>
                            <select id="select_endereco" name="endereco_id" class="form-control" onchange="preencherEndereco()" required>
                                <!-- Preenchido via JS -->
                            </select>
                        </div>

                        <div id="campos-endereco" style="display: none;">
                            <div class="form-group">
                                <label for="edit_cep">CEP:</label>
                                <input type="text" id="edit_cep" name="cep">
                            </div>
                            <div class="form-group">
                                <label for="edit_logradouro">Logradouro:</label>
                                <input type="text" id="edit_logradouro" name="logradouro">
                            </div>
                            <div class="form-group" style="display: flex; gap: 10px;">
                                <div style="flex: 2;">
                                    <label for="edit_numero">Número:</label>
                                    <input type="text" id="edit_numero" name="numero">
                                </div>
                                <div style="flex: 1;">
                                    <label for="edit_estado">UF:</label>
                                    <input type="text" id="edit_estado" name="estado" maxlength="2">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit_bairro">Bairro:</label>
                                <input type="text" id="edit_bairro" name="bairro">
                            </div>
                            <div class="form-group">
                                <label for="edit_cidade">Cidade:</label>
                                <input type="text" id="edit_cidade" name="cidade">
                            </div>
                        </div>
                    </div>

                    <!-- Botão Salvar ocupando a largura total embaixo -->
                    <div class="modal-footer-full">
                        <button type="submit" class="btn-principal">Salvar Alterações</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        let listaEnderecosGlobal = [];
        const modalEditar = document.getElementById("modalEditar");

        function abrirModalEditar(dados) {
            // Preenche dados do cliente
            document.getElementById("edit_cliente_id").value = dados.cliente.id;
            document.getElementById("edit_nome").value = dados.cliente.nome;
            document.getElementById("edit_telefone").value = dados.cliente.telefone;
            document.getElementById("edit_email").value = dados.cliente.email;
            document.getElementById("edit_cpf").value = dados.cliente.cpf;

            // Guarda os endereços na variável global
            listaEnderecosGlobal = dados.enderecos;
            const selectEnd = document.getElementById("select_endereco");
            selectEnd.innerHTML = "";

            if (listaEnderecosGlobal.length > 0) {
                document.getElementById("campos-endereco").style.display = "block";
                
                listaEnderecosGlobal.forEach((end, index) => {
                    let option = document.createElement("option");
                    option.value = end.id;
                    option.text = `Endereço ${index + 1}: ${end.logradouro}, nº ${end.numero} - ${end.bairro} (${end.cidade}/${end.estado})`;
                    selectEnd.appendChild(option);
                });

                preencherEndereco();
            } else {
                document.getElementById("campos-endereco").style.display = "none";
                let option = document.createElement("option");
                option.text = "Nenhum endereço cadastrado para este cliente";
                selectEnd.appendChild(option);
            }
            
            modalEditar.style.display = "flex";
        }

        function preencherEndereco() {
            const enderecoIdSelecionado = document.getElementById("select_endereco").value;
            const enderecoEncontrado = listaEnderecosGlobal.find(e => e.id == enderecoIdSelecionado);

            if (enderecoEncontrado) {
                document.getElementById("edit_cep").value = enderecoEncontrado.cep;
                document.getElementById("edit_logradouro").value = enderecoEncontrado.logradouro;
                document.getElementById("edit_numero").value = enderecoEncontrado.numero;
                document.getElementById("edit_bairro").value = enderecoEncontrado.bairro;
                document.getElementById("edit_cidade").value = enderecoEncontrado.cidade;
                document.getElementById("edit_estado").value = enderecoEncontrado.estado;
            }
        }

        // --- NOVA INTEGRAÇÃO COM A API DE CEP (ViaCEP) ---
        document.getElementById("edit_cep").addEventListener("blur", function() {
            let cep = this.value.replace(/\D/g, ''); // Remove traços e caracteres especiais

            if (cep.length === 8) {
                // Mostra um aviso visual leve de carregamento (opcional)
                document.getElementById("edit_logradouro").value = "Buscando CEP...";
                
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById("edit_logradouro").value = data.logradouro;
                            document.getElementById("edit_bairro").value = data.bairro;
                            document.getElementById("edit_cidade").value = data.localidade;
                            document.getElementById("edit_estado").value = data.uf;
                            // Foca automaticamente no campo de número após preencher
                            document.getElementById("edit_numero").focus();
                        } else {
                            alert("CEP não encontrado na base de dados da API.");
                            preencherEndereco(); // Restaura o original se falhar
                        }
                    })
                    .catch(error => {
                        console.error("Erro ao consultar a API de CEP:", error);
                        preencherEndereco();
                    });
            }
        });

        function fecharModalEditar() {
            modalEditar.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modalEditar) {
                modalEditar.style.display = "none";
            }
        }
    </script>

</body>
</html>