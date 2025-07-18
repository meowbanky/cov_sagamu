<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['html'])) {
    $tableHtml = $_POST['html'];
    if(isset($_POST['email']) && $_POST['email'] != ''){
        $email = $_POST['email'];
        $filename = $_POST['filename'];
    }


    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Convert HTML Table to DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($tableHtml);

    // Extract Table Rows
    $rows = $dom->getElementsByTagName('tr');

    // Iterate Over Rows and Cells
    $rowIndex = 1; // Excel rows start at 1
    foreach ($rows as $row) {
        $colIndex = 'A'; // Excel columns start at A
        $cells = $row->getElementsByTagName('td');
        if ($cells->length == 0) {
            $cells = $row->getElementsByTagName('th');
        }
        foreach ($cells as $cell) {
            $sheet->setCellValue($colIndex . $rowIndex, $cell->nodeValue);
            $colIndex++;
        }
        $rowIndex++;
    }

    // Write the spreadsheet to a temporary file
    $writer = new Xlsx($spreadsheet);
    $fileName = tempnam(sys_get_temp_dir(), 'xlsx');
    $writer->save($fileName);


    // Prepare to send the file as an email attachment
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.emmaggi.com' ; //$_ENV['SMTP_HOST'];  // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'cov@emmaggi.com' ; //$_ENV['SMTP_USER'];  // SMTP username
        $mail->Password = 'Banzoo@7980'; // $_ENV['SMTP_PASS'];  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = '587'; // $_ENV['SMTP_PORT'];

        // Recipients
        $mail->setFrom('cov@emmaggi.com', 'VCMS');
        $mail->addAddress($email, '');

        // Attachments
        $mail->addAttachment($fileName, $filename.'.xlsx');  // Add attachments

        // Content
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Subject = 'Master Transaction';
        $mail->Body    = 'Master Transaction file '.$filename.' excel file is attached';

        $mail->send();
//        echo 'Message has been sent';

        // Return the file as a response
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="export.xlsx"');
        header('Cache-Control: max-age=0');
        readfile($fileName);

        // Remove the temporary file
        unlink($fileName);

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    exit;
}
?>
