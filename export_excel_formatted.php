<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $colIndex = 'A'; // Start from column A now (no empty column)
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
            
            // Shorten month names in Period column (column B after skipping checkbox)
            if ($colIndex == 'B' && !$isHeaderRow) {
                // Convert "September - 2025" to "Sep-2025"
                $monthMap = [
                    'January' => 'Jan', 'February' => 'Feb', 'March' => 'Mar',
                    'April' => 'Apr', 'May' => 'May', 'June' => 'Jun',
                    'July' => 'Jul', 'August' => 'Aug', 'September' => 'Sep',
                    'October' => 'Oct', 'November' => 'Nov', 'December' => 'Dec'
                ];
                foreach ($monthMap as $fullMonth => $shortMonth) {
                    $value = str_replace($fullMonth . ' - ', $shortMonth . '-', $value);
                }
            }
            
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
    $headerRange = 'A1:' . $lastColumn . '1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 9, // Match body font size
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1E40AF'], // Dark blue
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true, // Wrap text in headers if needed
        ],
    ]);
    
    // 2. Apply borders to all cells
    $dataRange = 'A1:' . $lastColumn . $lastRow;
    $sheet->getStyle($dataRange)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);
    
    // 3. Align text columns (left) and number columns (right)
    $textColumns = ['A', 'B', 'C']; // Coop No, Period, Name
    foreach ($textColumns as $col) {
        $sheet->getStyle($col . '2:' . $col . $lastRow)
              ->getAlignment()
              ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
    
    // 3b. Enable text wrapping for Name column to prevent overflow
    $sheet->getStyle('C2:C' . $lastRow)
          ->getAlignment()
          ->setWrapText(true);
    
    // 4. Right-align all number columns
    for ($col = 'D'; $col <= $lastColumn; $col++) {
        $sheet->getStyle($col . '2:' . $col . $lastRow)
              ->getAlignment()
              ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
    
    // 5. Format total row (last row) - bold and gray background
    $totalRowRange = 'A' . $lastRow . ':' . $lastColumn . $lastRow;
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
    
    // 6. Set very narrow column widths to fit 15 columns (A-O) on one page
    // Total target width for A4 landscape: ~260mm or ~10.2 inches
    // With 0.2" margins on each side = 9.8" usable width
    // For 15 columns: 9.8 / 15 = 0.653" per column average
    
    $sheet->getColumnDimension('A')->setWidth(7);   // Coop No (shorter)
    $sheet->getColumnDimension('B')->setWidth(9);   // Period (Sep-2025)
    $sheet->getColumnDimension('C')->setWidth(22);  // Name (much shorter)
    
    // Set numeric columns to very narrow widths (9 units each)
    for ($col = 'D'; $col <= $lastColumn; $col++) {
        $sheet->getColumnDimension($col)->setWidth(9); // Very narrow for numbers
    }
    
    // 6b. Reduce font size to 9pt for better fit
    $sheet->getStyle('A1:' . $lastColumn . $lastRow)
          ->getFont()
          ->setSize(9);
    
    // ===== PAGE SETUP FOR PRINTING =====
    
    // 7. Page Setup - A4 Landscape with aggressive fit settings
    $sheet->getPageSetup()
          ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
          ->setPaperSize(PageSetup::PAPERSIZE_A4)
          ->setFitToWidth(1)   // FORCE fit to 1 page wide
          ->setFitToHeight(0); // As many pages tall as needed
    
    // Note: When FitToWidth is set, scale is automatically adjusted
    // Don't set both scale and fitToWidth as they conflict
    
    // 8. Set print area
    $sheet->getPageSetup()->setPrintArea($dataRange);
    
    // 9. Repeat header row on every page
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
    
    // 10. Set smaller margins for maximum space (in inches)
    $sheet->getPageMargins()
          ->setTop(0.5)    // Reduced from 0.75
          ->setRight(0.2)  // Reduced from 0.25
          ->setLeft(0.2)   // Reduced from 0.25
          ->setBottom(0.5) // Reduced from 0.75
          ->setHeader(0.2) // Reduced from 0.3
          ->setFooter(0.2); // Reduced from 0.3
    
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
    
    // Get email from POST if provided
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Send email if email address is provided
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'mail.emmaggi.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cov@emmaggi.com';
            $mail->Password = 'Banzoo@7980';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('cov@emmaggi.com', 'VCMS');
            $mail->addAddress($email);
            
            // Attachments
            $mail->addAttachment($tempFileName, $filename . '.xlsx');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Master Transaction Report - ' . $filename;
            $mail->Body = 'Dear Recipient,<br><br>The Master Transaction Report "' . htmlspecialchars($filename) . '" is attached.<br><br>Best regards,<br>VCMS';
            
            $mail->send();
        } catch (Exception $e) {
            // Continue with download even if email fails
            error_log("Email sending failed: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
        }
    }
    
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