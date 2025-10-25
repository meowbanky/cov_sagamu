<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['html'])) {
    $tableHtml = $_POST['html'];
    $filename = $_POST['filename'] ?? 'MasterTransaction';

    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Master Transaction');

    // Convert HTML Table to DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($tableHtml, 'HTML-ENTITIES', 'UTF-8'));

    // Extract Table Rows
    $rows = $dom->getElementsByTagName('tr');

    // Iterate Over Rows and Cells
    $rowIndex = 1;
    $isHeaderRow = true;
    $lastColumn = 'A';
    
    foreach ($rows as $row) {
        $colIndex = 'B'; // Start from column B (skip column A for Select checkbox)
        $cells = $row->getElementsByTagName('td');
        
        if ($cells->length == 0) {
            $cells = $row->getElementsByTagName('th');
        }
        
        foreach ($cells as $cellIdx => $cell) {
            // Skip first column (Select checkbox)
            if ($cellIdx == 0) continue;
            
            $value = trim($cell->nodeValue);
            
            // Clean up value - remove extra spaces and format numbers
            $value = preg_replace('/\s+/', ' ', $value);
            
            // Check if value is numeric (with commas)
            if (preg_match('/^[\d,]+\.?\d*$/', str_replace(',', '', $value))) {
                // It's a number, store as numeric value
                $numericValue = (float)str_replace(',', '', $value);
                $sheet->setCellValue($colIndex . $rowIndex, $numericValue);
                
                // Format as number with 2 decimals and thousands separator
                if (!$isHeaderRow) {
                    $sheet->getStyle($colIndex . $rowIndex)->getNumberFormat()
                          ->setFormatCode('#,##0.00');
                }
            } else {
                // It's text
                $sheet->setCellValue($colIndex . $rowIndex, $value);
            }
            
            $lastColumn = $colIndex;
            $colIndex++;
        }
        
        $isHeaderRow = false;
        $rowIndex++;
    }
    
    $lastRow = $rowIndex - 1;
    
    // ===== FORMATTING =====
    
    // 1. Header Row Styling (Row 1)
    $headerRange = 'B1:' . $lastColumn . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1E40AF'], // Dark green/blue
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);
    
    // 2. Apply borders to all cells
    $dataRange = 'B1:' . $lastColumn . $lastRow;
    $sheet->getStyle($dataRange)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);
    
    // 3. Align text columns (left) and number columns (right)
    $textColumns = ['B', 'C', 'D']; // Coop No, Period, Name
    foreach ($textColumns as $col) {
        $sheet->getStyle($col . '2:' . $col . $lastRow)
              ->getAlignment()
              ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
    
    // 4. Right-align all number columns
    for ($col = 'E'; $col <= $lastColumn; $col++) {
        $sheet->getStyle($col . '2:' . $col . $lastRow)
              ->getAlignment()
              ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
    
    // 5. Format total row (last row) - bold and gray background
    $totalRowRange = 'B' . $lastRow . ':' . $lastColumn . $lastRow;
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
    
    // 6. Auto-size columns with minimum width
    foreach (range('B', $lastColumn) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // After auto-size, set minimum widths
    $sheet->getColumnDimension('B')->setWidth(10);  // Coop No
    $sheet->getColumnDimension('C')->setWidth(15);  // Period
    $sheet->getColumnDimension('D')->setWidth(30);  // Name
    
    // ===== PAGE SETUP FOR PRINTING =====
    
    // 7. Page Setup - A4 Landscape
    $sheet->getPageSetup()
          ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
          ->setPaperSize(PageSetup::PAPERSIZE_A4)
          ->setFitToWidth(1)   // Fit to 1 page wide
          ->setFitToHeight(0); // As many pages tall as needed
    
    // 8. Set print area
    $sheet->getPageSetup()->setPrintArea($dataRange);
    
    // 9. Repeat header row on every page
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
    
    // 10. Set margins (in inches)
    $sheet->getPageMargins()
          ->setTop(0.75)
          ->setRight(0.25)
          ->setLeft(0.25)
          ->setBottom(0.75)
          ->setHeader(0.3)
          ->setFooter(0.3);
    
    // 11. Center on page horizontally
    $sheet->getPageSetup()->setHorizontalCentered(true);
    
    // 12. Set header and footer
    $sheet->getHeaderFooter()
          ->setOddHeader('&C&B' . $filename)
          ->setOddFooter('&LPrinted: &D &T&RPage &P of &N');
    
    // 13. Show gridlines for printing
    $sheet->setShowGridlines(false);
    $sheet->setPrintGridlines(false);
    
    // Write the spreadsheet to a file
    $writer = new Xlsx($spreadsheet);
    $tempFileName = tempnam(sys_get_temp_dir(), 'xlsx');
    $writer->save($tempFileName);
    
    // Send file to browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    readfile($tempFileName);
    
    // Remove the temporary file
    unlink($tempFileName);
    
    exit;
}
?>

