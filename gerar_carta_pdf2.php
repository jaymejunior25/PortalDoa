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
        
      
        $this->Ln(5); // Adiciona um pequeno espaçamento após o cabeçalho
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
    $pdf->Cell(0, 10, utf8_decode('Doador: ' . $_SESSION['pf'] .'       Doação: '. $grupo[0]['cdamostra'] .'     ' . 'Data Doação: ' .$grupo[0]['dtcoleta']), 0,1,'C');

    // Dados principais (incluindo data de nascimento e pf)
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode('Prezado(a) Doador(a)'), 0,1);
    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa
    // $pdf->Cell(0, 8, utf8_decode('CPF: ' . $_SESSION['cpf']), 0, 1,);  // Adicionando o CPF
    $pdf->Cell(0, 8, utf8_decode($grupo[0]['nmpesfis']), 0,1);
    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa
    $pdf->MultiCell(0, 5, utf8_decode($grupo[0]['tpendpesfi'].' '.$grupo[0]['dslograd']. ', '. $grupo[0]['nrlograd'].', '.$grupo[0]['dscomplend'] . ', '.$grupo[0]['nmbairro']. ', '.$grupo[0]['nmmuniclograd']. '- '.$grupo[0]['cdunidfed']. '- '.$grupo[0]['cdendpost']), 0); 
    // $pdf->Cell(0, 8, utf8_decode('Data de Coleta: ' . $grupo[0]['dtcoleta']), 0, 1);
    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa
    // Pega a data atual por extenso em português
    
    //$dataAtual = strftime('%A, %d de %B de %Y', time());
    // Pega a data atual por extenso em português
    setlocale(LC_TIME, 'pt_BR.utf8'); 
    $dataAtual = strftime('%d de %B de %Y', strtotime('today'));
    
    // $pdf->SetFont('Arial', '', 12);
    // $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa
    // Exibe a data e a cidade
    // $pdf->Cell(0, 8, utf8_decode("Belém, $dataAtual"), 0, 1);



    // Importar a classe IntlDateFormatter
    if (!class_exists('IntlDateFormatter')) {
        die('A extensão Intl não está habilitada.');
    }

    // Cria um formatador de data
    $formatter = new IntlDateFormatter(
        'pt_BR', 
        IntlDateFormatter::LONG, 
        IntlDateFormatter::NONE, 
        'America/Sao_Paulo', // Define o fuso horário, ajuste se necessário
        IntlDateFormatter::GREGORIAN, 
        'd \'de\' MMMM \'de\' yyyy' // Formato desejado
    );

    // Formata a data atual
    $dataAtual = $formatter->format(new DateTime());

    // Exibe a data e a cidade
    $pdf->Cell(0, 8, utf8_decode("Belém, $dataAtual"), 0, 1);




    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa
    $texto = "Você compareceu recentemente à Fundação HEMOPA para doar Sangue. Entretanto, a amostra coletada não permitiu a finalização dos exames de triagem sorologica (Sorologia Completa), comforme a legislação vigente do Ministerio da Saude, pois encontrava-se com aspecto gorduroso, provavelmente por interferencia alimentar ";

    $pdf->MultiCell(0, 6, utf8_decode($texto), 0 );

    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa

    $texto2 = "Solicitamos o seu comparecimento na fundação (Anexo I - Recepção de Laboratórios) nos horários de 10:00 às 16:00 horas, de segunda à sexta-feira, com a finalidade de coletarmos uma nova amostra de sangue para possibilitar a liberação de seus exames ";

    $pdf->MultiCell(0, 6, utf8_decode($texto2), 0 );
    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa

    $texto3 = "Aguardamos seu retorno nos proximos 07 (sete) dias uteis após o recebimento desta carta. ";

    $pdf->MultiCell(0, 6, utf8_decode($texto3), 0 );

    $pdf->Ln(15); // Adiciona espaçamento entre cada pesquisa
    

    $pdf->Cell(0, 8, utf8_decode(" Atenciosamente"), 0, 1);

    $pdf->Ln(15); // Adiciona espaçamento entre cada pesquisa

    $pdf->Cell(0, 8, utf8_decode(" Fundação Hemopa"), 0, 1);

    $pdf->Ln(15); // Adiciona espaçamento entre cada pesquisa

    $texto4 = "NOTA IMPORTANTE 1: Favor comparecer munido desta carta e de um documento oficial com foto e assinatura. EXCLUSIVAMENTE na fundação HEMOPA";

    $pdf->MultiCell(0, 5, utf8_decode($texto4), 0 );

    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa

    $pdf->Cell(0, 8, utf8_decode(" Av. Serzedelo Corrêa, 1726 - Bairro Batista Campos"), 0, 1);

    $pdf->Ln(5); // Adiciona espaçamento entre cada pesquisa

    $texto5 = "NOTA IMPORTANTE 2: Caso já tenha comparecido ao Ambulatório de Doadores, para receber o resultado referente a esta doação, desconsidere esta carta";

    $pdf->MultiCell(0, 5, utf8_decode($texto5), 0 );

    $pdf->Ln(15); // Adiciona espaçamento entre cada pesquisa

    // $pdf->Cell(0, 10, 'Obrigado por sua doacao!', 0, 1, 'C');

    $pdf->Output();
}
?>





