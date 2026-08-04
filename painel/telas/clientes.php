<?php
require_once '../controllers/controller_clientes.php';
$resultado = carregarDadosClientes();
$clientesComEnderecos = $resultado['clientes_com_enderecos'];
$termoBusca = $resultado['termoBusca'];
$erro_db = $resultado['erro_db'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - FerrazTech</title>
    <link rel="stylesheet" href="../css/clientes.css">
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

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-error"><?php echo $erro_db; ?></div>
        <?php endif; ?>

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
                    <?php if (count($clientesComEnderecos) > 0): ?>
                        <?php foreach ($clientesComEnderecos as $item): ?>
                            <?php 
                                $c = $item['cliente']; 
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                <td><?php echo htmlspecialchars($c['cpf']); ?></td>
                                <td class="acoes-btns">
                                    <!-- Botão Editar (Envia os dados unificados do controller para o JavaScript) -->
                                    <button type="button" class="btn-acao btn-editar" title="Editar Cliente e Endereços" onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">✏️</button>
                                    
                                    <!-- Botão Ver Detalhes -->
                                    <a href="detalhes_cliente.php?id=<?php echo $c['id']; ?>">
                                        <button type="button" class="btn-acao btn-info" title="Ver Detalhes, Agendamentos e Produtos">👁️</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #777; padding: 20px;">Nenhum cliente cadastrado até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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
            document.getElementById("edit_cliente_id").value = dados.cliente.id;
            document.getElementById("edit_nome").value = dados.cliente.nome;
            document.getElementById("edit_telefone").value = dados.cliente.telefone;
            document.getElementById("edit_email").value = dados.cliente.email;
            document.getElementById("edit_cpf").value = dados.cliente.cpf;

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

        document.getElementById("edit_cep").addEventListener("blur", function() {
            let cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                document.getElementById("edit_logradouro").value = "Buscando CEP...";
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById("edit_logradouro").value = data.logradouro;
                            document.getElementById("edit_bairro").value = data.bairro;
                            document.getElementById("edit_cidade").value = data.localidade;
                            document.getElementById("edit_estado").value = data.uf;
                            document.getElementById("edit_numero").focus();
                        } else {
                            alert("CEP não encontrado.");
                            preencherEndereco();
                        }
                    })
                    .catch(() => preencherEndereco());
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