<?php

// Executa o script Python
exec("python extrair_campanhas.py");

// Lê o JSON gerado
$jsonData = file_get_contents("campanhas.json");
$campaigns = json_decode($jsonData, true);

// Defina a data atual para uma data específica para teste (ex: 30/10/2024)
// $currentDate = new DateTime('2024-10-10'); // Substitua pela data desejada para o teste
$currentDate = new DateTime(); // Substitua pela data desejada para o teste
$closestCampaign = null;
$closestDate = null;

// Encontra a campanha mais próxima da data atual
foreach ($campaigns as $campaign) {
    // Verifica se a chave "Data" existe
    if (isset($campaign['Data'])) {
        // Converte a data para o formato adequado
        // Considerando que a data vem no formato "31/10/2024"
        $dataString = str_replace('/', '-', $campaign['Data']); // Troca '/' por '-'
        
        // Cria um objeto DateTime
        try {
            $campaignDate = new DateTime($dataString); // O DateTime pode aceitar "31-10-2024"
        } catch (Exception $e) {
            // Caso a data não consiga ser convertida, continue para a próxima
            continue;
        }
        
        // Verifica se a campanha está na data futura e é a mais próxima
        if ($campaignDate > $currentDate) {
            if ($closestDate === null || $campaignDate < $closestDate) {
                $closestDate = $campaignDate;
                $closestCampaign = $campaign;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações sobre Doação de Sangue</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <style>
        body { background-color: #f2f9f2; color: #28a745; }
        .info-container { 
            max-width: 1200px; 
            margin: auto; 
            padding: 2rem; 
            border-radius: 0 0 8px 8px ; 
            background-color: #ffffff; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            /* margin-top: 5%;  */
            display: flex; 
            gap: 2rem; 
        }
        .list-container, .highlight-container {
            flex: 1; /* Faz com que ambos os containers ocupem o mesmo espaço */
            min-width: 300px; /* Define uma largura mínima */
        }
        .list-group-item a { color: #28a745; }
        .highlight-container { 
            align-items: center; justify-content: center;
            background-color: #28a745; 
            border-radius: 8px; 
            padding: 1rem; 
            
            color: #ffffff; 
            text-align: center; 
            font-weight: bold; 
        }
        .highlight-container a { color: #28a745; font-size: 1.2rem; text-decoration: none; }
        h2 { color: #28a745; text-align: center; }
        .header-Portal {
            max-width: 1200px;
            background-color: #28a745;
            color: #fff; /* Texto branco */
            text-align: center;
            margin: 0 auto;
            padding: 1rem;
            font-size: 30px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            margin-top: 8%;
        }
       
    </style>
</head>
<body>
    <div class="header-Portal">Bem Vindo ao Portal do Doador <br> Fundação Hemopa</div>
    <div class="info-container">
        <div class="list-container">
            <h2><strong>Informações sobre Doação de Sangue</strong></h2>
            <ul class="list-group mt-3">
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/quem-pode-doar-sangue/" target="_blank">Quem pode doar sangue</a></li>
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/por-que-doar-sangue/" target="_blank">Por que doar sangue</a></li>
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/reacoes/" target="_blank">Possíveis Reações</a></li>
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/tipos-de-sangue/" target="_blank">Tipos de Sangue</a></li>
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/doacao-por-aferese/" target="_blank">Doações por aférese</a></li>
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/curiosidades-do-sangue/" target="_blank">Curiosidades do Sangue</a></li>
                <li class="list-group-item"><a href="https://www.hemopa.pa.gov.br/site/mitos-e-duvidas/" target="_blank">Mitos e Dúvidas</a></li>
            </ul>
        </div>
        
        <div class="highlight-container">
            
            <h3><strong>Próxima Campanha</strong></h3>
            <?php if ($closestCampaign): ?>
                <div>
                    <strong>Nome:</strong> <?php echo htmlspecialchars($closestCampaign['Nome'] ?? 'Não Informado'); ?><br>
                    <strong>Data:</strong> <?php echo htmlspecialchars($closestCampaign['Data'] ?? 'Não Informado'); ?><br>
                    <strong>Local:</strong> <?php echo htmlspecialchars($closestCampaign['Local'] ?? 'Não Informado'); ?><br>
                    <strong>Hora:</strong> <?php echo htmlspecialchars($closestCampaign['Hora'] ?? 'Não Informado'); ?><br>
                    <strong>Previsão de Voluntários:</strong> <?php echo htmlspecialchars($closestCampaign['Previsão de Voluntários'] ?? 'Não Informado'); ?><br>
                    <strong>Infraestrutura:</strong> <?php echo htmlspecialchars($closestCampaign['Infraestrutura'] ?? 'Não Informado'); ?><br>
                </div>
            <?php else: ?>
                <p>Nenhuma campanha futura encontrada.</p>
            <?php endif; ?>
            <!-- Div alterada para ter fundo branco e texto verde -->
            <div style="background-color: #ffffff; color: #28a745; padding: 1rem; border-radius: 5px;">
                <a href="https://www.hemopa.pa.gov.br/site/noticias/acompanhe-nossas-campanhas-externas-marco/" target="_blank">
                    <i class="fas fa-calendar-alt"></i> Para informações das Campanhas de Doações do Mês inteiro clique aqui
                </a>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="login.php" class="btn btn-secondary">Voltar ao Login</a>
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
</body>
</html>
