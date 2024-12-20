<?php
session_start();
include 'db.php';

$error = '';
$max_attempts = 5;  // Máximo de tentativas de login permitidas
$lockout_time = 900;  // Tempo de bloqueio em segundos (15 minutos)

// Função para validar CPF (simples, pode ser aprimorada)
function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return strlen($cpf) == 11; // Valida se o CPF tem 11 dígitos
}

// Função para validar RG (simplificada, pode variar conforme o estado)
function validaRG($rg) {
    $rg = preg_replace('/[^0-9]/', '', $rg);
    return strlen($rg) >= 7 && strlen($rg) <= 12; // Exemplo de validação
}

// Função para validar RGCPF (ou seja, qualquer um dos dois)
function validaRGCPF($documento) {
    return validaCPF($documento) || validaRG($documento); 
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Verificar se o usuário está bloqueado
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $max_attempts) {
        $last_attempt_time = $_SESSION['last_attempt_time'];
        $time_since_last_attempt = time() - $last_attempt_time;

        if ($time_since_last_attempt < $lockout_time) {
            $remaining_time = $lockout_time - $time_since_last_attempt;
            $_SESSION['error_message'] = "Muitas tentativas de login falhadas. Tente novamente em " . round($remaining_time / 60) . " minutos.";
            header('Location: login.php');
            exit();
        } else {
            // Resetar tentativas após o período de bloqueio
            $_SESSION['login_attempts'] = 0;
        }
    }
    $tipo_documento = $_POST['tipo_documento'];
    $documento = $_POST['documento'];
    $senha = $_POST['senha'];

    // Validação de acordo com o tipo de documento
    if ($tipo_documento == 'CPF' && !validaCPF($documento)) {
        $_SESSION['error_message'] = 'CPF inválido!';
        header('Location: login.php');
        exit();
    } elseif ($tipo_documento == 'RG' && !validaRG($documento)) {
        $_SESSION['error_message'] = 'RG inválido!';
        header('Location: login.php');
        exit();
    } elseif ($tipo_documento == 'RGCPF' && !validaRGCPF($documento)) {
        $_SESSION['error_message'] = 'Documento inválido!';
        header('Location: login.php');
        exit();
    }


    try {
    // Consulta para verificar se o CPF e senha estão corretos
    // Adapta a consulta de acordo com o tipo de documento
    // if ($tipo_documento == 'CPF') {
    //     $stmt = $dbconn2->prepare('SELECT * FROM doador WHERE cpf = :documento');
    // } elseif ($tipo_documento == 'RG') {
    //     $stmt = $dbconn2->prepare('SELECT * FROM doador WHERE rg = :documento');
    // } elseif ($tipo_documento == 'RGCPF') {
    //     $stmt = $dbconn2->prepare('SELECT * FROM doador WHERE cpf = :documento OR rg = :documento');
    // }
        // Consulta para verificar se o CPF e senha estão corretos
        $stmt = $dbconn2->prepare('SELECT * FROM doador WHERE cpf = :cpf');
        $stmt->execute([':cpf' => $documento]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    


    $sql = "SELECT pessoafisica.cdpesfis, 
                   nmpesfis, 
                   TO_CHAR(DHNASCTO, 'DD/MM/YYYY') AS data_nascimento, 
                   tpdoctoident, 
                   nrdoctoident, 
                   cdsexo,
                   cdgrpabo,
                   cdfatorrh,
                   dsfenotipagem,
                   cdclaspesfis
            FROM pessoafisica 
            JOIN doctopessoafisica ON pessoafisica.cdpesfis = doctopessoafisica.cdpesfis
            WHERE tpdoctoident = :tipo
            AND nrdoctoident = :numero";
    
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([':tipo' => $tipo_documento, ':numero' => $documento]);
    $user1 = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user && password_verify($senha, $user['senha'])) {
        // Login bem-sucedido, resetar tentativas
        $_SESSION['login_attempts'] = 0;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nome'];
        $_SESSION['cpf'] = $user['cpf'];
        $_SESSION['pf'] = $user1['cdpesfis'];
        $_SESSION['nasc'] = $user1['data_nascimento'];
        $_SESSION['sexo'] = $user1['cdsexo'];
        $_SESSION['abo'] = $user1['cdgrpabo'];
        $_SESSION['rh'] = $user1['cdfatorrh'];
        $_SESSION['situacao'] = $user1['cdclaspesfis'];
        
        header('Location: index.php');
        exit();
    } else {
            // Incrementar o contador de tentativas de login
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }

            $_SESSION['login_attempts'] += 1;
            $_SESSION['last_attempt_time'] = time();

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $_SESSION['error_message'] = 'Muitas tentativas de login falhadas. Tente novamente em 15 minutos.';
            } else {
                $_SESSION['error_message'] = 'CPF ou senha inválidos! Tentativas restantes: ' . ($max_attempts - $_SESSION['login_attempts']);
            }

            header('Location: login.php');
            exit();
    }
    } catch (PDOException $e) {
        // Evitar mostrar erros específicos ao usuário
        $_SESSION['error_message'] = 'Erro ao realizar login. Tente novamente 1.';
        // Logar o erro internamente (opcional)
        error_log('Erro de login: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="icon2.png" sizes="32x32" />
    <style>
        body {
            background-color: #f2f9f2; /* Fundo verde claro */
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            padding: 2rem;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* margin-top: 10%; */
        }
        .btn-custom {
            background-color: #28a745;
            color: white;
        }
        .btn-custom:hover {
            background-color: #218838;
        }
        .header-Portal {
            max-width: 400px;
            background-color: #28a745;
            color: #fff; /* Texto branco */
            text-align: center;
            margin: 0 auto;
            padding: 1rem;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            margin-top: 8%;
        }
        
    </style>
</head>
<body>
    <div class="header-Portal">Bem Vindo ao Portal do Doador <br> Fundação Hemopa</div>

    <div class="login-container">
        <h2 class="text-center mb-4" style="color: #28a745;">Login</h2>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
        <div class="form-group">
            <label for="documento" style="color: #28a745;">Tipo de Documento</label>
            <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                <option value="CPF">CPF</option>
                <option value="RG">RG</option>
                <option value="RGCPF">RGCPF</option>
            </select>
        </div>

        <div class="form-group">
            <label for="documento" style="color: #28a745;">Número do Documento</label>
            <input type="text" name="documento" id="documento" class="form-control" required>
        </div>
            <div class="form-group">
                <label for="senha" style="color: #28a745;">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-door-open"></i>Entrar</button>
        </form>
        <div class="text-center mt-3">
            <a href="register.php">Primeiro acesso? Cadastre sua senha aqui</a>
        </div>
        <!-- Novo botão para redirecionar para a página de informações -->
        <div class="text-center mt-3">
            <a href="informacoes.php" class="btn btn-secondary btn-block">Não sou doador</a>
        </div>
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
