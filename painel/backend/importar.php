<?php
// Exibe erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações do Banco de Dados MySQL Local
$host = 'localhost';
$dbname = 'ferraztech_db';
$username = 'root';
$password = ''; // Altere se o seu MySQL tiver senha

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se o arquivo foi enviado sem erros
    if (isset($_FILES['arquivo_txt']) && $_FILES['arquivo_txt']['error'] == 0) {
        $fileTmpPath = $_FILES['arquivo_txt']['tmp_name'];
        
        // Lê todas as linhas do arquivo .txt
        $linhas = file($fileTmpPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $dados = [];
        foreach ($linhas as $linha) {
            // Separa a chave e o valor usando o caractere ':'
            if (strpos($linha, ':') !== false) {
                list($chave, $valor) = explode(':', $linha, 2);
                $dados[trim($chave)] = trim($valor);
            }
        }

        // Valida se os campos essenciais foram encontrados no arquivo
        if (!isset($dados['CPF']) || !isset($dados['NOME'])) {
            header("Location: ../telas/index.php?status=erro_formato");
            exit;
        }

        $nome             = $dados['NOME'] ?? '';
        $telefone         = $dados['TELEFONE'] ?? '';
        $email            = $dados['EMAIL'] ?? '';
        $cpf              = $dados['CPF'] ?? '';
        $tipo_residencia  = $dados['TIPO_RESIDENCIA'] ?? '';
        $cep              = $dados['CEP'] ?? '';
        $logradouro       = $dados['LOGRADOURO'] ?? '';
        $numero           = $dados['NUMERO'] ?? '';
        $bairro           = $dados['BAIRRO'] ?? '';
        $cidade           = $dados['CIDADE'] ?? '';
        $estado           = $dados['ESTADO'] ?? '';
        $tipo_solicitacao = $dados['TIPO_SOLICITACAN'] ?? ($dados['TIPO_SOLICITACAO'] ?? '');

        try {
            // Conexão com o Banco de Dados via PDO
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->beginTransaction();

            // 1. Verifica se o cliente já existe pelo CPF
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE cpf = ?");
            $stmt->execute([$cpf]);
            $cliente = $stmt->fetch();

            if ($cliente) {
                $cliente_id = $cliente['id'];
                // Atualiza dados do cliente se necessário
                $updateCliente = $pdo->prepare("UPDATE clientes SET nome = ?, telefone = ?, email = ? WHERE id = ?");
                $updateCliente->execute([$nome, $telefone, $email, $cliente_id]);
            } else {
                // Insere novo cliente
                $insertCliente = $pdo->prepare("INSERT INTO clientes (nome, telefone, email, cpf) VALUES (?, ?, ?, ?)");
                $insertCliente->execute([$nome, $telefone, $email, $cpf]);
                $cliente_id = $pdo->lastInsertId();
            }

            // 2. Salva o Endereço vinculado ao Cliente
            $insertEndereco = $pdo->prepare("INSERT INTO enderecos (cliente_id, cep, logradouro, numero, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertEndereco->execute([$cliente_id, $cep, $logradouro, $numero, $bairro, $cidade, $estado]);

            // 3. Salva o Orçamento na tabela `orcamentos`
            $insertOrcamento = $pdo->prepare("INSERT INTO orcamentos (cliente_id, tipo_residencia, tipo_solicitacao, status) VALUES (?, ?, ?, 'Pendente')");
            $insertOrcamento->execute([$cliente_id, $tipo_residencia, $tipo_solicitacao]);

            $pdo->commit();

            header("Location: ../telas/index.php?status=sucesso");
            exit;

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Mostra o erro real na tela para sabermos o que aconteceu
            echo "<h3>Erro detalhado do Banco de Dados:</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            exit;
        }

    } else {
        header("Location: ../telas/index.php?status=erro_upload");
        exit;
    }
} else {
    header("Location: ../telas/index.php");
    exit;
}
?>