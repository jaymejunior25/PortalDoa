<?php
session_start();
include 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Função para obter os dados (mesma função usada no arquivo principal)
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

// Função para gerar o código ZPL da etiqueta (mesma função usada no arquivo principal)
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

// Lógica principal de impressão
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];

    // Obter os dados da pessoa usando o CPF informado
    $dados = obterDados($cpf);

    if ($dados) {
        // Gerar o código ZPL para a etiqueta
        $zpl = gerarEtiquetaZPL($dados);

        // Imprimir a etiqueta na impressora Zebra conectada via USB
        imprimirEtiquetaUSB($zpl);

        echo "Etiqueta impressa com sucesso!";
    } else {
        echo "Dados não encontrados para o CPF informado.";
    }
}
?>
