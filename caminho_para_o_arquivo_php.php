<?php
header('Content-Type: application/json');

// Conectar ao banco de dados
include 'db.php';

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

// Verificar se o CPF foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf'])) {
    $cpf = $_POST['cpf'];
    $dados = obterDados($cpf);
    echo json_encode($dados);
} else {
    echo json_encode(null);
}
?>
