<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Variáveis da sessão
$documento = $_SESSION['user_id'];
$name = $_SESSION['username'];
$cpf = $_SESSION['cpf'];
$pf = $_SESSION['pf'];

// Inicializa a variável que armazenará os resultados da query
$resultados = [];

// Primeira query
try {
    $stmt = $dbconn->prepare("
        SELECT 
            PESSOAFISICA.CDPESFIS, 
            PESSOAFISICA.NMPESFIS, 
            TO_CHAR(PESSOAFISICA.DHNASCTO, 'DD/MM/YYYY') AS DATA_NASCIMENTO,
            PESSOAFISICA.TPENDPESFI, PESSOAFISICA.DSLOGRAD, PESSOAFISICA.NRLOGRAD, PESSOAFISICA.DSCOMPLEND, 
            PESSOAFISICA.NMBAIRRO, PESSOAFISICA.NMMUNICLOGRAD, PESSOAFISICA.CDUNIDFED, PESSOAFISICA.CDENDPOST, 
            PESSOAFISICA.CDCLASPESFIS,  
            COLETA.CDAMOSTRA, 
            COLETA.CDTRIAGEM, 
            TO_CHAR(COLETA.DTCOLETA, 'DD/MM/YYYY') AS DTCOLETA, 
            PESQUISAREALIZADA.CDPESQUISA, 
            PESQUISA.DSPESQUISA,  
            TESTE.DSTECNICA AS METODO, 
            TIPAGEMDIRETA.CDGRPABO, 
            TIPAGEMRH.CDTIPFATORRH
        FROM PESSOAFISICA, COLETA, TIPAGEMDIRETA, TIPAGEMRH, PESQUISAREALIZADA, PESQUISA, TESTE
        WHERE PESSOAFISICA.CDPESFIS = COLETA.CDPESFISCOLETA
        AND COLETA.CDAMOSTRA = PESQUISAREALIZADA.CDAMOSTRA
        AND PESQUISAREALIZADA.CDPESQUISA = PESQUISA.CDPESQUISA
        AND PESQUISAREALIZADA.CDPESQUISA = TESTE.CDPESQUISA
        AND COLETA.CDAMOSTRA = TIPAGEMDIRETA.CDAMOSTRA
        AND COLETA.CDAMOSTRA = TIPAGEMRH.CDAMOSTRA
        AND TESTE.cdteste IN ('TP-IM')
        AND PESSOAFISICA.CDPESFIS = :pf
        ORDER BY COLETA.DTCOLETA DESC
    ");
    $stmt->bindParam(':pf', $pf);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao executar a query: " . htmlspecialchars($e->getMessage()) . "</div>";
}



// Segunda query dscomplend
try {
    $stmt = $dbconn->prepare("
        SELECT 
            PESSOAFISICA.CDPESFIS, 
            PESSOAFISICA.NMPESFIS, 
            TO_CHAR(PESSOAFISICA.DHNASCTO, 'DD/MM/YYYY') AS DATA_NASCIMENTO, 
            PESSOAFISICA.TPENDPESFI, PESSOAFISICA.DSLOGRAD, PESSOAFISICA.NRLOGRAD, PESSOAFISICA.DSCOMPLEND, 
            PESSOAFISICA.NMBAIRRO, PESSOAFISICA.NMMUNICLOGRAD, PESSOAFISICA.CDUNIDFED, PESSOAFISICA.CDENDPOST, 
            PESSOAFISICA.CDCLASPESFIS, 
            COLETA.CDAMOSTRA, 
            COLETA.CDTRIAGEM, 
            COLETA.DTCOLETA, 
            PESQUISAREALIZADA.CDPESQUISA, 
            PESQUISA.DSPESQUISA,  
            TESTE.DSTECNICA AS METODO, 
            PESQUISAREALIZADA.CDRESULT, 
            TESTEREFERENCIA.DSREFERENCIA, PESQUISAREALIZADA.CDMOTDESCARTE
        FROM PESSOAFISICA, COLETA, PESQUISAREALIZADA, PESQUISA, TESTE, TESTEREFERENCIA
        WHERE PESSOAFISICA.CDPESFIS = COLETA.CDPESFISCOLETA
        AND COLETA.CDAMOSTRA = PESQUISAREALIZADA.CDAMOSTRA
        AND PESQUISAREALIZADA.CDPESQUISA = PESQUISA.CDPESQUISA
        AND PESQUISAREALIZADA.CDPESQUISA = TESTE.CDPESQUISA
        AND TESTE.CDTESTE = TESTEREFERENCIA.CDTESTE
        AND TESTE.cdteste IN ('TP-IM', 'NATHCV', 'NATHIV', 'HCV', 'EH', 'HBC', 'PAI', 'NATHBV', 'VDRL', 'HIVA', 'HEPB', 'HTLVI', 'EIA-C', 'NATMAL')
        AND DSREFERENCIA IN ('<p>N&atilde;o Reagente</p>', '<p>Indetectavel</p>', '<p>Negativo</p>', '<p>Normal</p>')
        AND PESSOAFISICA.CDPESFIS = :pf
        ORDER BY COLETA.DTCOLETA DESC
    ");
    $stmt->bindParam(':pf', $pf);
    $stmt->execute();
    $resultados1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao executar a query: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Combinar os dois resultados
$resultadosCombinados = array_merge($resultados, $resultados1);

// Agrupar por Código da Amostra e Código da Triagem
$dadosAgrupados = [];
foreach ($resultadosCombinados as $row) {
    $chave = $row['cdamostra'] . '-' . $row['cdtriagem'];
    $dadosAgrupados[$chave][] = $row;
}


foreach ($dadosAgrupados as $chave => $grupo) { 
    // Verificar se cdclaspesfis é igual a '03' e algum cdresult for igual a 'P'
    $temResultadoPositivo = false;
    $temResultadoimcompleto = false;
    foreach ($grupo as $item) {
        if (isset($item['cdclaspesfis']) &&  $item['cdclaspesfis'] == '03' && isset($item['cdresult']) && $item['cdresult'] == 'P') {
            $temResultadoPositivo = true;
            break;
        }
    }
    if($temResultadoPositivo){
        break;
    }
    foreach ($grupo as $item) {
        if (isset($item['cdclaspesfis']) &&  $item['cdclaspesfis'] == '03' && isset($item['cdmotdescarte']) &&  $item['cdmotdescarte'] == '01' && isset($item['cdresult']) && $item['cdresult'] == 'S') {
            $temResultadoimcompleto = true;
            break;
        }
    }
    if($temResultadoimcompleto){
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da Pesquisa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <style>
        .collapse-table-row {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading text-white">Menu</div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white">Home</a>
                <!-- <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('home')">Home</a> -->
                <a href="ResultadoE.php" class="list-group-item list-group-item-action bg-dark text-white">Resultado dos Exames</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('about')">Serviço 1</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('services')">Serviço 2</a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="loadPage('Etiqueta')">Etiqueta 3</a>
            </div>
        </div>

        <!-- Page Content -->
         
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom" id="top-navbar">
                <button class="btn btn-primary" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="logout.php" class="btn btn-danger ml-auto"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            
            <div class="container-fluid" id="content">
                <h2 class="mt-4">Resultados dos Exames</h2>

                <!-- Exibir informações Nome, CPF e Data de Nascimento -->
                <div class="mb-4">
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($name); ?></p>
                    <p><strong>CPF:</strong> <?php echo htmlspecialchars($cpf); ?></p>
                    <p><strong>Data de Nascimento:</strong> <?php echo !empty($resultados) ? htmlspecialchars($resultados[0]['data_nascimento']) : ''; ?></p>
                </div>

                <?php if (!empty($dadosAgrupados)) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                        <?php if ((!$temResultadoPositivo) && (!$temResultadoimcompleto) )   { ?>
                            <thead class="thead-dark">
                                <tr>
                                    <th>Código da Amostra</th>
                                    <th>Código da Triagem</th>
                                    <th>Data da Coleta</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <?php }?>
                            <tbody>
                                <?php foreach ($dadosAgrupados as $chave => $grupo) { 
                                            // Verificar se cdclaspesfis é igual a '03' e algum cdresult for igual a 'P'
                                            // $temResultadoPositivo = false;
                                            // foreach ($grupo as $item) {
                                            //     if (isset($item['cdclaspesfis']) && $item['cdclaspesfis'] == '03' && $item['cdresult'] == 'P') {
                                            //         $temResultadoPositivo = true;
                                            //         break;
                                            //     }
                                            // }
                                            
                                            if ((!$temResultadoPositivo) && (!$temResultadoimcompleto) || (isset($grupo[0]['cdresult']) && (!$grupo[0]['cdresult'] == 'S')) ) { ?>                                
                                                <tr class="collapse-table-row" data-toggle="collapse" data-target="#grupo-<?php echo htmlspecialchars($chave); ?>">
                                                    <td><?php echo htmlspecialchars($grupo[0]['cdamostra']); ?></td>
                                                    <td><?php echo htmlspecialchars($grupo[0]['cdtriagem']); ?></td>
                                                    <td><?php echo htmlspecialchars($grupo[0]['dtcoleta']); ?></td>
                                                    <td>
                                                        <?php if ($temResultadoPositivo === false ) { ?>
                                                            <!-- Exibir botão para expandir resultados normais -->
                                                            <button class="btn btn-info">Expandir</button>
                                                            <form action="gerar_pdf_resu.php" method="post" target="_blank" style="display:inline;">
                                                                <input type="hidden" name="dados_grupo" value="<?php echo htmlspecialchars(json_encode($grupo)); ?>">
                                                                <button type="submit" class="btn btn-primary">Gerar PDF</button>
                                                            </form>
                                                            
                                                        <?php }?>
                                        </td>
                                        <?php } ?>
                                        <!-- <td><button class="btn btn-info">Expandir</button>
                                        <form action="gerar_pdf_resu.php" method="post" target="_blank" style="display:inline;">
                                            <input type="hidden" name="dados_grupo" value="<?php echo htmlspecialchars(json_encode($grupo)); ?>">
                                            <button type="submit" class="btn btn-primary">Gerar PDF</button>
                                        </form></td> -->
                                    </tr>
                                    <tr id="grupo-<?php echo htmlspecialchars($chave); ?>" class="collapse">
                                        <td colspan="4">
                                            <?php if ((!$temResultadoPositivo) && (!$temResultadoimcompleto) || (isset($grupo[0]['cdresult']) && (!$grupo[0]['cdresult'] == 'S')) ) { ?>
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Pesquisa</th>
                                                            <th>Método</th>
                                                            <th>Grupo ABO</th>
                                                            <th>Tipo RH</th>
                                                            <th>Resultado</th>
                                                            <th>Referência</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($grupo as $item) { ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($item['dspesquisa']); ?></td>
                                                                <td><?php echo htmlspecialchars($item['metodo']); ?></td>
                                                                <td><?php echo htmlspecialchars($item['cdgrpabo']?? ''); ?></td>
                                                                <td><?php echo htmlspecialchars($item['cdtipfatorrh']?? ''); ?></td>
                                                                <td><?php echo htmlspecialchars($item['cdresult']?? ''); ?></td>
                                                                <td><?php echo htmlspecialchars(html_entity_decode(strip_tags($item['dsreferencia'] ?? ''))); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            <?php } ?>
                                        </td>
                                    </tr>

                                <?php } ?>
                                <?php if ($temResultadoPositivo) { ?>
                                        <!-- Exibir botão para gerar carta PDF em vez dos resultados -->
                                        <div class="alert alert-info">A amostra coletada não permitiu a finalização dos exames, por favor gere o documento para mais informações</div>
                                        <form action="gerar_carta_pdf.php" method="post" target="_blank" style="display:inline;">
                                            <input type="hidden" name="dados_grupo" value="<?php echo htmlspecialchars(json_encode($grupo)); ?>">
                                            <button type="submit" class="btn btn-warning">Gerar Carta</button>
                                        </form>
                                    <?php } ?>
                                    <?php if ($temResultadoimcompleto) { ?>
                                        <!-- Exibir botão para gerar carta PDF em vez dos resultados -->
                                        <div class="alert alert-info">A amostra coletada não permitiu a finalização dos exames, por favor gere o documento para mais informações</div>
                                        <form action="gerar_carta_pdf2.php" method="post" target="_blank" style="display:inline;">
                                            <input type="hidden" name="dados_grupo" value="<?php echo htmlspecialchars(json_encode($grupo)); ?>">
                                            <button type="submit" class="btn btn-warning">Gerar Carta</button>
                                        </form>
                                    <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p>Nenhum resultado encontrado.</p>
                <?php } ?>
            </div>
        </div>
    </div>

 
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rows = document.querySelectorAll('.collapse-table-row');
            rows.forEach(function (row) {
                row.addEventListener('click', function () {
                    var target = this.getAttribute('data-target');
                    var button = this.querySelector('button');
                    var collapse = document.querySelector(target);
                    if (collapse.classList.contains('show')) {
                        button.textContent = 'Expandir';
                    } else {
                        button.textContent = 'Colapsar';
                    }
                });
            });
        });
        $(document).ready(function() {
        $("#sidebar-toggle").on("click", function() {
            $("#sidebar-wrapper").toggleClass("d-none");
        });
    });
    </script>
    
</body>
</html>
