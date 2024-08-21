<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tpdoctoident = $_POST['tpdoctoident'];
    $nrdoctoident = $_POST['nrdoctoident'];

    $stmt = $dbconn->prepare('
        SELECT * 
        FROM pessoafisica p
        LEFT JOIN doctopessoafisica d 
        ON p.cdpesfis = d.cdpesfis
        WHERE d.tpdoctoident = :tpdoctoident 
        AND d.nrdoctoident = :nrdoctoident
    ');
    $stmt->execute([
        ':tpdoctoident' => $tpdoctoident, 
        ':nrdoctoident' => $nrdoctoident
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['cdpesfis'];
        $_SESSION['username'] = $user['nmpesfis'];
        //$_SESSION['user_type'] = 'standard'; // Definir o tipo de conta se aplicável

        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Documento não encontrado!';
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
    <div class="form-group">
        <label for="tpdoctoident" style="color: #28a745;">Tipo de Documento</label>
        <select name="tpdoctoident" id="tpdoctoident" class="form-control" required>
            <option value="RG">RG</option>
            <option value="CPF">CPF</option>
        </select>
    </div>
    <div class="form-group">
        <label for="nrdoctoident" style="color: #28a745;">Número do Documento</label>
        <input type="text" name="nrdoctoident" id="nrdoctoident" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-custom btn-block">Entrar</button>
</form>

    </div>
    <div class="fixed-bottom toggle-footer cursor_to_down" id="footer_fixed" >
            <!-- style="margin-top:50px;" -->
            <div class="fixed-bottom border-top bg-light text-center footer-content p-2" style="z-index:4; ">
                <!-- w3-card  -->
                <div class="footer-text" >
                    Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                    <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
                </div>
            </div>
        </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
