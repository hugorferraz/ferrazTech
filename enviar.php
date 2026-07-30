<?php
// Exibe erros para facilitar o desenvolvimento (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Importações do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recebe e limpa os dados do formulário
    $nome             = trim($_POST['nome'] ?? '');
    $telefone         = trim($_POST['telefone'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $cpf              = trim($_POST['cpf'] ?? '');
    $tipo_residencia  = trim($_POST['tipo_residencia'] ?? '');
    $cep              = trim($_POST['cep'] ?? '');
    $cidade           = trim($_POST['cidade'] ?? '');
    $logradouro       = trim($_POST['logradouro'] ?? '');
    $numero           = trim($_POST['numero'] ?? '');
    $bairro           = trim($_POST['bairro'] ?? '');
    $estado           = trim($_POST['estado'] ?? '');
    $tipo_solicitacao = trim($_POST['tipo_solicitacao'] ?? '');

    // Validação básica anti-vazio
    if (empty($nome) || empty($telefone) || empty($email) || empty($cpf) || empty($cep)) {
        header("Location: index.html?status=erro_campos");
        exit;
    }

    // 2. Cria o conteúdo do arquivo .txt estruturado para importação posterior
    // Usamos um formato limpo que o futuro painel administrativo poderá ler facilmente
    $conteudoTxt  = "--- DADOS DO ORCAMENTO FERRAZTECH ---\n";
    $conteudoTxt .= "NOME: " . $nome . "\n";
    $conteudoTxt .= "TELEFONE: " . $telefone . "\n";
    $conteudoTxt .= "EMAIL: " . $email . "\n";
    $conteudoTxt .= "CPF: " . $cpf . "\n";
    $conteudoTxt .= "TIPO_RESIDENCIA: " . $tipo_residencia . "\n";
    $conteudoTxt .= "CEP: " . $cep . "\n";
    $conteudoTxt .= "LOGRADOURO: " . $logradouro . "\n";
    $conteudoTxt .= "NUMERO: " . $numero . "\n";
    $conteudoTxt .= "BAIRRO: " . $bairro . "\n";
    $conteudoTxt .= "CIDADE: " . $cidade . "\n";
    $conteudoTxt .= "ESTADO: " . $estado . "\n";
    $conteudoTxt .= "TIPO_SOLICITACAO: " . $tipo_solicitacao . "\n";
    $conteudoTxt .= "DATA_ENVIO: " . date('Y-m-d H:i:s') . "\n";

    // Nome único para o arquivo temporário (ex: orcamento_123456789.txt)
    $nomeArquivoTemp = sys_get_temp_dir() . '/' . 'orcamento_' . time() . '.txt';
    file_put_contents($nomeArquivoTemp, $conteudoTxt);

    // 3. Envio do E-mail via PHPMailer com o .txt anexado
    $mail = new PHPMailer(true);

    try {
        // Configurações do Servidor SMTP (coloque seus dados reais)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hugoferrazr1903@gmail.com'; // Seu e-mail
        $mail->Password   = 'rhcw kzxm tkvx opil';    // Senha de aplicativo do Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remetente e Destinatário
        $mail->setFrom('hugoferrazr1903@gmail.com', 'FerrazTech - Orçamentos');
        $mail->addAddress('hugoferrazr1903@gmail.com', 'FerrazTech'); // E-mail que recebe o pedido
        $mail->addReplyTo($email, $nome);

        // Anexa o arquivo .txt gerado para facilitar a importação
        $mail->addAttachment($nomeArquivoTemp, 'dados_orcamento_' . preg_replace('/[^0-9]/', '', $cpf) . '.txt');

        // Conteúdo do E-mail em HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Nova Solicitação de Orçamento - $nome";
        
        $mail->Body    = "
            <h2>Nova Solicitação de Orçamento recebida pelo Site</h2>
            <p><strong>Nome:</strong> {$nome}</p>
            <p><strong>Telefone/WhatsApp:</strong> {$telefone}</p>
            <p><strong>E-mail:</strong> {$email}</p>
            <p><strong>CPF:</strong> {$cpf}</p>
            <hr>
            <h3>Endereço do Local:</h3>
            <p><strong>Logradouro:</strong> {$logradouro}, Nº {$numero} - {$bairro}</p>
            <p><strong>Cidade/UF:</strong> {$cidade} / {$estado}</p>
            <p><strong>CEP:</strong> {$cep}</p>
            <hr>
            <h3>Detalhes do Serviço:</h3>
            <p><strong>Tipo de Propriedade:</strong> {$tipo_residencia}</p>
            <p><strong>Tipo de Solicitação:</strong> {$tipo_solicitacao}</p>
            <br>
            <p><em>* Um arquivo de texto (.txt) contendo estes dados foi anexado a esta mensagem para posterior importação no painel de controle.</em></p>
        ";

        $mail->send();
        
        // Remove o arquivo temporário do servidor para não ocupar espaço
        if (file_exists($nomeArquivoTemp)) {
            unlink($nomeArquivoTemp);
        }

        // Redireciona com sucesso
        header("Location: index.html?status=sucesso");
        exit;

    } catch (Exception $e) {
        if (file_exists($nomeArquivoTemp)) {
            unlink($nomeArquivoTemp);
        }
        header("Location: index.html?status=erro_email");
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}
?>