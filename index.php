<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$documento =  $_SESSION['user_id'];
$name =  $_SESSION['username']
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Doador</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading text-white">Menu</div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white">Voltar ao Index</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('home')">Home</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('about')">Serviço 1</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('services')">Serviço 2</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('Etiqueta')">Etiqueta 3</a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom" id="top-navbar">
                <button class="btn btn-primary" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>

                <a href="logout.php" class="btn btn-danger mt-3"><i class="fas fa-sign-out-alt"></i> Logout</a>

            </nav>
            <div class="container-fluid" id="content">
                <h1 class="mt-4">Bem Vindo ao Portal <?php echo ucfirst($name); ?></h1>
                <p>Escolha uma das opções ao lado.</p>
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
</body>
</html>
