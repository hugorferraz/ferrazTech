<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = strip_tags(trim($_POST["nome"]));
    $telefone = strip_tags(trim($_POST["telefone"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $cpf = strip_tags(trim($_POST["cpf"]));
    $tipo_residencia = strip_tags(trim($_POST["tipo_residencia"]));
    
    // Novos campos de endereço individuais
    $cep = strip_tags(trim($_POST["cep"]));
    $logradouro = strip_tags(trim($_POST["logradouro"]));
    $numero = strip_tags(trim($_POST["numero"]));
    $bairro = strip_tags(trim($_POST["bairro"]));
    $cidade = strip_tags(trim($_POST["cidade"]));
    $estado = strip_tags(trim($_POST["estado"]));

    $tipo_solicitacao = strip_tags(trim($_POST["tipo_solicitacao"]));

    // Validação básica se algum campo obrigatório veio vazio
    if (empty($nome) || empty($telefone) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($cpf) || empty($cep) || empty($logradouro) || empty($numero) || empty($bairro) || empty($cidade) || empty($estado)) {
        header("HTTP/1.1 400 Bad Request");
        echo "Por favor, preencha todos os campos obrigatórios corretamente.";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hugoferrazr1903@gmail.com'; 
        $mail->Password   = 'rhcw kzxm tkvx opil'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('hugoferrazr1903@gmail.com', 'FerrazTech Portal');
        $mail->addAddress('hugoferrazr1903@gmail.com', 'Profissional / Pai');
        $mail->addReplyTo($email, $nome);

        $mail->isHTML(false);
        $mail->Subject = "Nova Solicitação de Orçamento: " . $tipo_solicitacao . " - " . $nome;
        
        $mensagem = "=========================================\n";
        $mensagem .= "NOVA SOLICITAÇÃO DE ORÇAMENTO - FERRAZTECH\n";
        $mensagem .= "=========================================\n\n";
        $mensagem .= "Nome Completo: " . $nome . "\n";
        $mensagem .= "Telefone/WhatsApp: " . $telefone . "\n";
        $mensagem .= "E-mail: " . $email . "\n";
        $mensagem .= "CPF: " . $cpf . "\n";
        $mensagem .= "Tipo de Propriedade: " . $tipo_residencia . "\n\n";
        
        $mensagem .= "--- ENDEREÇO DO CLIENTE ---\n";
        $mensagem .= "CEP: " . $cep . "\n";
        $mensagem .= "Logradouro: " . $logradouro . ", Nº " . $numero . "\n";
        $mensagem .= "Bairro: " . $bairro . "\n";
        $mensagem .= "Cidade/UF: " . $cidade . " - " . $estado . "\n\n";
        
        $mensagem .= "Tipo de Solicitação: " . $tipo_solicitacao . "\n\n";
        $mensagem .= "=========================================\n";
        $mensagem .= "Este e-mail foi gerado automaticamente pelo portal web.\n";

        $mail->Body = $mensagem;

        $mail->send();
        header("Location: index.html?status=sucesso");
        exit;
    } catch (Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Erro ao enviar a mensagem. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    echo "Acesso restrito.";
}
?>