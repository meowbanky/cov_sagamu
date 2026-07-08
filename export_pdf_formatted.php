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
    
    public function Header() {
        // Header handled by <thead> in main HTML
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
            .report-title { text-align: center; }
            .report-title .t1 { font-size: 13pt; font-weight: bold; color: #1E40AF; }
            .report-title .t2 { font-size: 9pt; color: #374151; }
            .report-title .t3 { font-size: 7pt; color: #6B7280; }
            table {
                border-collapse: collapse;
                width: 100%;
                font-size: 6pt;
            }
            th {
                background-color: #1E40AF;
                color: #FFFFFF;
                font-weight: bold;
                text-align: center;
                padding: 3px 1px;
                border: 1px solid #93A3C4;
                font-size: 6pt;
            }
            td {
                padding: 3px 1px;
                border: 1px solid #C7CDD6;
                font-size: 6pt;
            }
            .alt td {
                background-color: #EEF2FF;
            }
            .totals td {
                background-color: #DBE3F4;
                font-weight: bold;
                border-color: #93A3C4;
            }
            .text-left { text-align: left; }
            .text-right { text-align: right; }
        </style>';

        // Report title block
        $periodLabel = str_replace('_', ' to ', $filename);
        $html .= '<div class="report-title">'
            . '<span class="t1">Master Transaction Report</span><br/>'
            . '<span class="t2">' . htmlspecialchars($periodLabel) . '</span><br/>'
            . '<span class="t3">Generated: ' . date('d M Y, H:i') . '</span>'
            . '</div><br/>';

        $html .= '<table cellpadding="2" cellspacing="0" border="1">';
        $html .= '<thead>';

        // Process rows
        $rows = $table->getElementsByTagName('tr');
        $totalRows = $rows->length;
        $rowIndex = 0;

        foreach ($rows as $row) {
            if (!$row instanceof DOMElement) {
                continue;
            }

            // Row class: header (none), totals (last row), or zebra for alternate data rows
            $trClass = '';
            if ($rowIndex > 0) {
                if ($rowIndex === $totalRows - 1) {
                    $trClass = ' class="totals"';
                } elseif ($rowIndex % 2 === 0) {
                    $trClass = ' class="alt"';
                }
            }
            $rowHtml = '<tr' . $trClass . '>';
            
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

                // Align text columns left (Coop No, Period, Name) and numbers right.
                // TCPDF honours an inline text-align style on the cell most reliably
                // (CSS classes and the align attribute are ignored for <td>).
                if ($rowIndex == 0) {
                    $align = 'center';
                } else {
                    $align = ($cellIndex <= 3) ? 'left' : 'right';
                }

                $rowHtml .= "<$cellTag style='text-align:$align;' align='$align'>$value</$cellTag>";
                
                $cellIndex++;
            }
            
            $rowHtml .= '</tr>';
            
            $html .= $rowHtml;
            
            if ($rowIndex == 0) {
                $html .= '</thead><tbody>';
            }
            
            $rowIndex++;
        }
        
        $html .= '</tbody></table>';
        
        // HTML is now prepared with header row stored separately
    } else {
        // Fallback: use original HTML
        $html = $tableHtml;
    }
    
    // Create PDF with custom header and footer - Only create ONCE
    $pdf = new CustomPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->customFilename = $filename;
    
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
    $pdf->SetFont('helvetica', '', 6);
    
    $pdf->AddPage();
    
    $pdf->writeHTML($html, true, false, true, false, '');

    // Set the filename
    $pdfFilename = $filename . '.pdf';
    
    // Save PDF to temporary file for email attachment
    $tempFilePath = sys_get_temp_dir() . '/' . uniqid('pdf_') . '.pdf';
    $pdf->Output($tempFilePath, 'F');
    
    // Delivery mode: 'download' (default), 'email', or 'both'
    $mode = $_POST['mode'] ?? 'download';
    $needsEmail = in_array($mode, ['email', 'both'], true);

    if ($needsEmail && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        @unlink($tempFilePath);
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'A valid email address is required for this option.']);
        exit;
    }

    $emailSent = false;
    $emailError = '';
    if ($needsEmail) {
        try {
            // Load SMTP settings from the single .env (no hardcoded credentials)
            require_once __DIR__ . '/config/EnvConfig.php';
            $mc = EnvConfig::getMailConfig();

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $mc['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mc['username'];
            $mail->Password = $mc['password'];
            $mail->SMTPSecure = $mc['encryption'] ?: PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $mc['port'];

            $mail->setFrom($mc['from_address'], $mc['from_name']);
            $mail->addAddress($email);
            $mail->addAttachment($tempFilePath, $pdfFilename);

            $mail->isHTML(true);
            $mail->Subject = 'Master Transaction Report - ' . $filename;
            $mail->Body = 'Dear Recipient,<br><br>The Master Transaction Report "' . htmlspecialchars($filename) . '" is attached.<br><br>Best regards,<br>VCMS';

            $mail->send();
            $emailSent = true;
        } catch (Exception $e) {
            $emailError = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
            error_log("PDF email sending failed: " . $emailError);
        }
    }

    // Email-only: return a JSON result, no file download
    if ($mode === 'email') {
        @unlink($tempFilePath);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $emailSent,
            'message' => $emailSent
                ? ('PDF report sent to ' . $email)
                : ('Failed to send email' . ($emailError ? ': ' . $emailError : '.')),
        ]);
        exit;
    }

    // Download or both: stream the PDF to the browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
    readfile($tempFilePath);

    // Clean up temporary file
    @unlink($tempFilePath);
    exit;
}
?>