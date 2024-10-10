<?php
session_start();
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar se o token é válido e se não expirou
    $stmt = $dbconn2->prepare('
        SELECT * FROM senha_reset WHERE token = :token AND expire_at > NOW()
    ');
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $_SESSION['error_message'] = 'Token inválido ou expirado!';
        header('Location: solicitar_cadastro.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        if ($nova_senha !== $confirmar_senha) {
            $_SESSION['error_message'] = 'As senhas não correspondem.';
        } else {
            // Atualizar a senha do usuário
            $stmt = $dbconn2->prepare('
                UPDATE doador SET senha = :senha WHERE cpf = :cpf
            ');
            $stmt->execute([
                ':senha' => password_hash($nova_senha, PASSWORD_BCRYPT),
                ':cpf' => $reset['cpf']
            ]);

            // Remover o token usado
            $stmt = $dbconn2->prepare('DELETE FROM senha_reset WHERE token = :token');
            $stmt->execute([':token' => $token]);

            $_SESSION['success_message'] = 'Senha redefinida com sucesso!';
            header('Location: login.php');
            exit();
        }
    }
} else {
    header('Location: solicitar_cadastro.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
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
        <h2 class="text-center mt-4" style="color: #28a745;">Redefinir Senha</h2>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nova_senha" style="color: #28a745;">Nova Senha</label>
                <input type="password" name="nova_senha" id="nova_senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha" style="color: #28a745;">Confirmar Senha</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-key"></i>Redefinir Senha</button>
        </form>
        <div class="text-center mt-4">
            <a href="login.php" class="btn btn-secondary btn-block"><i class="fas fa-angle-left"></i> Login</a>

            <!-- <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-angle-left"></i> Login</button> -->
        </div>
    </div>
</body>
</html>
