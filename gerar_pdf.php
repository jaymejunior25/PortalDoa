<?php
include 'fpdf.php';
include 'db.php';
class PDF extends FPDF
{
    function Header()
    {
        $this->Image('icon2.png', 10, 6, 16); // Adicionar imagem (ajuste a posição e o tamanho conforme necessário)
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 5, utf8_decode('GOVERNO DO ESTADO DO PARÁ'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('SECRETARIA EXECUTIVA DE SAÚDE PÚBLICA'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('CENTRO DE HEMOTERAPIA E HEMATOLOGIA DO PARÁ'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('TV. PADRE EUTIQUIO, 2109 - Batista Campos TEL: (91) 3110-6500'), 0, 1, 'C');
        $this->Ln(10); // Adiciona um pequeno espaçamento após o cabeçalho
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
    $pdf->SetFont('Arial', 'B', 16);
    //$pdf->Cell(0, 10, 'Portal do Doador', 0, 1, 'C');
    
    // Adicione os detalhes do usuário
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Nome: ' . $name, 0, 1);
    $pdf->Cell(0, 10, 'CPF: ' . $cpf, 0, 1);
    $pdf->Cell(0, 10, 'Data de Nascimento: ' . $nascimento, 0, 1);
    $pdf->Cell(0, 10, 'Tipagem: ' . $tipagem, 0, 1);
    
    $pdf->Ln(15);
    $pdf->Cell(0, 5, utf8_decode('Declaração de doação'), 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->Cell(0, 5, utf8_decode('Declara-se para os devidos fins as doações de sangue realizadas pelo(a) doador(a) citado(a)'), 0, 1, 'L');
    $pdf->Cell(0, 5, utf8_decode('acima na fundação hemopa nas datas respectivas:'), 0, 1, 'L');

    $pdf->Ln(15);
    // Adicione a próxima data de doação, se disponível
    if ($proxima_doacao) {
        $pdf->Cell(0, 10, utf8_decode('Data da próxima doação: Apartir de ') . $proxima_doacao, 0, 1);
    }
    $pdf->Ln(15);

    $pdf->Cell(0, 10, utf8_decode('Total de doações: ' . count($resultados)), 0);

    $pdf->Ln(15);

    // Adicione a tabela de resultados de coleta e doação
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, utf8_decode('Data da Coleta'), 1);
    $pdf->Cell(50, 10, utf8_decode('Tipo de Doação'), 1);
    $pdf->Ln();

    // Verifique se há resultados
    if (!empty($resultados)) {
        $pdf->SetFont('Arial', '', 12);
        foreach ($resultados as $row) {
            $pdf->Cell(50, 10, utf8_decode($row['data_coleta']), 1);
            $tipoDoacao = isset($row['cdtipobtdoacao']) ? $row['cdtipobtdoacao'] : $row['TP_OBTHE'];
            $pdf->Cell(50, 10, utf8_decode($tipoDoacao), 1);
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 10, 'Nenhum resultado encontrado.', 1, 1);
    }

    // Gere o PDF
    $pdf->Output('I', 'PortalDoador.pdf');
}
?>
