<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'mail.oouthbid.com.ng';                 //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'no-replyoouth@oouthbid.com.ng';        //SMTP username
    $mail->Password   = 'no-replyoouth@123';                    //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('fno-replyoouth@oouthbid.com.ng', 'Mailer');
    $mail->addAddress('bankole.adesoji@gmail.com', 'Banky');     //Add a recipient
    $mail->addAddress('bankole.adesoji@gmail.com');                     //Name is optional
    $mail->addReplyTo('no-replyoouth@oouthbid.com.ng', 'Information');
    $mail->addCC('no-replyoouth@oouthbid.com.ng');
    $mail->addBCC('no-replyoouth@oouthbid.com.ng');

    //Attachments
  //  $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
   // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = "You've recently created a new username account linked to the email address: <br /><br />To activate your account, click the link below:<br /><br /> https://www.oouthcom/caree/validate.php?act=auth&jam=token="
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
