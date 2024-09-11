<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$documento =  $_SESSION['user_id'];
$name =  $_SESSION['username'];
$cpf =  $_SESSION['cpf'];
$pf =  $_SESSION['pf'];
$nascimento =  $_SESSION['nasc'];
$sexo = $_SESSION['sexo'];
if ($_SESSION['rh'] === 'P'){
    $rh = '+';
}else{
    $rh = '-';
}
$tipagem =  $_SESSION['abo'].$rh;

// Inicializa a variável que armazenará os resultados da query
$resultados = [];

// Executa a query e armazena os resultados
try {
    $stmt = $dbconn->prepare("
        SELECT 
            TO_CHAR(dtcoleta, 'DD/MM/YYYY') AS DATA_COLETA, 
            cdtipobtdoacao, 
            triagemcandidato.cdavaltriagem, 
            triagemcandidato.qtdiasinaptidao, 
            triagemcandidato.dttriagem
        FROM coleta
        JOIN triagemcandidato ON coleta.CDTRIAGEM = triagemcandidato.CDTRIAGEM
        WHERE coleta.cdpesfiscoleta = :PF
        AND hrtermcoleta IS NOT NULL

        UNION

        SELECT 
            TO_CHAR(dt_doaca, 'DD/MM/YYYY') AS DATA_COLETA, 
            OBJ140.TP_OBTHE, 
            NULL AS cdavaltriagem, 
            NULL AS qtdiasinaptidao, 
            NULL AS dttriagem
        FROM OBJ110
        JOIN OBJ140 ON OBJ110.CD_PESFIDOA = OBJ140.CD_PESFI
        WHERE OBJ110.CD_PESFIDOA = :PF
        AND OBJ140.hr_TERMI IS NOT NULL

        ORDER BY DATA_COLETA DESC
    "); 
    $stmt->bindParam(':PF', $pf); 
    $stmt->execute();
   // echo "Query executada com sucesso.<br>";



    // Armazena os resultados
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // echo "<pre>";
    //print_r($resultados);
   // echo "</pre>";


    // Ordena o array de resultados de forma decrescente pela 'data_coleta'
    usort($resultados, function ($a, $b) {
        $dateA = DateTime::createFromFormat('d/m/Y', $a['data_coleta']);
        $dateB = DateTime::createFromFormat('d/m/Y', $b['data_coleta']);
        return $dateB <=> $dateA; // Ordenação decrescente
    });

// Calcula a data da próxima doação com base na data de coleta mais recente e no sexo
if (!empty($resultados)) {
    $dataColetaMaisRecente = DateTime::createFromFormat('d/m/Y', $resultados[0]['data_coleta']);
    $cdavaltriagem = $resultados[0]['cdavaltriagem'];
    $qtdiasinaptidao = $resultados[0]['qtdiasinaptidao'] ?? 0;
    $dttriagem = isset($resultados[0]['dttriagem']) ? DateTime::createFromFormat('Y-m-d', $resultados[0]['dttriagem']) : null;
    
    if ($cdavaltriagem === 'AP') {
        // Verifica o número de doações nos últimos 12 meses
        $doacoesUltimos12Meses = 0;
        $dataLimite12Meses = (new DateTime())->modify('-12 months');

        foreach ($resultados as $resultado) {
            $dataColeta = DateTime::createFromFormat('d/m/Y', $resultado['data_coleta']);
            if ($dataColeta >= $dataLimite12Meses) {
                $doacoesUltimos12Meses++;
            } else {
                break; // Como a lista está ordenada por data desc, podemos parar o loop
            }
        }

        // Calcula a próxima data de doação com base nas regras e no número de doações nos últimos 12 meses
        if ($sexo === 'M') {
            // Limite de 4 doações nos últimos 12 meses para homens
            if ($doacoesUltimos12Meses < 4) {
                $dataProximaDoacao = $dataColetaMaisRecente->modify('+2 months +2 days');
            } else { 
                $dataProximaDoacao = $dataLimite12Meses->modify('+12 months +2 days');
            }
        } else if ($sexo === 'F') {
            // Limite de 3 doações nos últimos 12 meses para mulheres
            if ($doacoesUltimos12Meses < 3) {
                $dataProximaDoacao = $dataColetaMaisRecente->modify('+3 months +2 days');
            } else {
                $dataProximaDoacao = $dataLimite12Meses->modify('+12 months +2 days');
            }
        }
    }elseif ($cdavaltriagem === 'RT' && $dttriagem !== null) {
        // Se for "RT", usa `qtdiasinaptidao` para calcular a próxima data com base na `dttriagem`
        $dataProximaDoacao = $dttriagem->modify("+$qtdiasinaptidao days");
    } elseif ($cdavaltriagem === 'RD') {
        // Se for "RD", não há data de próxima doação
        $dataProximaDoacao = null;
        $mensagem = 'O usuário foi recusado em definitivo.';
    }
    if (isset($dataProximaDoacao)) {
        $proximaDoacaoFormatada = $dataProximaDoacao->format('d/m/Y');
    }
}
} catch (PDOException $e) {
    echo "Erro ao executar a query: " . $e->getMessage();
}
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

                <a href="logout.php" class="btn btn-danger "><i class="fas fa-sign-out-alt"></i> Logout</a>
                <!-- Botão para gerar PDF -->
                <form action="gerar_pdf.php" method="post">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                    <input type="hidden" name="cpf" value="<?php echo htmlspecialchars($cpf); ?>">
                    <input type="hidden" name="nascimento" value="<?php echo htmlspecialchars($nascimento); ?>">
                    <input type="hidden" name="sexo" value="<?php echo htmlspecialchars($sexo); ?>">
                    <input type="hidden" name="tipagem" value="<?php echo htmlspecialchars($tipagem); ?>">
                    <input type="hidden" name="resultados" value="<?php echo htmlspecialchars(json_encode($resultados)); ?>">
                    <?php if (isset($proximaDoacaoFormatada)) { ?>
                        <input type="hidden" name="proxima_doacao" value="<?php echo htmlspecialchars($proximaDoacaoFormatada); ?>">
                    <?php } ?>
                    <button type="submit" class="btn btn-primary">Gerar PDF</button>
                </form>

            </nav>
            <div class="container-fluid" id="content">
                <h2 class="mt-4">Bem Vindo ao Portal do Doador <br> Nome: <?php echo ucfirst($name); ?> <br> CPF: <?php echo ucfirst($cpf); ?> 
                <br> Data Nascimento: <?php echo ucfirst($nascimento); ?> <br> Tipagem: <?php echo ucfirst($tipagem); ?>  </h2>
                
                <!-- <p>Escolha uma das opções ao lado.</p> -->
                <!-- Exibir os resultados da query -->

                <h2>Resultados de Coleta e Doação</h2>

                <p><strong>Total de doações:</strong> <?php echo count($resultados); ?></p>

                <?php if (isset($mensagem)) { ?>
                    <p><strong>Status:</strong> <?php echo $mensagem; ?></p>
                <?php } elseif (isset($proximaDoacaoFormatada)) { ?>
                    <p><strong>Data da próxima doação:</strong> <?php echo $proximaDoacaoFormatada; ?></p>
                <?php } ?>
                <?php if (!empty($resultados)) { ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Data da Coleta</th>
                                <th>Tipo de Doação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['data_coleta'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['cdtipobtdoacao'] ?? $row['TP_OBTHE']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p>Nenhum resultado encontrado.</p>
                <?php } ?>
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
