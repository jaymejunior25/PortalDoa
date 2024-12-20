<?php 
include 'db.php';

$user = []; // Inicializa a variável para evitar erros se não houver usuário

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
        }
    }
} 
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css"> <!-- Link para o arquivo CSS -->
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <title>Validação de Documento</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8; /* Fundo claro */
            color: #333; /* Texto escuro */
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff; /* Fundo da caixa */
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            color: #4CAF50; /* Verde */
            margin-bottom: 10px;
        }

        h2 {
            color: #388E3C; /* Verde mais escuro */
            margin: 10px 0;
        }

        h3 {
            color: #555; /* Cinza para subtítulos */
            margin: 5px 0;
        }

        .highlight {
            background-color: #B2FFB2; /* Verde claro para destaque */
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .info {
            background-color: #E8F5E9; /* Verde bem claro */
            border-left: 5px solid #4CAF50; /* Borda verde */
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .icon {
            width: 50px; /* Ajuste o tamanho da imagem */
            margin: 10px 0;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777; /* Cinza para o texto do rodapé */
        }

        /* Responsividade */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }

            .icon {
                width: 40px; /* Ajuste para dispositivos menores */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h3>GOVERNO DO ESTADO DO PARÁ</h3>
            <h3>SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA</h3>
            <h3>CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ</h3>
            <h3>TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500</h3>
            <img src="icon2.png" alt="Icone" class="icon">
        </header>
        <main>
            <h2>Documento Oficial</h2>
            <p>Este documento é oficial e válido.</p>
            <h3>Informações Pessoais</h3>
            <div class="info"><strong>Nome:</strong> <?php echo isset($user['nome']) ? htmlspecialchars($user['nome']) : 'Usuário não encontrado.'; ?></div>
            <div class="info"><strong>CPF:</strong> <?php echo isset($user['cpf']) ? htmlspecialchars($user['cpf']) : ''; ?></div>
            <div class="info"><strong>Data de Geração:</strong> 
                <?php 
                if (isset($pdfLog['created_at'])) {
                    $createdAt = new DateTime($pdfLog['created_at']);
                    echo htmlspecialchars($createdAt->format('d-m-Y H:i'));
                 } else {
                    echo 'Data não disponível.';
                }
                ?>
            </div>
        </main>
        <footer>
            <div class="footer-text">
                Desenvolvido com &#128151; por Gerencia de Informatica - GETIN <br>
                <a class="text-reset fw-bold" href="http://www.hemopa.pa.gov.br/site/">© Todos os direitos reservados 2024 Hemopa.</a>
            </div>
        </footer>
    </div>
</body>
</html>
