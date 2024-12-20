<?php
session_start();
include 'fpdf.php';
include 'db.php';

require 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader; // Importação da classe StreamReader

class PDF extends FPDF
{
    function Header()
    {
        // $this->Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
        // $this->SetFont('Arial', 'B', 8);
        // $this->Cell(0, 5, utf8_decode('GOVERNO DO ESTADO DO PARÁ'), 0, 1, 'C');
        // $this->Cell(0, 5, utf8_decode('SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA'), 0, 1, 'C');
        // $this->Cell(0, 5, utf8_decode('CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ'), 0, 1, 'C');
        // $this->Cell(0, 5, utf8_decode('TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500'), 0, 1, 'C');
        $this->Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 5, utf8_decode('FUNDAÇÃO HEMOPA'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('DECLARAÇÃO DE DOAÇÃO'), 0, 1, 'C');
        $this->Ln(20); // Adiciona um pequeno espaçamento após o cabeçalho
    }

    function Footer()
    {
        // Pega a data atual por extenso em português
        setlocale(LC_TIME, 'pt_BR.utf8');
        //$dataAtual = strftime('%A, %d de %B de %Y', time());
        $dataAtual = strftime('%d/%m/%Y', time());
        // Posiciona a 1.5 cm do fim da página
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 7);

        // Exibe a data e a cidade
        $this->Cell(0, 3, utf8_decode("Belém, $dataAtual"), 0, 1);

        // Exibe as informações de rodapé
        // $this->SetFont('Arial', '', 8);
        // $textoRodape = "RESULTADO(S) APROVADO(S) E LIBERADO(S) ELETRONICAMENTE:Letícia Nóbrega Guimarães - CRF 1863/PA\n"
        //     . "Patricia Danin Jordao de Sousa - CRF 2045/PALarissa Tatiana V. Martins Frances - CRF 7104\n"
        //     . "LABORATÓRIO REGISTRADO NO CONSELHO REGIONAL DE BIOMEDICINA REG. 165/PJ, RESPONSABILIDADE TÉCNICA\n"
        //     . "DR. Mauricio Koury Palmeira - CRBM4: 220";

        // $this->MultiCell(0, 3, utf8_decode($textoRodape), 0 );

        // Exibe o número da página
         
         $this->Cell(0, 7, utf8_decode('GETRD-RGT-XXX-DECLARAÇÂO DE DOAÇÂO. REV. 0 '), 0, 0);
         $this->Cell(0, 7, utf8_decode('Página ' . $this->PageNo()), 0, 0, 'R');
    }

}
// Verifique se a requisição POST foi enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupere os dados enviados pelo formulário
    $name = $_POST['name'];
    $cpf = $_POST['cpf'];
    $nascimento = $_POST['nascimento'];
    $sexo = $_POST['sexo'];
    $resultados = json_decode($_POST['resultados'], true);
    $proxima_doacao = $_POST['proxima_doacao'] ?? null;
    $tipagem = $_POST['tipagem'];

    // Crie uma nova instância do FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();

    
    $pdf->AddPage();

    // Defina o título do PDF
    $pdf->SetFont('Arial', 'B', 10);
    //$pdf->Cell(0, 10, 'Portal do Doador', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B',10); // Define a fonte como negrito
    $pdf->Cell(15, 10, 'NOME: ', 0, 0); // Imprime 'Nome: ' em negrito
    $pdf->SetFont('Arial', '',10); // Restaura a fonte para normal
    $pdf->Cell(0, 10, $name, 0, 1); // Imprime o nome normalmente
    $pdf->SetFont('Arial', 'B',10); 
    $pdf->Cell(15, 10, 'CPF: ', 0, 0); 
    $pdf->SetFont('Arial', '',10); 
    $pdf->Cell(0, 10, $cpf, 0, 1);
    $pdf->SetFont('Arial', 'B',10); 
    $pdf->Cell(45, 10, 'DATA DE NASCIMENTO: ', 0, 0); 
    $pdf->SetFont('Arial', '',10); 
    $pdf->Cell(0, 10, $nascimento, 0, 1);
    // $pdf->Cell(0, 10, 'Data de Nascimento: ' . $nascimento, 0, 1);
    // $pdf->Cell(0, 10, 'Tipagem: ' . $tipagem, 0, 1);
    
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'B',12); 
    $pdf->Cell(0, 5, utf8_decode('DECLARAÇÃO DE DOAÇÃO'), 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '',10); 
    $pdf->Cell(0, 5, utf8_decode('Declara-se, para os devidos fins, as doações de sangue realizadas pelo(a) doador(a) citado(a)'), 0, 1, 'L');
    $pdf->Cell(0, 5, utf8_decode('acima na Fundação Hemopa nas respectivas datas:'), 0, 1, 'L');

    $pdf->Ln(15);

    $pdf->Cell(0, 10, utf8_decode('Total de doações: ' . count($resultados)), 0);

    $pdf->Ln(15);

    // Adicione a tabela de resultados de coleta e doação
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 10, utf8_decode('Data da Coleta'), 1);
    $pdf->Cell(50, 10, utf8_decode('Tipo de Doação'), 1);
    $pdf->Ln();

    // Verifique se há resultados
    if (!empty($resultados)) {
        $pdf->SetFont('Arial', '', 10);
        foreach ($resultados as $row) {
            $pdf->Cell(50, 10, utf8_decode($row['data_coleta']), 1);
            // $tipoDoacao = isset($row['cdtipobtdoacao']) ? $row['cdtipobtdoacao'] : $row['TP_OBTHE'];
            // $pdf->Cell(50, 10, utf8_decode($tipoDoacao), 1);
            // Captura o valor da coluna 'cdtipobtdoacao' ou 'TP_OBTHE'
            $tipoDoacao = isset($row['cdtipobtdoacao']) ? $row['cdtipobtdoacao'] : $row['TP_OBTHE'];

            // Verifica o valor e exibe o texto correspondente
            if ($tipoDoacao === 'FR') {
                $pdf->Cell(50, 10, utf8_decode('Convencional'), 1);
            } elseif ($tipoDoacao === 'AF') {
                $pdf->Cell(50, 10, utf8_decode('Aférese'), 1);
            } else {
                // Se não for nenhum dos dois, exibe o valor original (ou algo padrão)
                $pdf->Cell(50, 10, utf8_decode($tipoDoacao), 1);
            }
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 10, 'Nenhum resultado encontrado.', 1, 1);
    }
    $pdf->Ln(10);
        // Adicione a próxima data de doação, se disponível
        if ($proxima_doacao) {
            $pdf->Cell(0, 10, utf8_decode('Data da próxima doação: A partir de ') . $proxima_doacao, 0, 1);
        }
        $pdf->Ln(15);

    // Geração do conteúdo do PDF
        $pdf_content = $pdf->Output('S'); // 'S' retorna o conteúdo como string

        // Calcular hash do PDF
        $pdf_hash = hash('sha256', $pdf_content);


        // Calcular a data de validade (6 meses a partir da data atual)
        $valid_until = (new DateTime())->modify('+6 months')->format('Y-m-d H:i:s');

        // Salvar o hash no banco de dados
        try {
            $stmt = $dbconn2->prepare("INSERT INTO pdf_logs (user_id, pdf_hash, created_at, valid_until) VALUES (:user_id, :pdf_hash, NOW(), :valid_until)");            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':pdf_hash' => $pdf_hash,
                ':valid_until' => $valid_until
            ]);
        } catch (PDOException $e) {
            echo "Erro ao salvar o hash no banco: " . $e->getMessage();
        }
        
        // Geração do QR Code com URL de validação
        $url_validacao = "http://10.95.2.134/portalD/validar-documento.php?hash=" . $pdf_hash;
        
        // Criar o objeto QR Code
        $qrCode = new QrCode($url_validacao);
        $qrCode->setSize(150); // Tamanho do QR Code
        
        // Salvar o QR Code como imagem PNG
        $writer = new PngWriter();
        $writer->write($qrCode)->saveToFile('qrcode.png');
        
        // 5. Criar um novo PDF para combinar o conteúdo original e o QR Code usando FPDI
        $pdf_final = new Fpdi();
        $pageCount = $pdf_final->setSourceFile(StreamReader::createByString($pdf_content)); // Carregar o PDF original

    // Importa todas as páginas do PDF original
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf_final->AddPage();
        $tplIdx = $pdf_final->importPage($pageNo);
        $pdf_final->useTemplate($tplIdx);

        // Adicionar o QR Code apenas na última página
        if ($pageNo == $pageCount) {
            // Definir a posição para o texto à esquerda do QR Code
            $pdf_final->SetXY(20, 245); // Ajuste a coordenada X (mais à esquerda) e Y conforme necessário
            $pdf_final->SetFont('Arial', '', 10); // Defina a fonte e o tamanho
            $texto = "Esse documento é assinado eletrônicamente pela Fundação Hemopa.\n"
            . "e validado pelo Qrcode";

            // Escreva o texto de múltiplas linhas usando MultiCell
            $pdf_final->MultiCell(0, 8, utf8_decode($texto)); // Ajuste a largura e altura da célula conforme necessário
            $pdf_final->Image('qrcode.png', 140, 230, 50, 50);
        }
    }
        // 8. Exibir o PDF final no navegador
        $pdf_final->Output('D', $name.' declaracao_doacoes.pdf');
    }
?>
