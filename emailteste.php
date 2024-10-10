<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Configurações do servidor SMTP do Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Servidor SMTP do Gmail
    $mail->SMTPAuth   = true;              // Habilitar autenticação SMTP
    $mail->Username   = 'portaldoador@gmail.com'; // Seu e-mail do Gmail
    $mail->Password   = 'mpqi bxcy xitm gdei'; // Sua senha do Gmail
    $mail->SMTPSecure = 'tls';             // Criptografia TLS
    $mail->Port       = 587;               // Porta do servidor SMTP do Gmail

    // Configuração de remetente e destinatário
    $mail->setFrom('portaldoador@gmail.com', 'Portal Doador');
    $mail->addAddress('jaymejunior@yahoo.com.br');  // Destinatário

    // Conteúdo do e-mail
    $mail->isHTML(true);                                  // Definir como HTML
    $mail->Subject = 'Testando envio de e-mail via Gmail';
    $mail->Body    = 'Este é um e-mail de teste usando o servidor Gmail com PHPMailer.';

    // Enviar o e-mail
    $mail->send();
    echo 'E-mail enviado com sucesso!';
} catch (Exception $e) {
    echo "Erro no envio do e-mail: {$mail->ErrorInfo}";
}
?>
