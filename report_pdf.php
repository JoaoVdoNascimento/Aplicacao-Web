<?php
// Inclui a biblioteca FPDF
require('fpdf.php'); // Certifique-se de que fpdf.php está no mesmo diretório ou ajuste o caminho

// Configurações do banco de dados (as mesmas de api.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop_db";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Classe estendida da FPDF para customização do cabeçalho e rodapé (opcional)
class PDF extends FPDF
{
    // Cabeçalho
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, utf8_decode('Relatório de Atendimentos - Pet Shop '), 0, 1, 'C');
        $this->Ln(10); // Quebra de linha
    }

    // Rodapé
    function Footer()
    {
        $this->SetY(-15); // Posição 1.5 cm do final
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Tabela de dados
    function BasicTable($header, $data)
    {
        // Larguras das colunas
        $w = array(40, 30, 35, 30, 20, 20, 15); // Ajuste conforme necessário

        // Cabeçalho
        $this->SetFillColor(230, 230, 230); // Cor de fundo para o cabeçalho
        $this->SetFont('Arial', 'B', 10);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, utf8_decode($header[$i]), 1, 0, 'C', true); // Centralizado, com preenchimento
        }
        $this->Ln(); // Nova linha

        // Dados
        $this->SetFont('Arial', '', 9); // Fonte para os dados
        $this->SetFillColor(255, 255, 255); // Cor de fundo para as linhas de dados (branco)
        $fill = false; // Alternar cor de fundo para linhas

        foreach ($data as $row) {
            // Verifica e formata a data para exibir vazio se for '0000-00-00' ou nula
            $displayDataAtendimento = ''; // Valor padrão: string vazia
            if ($row['dataAtendimento'] != '0000-00-00' && $row['dataAtendimento'] != null) {
                $displayDataAtendimento = utf8_decode(date('d/m/Y', strtotime($row['dataAtendimento'])));
            }

            // Formata a hora para exibir apenas HH:MM
            $displayHoraAtendimento = utf8_decode(substr($row['horaAtendimento'], 0, 5));

            $this->Cell($w[0], 6, utf8_decode($row['cliente_nome']), 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, utf8_decode($row['cliente_nome_pet']), 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, utf8_decode($row['atendente_nome']), 'LR', 0, 'L', $fill);
            $this->Cell($w[3], 6, utf8_decode($row['tipoServico']), 'LR', 0, 'L', $fill);
            $this->Cell($w[4], 6, utf8_decode(number_format($row['valor'], 2, ',', '.')), 'LR', 0, 'R', $fill); // Formato moeda
            $this->Cell($w[5], 6, $displayDataAtendimento, 'LR', 0, 'C', $fill); // Usa a data formatada
            $this->Cell($w[6], 6, $displayHoraAtendimento, 'LR', 0, 'C', $fill); // Usa a hora formatada
            $this->Ln(); // Nova linha
            $fill = !$fill; // Alterna a cor de fundo
        }
        // Linha de fechamento da tabela
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// Cria uma nova instância do PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // Necessário para o número total de páginas no rodapé
$pdf->AddPage(); // Adiciona uma nova página

// Cabeçalhos da tabela
$header = array('Cliente', 'Pet', 'Atendente', 'Serviço', 'Valor (R$)', 'Data', 'Hora');

// Consulta SQL para obter os dados de atendimentos com nomes de cliente e atendente
$sql = "SELECT
            a.id,
            c.nome AS cliente_nome,
            c.nomePet AS cliente_nome_pet,
            t.nome AS atendente_nome,
            a.tipoServico,
            a.valor,
            a.dataAtendimento,
            a.horaAtendimento
        FROM atendimentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN atendentes t ON a.atendente_id = t.id
        ORDER BY a.timestamp_registro DESC"; // Ordena pelos mais recentes

$result = $conn->query($sql);
$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Constrói a tabela no PDF
$pdf->BasicTable($header, $data);

// Define o nome do arquivo para exibição (será o título da aba)
$filename = utf8_decode("Relatório de Atendimentos - Pet Shop Manager.pdf");

// Saída do PDF para o navegador para exibição (inline)
$pdf->Output('I', $filename);

$conn->close();
?>
