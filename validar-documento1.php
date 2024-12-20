<?php
include 'db.php';

if (isset($_GET['hash'])) {
    $hash = $_GET['hash'];

    // Verificar se o hash existe no banco de dados e se ainda é válido
    $stmt = $dbconn2->prepare("SELECT * FROM pdf_logs WHERE pdf_hash = :pdf_hash AND valid_until >= NOW()");
    $stmt->bindParam(':pdf_hash', $hash);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $pdfLog = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Exibir informações pessoais
        $user_id = $pdfLog['user_id'];

        // Buscar informações pessoais do usuário
        $userStmt = $dbconn2->prepare("SELECT * FROM doador WHERE id = :user_id");
        $userStmt->bindParam(':user_id', $user_id);
        $userStmt->execute();
        
        if ($userStmt->rowCount() > 0) {
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        echo '</h1>GOVERNO DO ESTADO DO PARÁ</h1>';
        echo '</h1>SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA</h1>';
        echo '</h1> CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ</h1>';
        echo '</h1>TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500</h1>';
        Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
            echo "<h2>Documento Oficial</h2>";
            echo "<p>Este documento é oficial e válido.</p>";
            echo "<h3>Informações Pessoais</h3>";
            echo "<p><strong>Nome:</strong> " . htmlspecialchars($user['nome']) . "</p>";
            echo "<p><strong>CPF:</strong> " . htmlspecialchars($user['cpf']) . "</p>";
            // echo "<p><strong>Data de Nascimento:</strong> " . htmlspecialchars($user['data_nascimento']) . "</p>";
            // Formatar a data de geração até os minutos
            $created_at = new DateTime($pdfLog['created_at']);
            $formattedDate = $created_at->format('d-m-Y H:i');

            echo "<p><strong>Data de Geração:</strong> " . htmlspecialchars($formattedDate) . "</p>";// Supondo que você tenha uma coluna created_at
        } else {
            echo "<p>Usuário não encontrado.</p>";
        }
    } else {
        echo "<p>Documento não encontrado ou não é oficial.</p>";
    }
} else {
    echo "<p>Hash inválido.</p>";
}
?>