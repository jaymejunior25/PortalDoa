<?php
// Configurações de conexão com o banco de dados
$host = '10.95.2.31'; // endereço do servidor PostgreSQL
$dbname = 'sbs_prod'; // nome do banco de dados
$port = "5432";
$user = 'sbsadmin'; // usuário do banco de dados
$password = 'sbs2011'; // senha do banco de dados

try {
    $dbconn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   // echo "Erro: " . $e->getMessage();
    //die();
    error_log("Erro na conexão: " . $e->getMessage(), 3, 'C:\xampp\php\logs\php_error.log');
    die(json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]));
}  
/*$connectionString = "host=$host port=$port dbname=$dbname user=$user password=$password";
$dbconn = pg_connect($connectionString);
// Verifica se a conexão foi bem-sucedida
if (!$dbconn) {
    die("Erro: Não foi possível conectar ao banco de dados.");
}*/

// Configurações de conexão com o banco de dados
$host1 = 'Localhost'; // endereço do servidor PostgreSQL
$dbname1 = 'PortalD'; // nome do banco de dados
$port1 = "5432";
$user1 = 'postgres'; // usuário do banco de dados
$password1 = 'admin'; // senha do banco de dados

try {
    $dbconn2 = new PDO("pgsql:host=$host1;port=$port1;dbname=$dbname1", $user1, $password1);
    $dbconn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   // echo "Erro: " . $e->getMessage();
    //die();
    error_log("Erro na conexão: " . $e->getMessage(), 3, 'C:\xampp\php\logs\php_error.log');
    die(json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]));
}  