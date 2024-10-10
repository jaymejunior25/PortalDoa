<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    // Consulta para verificar se o CPF e senha estão corretos
    $stmt = $dbconn2->prepare('SELECT * FROM doador WHERE cpf = :cpf');
    $stmt->execute([':cpf' => $cpf]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    $sql = "SELECT pessoafisica.cdpesfis, 
                   nmpesfis, 
                   TO_CHAR(DHNASCTO, 'DD/MM/YYYY') AS data_nascimento, 
                   tpdoctoident, 
                   nrdoctoident, 
                   cdsexo,
                   cdgrpabo,
                   cdfatorrh,
                   dsfenotipagem,
                   cdclaspesfis
            FROM pessoafisica 
            JOIN doctopessoafisica ON pessoafisica.cdpesfis = doctopessoafisica.cdpesfis
            WHERE tpdoctoident = 'RG' 
            AND nrdoctoident = :CPF";
    
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([':CPF' => $cpf]);
    $user1 = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nome'];
        $_SESSION['cpf'] = $user['cpf'];
        $_SESSION['pf'] = $user1['cdpesfis'];
        $_SESSION['nasc'] = $user1['data_nascimento'];
        $_SESSION['sexo'] = $user1['cdsexo'];
        $_SESSION['abo'] = $user1['cdgrpabo'];
        $_SESSION['rh'] = $user1['cdfatorrh'];
        $_SESSION['situacao'] = $user1['cdclaspesfis'];
        
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'CPF ou senha inválidos!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        <h2 class="text-center mb-4" style="color: #28a745;">Login</h2>
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
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-door-open"></i>Entrar</button>
        </form>
        <div class="text-center mt-3">
            <a href="register.php">Primeiro acesso? Cadastre sua senha aqui</a>
        </div>
    </div>
</body>
</html>
