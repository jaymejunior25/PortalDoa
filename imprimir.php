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
function gerarEtiquetaZPL($dados, $cpf) {
    $zpl = "^XA";
    $zpl .= "^FO30,30^A0N,40,40^FD" . $dados['nmpesfis'] . "^FS";  // Nome
    $zpl .= "^FO30,80^A0N,35,35^FDCPF: " . $cpf . "^FS";  // CPF
    $zpl .= "^FO30,130^A0N,35,35^FDData Nasc: " . $dados['data_nascimento'] . "^FS";  // Data de nascimento
    $zpl .= "^FO30,180^A0N,35,35^FDFenótipo: " . $dados['dsfenotipagem'] . "^FS";  // Fenótipo
    $zpl .= "^XZ";
    
    return $zpl;
}


// Função para enviar o ZPL para a impressora Zebra conectada via USB usando o comando print
function imprimirEtiquetaUSB($zpl) {
    // Cria um arquivo temporário para armazenar o código ZPL
    $tempFile = tempnam(sys_get_temp_dir(), 'zebra_');
    file_put_contents($tempFile, $zpl);
    
    // Comando para imprimir o arquivo usando a impressora Zebra (ajuste 'Zebra ZT410' para o nome da sua impressora)
    $printerName = "ZDesigner ZT410-203dpi ZPL (Copiar 1)";
    $command = 'print /D:"\\\localhost\\' . $printerName . '" ' . $tempFile;
    
    // Executa o comando
    exec($command);
    
    // Remove o arquivo temporário após a impressão
    unlink($tempFile);
}

// Lógica principal de impressão
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cpf = $_POST['cpf'];

    // Obter os dados da pessoa usando o CPF informado
    $dados = obterDados($cpf);

    if ($dados) {
        // Gerar o código ZPL para a etiqueta
        $zpl = gerarEtiquetaZPL($dados, $cpf);

        // Imprimir a etiqueta na impressora Zebra conectada via USB
        imprimirEtiquetaUSB($zpl);

        echo "Etiqueta impressa com sucesso!";
    } else {
        echo "Dados não encontrados para o CPF informado.";
    }
}
?>
