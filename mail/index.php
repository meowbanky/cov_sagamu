<?php

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
include_once "..\classes\class.db.php";

$query = $conn->prepare('SELECT
tbl_exco.coop_id,
tbl_exco.email,
tblusers_online.PlainPassword,
tblusers_online.Username,
tblemployees.FirstName
FROM
tbl_exco
INNER JOIN tblusers_online ON tbl_exco.coop_id = tblusers_online.Username
INNER JOIN tblemployees ON tblemployees.CoopID = tblusers_online.Username');
$fin = $query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row => $link) {
	$email_register = $link["email"];
	$username = $link["Username"];
	$password = $link["PlainPassword"];
	$first_name = $link["FirstName"];

	$sendmessage = "You have been registered on the OOUTH-COOP mobile App. Your login details can be found below: <br>username = {$username}<br> Password = {$password} <br />To download the app to your phone, click the link below:<br /><br /> <a href='https://emmaggi.com/download/oouth_coop.apk' >here</a>";

	require "mail/vendor/autoload.php";

	//Create a new PHPMailer instance
	$mail = new PHPMailer();

	//Tell PHPMailer to use SMTP
	$mail->isSMTP();

	//Enable SMTP debugging
	//SMTP::DEBUG_OFF = off (for production use)
	//SMTP::DEBUG_CLIENT = client messages
	//SMTP::DEBUG_SERVER = client and server messages
	$mail->SMTPDebug = SMTP::DEBUG_OFF;

	//Set the hostname of the mail server
	$mail->Host = "mail.oouth.com";
	//Use `$mail->Host = gethostbyname('smtp.gmail.com');`
	//if your network does not support SMTP over IPv6,
	//though this may cause issues with TLS

	//Set the SMTP port number:
	// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
	// - 587 for SMTP+STARTTLS
	$mail->Port = 465;

	//Set the encryption mechanism to use:
	// - SMTPS (implicit TLS on port 465) or
	// - STARTTLS (explicit TLS on port 587)
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;

	//Username to use for SMTP authentication - use full email address for gmail
	$mail->Username = "vcms@emmaggi.com";

	//Password to use for SMTP authentication
	$mail->Password = "Banzoo@7980";

	//Set who the message is to be sent from
	//Note that with gmail you can only use your account address (same as `Username`)
	//or predefined aliases that you have configured within your account.
	//Do not use user-submitted addresses in here
	$mail->setFrom("no-reply@emmaggi.com", "VCMS");

	//Set an alternative reply-to address
	//This is a good place to put user-submitted addresses
	$mail->addReplyTo("no-reply@emmaggi.com", "VCMS");

	//Set who the message is to be sent to
	$mail->addAddress($email_register, $first_name);
	$mail ->addBCC('bankole.adesoji@gmail.com');
	
	//Set the subject line
	$mail->Subject = "VCMS MOBILE APP";

	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);

	//Replace the plain text body with one created manually
	$mail->AltBody = "This is a plain-text message body";

	$mail->Body = $sendmessage;

	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');

	//send the message, check for errors
	if (!$mail->send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
		echo "2";
	}
}

?>
