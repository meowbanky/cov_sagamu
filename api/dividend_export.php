<?php
require_once('../Connections/cov.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

mysqli_select_db($cov, $database_cov);

$period = intval($_GET['period'] ?? 0);
$percentage = floatval($_GET['percentage'] ?? 0);

if (!$period || !$percentage) {
    die('Period and percentage are required.');
}

// Get dividend data
$query = "
    SELECT 
        m.memberid,
        CONCAT(IFNULL(p.Lname,''), ' ', IFNULL(p.Mname,''), ' ', IFNULL(p.Fname,'')) AS name,
        IFNULL(SUM(m.shares), 0) + IFNULL(SUM(m.savings), 0) AS total_holdings,
        (IFNULL(SUM(m.shares), 0) + IFNULL(SUM(m.savings), 0)) * ? AS dividend,
        a.AccountNo,
        b.bank,
        b.bankcode
    FROM tlb_mastertransaction m
    INNER JOIN tbl_personalinfo p ON p.memberid = m.memberid
    LEFT JOIN tblaccountno a ON a.COOPNO = p.memberid
    LEFT JOIN tblbankcode b ON b.bankcode = a.bank_code
    WHERE m.periodid <= ? AND p.Status = 'Active'
    GROUP BY m.memberid
    HAVING dividend > 0
    ORDER BY name ASC
";

$stmt = $cov->prepare($query);
$stmt->bind_param("di", $percentage, $period);
$stmt->execute();
$result = $stmt->get_result();

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Dividend Report');

// Set headers
$headers = ['S/N', 'Member ID', 'Name', 'Share & Savings', 'Percentage', 'Dividend', 'Account No', 'Bank', 'Bank Code'];
$colIndex = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($colIndex . '1', $header);
    $colIndex++;
}

// Style header row
$headerRange = 'A1:I1';
$sheet->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1E40AF'], // Dark blue
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
]);

// Populate data
$rowIndex = 2;
$sn = 1;
$totalHoldings = 0;
$totalDividend = 0;

while ($row = $result->fetch_assoc()) {
    $dividend = floatval($row['dividend']);
    $holdings = floatval($row['total_holdings']);
    
    $totalHoldings += $holdings;
    $totalDividend += $dividend;
    
    $sheet->setCellValue('A' . $rowIndex, $sn);
    $sheet->setCellValue('B' . $rowIndex, $row['memberid']);
    $sheet->setCellValue('C' . $rowIndex, $row['name']);
    $sheet->setCellValue('D' . $rowIndex, $holdings);
    $sheet->setCellValue('E' . $rowIndex, $percentage);
    $sheet->setCellValue('F' . $rowIndex, $dividend);
    $sheet->setCellValue('G' . $rowIndex, $row['AccountNo'] ?? '');
    $sheet->setCellValue('H' . $rowIndex, $row['bank'] ?? '');
    $sheet->setCellValue('I' . $rowIndex, $row['bankcode'] ?? '');
    
    // Format numbers
    $sheet->getStyle('D' . $rowIndex)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E' . $rowIndex)->getNumberFormat()->setFormatCode('0.0000');
    $sheet->getStyle('F' . $rowIndex)->getNumberFormat()->setFormatCode('#,##0.00');
    
    $rowIndex++;
    $sn++;
}

// Add total row
$totalRow = $rowIndex;
$sheet->setCellValue('A' . $totalRow, '');
$sheet->setCellValue('B' . $totalRow, '');
$sheet->setCellValue('C' . $totalRow, 'TOTAL');
$sheet->setCellValue('D' . $totalRow, $totalHoldings);
$sheet->setCellValue('E' . $totalRow, '');
$sheet->setCellValue('F' . $totalRow, $totalDividend);

// Format total row
$sheet->getStyle('D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('F' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

$totalRowRange = 'A' . $totalRow . ':I' . $totalRow;
$sheet->getStyle($totalRowRange)->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E5E7EB'], // Light gray
    ],
]);

// Apply borders to all cells
$dataRange = 'A1:I' . $totalRow;
$sheet->getStyle($dataRange)->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
]);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(6);  // S/N
$sheet->getColumnDimension('B')->setWidth(12); // Member ID
$sheet->getColumnDimension('C')->setWidth(30); // Name
$sheet->getColumnDimension('D')->setWidth(18); // Share & Savings
$sheet->getColumnDimension('E')->setWidth(12); // Percentage
$sheet->getColumnDimension('F')->setWidth(18); // Dividend
$sheet->getColumnDimension('G')->setWidth(18); // Account No
$sheet->getColumnDimension('H')->setWidth(25); // Bank
$sheet->getColumnDimension('I')->setWidth(12); // Bank Code

// Align columns
$sheet->getStyle('A2:B' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('C2:C' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('D2:F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('G2:I' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Generate filename
$filename = 'Dividend_Report_' . date('Y-m-d_His');

// Write to file
$writer = new Xlsx($spreadsheet);
$tempFileName = tempnam(sys_get_temp_dir(), 'xlsx');
$writer->save($tempFileName);

// Send file to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
header('Cache-Control: max-age=0');

readfile($tempFileName);

// Clean up
unlink($tempFileName);
exit;
?>
