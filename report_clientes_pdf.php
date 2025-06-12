<?php
require('fpdf.php'); // Certifique-se de que o caminho para a pasta fpdf está correto

class PDF extends FPDF
{
    // Cabeçalho
    function Header()
    {
        // Logo (opcional, se você tiver uma imagem de logo)
        // $this->Image('logo.png',10,6,30);
        
        // Fonte do cabeçalho
        $this->SetFont('Arial','B',15);
        // Move para a direita
        $this->Cell(80);
        // Título
        $this->Cell(30,10,'Relatorio de Clientes Cadastrados',0,0,'C');
        // Quebra de linha
        $this->Ln(20);
    }

    // Rodapé
    function Footer()
    {
        // Posição a 1.5 cm do final
        $this->SetY(-15);
        // Fonte do rodapé
        $this->SetFont('Arial','I',8);
        // Número da página
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cria a instância do PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Cabeçalhos da tabela
$pdf->SetFont('Arial','B',10);
$pdf->Cell(15, 10, 'ID', 1);
$pdf->Cell(50, 10, 'Nome', 1);
$pdf->Cell(30, 10, 'Telefone', 1);
$pdf->Cell(55, 10, 'Email', 1);
$pdf->Cell(20, 10, 'Pet', 1);
$pdf->Cell(20, 10, 'Tipo', 1);
$pdf->Ln();

// Busca os dados dos clientes
$sql = "SELECT id, nome, telefone, email, nomePet, tipoPet FROM clientes ORDER BY nome ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $pdf->SetFont('Arial','',8);
    while($row = $result->fetch_assoc()) {
        // Converte os dados para UTF-8, se necessário, para FPDF
        $nome = mb_convert_encoding($row['nome'], 'ISO-8859-1', 'UTF-8');
        $nomePet = mb_convert_encoding($row['nomePet'], 'ISO-8859-1', 'UTF-8');
        
        $pdf->Cell(15, 10, $row['id'], 1);
        $pdf->Cell(50, 10, $nome, 1);
        $pdf->Cell(30, 10, $row['telefone'], 1);
        $pdf->Cell(55, 10, $row['email'], 1);
        $pdf->Cell(20, 10, $nomePet, 1);
        $pdf->Cell(20, 10, $row['tipoPet'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0,10,'Nenhum cliente encontrado.',1,1,'C');
}

$conn->close();

// Saída do PDF no navegador
$pdf->Output('I', 'relatorio_clientes.pdf');
?>