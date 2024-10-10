<?php
session_start();
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
        $this->Cell(0, 5, utf8_decode('AGRADECEMOS SUA DOAÇÃO, CONTINUE EXERCENDO SUA CIDADANIA'), 0, 1, 'C');
        if (!($this->PageNo() == 1)) { $this->Line(8, $this->GetY(), 200, $this->GetY());} // Linha horizontal na largura da página
        $this->Ln(5); // Adiciona um pequeno espaçamento após o cabeçalho
    }

    function Footer()
    {
        // Pega a data atual por extenso em português
        setlocale(LC_TIME, 'pt_BR.utf8');
        //$dataAtual = strftime('%A, %d de %B de %Y', time());
        $dataAtual = strftime('%d/%m/%Y', time());
        // Posiciona a 1.5 cm do fim da página
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);

        // Exibe a data e a cidade
        $this->Cell(0, 3, utf8_decode("Belém, $dataAtual"), 0, 1);

        // Exibe as informações de rodapé
        $this->SetFont('Arial', '', 8);
        $textoRodape = "RESULTADO(S) APROVADO(S) E LIBERADO(S) ELETRONICAMENTE:Letícia Nóbrega Guimarães - CRF 1863/PA\n"
            . "Patricia Danin Jordao de Sousa - CRF 2045/PALarissa Tatiana V. Martins Frances - CRF 7104\n"
            . "LABORATÓRIO REGISTRADO NO CONSELHO REGIONAL DE BIOMEDICINA REG. 165/PJ, RESPONSABILIDADE TÉCNICA\n"
            . "DR. Mauricio Koury Palmeira - CRBM4: 220";

        $this->MultiCell(0, 3, utf8_decode($textoRodape), 0 );

        // Exibe o número da página
        // $this->SetY(-10);
        // $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }

}

if (isset($_POST['dados_grupo'])) {
    $grupo = json_decode($_POST['dados_grupo'], true);

    // Criação do PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Cabeçalho personalizado
    // $pdf->Header();

    // Título do documento
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Resultado Doador', 0, 1, 'C');

    // Dados principais (incluindo data de nascimento e pf)
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, utf8_decode('Doador: ' . $_SESSION['pf'] .'    '. $grupo[0]['nmpesfis'] .'     ' . 'CPF: ' . $_SESSION['cpf']), 0,1,'C');
    // $pdf->Cell(0, 8, utf8_decode('CPF: ' . $_SESSION['cpf']), 0, 1,);  // Adicionando o CPF
    $pdf->Cell(0, 8, utf8_decode('Data de Coleta: ' . $grupo[0]['dtcoleta'].'     ' .  'Data de Nascimento: ' . $grupo[0]['data_nascimento']), 0,1,'C');
    // $pdf->Cell(0, 8, utf8_decode('Data de Nascimento: ' . $grupo[0]['data_nascimento']), 0, 1,'C'); // Adicionando a data de nascimento
    // $pdf->Cell(0, 8, utf8_decode('Código PF: ' . $_SESSION['pf']), 0, 1);  // Adicionando o código PF
    // $pdf->Cell(60, 8, utf8_decode('Amostra: ' . $grupo[0]['cdamostra']), 0,0,'C');
    $pdf->Cell(0, 8, utf8_decode('Amostra: ' . $grupo[0]['cdamostra'] .'      '.'Triagem: ' . $grupo[0]['cdtriagem']), 0, 1,'C');
    // $pdf->Cell(0, 8, utf8_decode('Data de Coleta: ' . $grupo[0]['dtcoleta']), 0, 1);

    // // Cabeçalho da tabela
    // $pdf->Ln(10);
    // $pdf->SetFont('Arial', 'B', 12);
    // $pdf->Cell(40, 10,  utf8_decode('Pesquisa'), 1);
    // $pdf->Cell(40, 10,  utf8_decode('Método'), 1);
    // $pdf->Cell(40, 10,  utf8_decode('Grupo ABO'), 1);
    // $pdf->Cell(40, 10,  utf8_decode('Tipo RH'), 1);
    // $pdf->Cell(40, 10,  utf8_decode('Resultado'), 1);
    // $pdf->Ln();

    // // Dados da tabela
    // $pdf->SetFont('Arial', '', 12);
    // foreach ($grupo as $item) {
    //     $pdf->Cell(40, 10,  utf8_decode($item['dspesquisa']), 1);
    //     $pdf->Cell(40, 10,  utf8_decode($item['metodo']), 1);
    //     $pdf->Cell(40, 10,  utf8_decode($item['cdgrpabo'] ?? ''), 1);
    //     $pdf->Cell(40, 10,  utf8_decode($item['cdtipfatorrh'] ?? ''), 1);
    //     $pdf->Cell(40, 10,  utf8_decode($item['cdresult'] ?? ''), 1);
    //     $pdf->Ln();
    // }

    foreach ($grupo as $item) {

        // $pdf->Ln(4); // Adiciona espaçamento entre cada pesquisa


        // Desenha uma linha horizontal antes do conteúdo
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Linha horizontal na largura da página
        $pdf->Ln(4.5); // Adiciona espaçamento entre cada pesquisa

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, utf8_decode('Pesquisa: ' . $item['dspesquisa']), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 5, utf8_decode('Método: ' . $item['metodo']), 0, 1);

        // Verifica o valor de cdtipfatorrh e ajusta o texto conforme "P" ou "N"
        $fatorRh = '';
        if (isset($item['cdtipfatorrh'])) {
            if ($item['cdtipfatorrh'] === 'P') {
                $fatorRh = 'Positivo';
            } elseif ($item['cdtipfatorrh'] === 'N') {
                $fatorRh = 'Negativo';
            }else {
                $fatorRh = '';
            }
        }
        

        $resultado = 'Resultado: ' .  ($item['cdgrpabo'] ?? '') . ' ' . $fatorRh . ' ' . html_entity_decode(strip_tags($item['dsreferencia'] ?? '')). '         Valor de Referencia: ' . html_entity_decode(strip_tags($item['dsreferencia'] ?? ''));
        $pdf->Cell(0, 5, utf8_decode($resultado), 0, 1);
        $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa
        
    }
            // Desenha uma linha horizontal antes do conteúdo
            $pdf->Line(8, $pdf->GetY(), 200, $pdf->GetY()); // Linha horizontal na largura da página
    $pdf->Ln();
    $textoOBS = "OBS: OS TESTES SOROLÓGICOS REALIZADOS SÃO DE TRIAGEM, SUJEITOS A COMFIRMAÇÃO, COMFORME A " 
    ."LEGISLAÇÃO VIGENTE DO MINISTÉRIO DA SAÚDE.";

    $pdf->MultiCell(0, 6, utf8_decode($textoOBS), 0 );
    // Rodapé do documento
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Obrigado por sua doacao!', 0, 1, 'C');

    $pdf->Output();
}
?>
