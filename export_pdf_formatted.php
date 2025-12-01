<?php
// Include the Composer autoload file
require __DIR__ . '/vendor/autoload.php';

// Explicitly include TCPDF
require __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

// Include PHPMailer for email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Custom PDF class with repeating header and footer - Define BEFORE using
class CustomPDF extends TCPDF {
    public $customFilename = '';
    public $tableHeaderHtml = '';
    
    public function Header() {
        if (!empty($this->tableHeaderHtml)) {
            // Position at top with small margin
            $this->SetY(10);
            $this->SetFont('helvetica', '', 7);
            
            // Write the table header HTML
            $this->writeHTML($this->tableHeaderHtml, true, false, true, false, '');
        }
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, 'Printed: ' . date('Y-m-d H:i') . ' | ' . $this->customFilename, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['html'])) {
    $tableHtml = $_POST['html'];
    $filename = isset($_POST['filename']) ? $_POST['filename'] : 'MasterTransaction';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Store header HTML for repetition on each page
    $headerHtml = '';
    $html = '';
    $headerRowHtml = '';
    
    // Parse HTML to clean and format it
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($tableHtml, 'HTML-ENTITIES', 'UTF-8'));
    
    // Get table
    $tables = $dom->getElementsByTagName('table');
    if ($tables->length > 0) {
        $table = $tables->item(0);
        
        // Build formatted HTML table with inline styles
        $html = '<style>
            table { 
                border-collapse: collapse; 
                width: 100%; 
                font-size: 7pt;
            }
            th { 
                background-color: #1E40AF; 
                color: #FFFFFF; 
                font-weight: bold; 
                text-align: center; 
                padding: 4px 2px;
                border: 1px solid #000000;
                font-size: 7pt;
            }
            td { 
                padding: 3px 2px;
                border: 1px solid #000000;
                font-size: 7pt;
            }
            tr:last-child td {
                background-color: #E5E7EB;
                font-weight: bold;
            }
            .text-left { text-align: left; }
            .text-right { text-align: right; }
        </style>';
        
        $html .= '<table cellpadding="2" cellspacing="0" border="1">';
        
        // Process rows
        $rows = $table->getElementsByTagName('tr');
        $rowIndex = 0;
        $headerRowHtml = '';
        
        foreach ($rows as $row) {
            $rowHtml = '<tr>';
            
            // Get cells (th or td)
            $cells = $row->getElementsByTagName('th');
            if ($cells->length == 0) {
                $cells = $row->getElementsByTagName('td');
            }
            
            $cellIndex = 0;
            foreach ($cells as $cell) {
                // Skip first column (checkbox)
                if ($cellIndex == 0) {
                    $cellIndex++;
                    continue;
                }
                
                $value = trim($cell->nodeValue);
                
                // Shorten month names in Period column (2nd visible column)
                if ($cellIndex == 2 && $rowIndex > 0) {
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
                
                // Determine cell type and alignment
                $cellTag = ($rowIndex == 0) ? 'th' : 'td';
                
                // Align text columns left (Coop No, Period, Name) and numbers right
                $alignment = ($cellIndex <= 3) ? 'text-left' : 'text-right';
                
                $rowHtml .= "<$cellTag class='$alignment'>$value</$cellTag>";
                
                $cellIndex++;
            }
            
            $rowHtml .= '</tr>';
            
            // Store header row for repetition
            if ($rowIndex == 0) {
                $headerRowHtml = $rowHtml;
            }
            
            $html .= $rowHtml;
            $rowIndex++;
        }
        
        $html .= '</table>';
        
        // HTML is now prepared with header row stored separately
    } else {
        // Fallback: use original HTML
        $html = $tableHtml;
    }
    
    // Create PDF with custom header and footer - Only create ONCE
    $pdf = new CustomPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->customFilename = $filename;
    
    // Set the table header HTML for repetition on each page
    if (!empty($headerRowHtml)) {
        $styleTag = '<style>
            table { border-collapse: collapse; width: 100%; font-size: 7pt; }
            th { background-color: #1E40AF; color: #FFFFFF; font-weight: bold; text-align: center; 
                 padding: 4px 2px; border: 1px solid #000000; font-size: 7pt; }
            .text-left { text-align: left; }
            .text-right { text-align: right; }
        </style>';
        $pdf->tableHeaderHtml = $styleTag . '<table cellpadding="2" cellspacing="0" border="1">' . $headerRowHtml . '</table>';
    }
    
    $pdf->SetCreator('Cooperative Management System');
    $pdf->SetAuthor('VCMS');
    $pdf->SetTitle($filename);
    $pdf->SetSubject('Master Transaction Report');
    
    $pdf->setPrintHeader(true);  // Enable header for repeating table header
    $pdf->setPrintFooter(true);
    
    $pdf->SetMargins(5, 20, 5);  // Increased top margin to 20mm for header
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->SetFont('helvetica', '', 7);
    
    $pdf->AddPage();
    
    // Write only the data rows (skip the header row as it's now in Header())
    // Only remove header if we have headerRowHtml defined (from table parsing)
    if (!empty($headerRowHtml) && !empty($html)) {
        // Remove the header row from the HTML before writing
        $htmlWithoutHeader = preg_replace('/<tr>.*?<\/tr>/', '', $html, 1);
        $pdf->writeHTML($htmlWithoutHeader, true, false, true, false, '');
    } else {
        // Use full HTML if no header separation was done
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    // Set the filename
    $pdfFilename = $filename . '.pdf';
    
    // Save PDF to temporary file for email attachment
    $tempFilePath = sys_get_temp_dir() . '/' . uniqid('pdf_') . '.pdf';
    $pdf->Output($tempFilePath, 'F');
    
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
            $mail->addAttachment($tempFilePath, $pdfFilename);
            
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

    // Output the PDF as a download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
    readfile($tempFilePath);
    
    // Clean up temporary file
    @unlink($tempFilePath);
    exit;
}
?>