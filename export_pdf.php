<?php
// Enable error reporting
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Include the Composer autoload file
require __DIR__ . '/vendor/autoload.php';

// Explicitly include TCPDF
require __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['html'])) {
    $tableHtml = $_POST['html'];
    $filename = isset($_POST['filename']) ? $_POST['filename'] : 'table';

    // Create new PDF document
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Exported Table');
    $pdf->SetSubject('Table Data');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Exported Table', '');

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page
    $pdf->AddPage();

    // Write the HTML content
    $pdf->writeHTML($tableHtml, true, false, true, false, '');

    // Set the filename
    $pdfFilename = $filename . '.pdf';

    // Output the PDF as a download
    $pdf->Output($pdfFilename, 'D');
}
?>
