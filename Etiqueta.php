    <?php
    session_start();
    include 'db.php';

    // if (!isset($_SESSION['user_id'])) {
    //     header('Location: login.php');
    //     exit();
    // }

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
    function gerarEtiquetaZPL($dados, $cpf) {
        $nome = $dados['nmpesfis'];
        $fenotipo = $dados['dsfenotipagem'];
        $maxCharsPerLine = 37;  // Número máximo de caracteres por linha a depender do tamanho da fonte
        $maxCharsPerLine2 = 45;  // Número máximo de caracteres por linha a depender do tamanho da fonte
        $fontSize = 27;  // Fonte para a primeira linha
        $fontSize2 = 20;  // Fonte para a segunda linha e seguintes

        // Se o nome for muito longo, quebrar em múltiplas linhas
        if (strlen($nome) > $maxCharsPerLine) {
            $nome = wordwrap($nome, $maxCharsPerLine, "&", true);
            $linhasNome = explode("&", $nome);
        } else {
            $linhasNome = [$nome];
        }

        // Tratamento para o fenótipo se ele for muito longo
        if (strlen($fenotipo) > $maxCharsPerLine2) {
            $fenotipo = wordwrap($fenotipo, $maxCharsPerLine2, "&", true);
            $linhasFenotipo = explode("&", $fenotipo);
            if (count($linhasFenotipo) > 1) {
                $maxCharsPerLine2 += 10; // Aumentar limite de caracteres se "Fenotipo" for maior
            }
        } else {
            $linhasFenotipo = [$fenotipo];
        }
        // Ajuste do limite de caracteres por linha para o nome se o fenótipo tiver múltiplas linhas
        if (count($linhasFenotipo) > 1) {
            $maxCharsPerLine2 += 7; // Aumentar limite de caracteres se "Fenotipo" for maior
        }

        $zpl = "^XA";
        $yOffset = 30;  // Posição inicial Y para o texto
        $zpl .= "^FO30,$yOffset^A0N,$fontSize,$fontSize^FD   Nome: " . $linhasNome[0] . "^FS";  // Primeira linha do nome

        // Adiciona linhas adicionais do nome se houver
        for ($i = 1; $i < count($linhasNome); $i++) {
            $yOffset += 40; // Incrementa a posição Y para evitar sobreposição (ajuste conforme necessário)
            $zpl .= "^FO30,$yOffset^A0N,$fontSize,$fontSize^FD" . $linhasNome[$i] . "^FS";
        }

        // Adiciona as outras informações
        $yOffset += 45;  // Adiciona um espaço extra após o nome
        $zpl .= "^FO30,$yOffset^A0N,$fontSize,$fontSize^FD   CPF: " . $cpf . "   Data Nasc: " . $dados['data_nascimento']. "^FS";  // CPF
        // $yOffset += 50;
        // $zpl .= "^FO30,$yOffset^A0N,$fontSize,$fontSize^FD  Data Nasc: " . $dados['data_nascimento'] . "^FS";  // Data de nascimento

        // Adiciona o fenótipo, linha por linha
        $yOffset += 45; // Adiciona um espaço extra antes do fenótipo
        $zpl .= "^FO30,$yOffset^A0N,$fontSize2,$fontSize2^FD   Fenotipo: " . $linhasFenotipo[0] . "^FS";  // Primeira linha do fenótipo

        for ($i = 1; $i < count($linhasFenotipo); $i++) {
            $yOffset += 35;  // Incrementa a posição Y para evitar sobreposição
            $zpl .= "^FO30,$yOffset^A0N,$fontSize2,$fontSize2^FD  " . $linhasFenotipo[$i] . "^FS";
        }

        $zpl .= "^XZ";
        
        return $zpl;
    }

    // Função para enviar o ZPL para a impressora Zebra conectada via USB
    function imprimirEtiquetaUSB($zpl, $user_ip) {
        // URL deve usar o IP do usuário
        $url = "http://{$user_ip}:5000/imprimir";
        $data = ['zpl' => $zpl];
    
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];
    
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
    
        if ($result === FALSE) {
            echo "Erro ao enviar a etiqueta para o servidor local.";
        }
    }
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $dados = null; // Variável para armazenar dados se o CPF for enviado

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cpf = $_POST['cpf'];
    
        // Obter o IP do usuário (isso pode variar dependendo de sua configuração de rede)
        $user_ip = $_SERVER['REMOTE_ADDR'];
    
        if (isset($_POST['imprimir'])) {
            $dados = obterDados($cpf);
            if ($dados) {
                $zpl = gerarEtiquetaZPL($dados, $cpf);
                imprimirEtiquetaUSB($zpl, $user_ip);
                echo "<p>Etiqueta enviada para a impressora.</p>";
            } else {
                echo "<p>Dados não encontrados para o CPF informado.</p>";
            }
        } else {
            // Quando apenas o CPF é enviado
            $dados = obterDados($cpf);
            if (!$dados) {
                echo "<p>Dados não encontrados para o CPF informado.</p>";
            }
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
                <!-- <p>IP: <?= $ip; ?></p>
                <p>IP2: <?= $user_ip; ?></p>  -->
                <button type="submit" class="btn btn-primary">Buscar Informações</button>
            </form>

            <?php if ($dados): ?>
                <h3>Informações do CPF</h3>
                <p>Nome: <?= $dados['nmpesfis']; ?></p>
                <p>CPF: <?= $cpf; ?></p>
                <p>Data de Nascimento: <?= $dados['data_nascimento']; ?></p>
                <p>Fenótipo: <?= $dados['dsfenotipagem']; ?></p>

                <!-- Formulário para confirmar impressão -->
                <form method="POST">
                    <input type="hidden" name="cpf" value="<?= $cpf; ?>">
                    <input type="hidden" name="imprimir" value="true">
                    <button type="submit" class="btn btn-success">Confirmar Impressão da Etiqueta</button>
                </form>
            <?php endif; ?>
        </div>
    </body>
    </html>
