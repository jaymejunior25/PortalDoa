<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];
    $nascimento =  $_SESSION['nasc'];
    // Verifica se o CPF existe no banco de dados fixo
    $stmt = $dbconn->prepare('
        SELECT * 
        FROM pessoafisica p
        LEFT JOIN doctopessoafisica d 
        ON p.cdpesfis = d.cdpesfis
        WHERE d.nrdoctoident = :nrdoctoident
    ');
    $stmt->execute([':nrdoctoident' => $cpf]);
    $  = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Gerar um token seguro para o link de redefinição de senha
        $token = bin2hex(random_bytes(50));
        $expire_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Armazenar o token e seu prazo de validade no banco de dados editável
        $stmt = $dbconn2->prepare('
            INSERT INTO senha_reset (cpf, token, expire_at) 
            VALUES (:cpf, :token, :expire_at)
        ');
        $stmt->execute([
            ':cpf' => $cpf,
            ':token' => $token,
            ':expire_at' => $expire_at
        ]);

        // Enviar o e-mail ao usuário com o link para criar a senha
        $link = "http://10.95.2.134/portalD/redefinir_senha.php?token=" . $token;
        $subject = "Criação de senha - Sistema";
        $message = "Olá " . $user['nmpesfis'] . ",<br><br>";
        $message .= "CPF: " . $cpf . "<br>";
        $message .= "Data de Nascimento: " . $nascimento . "<br><br>";
        $message .= "Clique no link abaixo para criar sua senha:<br>";
        $message .= "<a href='" . $link . "'>Criar Senha</a><br><br>";
        $message .= "Este link é válido por 1 hora.<br><br>";
        $message .= " Suporte Portal do Doador.<br>";
        $message .= " Fundação Hemopa.<br><br>";
        $message .= " Mensagem gerada automaticamente, não sendo necessário responder.<br><br>";
       
        

        // PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor SMTP do Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Servidor SMTP do Gmail
            $mail->SMTPAuth   = true;              // Habilitar autenticação SMTP
            $mail->Username   = 'portaldoador@gmail.com'; // Seu e-mail do Gmail
            $mail->Password   = 'mpqi bxcy xitm gdei';    // Sua senha de aplicativo
            $mail->SMTPSecure = 'tls';             // Criptografia TLS
            $mail->Port       = 587;               // Porta do servidor SMTP do Gmail

            $mail->CharSet = 'UTF-8';              // Definir a codificação correta


            // Configuração de remetente e destinatário
            $mail->setFrom('portaldoador@gmail.com', 'Portal Doador');
            $mail->addAddress($user['txemail']);  // Destinatário

            // Conteúdo do e-mail
            $mail->isHTML(true);                                  // Definir como HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;

            // Enviar o e-mail
            $mail->send();
            $_SESSION['success_message'] = 'Um e-mail foi enviado com instruções para criar sua senha.';
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erro no envio do e-mail: {$mail->ErrorInfo}";
        }

    } else {
        $_SESSION['error_message'] = 'CPF não encontrado!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Criação de Senha</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <style>
        body {
            background-color: #f2f9f2; /* Fundo verde claro */
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            padding: 2rem;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 10%;
        }
        .btn-custom {
            background-color: #28a745;
            color: white;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mt-4" style="color: #28a745;">Solicitar Criação de Senha</h2>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="cpf" style="color: #28a745;">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="far fa-envelope"></i>Enviar E-mail</button>
        </form>
        <div class="text-center mt-4">
            <a href="login.php" class="btn btn-secondary btn-block"><i class="fas fa-angle-left"></i> Login</a>
            <!-- <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-angle-left"></i> Login</button> -->
        </div>
    </div>
</body>
</html>
