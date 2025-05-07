<?php

//make sure to install the tcpdf package

require('include/tcpdf/tcpdf');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid credit note ID');
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT cn.*, c.name 
                          FROM credit_notes cn 
                          JOIN llx_customers c ON cn.customer_id = c.id 
                          WHERE cn.id = ?");
    $stmt->execute([$id]);
    $credit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$credit) {
        die('Credit note not found');
    }

    $stmt = $pdo->prepare("SELECT * FROM credit_note_items WHERE credit_note_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    class CreditNotePDF extends TCPDF
    {
        protected $credit;

        public function __construct($credit, $orientation = 'P', $unit = 'mm', $format = 'A4')
        {
            parent::__construct($orientation, $unit, $format);
            $this->credit = $credit;
        }

        function Header()
        {
            $this->SetFont('helvetica', 'B', 14);
            $this->Cell(0, 10, 'Credit Note #' . $this->credit['id'], 0, 1, 'C');
            $this->SetFont('helvetica', '', 10);
            $this->Cell(50, 10, 'Ref No: ' . $this->credit['ref_no'], 0, 0, 'L');
            $this->Cell(50, 10, 'Customer: ' . $this->credit['name'], 0, 0, 'L');
            $this->Cell(0, 10, 'Date: ' . date('d/m/Y', strtotime($this->credit['credit_note_date'])), 0, 1, 'L');

            $this->Ln(5);
        }

        function BasicTable($header, $data)
        {
            // Column widths
            $w = array(50, 50, 20, 35, 35);

            // Header
            for ($i = 0; $i < count($header); $i++) {
                $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
            }
            $this->Ln();

            // Data
            foreach ($data as $row) {
                $this->Cell($w[0], 6, $row['product'], 'LR');
                $this->Cell($w[1], 6, $row['description'], 'LR');
                $this->Cell($w[2], 6, $row['quantity'], 'LR', 0, 'R');
                $this->Cell($w[3], 6, number_format($row['unit_price'], 2), 'LR', 0, 'R');
                $this->Cell($w[4], 6, number_format($row['amount'], 2), 'LR', 0, 'R');
                $this->Ln();
            }

            // Closing line
            $this->Cell(array_sum($w), 0, '', 'T');
        }

    }

    $pdf = new CreditNotePDF($credit);
    $pdf->SetMargins(15, 25, 15);
    $pdf->AddPage();

    $header = ['Product', 'Description', 'Qty', 'Unit Price', 'Amount'];
    $pdf->BasicTable($header, $items);

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Total: ' . number_format($credit['total_amount'], 2), 0, 1, 'R');
    $pdf->Cell(0, 10, 'Remaining: ' . number_format($credit['remaining_amount'], 2), 0, 1, 'R');

    $pdf->Output('credit_note_' . $id . '.pdf', 'I');
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
