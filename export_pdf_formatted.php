<?php
// Include the Composer autoload file
require __DIR__ . '/vendor/autoload.php';

// Explicitly include TCPDF
require __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['html'])) {
    $tableHtml = $_POST['html'];
    $filename = isset($_POST['filename']) ? $_POST['filename'] : 'MasterTransaction';

    // Create new PDF document - Landscape A4
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Cooperative Management System');
    $pdf->SetAuthor('VCMS');
    $pdf->SetTitle($filename);
    $pdf->SetSubject('Master Transaction Report');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // Set footer with page numbers
    $pdf->setFooterFont(Array('helvetica', '', 8));
    
    // Set margins - smaller for landscape fit
    $pdf->SetMargins(5, 10, 5); // left, top, right (5mm = 0.2 inches)
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Set font
    $pdf->SetFont('helvetica', '', 7); // Small font for horizontal fit
    
    // Store header HTML for repetition on each page
    $headerHtml = '';

    // Add a page
    $pdf->AddPage();
    
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
        
        // Write the formatted HTML
        $pdf->writeHTML($html, true, false, true, false, '');
    } else {
        // Fallback: use original HTML
        $pdf->writeHTML($tableHtml, true, false, true, false, '');
    }
    
    // Custom PDF class with repeating header and footer
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
    
    // Recreate PDF with custom header and footer
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
    // Remove the header row from the HTML before writing
    $htmlWithoutHeader = preg_replace('/<tr>.*?<\/tr>/', '', $html, 1);
    $pdf->writeHTML($htmlWithoutHeader, true, false, true, false, '');

    // Set the filename
    $pdfFilename = $filename . '.pdf';

    // Output the PDF as a download
    $pdf->Output($pdfFilename, 'D');
}
?>

