<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

include_once('classes/functions.php');

$coop_no = filter_var($_POST['coop_no'], FILTER_SANITIZE_STRING);
$mobile = filter_var($_POST['mobile'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); 
$account_no = filter_var($_POST['account_no'], FILTER_SANITIZE_STRING);
$bank = filter_var($_POST['bank'], FILTER_SANITIZE_STRING);
$surname = filter_var($_POST['surname'], FILTER_SANITIZE_STRING);
$firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
$middlename = filter_var($_POST['middlename'], FILTER_SANITIZE_STRING);
$succes = 0;

if(isset($coop_no)){

$query = $conn->prepare('SELECT tbl_personalinfo.memberid FROM tbl_personalinfo WHERE memberid = ?');
					$res = $query->execute(array($coop_no));
					$existtrans = $query->fetch();

					if ($existtrans) {

						$queryupdate = $conn->prepare('UPDATE tbl_personalinfo SET tbl_personalinfo.mobilephone = ?,tbl_personalinfo.emailaddress = ?,lname = ?,fname = ?,mname = ? WHERE memberid = ?');
							$res = $queryupdate->execute(array($mobile,$email,strtoupper($surname),strtoupper($firstname),strtoupper($middlename),$coop_no));


					 }

					 $query = $conn->prepare('SELECT * FROM tblaccountno WHERE coopno = ?');
					$res = $query->execute(array($coop_no));
					$existtrans = $query->fetch();

					if ($existtrans) {

						$queryupdate = $conn->prepare('UPDATE tblaccountno SET accountNo = ?,bankcode = ? WHERE coopno = ?');
						$res = $queryupdate->execute(array($account_no,$bank,$coop_no));


					 }else{
					 	$queryupdate = $conn->prepare('INSERT INTO tblaccountno (accountNo,bankCode,coopno)VALUES (?,?,?)');
						$res = $queryupdate->execute(array($account_no,$bank,$coop_no));



					 }

					 $succes = 1;


}

?>

<?php
if($succes == 1){

$query = $conn->prepare('SELECT tblusers.Username, tblusers.PlainPassword, tblusers.lastname, tblusers.middlename, tblusers.firstname,tbl_personalinfo.emailaddress FROM tblusers INNER JOIN tbl_personalinfo ON tblusers.UserID = tbl_personalinfo.memberid WHERE UserID = ?');
$fin = $query->execute(array($coop_no));

$res = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row => $link) {
	$email_register = $link["emailaddress"];
	$username = $link["Username"];
	$password = $link["PlainPassword"];
	$first_name = $link["firstname"];

	$sendmessage = "Dear {$first_name}, thank you for updating your information. Your login details can be found below: <br>username = {$username}<br> Password = {$password} <br />To download the app to your phone, click the link below:<br /> <a href='https://emmaggi.com/download/vcms.apk'>Download vcms mobile App here</a>";

	require "mail/mail/vendor/autoload.php";

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
	$mail->Host = "mail.emmaggi.com";
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

}
?>