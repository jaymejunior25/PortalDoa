<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

    if ($senha !== $confirmar_nova_senha) {
        $_SESSION['error_message'] = 'A nova senha e a confirmação da nova senha não correspondem.';
    } else {
        // Verifica se o CPF já existe no banco fixo
        $stmt = $dbconn->prepare('
            SELECT * 
            FROM pessoafisica p
            LEFT JOIN doctopessoafisica d 
            ON p.cdpesfis = d.cdpesfis
            WHERE d.nrdoctoident = :nrdoctoident
        ');
        $stmt->execute([':nrdoctoident' => $cpf]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Salva as informações no banco editável
            $stmt = $dbconn2->prepare('INSERT INTO doador (cpf, nome, senha) VALUES (:cpf, :nome, :senha)');
            $stmt->execute([
                ':cpf' => $cpf,
                ':nome' => $user['nmpesfis'],
                ':senha' => password_hash($senha, PASSWORD_BCRYPT),
            ]);

            $_SESSION['success_message'] = 'Senha cadastrada com sucesso!';
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['error_message'] = 'CPF não encontrado!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Senha</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
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
        <h2 class="text-center mb-4" style="color: #28a745;">Cadastro de Senha</h2>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="cpf" style="color: #28a745;">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="senha" style="color: #28a745;">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirmar_nova_senha" style="color: #28a745;">Confirmar Nova Senha:</label>
                <input type="password" name="confirmar_nova_senha" id="confirmar_nova_senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-key"></i>Cadastrar Senha</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-secondary"><i class="fas fa-angle-left"></i> Login</a>
        </div>
    </div>
</body>
</html>
