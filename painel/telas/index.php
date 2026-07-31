<?php
// Conexão rápida para puxar os contadores do dashboard
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = '';

$totalClientes = 0;
$totalOrcamentos = 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Conta total de clientes
    $stmtC = $pdo->query("SELECT COUNT(*) FROM clientes");
    $totalClientes = $stmtC->fetchColumn();

    // Conta total de orçamentos
    $stmtO = $pdo->query("SELECT COUNT(*) FROM orcamentos");
    $totalOrcamentos = $stmtO->fetchColumn();

} catch (Exception $e) {
    // Caso dê erro na contagem, segue o jogo sem quebrar a tela
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FerrazTech</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>

    <!-- Puxa o menu padronizado -->
    <?php include 'menu.php'; ?>

    <div class="container-dashboard">
        
        <!-- Cabeçalho da Página com o Botão Discreto de Importar -->
        <div class="dash-header">
            <div>
                <h2>Visão Geral do Sistema</h2>
                <p>Acompanhe os principais indicadores e movimentações da FerrazTech.</p>
            </div>
            <!-- Botão menor e discreto alinhado à direita -->
            <button type="button" id="btnAbrirModal" class="btn-discreto">
                📁 Importar .txt
            </button>
        </div>

        <!-- Alerta de Status (Se houver) -->
        <?php if (isset($_GET['status'])): ?>
            <div id="alertaStatus" class="alert <?php echo ($_GET['status'] == 'sucesso') ? 'alert-success' : 'alert-error'; ?>">
                <?php 
                    if ($_GET['status'] == 'sucesso') echo 'Cliente importado e cadastrado com sucesso!';
                    elseif ($_GET['status'] == 'erro_formato') echo 'Erro: O arquivo enviado não possui o formato esperado.';
                    elseif ($_GET['status'] == 'erro_upload') echo 'Erro ao carregar o arquivo. Tente novamente.';
                    else echo 'Erro no banco de dados ao salvar o registro.';
                ?>
            </div>
        <?php endif; ?>

        <!-- Cards de Indicadores (Dashboard) -->
        <div class="cards-grid">
            <div class="card-metric">
                <h3>Total de Clientes</h3>
                <p class="numero"><?php echo $totalClientes; ?></p>
            </div>
            <div class="card-metric">
                <h3>Orçamentos Registrados</h3>
                <p class="numero"><?php echo $totalOrcamentos; ?></p>
            </div>
            <div class="card-metric">
                <h3>Sistema</h3>
                <p class="status-ativo">● Online / Ativo</p>
            </div>
        </div>

    </div>

    <!-- Janela Modal (Pop-up) de Importação -->
    <div id="modalImportar" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal">&times;</span>
            <h2>Importar Orçamento</h2>
            <p>Selecione o arquivo <strong>.txt</strong> para processamento automático.</p>
            
            <form action="../backend/importar.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="arquivo_txt">Arquivo .txt do Orçamento:</label>
                    <input type="file" id="arquivo_txt" name="arquivo_txt" accept=".txt" required>
                </div>
                
                <button type="submit" class="btn-principal">Processar e Salvar</button>
            </form>
        </div>
    </div>

    <!-- Scripts JavaScript: Modal e Limpeza de URL/Alerta -->
    <script>
        // Controle do Modal
        const modal = document.getElementById("modalImportar");
        const btnAbrir = document.getElementById("btnAbrirModal");
        const spanFechar = document.getElementsByClassName("fechar-modal")[0];

        btnAbrir.onclick = function() { modal.style.display = "flex"; }
        spanFechar.onclick = function() { modal.style.display = "none"; }
        window.onclick = function(event) { if (event.target == modal) modal.style.display = "none"; }

        // Sumir com o alerta após 4 segundos e limpar a URL do navegador
        window.onload = function() {
            const alerta = document.getElementById("alertaStatus");
            if (alerta) {
                setTimeout(function() {
                    // Efeito suave de sumir
                    alerta.style.transition = "opacity 0.5s ease";
                    alerta.style.opacity = "0";
                    setTimeout(function() { alerta.remove(); }, 500);

                    // Limpa os parâmetros da URL sem recarregar a página
                    if (window.history.replaceState) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                    }
                }, 4000); // 4000 milissegundos = 4 segundos
            }
        };
    </script>

</body>
</html>