<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Função para obter os dados
function obterDados($cpf) {
    global $dbconn;
    $sql = "SELECT pessoafisica.cdpesfis, 
                   nmpesfis, 
                   TO_CHAR(DHNASCTO, 'DD/MM/YYYY') AS data_nascimento, 
                   tpdoctoident, 
                   nrdoctoident, 
                   dsfenotipagem 
            FROM pessoafisica 
            JOIN doctopessoafisica ON pessoafisica.cdpesfis = doctopessoafisica.cdpesfis
            WHERE tpdoctoident = 'CPF' 
            AND nrdoctoident = :CPF";
    
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([':CPF' => $cpf]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para gerar o código ZPL da etiqueta
function gerarEtiquetaZPL($dados) {
    $zpl = "^XA";
    $zpl .= "^FO30,30^A0N,40,40^FD" . $dados['nmpesfis'] . "^FS";  // Nome em posição mais alta e maior
    $zpl .= "^FO30,100^A0N,35,35^FDData Nasc: " . $dados['data_nascimento'] . "^FS";  // Data de nascimento
    $zpl .= "^FO30,170^A0N,35,35^FDFenótipo: " . $dados['dsfenotipagem'] . "^FS";  // Fenótipo
    $zpl .= "^XZ";
    
    return $zpl;
}


// Função para enviar o ZPL para a impressora Zebra conectada via USB
function imprimirEtiquetaUSB($zpl) {
    $file = "LPT1";  // Porta padrão de uma impressora USB no Windows

    // Enviar o ZPL diretamente para a impressora
    file_put_contents($file, $zpl);
}

// Lógica principal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];
    $dados = obterDados($cpf);

    if ($dados) {
        // Exibe as informações na tela antes de imprimir
        echo "<h3>Informações do CPF</h3>";
        echo "Nome: " . $dados['nmpesfis'] . "<br>";
        echo "Data de Nascimento: " . $dados['data_nascimento'] . "<br>";
        echo "Fenótipo: " . $dados['dsfenotipagem'] . "<br>";

        // Formulário para confirmar impressão
        echo '<form method="POST" action="imprimir.php">';
        echo '<input type="hidden" name="cpf" value="' . $cpf . '">';
        echo '<button type="submit">Confirmar Impressão da Etiqueta</button>';
        echo '</form>';
    } else {
        echo "Dados não encontrados para o CPF informado.";
    }
}
?>

<!-- Formulário HTML para informar o CPF -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Etiqueta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
</head>
<body>
    <div class="container">
        <h1>Impressão de Etiqueta</h1>
        <form method="POST">
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" class="form-control" id="cpf" name="cpf" required>
            </div>
            <button type="submit" class="btn btn-primary">Buscar Informações</button>
        </form>
    </div>
</body>
</html>
