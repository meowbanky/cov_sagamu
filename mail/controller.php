<?php
	
	//Import PHPMailer classes into the global namespace
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	session_start();
	include_once('functions.php');
	include_once('cinfig.php');
	include_once('passwordHash.php');
	//$act = strip_tags(addslashes($_GET['act'));
	$act = filter_var($_GET['act'], FILTER_SANITIZE_STRING);
	$source = $_SERVER['HTTP_REFERER'];
	//$hostname = gethostbyname($_SERVER['REMOTE_ADDR');
	//session variables
	$comp = '1';



	switch ($act) {
		case 'login':
			$uname = filter_var((filter_var($_POST['uname'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			$pass = filter_var($_POST['upassword'], FILTER_SANITIZE_STRING);
			//$upass = password_hash(filter_var($_POST['upassword'], FILTER_SANITIZE_STRING), PASSWORD_BCRYPT);

			try{
				$query = $conn->prepare('SELECT * FROM tbl_username WHERE email = ?');
				$fin = $query->execute(array($uname));

				//unset($_SESSION('email'));
        		//unset($_SESSION('first_name'));
        		//unset($_SESSION('last_name'));

        		if (isset($_SESSION['periodstatuschange'])) {
	    			unset($_SESSION['periodstatuschange']);
	    		}

				if (($row = $query->fetch()) AND (password_verify($pass, $row['pwd']))) {

					if($row['status'] == '0'){

						echo '3';
						exit;
					}
					
					$_SESSION['logged_in'] = '1';
					$_SESSION['userid'] = $row['id'];
					$_SESSION['email'] = $row['email'];
					$_SESSION['phone'] = $row['phone'];
            		$_SESSION['first_name'] = $row['first_name'];
            		$_SESSION['last_name'] = $row['last_name'];

            		echo '1';
            		
				}
				else {
					
					echo '2';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			
		break;

		case 'saveProfile':



			$first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
			$last_name= filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
			$gender_id= filter_var($_POST['gender_id'], FILTER_SANITIZE_STRING);
			$marital_status_id= filter_var($_POST['marital_status_id'], FILTER_SANITIZE_STRING);
			$street_address= filter_var($_POST['street_address'], FILTER_SANITIZE_STRING);
			$country_id= filter_var($_POST['country_id'], FILTER_SANITIZE_STRING);
			$state_id= filter_var($_POST['state_id'], FILTER_SANITIZE_STRING);
			$lg_of_residence= filter_var($_POST['lg_of_residence'], FILTER_SANITIZE_STRING);
			$date_of_birth= filter_var($_POST['date_of_birth'], FILTER_SANITIZE_STRING);
			$bond_status= filter_var($_POST['bond_status'], FILTER_SANITIZE_STRING);
			//$Phone= filter_var($_POST['Phone'], FILTER_SANITIZE_STRING);
			$home_address= filter_var($_POST['home_address'], FILTER_SANITIZE_STRING);
			$bond_details= filter_var($_POST['bond_details'], FILTER_SANITIZE_STRING);
			$place_birth= filter_var($_POST['place_birth'], FILTER_SANITIZE_STRING);
			if(isset($_POST['middle_name'])){
			$Middle= filter_var($_POST['middle_name'], FILTER_SANITIZE_STRING);
			}else{
			$Middle	= '';
			}

			
				try{

					$query = $conn->prepare('SELECT * FROM profile WHERE user_id = ?');
					$res = $query->execute(array($_SESSION['userid']));
					$existtrans = $query->fetch();

					if ($existtrans) {

						$query = 'UPDATE profile SET bond_status=?,place_birth=?,middle_name=?,first_name=?,last_name=?,gender_id=?,marital_status_id=?,street_address=?,country_id=?,state_id=?,lg_of_residence=?,date_of_birth=?,home_address=?,bond_details=? WHERE user_id=?';
						$conn->prepare($query)->execute(array($bond_status,$place_birth,$Middle,$first_name,$last_name,$gender_id,$marital_status_id,$street_address,$country_id,$state_id,$lg_of_residence,$date_of_birth,$home_address,$bond_details,$_SESSION['userid']));

						echo "1";
						
					}else {
						

						$query = 'INSERT INTO profile (bond_status,place_birth,user_id,middle_name,first_name,last_name,gender_id,marital_status_id,street_address,country_id,state_id,lg_of_residence,date_of_birth,home_address,bond_details) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($bond_status,$place_birth,$_SESSION['userid'],$Middle,$first_name,$last_name,$gender_id,$marital_status_id,$street_address,$country_id,$state_id,$lg_of_residence,$date_of_birth,$home_address,$bond_details));

						echo "2";
					}


if(isset($_FILES["image"]["type"]))
{
$validextensions = array("jpeg", "jpg", "png");
$temporary = explode(".", $_FILES["image"]["name"]);
$file_extension = end($temporary);
if ((($_FILES["image"]["type"] == "image/png") || ($_FILES["image"]["type"] == "image/jpg") || ($_FILES["image"]["type"] == "image/jpeg")
)/// && ($_FILES["file"]["size"] < 100000)//Approx. 100kb files can be uploaded.
&& in_array($file_extension, $validextensions)) {
if ($_FILES["image"]["error"] > 0){
//echo "Return Code: " . $_FILES["image"]["error"] . "<br/><br/>";
}else{
if (file_exists("../upload/" . $_FILES["image"]["name"])) {
//echo $_FILES["image"]["name"] . " <span id='invalid'><b>already exists.</b></span> ";
}else{
$date = date("Ymds");
$exploded = explode(".",$_FILES['image']['name']);
$ext = end($exploded);
$sourcePath = $_FILES['image']['tmp_name']; // Storing source path of the file in a variable
$targetPath = "../upload/".$date.'.'.$ext;//$_FILES['file']['name']; // Target path where file is to be stored
move_uploaded_file($sourcePath,$targetPath) ; // Moving Uploaded file

$dbUpload = "upload/".$date.'.'.$ext;

					$query = $conn->prepare('SELECT * FROM profile_pix WHERE user_id = ?');
					$res = $query->execute(array($_SESSION['userid']));
					$existtrans = $query->fetch();
    				if ($existtrans) {
    					if (file_exists("../".$existtrans['directory'])) {
						unlink("../".$existtrans['directory']);
						}

    					
						$query = 'UPDATE profile_pix SET directory=? WHERE user_id=?';
						$conn->prepare($query)->execute(array($dbUpload,$_SESSION['userid']));

						//echo "1";
						
					}else {
						$query = 'INSERT INTO profile_pix (directory,user_id) VALUES (?,?)';
						$conn->prepare($query)->execute(array($dbUpload,$_SESSION['userid']));

						//echo "2";
					}
            
 

}
}
}else{

}
}


				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
		break;

		
		case 'adduser':
			//
			$ufname = filter_var($_POST['ufname'], FILTER_SANITIZE_STRING);
			$ulname = filter_var($_POST['ulname'], FILTER_SANITIZE_STRING);
			$uemail = filter_var((filter_var($_POST['uemail'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
			$upass1 = filter_var($_POST['upass'], FILTER_SANITIZE_STRING);
			$upass2 = filter_var($_POST['upass1'], FILTER_SANITIZE_STRING);

			if ($upass1 == $upass2) {
				try{

					$query = $conn->prepare('SELECT * FROM users WHERE emailAddress = ? AND companyId = ? AND active = ? ');
					$res = $query->execute(array($uemail, $_SESSION['companyid'], '1'));
					$existtrans = $query->fetch();

					if ($existtrans) {
						//user exists
						$_SESSION['msg'] = "A user account associated with the supplied email exists.";
						$_SESSION['alertcolor'] = "danger";
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
					}
					else {
						$upass = password_hash($upass1, PASSWORD_DEFAULT);

						$query = 'INSERT INTO users (emailAddress, password, userTypeId, firstName, lastName, companyId, active) VALUES (?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($uemail, $upass, '1', $ufname, $ulname, $_SESSION['companyid'], '1'));

						$_SESSION['msg'] = $msg = 'User Successfully Created';
						$_SESSION['alertcolor'] = $type = 'success';
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
					}
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			}
			else {

				$_SESSION['msg'] = $msg = 'Entered passwords are not matching.';
				$_SESSION['alertcolor'] = $type = 'danger';
				header('Location: ' . $source);
			}
			
		break;

		case 'saveJob':
			//
			$job_title = $_POST['job_title'] ; //filter_var($_POST['job_title'], FILTER_SANITIZE_STRING);
			$qua_exp = $_POST['qua_exp'] ; //filter_var($_POST['qua_exp'], FILTER_SANITIZE_STRING);
			$salary =$_POST['salary'] ; // filter_var($_POST['salary'], FILTER_SANITIZE_STRING);
			$method = $_POST['method'] ; //filter_var($_POST['method'], FILTER_SANITIZE_STRING);
			$opening_date = $_POST['opening_date'];  //filter_var($_POST['opening_date'], FILTER_SANITIZE_STRING);
			$closing_date = $_POST['closing_date'] ; //filter_var($_POST['closing_date'], FILTER_SANITIZE_STRING);
			
				try{

					$query = 'INSERT INTO tbl_job (job_title, qua_exp, salary, method, opening_date, closing_date) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($job_title,$qua_exp,$salary,$method,$opening_date,$closing_date));

						echo '1';
						
											}
				
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'favourite':
			//
			$job_id = $_GET['job_id'] ; //filter_var($_POST['job_title'], FILTER_SANITIZE_STRING);
						
				try{

					$query = $conn->prepare('SELECT * FROM tbl_favourite WHERE job_id = ? AND user_id = ? ');
					$res = $query->execute(array($job_id,$_SESSION['userid']));
					$existtrans = $query->fetch();

					if ($existtrans) {

						$query = 'DELETE FROM tbl_favourite WHERE job_id = ? AND user_id = ?';
						$conn->prepare($query)->execute(array($job_id,$_SESSION['userid']));

						echo '0';
					}else {

						$query = 'INSERT INTO tbl_favourite (job_id, user_id) VALUES (?,?)';
						$conn->prepare($query)->execute(array($job_id,$_SESSION['userid']));
						echo '1';
						}
					}
				
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'applyjob':
			

			if(!isset($_SESSION['userid'])){

				echo '-1';


			}else{

			$job_id = $_GET['job_id'] ; //filter_var($_POST['job_title'], FILTER_SANITIZE_STRING);
						
				try{

					$query = $conn->prepare('SELECT * FROM tbl_job_application WHERE job_id = ? AND user_id = ? ');
					$res = $query->execute(array($job_id,$_SESSION['userid']));
					$existtrans = $query->fetch();

					if ($existtrans) {
						echo '0';
					}else {

						$query = 'INSERT INTO tbl_job_application (job_id, user_id) VALUES (?,?)';
						$conn->prepare($query)->execute(array($job_id,$_SESSION['userid']));
						echo '1';
						}
					}
				
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			}
			
		break;


		case 'register':
			//create new company
			
			$first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
			$email_register = filter_var((filter_var($_POST['email_register'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			$last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
			$phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
			$password_register = filter_var($_POST['password_register'], FILTER_SANITIZE_STRING);
			$password_register = password_hash($password_register, PASSWORD_DEFAULT);
			$tokenrecordtime = date('Y-m-d H:i:s');
			$reset_token = bin2hex(openssl_random_pseudo_bytes(32));

			

			try{
				$query = $conn->prepare('SELECT * FROM tbl_username WHERE email = ? OR phone = ? ');
				$res = $query->execute(array($email_register, $phone));
				$existtrans = $query->fetch();

				if ($existtrans) {
					
					$_SESSION['msg'] = "A user account associated with the supplied email exists.";
					$_SESSION['alertcolor'] = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//header('Location: ' . $source);

					echo '1';
				}
				else {
					
					$query = 'INSERT INTO tbl_username (first_name, last_name, email, phone, pwd,dateregistered,token) VALUES (?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($first_name, $last_name, $email_register, $phone, $password_register,$tokenrecordtime,$reset_token));
					$latestuserinsert = $conn->lastInsertId();
					
					//$query = 'INSERT INTO users (emailAddress, password, userTypeId, firstName, lastName, companyId, active) VALUES (?,?,?,?,?,?,?)';
					//$conn->prepare($query)->execute(array($useremail, $userpass, '1', $userfname, $userlname, $last_id, '0'));
					//$latestuserinsert = $conn->lastInsertId();
				
					//user account becomes active after validating emailed link
					//Send email validation
					//Generate update token
					//$reset_token = bin2hex(openssl_random_pseudo_bytes(32));
					
					//write token to token table and assign validity state, creation timestamp
					//$tokenrecordtime = date('Y-m-d H:i:s');


					//check for any previous tokens and invalidate
						$tokquery = $conn->prepare('SELECT * FROM reset_token WHERE username = ? AND valid = ? AND type = ?');
						$fin = $tokquery->execute(array($email_register, '1', '2'));
						
						if($row = $tokquery->fetch()){
							$upquery = 'UPDATE reset_token SET valid = ? WHERE username = ? AND valid = ?';
							$conn->prepare($upquery)->execute('0', $email_register, '1');
						}

					$tokenquery = 'INSERT INTO reset_token (username, token, creationTime, valid, type) VALUES (?,?,?,?,?)';
					$conn->prepare($tokenquery)->execute(array($email_register, $reset_token, $tokenrecordtime, '1', '2'));
						
					//exit($resetemail . " " . $reset_token);
					
					$sendmessage = "You've recently created a new username account linked to the email address: " . $email_register . "<br /><br />To activate your account, click the link below:<br /><br /> <a href='https://www.oouth.com/career/classes/controller.php?act=activate&token=" . $reset_token."' >here</a>";

					
					//generate reset code and append to email submitted

					/**
					 * This example shows settings to use when sending via Google's Gmail servers.
					 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
					 * example to see how to use XOAUTH2.
					 * The IMAP section shows how to save this message to the 'Sent Mail' folder using IMAP commands.
					 */



					require 'mail/vendor/autoload.php';

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
					$mail->Host = 'mail.oouth.com';
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
					$mail->Username = 'test@oouth.com';

					//Password to use for SMTP authentication
					$mail->Password = 'Banzoo@7980';

					//Set who the message is to be sent from
					//Note that with gmail you can only use your account address (same as `Username`)
					//or predefined aliases that you have configured within your account.
					//Do not use user-submitted addresses in here
					$mail->setFrom('no-reply@oouth.com', 'OOUTH CAREER');

					//Set an alternative reply-to address
					//This is a good place to put user-submitted addresses
					$mail->addReplyTo('no-reply@oouth.com', 'OOUTH CAREER');

					//Set who the message is to be sent to
					$mail->addAddress($email_register, $first_name);

					//Set the subject line
					$mail->Subject = 'Activation Link';

					//Read an HTML message body from an external file, convert referenced images to embedded,
					//convert HTML into a basic plain-text alternative body
					//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);

					//Replace the plain text body with one created manually
					$mail->AltBody = 'This is a plain-text message body';

					$mail->Body    = $sendmessage;

					//Attach an image file
					//$mail->addAttachment('images/phpmailer_mini.png');

					//send the message, check for errors
					if (!$mail->send()) {
					    echo 'Mailer Error: ' . $mail->ErrorInfo;
					} else {
					    echo '2';
					    //Section 2: IMAP
					    //Uncomment these to save your message in the 'Sent Mail' folder.
					    #if (save_mail($mail)) {
					    #    echo "Message saved!";
					    #}
					}

					//Section 2: IMAP
					//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
					//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
					//You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
					//be useful if you are trying to get this working on a non-Gmail IMAP server.

					//function save_mail($mail)
					//{
					    //You can change 'Sent Mail' to any other folder or tag
					 //   $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

					    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
					//    $imapStream = imap_open($path, $mail->Username, $mail->Password);

					 //   $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
					 //   imap_close($imapStream);

					 //   return $result;
					//}

				}

					/*
					********
					********
					Check if user account exists
					********
					********
					*/

				


			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

			//exit($companyname . ', ' . $contactemail . ', ' . $last_id);
		break;


		case 'addcostcenter':
			$ccname = filter_var($_POST['cctrname'], FILTER_SANITIZE_STRING);

			try{
				$query = 'INSERT INTO company_costcenters (companyId, costCenterName, active) VALUES (?,?,?)';
				$conn->prepare($query)->execute(array($_SESSION['companyid'], $ccname, '1'));
				$_SESSION['msg'] = $msg = 'Cost Center successfully Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;

		case 'resendactivation':
			//create new company
			$email = filter_var((filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			

			try{
				$query = $conn->prepare('SELECT * FROM tbl_username WHERE email = ? ');
				$res = $query->execute(array($email));
				$existtrans = $query->fetch();

				if ($existtrans) {

					$reset_token = $existtrans['token'];
					$first_name = $existtrans['first_name'];
					
					$sendmessage = "You've recently created a new username account linked to the email address: " . $email . "<br /><br />To activate your account, click the link below:<br /><br /> <a href='https://www.oouth.com/career/classes/controller.php?act=activate&token=" . $reset_token."' >here</a>";

					
					//generate reset code and append to email submitted

					/**
					 * This example shows settings to use when sending via Google's Gmail servers.
					 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
					 * example to see how to use XOAUTH2.
					 * The IMAP section shows how to save this message to the 'Sent Mail' folder using IMAP commands.
					 */



					require 'mail/vendor/autoload.php';

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
					$mail->Host = 'mail.oouth.com';
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
					$mail->Username = 'test@oouth.com';

					//Password to use for SMTP authentication
					$mail->Password = 'Banzoo@7980';

					//Set who the message is to be sent from
					//Note that with gmail you can only use your account address (same as `Username`)
					//or predefined aliases that you have configured within your account.
					//Do not use user-submitted addresses in here
					$mail->setFrom('no-reply@oouth.com', 'OOUTH CAREER');

					//Set an alternative reply-to address
					//This is a good place to put user-submitted addresses
					$mail->addReplyTo('no-reply@oouth.com', 'OOUTH CAREER');

					//Set who the message is to be sent to
					$mail->addAddress($email, $first_name);

					//Set the subject line
					$mail->Subject = 'Activation Link';

					//Read an HTML message body from an external file, convert referenced images to embedded,
					//convert HTML into a basic plain-text alternative body
					//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);

					//Replace the plain text body with one created manually
					$mail->AltBody = 'This is a plain-text message body';

					$mail->Body    = $sendmessage;

					//Attach an image file
					//$mail->addAttachment('images/phpmailer_mini.png');

					//send the message, check for errors
					if (!$mail->send()) {
					    echo 'Mailer Error: ' . $mail->ErrorInfo;
					} else {
					    echo '1';
					    //Section 2: IMAP
					    //Uncomment these to save your message in the 'Sent Mail' folder.
					    #if (save_mail($mail)) {
					    #    echo "Message saved!";
					    #}
					}

					//Section 2: IMAP
					//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
					//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
					//You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
					//be useful if you are trying to get this working on a non-Gmail IMAP server.

					//function save_mail($mail)
					//{
					    //You can change 'Sent Mail' to any other folder or tag
					 //   $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

					    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
					//    $imapStream = imap_open($path, $mail->Username, $mail->Password);

					 //   $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
					 //   imap_close($imapStream);

					 //   return $result;
					//}

				
				}else {

					echo '2';
				
					

				


			}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'forgotpassword':
			//create new company
			$email = filter_var((filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			

			try{
				$query = $conn->prepare('SELECT * FROM tbl_username WHERE email = ? ');
				$res = $query->execute(array($email));
				$existtrans = $query->fetch();

				if ($existtrans) {

					$username = $existtrans['email'];
					$first_name = $existtrans['first_name'];
					$reset_token = bin2hex(openssl_random_pseudo_bytes(32));
					$tokenrecordtime = date('Y-m-d H:i:s');

					$tokquery = $conn->prepare('SELECT * FROM reset_token WHERE username = ? AND valid = ?');
					$fin = $tokquery->execute (array($username, '1'));
					
					if($row = $tokquery->fetch()){
						$upquery = 'UPDATE reset_token SET valid = ? WHERE username = ? AND valid = ?';
						$conn->prepare($upquery)->execute (array('0', $username, '1'));
					}

					$query = 'INSERT INTO reset_token (username, token, creationTime,valid) VALUES (?,?,?,?)';
					$conn->prepare($query)->execute(array($username, $reset_token, $tokenrecordtime,'1'));
					
					$sendmessage = "You've recently asked to reset the password linked to the email address: " . $username . "<br /><br />To activate your account, click the link below:<br /><br /> <a href='https://www.oouth.com/career/classes/controller.php?act=activateforgotpassword&token=" . $reset_token."' >here</a>";

					
					

					require 'mail/vendor/autoload.php';

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
					$mail->Host = 'mail.oouth.com';
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
					$mail->Username = 'test@oouth.com';

					//Password to use for SMTP authentication
					$mail->Password = 'Banzoo@7980';

					//Set who the message is to be sent from
					//Note that with gmail you can only use your account address (same as `Username`)
					//or predefined aliases that you have configured within your account.
					//Do not use user-submitted addresses in here
					$mail->setFrom('no-reply@oouth.com', 'OOUTH CAREER');

					//Set an alternative reply-to address
					//This is a good place to put user-submitted addresses
					$mail->addReplyTo('no-reply@oouth.com', 'OOUTH CAREER');

					//Set who the message is to be sent to
					$mail->addAddress($username, $first_name);

					//Set the subject line
					$mail->Subject = 'Reset Password Link';

					//Read an HTML message body from an external file, convert referenced images to embedded,
					//convert HTML into a basic plain-text alternative body
					//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);

					//Replace the plain text body with one created manually
					$mail->AltBody = 'This is a plain-text message body';

					$mail->Body    = $sendmessage;

					//Attach an image file
					//$mail->addAttachment('images/phpmailer_mini.png');

					//send the message, check for errors
					if (!$mail->send()) {
					    echo 'Mailer Error: ' . $mail->ErrorInfo;
					} else {
					    echo '1';
					    //Section 2: IMAP
					    //Uncomment these to save your message in the 'Sent Mail' folder.
					    #if (save_mail($mail)) {
					    #    echo "Message saved!";
					    #}
					}

					//Section 2: IMAP
					//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
					//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
					//You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
					//be useful if you are trying to get this working on a non-Gmail IMAP server.

					//function save_mail($mail)
					//{
					    //You can change 'Sent Mail' to any other folder or tag
					 //   $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

					    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
					//    $imapStream = imap_open($path, $mail->Username, $mail->Password);

					 //   $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
					 //   imap_close($imapStream);

					 //   return $result;
					//}

				
				}else {

					echo '2';
				
					

				


			}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;

		case 'activateforgotpassword':
			$token = filter_var($_GET['token'], FILTER_SANITIZE_STRING);
			
			try{

				$query = $conn->prepare('SELECT * FROM reset_token WHERE token = ? AND valid = ? ');
				$res = $query->execute(array($token,1));
				$existtrans = $query->fetch();

				if ($existtrans) {

						if($existtrans){

						//$query = 'UPDATE tbl_username SET token_status = ?, status = ? WHERE token = ?';
						//$conn->prepare($query)->execute(array(1, 1, $token));

						$_SESSION['token'] = $existtrans['token'];

						$source = '../resetpassword.php';

						header('Location: ' . $source);
						}else{

							$source = '../resetpassword.php';

							header('Location: ' . $source);


						}


				}else{

					$source = '../resetpassword.php?token_status=ilegal';

					header('Location: ' . $source);



				}

			
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;

		case 'changepassword':
			$token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
			
			try{

				$query = $conn->prepare('SELECT * FROM reset_token WHERE token = ? and valid = ? ');
				$res = $query->execute(array($token,1));
				$existtrans = $query->fetch();

				if ($existtrans) {

						if($existtrans){
							$password_register = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
							$password_register = password_hash($password_register, PASSWORD_DEFAULT);

						$query = 'UPDATE tbl_username SET pwd = ? WHERE email = ?';
						$conn->prepare($query)->execute(array($password_register, $existtrans['username']));

						$upquery = 'UPDATE reset_token SET valid = ? WHERE username = ? AND valid = ?';
						$conn->prepare($upquery)->execute (array('0', $existtrans['username'], '1'));

						$source = '../index.php';

						//header('Location: ' . $source);
						echo '1';
						}

				}else{

					//$source = '../verify.php?token_status=ilegal';

					//header('Location: ' . $source);

					echo '2';



				}

			
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'activate':
			$token = filter_var($_GET['token'], FILTER_SANITIZE_STRING);
			
			try{

				$query = $conn->prepare('SELECT * FROM tbl_username WHERE token = ? ');
				$res = $query->execute(array($token));
				$existtrans = $query->fetch();

				if ($existtrans) {

						if($existtrans['token_status'] == 0){

						$query = 'UPDATE tbl_username SET token_status = ?, status = ? WHERE token = ?';
						$conn->prepare($query)->execute(array(1, 1, $token));

						$source = '../verify.php?token_status=success&status='.$existtrans['token_status'];

						header('Location: ' . $source);
						}else{

							$source = '../verify.php?token_status=activated&status='.$existtrans['token_status'];

							header('Location: ' . $source);


						}


				}else{

					$source = '../verify.php?token_status=ilegal';

					header('Location: ' . $source);



				}

			
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'adddepartment':
			$dept = filter_var($_POST['deptname'], FILTER_SANITIZE_STRING);
			
			try{
				$query = 'INSERT INTO company_departments (companyId, companyDescription, active) VALUES (?,?,?)';
				$conn->prepare($query)->execute(array($_SESSION['companyid'], $dept, '1'));
				$_SESSION['msg'] = $msg = 'Department successfully Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'addearning':
			$newearning = filter_var($_POST['eddescription'], FILTER_SANITIZE_STRING);
			$recurrent = filter_var($_POST['recurrent'], FILTER_VALIDATE_INT);

			try{
				$getlast = $conn->prepare('SELECT edCode FROM earnings_deductions WHERE edType = ? AND companyId = ? AND active = ? ORDER BY id DESC');
				$res = $getlast->execute(array('Earning', $_SESSION['companyid'], '1'));

				if ($row = $getlast->fetch()) {
			            $latestcode = intval($row['edCode']);
						$insertcode = $latestcode + 1;
			        }

			
				$query = 'INSERT INTO earnings_deductions (edCode, edDesc, edType, companyId, active, recurrentEd) VALUES (?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($insertcode, $newearning, 'Earning', $_SESSION['companyid'], '1', $recurrent));

				$_SESSION['msg'] = $msg = 'New earning Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'adddeduction':
			$newearning = filter_var($_POST['eddescription'], FILTER_SANITIZE_STRING);
			$recurrent = filter_var($_POST['recurrent'], FILTER_VALIDATE_INT);

			try{
				$getlast = $conn->prepare('SELECT edCode FROM earnings_deductions WHERE edType = ? AND companyId = ? AND active = ? ORDER BY id DESC');
				$res = $getlast->execute(array('Deduction', $_SESSION['companyid'], '1'));

				if ($row = $getlast->fetch()) {

			            $latestcode = intval($row['edCode']);
						$insertcode = $latestcode + 1;
			        }
			
				$query = 'INSERT INTO earnings_deductions (edCode, edDesc, edType, companyId, active, recurrentEd) VALUES (?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($insertcode, $newearning, 'Deduction', $_SESSION['companyid'], '1', $recurrent));

				$_SESSION['msg'] = $msg = 'New Deduction Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'addloan':
			$newloandesc = filter_var($_POST['newloandesc'], FILTER_SANITIZE_STRING);
			//define 900** as the loan ED Code
			try{
				$getlast = $conn->prepare('SELECT edCode FROM earnings_deductions WHERE edType = ? AND companyId = ? AND active = ? ORDER BY id DESC');
				$res = $getlast->execute(array('Loan', $_SESSION['companyid'], '1'));

				if ($row = $getlast->fetch()) {
					$latestcode = intval($row['edCode']);
					$principleinsertcode = $latestcode + 1;
					$repaymentinsertcode = $latestcode + 2;
				}
				$principleinsertdesc = $newloandesc . 'Loan Principle';
				$repaymentinsertdesc = $newloandesc . 'Loan Repayment';
				exit($principleinsertcode . ',' . $repaymentinsertcode);

				$query = 'INSERT INTO earnings_deductions (edCode, edDesc, edType, companyId, active, recurrentEd) VALUES (?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($principleinsertcode, $principleinsertdesc, 'Loan', $_SESSION['companyid'], '1', '0'));

				$query = 'INSERT INTO earnings_deductions (edCode, edDesc, edType, companyId, active, recurrentEd) VALUES (?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($repaymentinsertcode, $repaymentinsertdesc, 'Deduction', $_SESSION['companyid'], '1', '1'));



			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

			exit($newloandesc);
		break;


		case 'addemployeeearning':
			$currentempl = $_POST['curremployee'];
			$edtype = $_POST['edtype'];
			$edcode = $_POST['newearningcode'];
			$earningamount = $_POST['earningamount'];
			$recordtime = date('Y-m-d H:i:s');

			try{
				$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND payperiod = ? AND earningDeductionCode = ? AND active = ?');
				$res = $query->execute(array($currentempl, $_SESSION['currentactiveperiod'], $edcode, '1'));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$_SESSION['alertcolor'] = $type = "danger";
					$msg = "Duplicate Earning not allowed";
					$source = $_SERVER['HTTP_REFERER'];
					redirect($msg, $type, $source);
				} else {
					$query = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($currentempl, $_SESSION['companyid'], $edtype, $edcode, $earningamount, $_SESSION['currentactiveperiod'], '1', '1', $recordtime, $_SESSION['user']));
					$_SESSION['msg'] = $msg = "Earning successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break;


		case 'addemployeededuction':
			$currentempl = $_POST['curremployee'];
			$edtype = $_POST['edtype'];
			$edcode = $_POST['newdeductioncode'];
			$deductionamount = $_POST['deductionamount'];
			$recordtime = date('Y-m-d H:i:s');
			//exit($currentempl . " " . $edtype . " " .$edcode . " " . $deductionamount);
			try{
				$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND payperiod = ? AND earningDeductionCode = ? and active = ?');
				$res = $query->execute(array($currentempl, $_SESSION['currentactiveperiod'], $edcode, '1'));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$_SESSION['alertcolor'] = $type = "danger";
					$_SESSION['msg'] = $msg = "Duplicate Deduction not allowed";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				} else {
					$query = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($currentempl, $_SESSION['companyid'], $edtype, $edcode, $deductionamount, $_SESSION['currentactiveperiod'], '1', '1', $recordtime, $_SESSION['user']));
					$_SESSION['msg'] = $msg = "Deduction successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break;


		case 'editorganization':
			$compname = filter_var($_POST['compname'], FILTER_SANITIZE_STRING);
			$city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
			$county = filter_var($_POST['county'], FILTER_SANITIZE_STRING);
			$compemail = filter_var((filter_var($_POST['compemail'], FILTER_SANITIZE_STRING)), FILTER_VALIDATE_EMAIL);
			$compphone = filter_var($_POST['compphone'], FILTER_SANITIZE_STRING);
			$companypin = filter_var($_POST['companypin'], FILTER_SANITIZE_STRING);
			$nssfnumber = filter_var($_POST['nssfnumber'], FILTER_SANITIZE_STRING);
			$nhifnumber = filter_var($_POST['nhifnumber'], FILTER_SANITIZE_STRING);

			$startyear = date('Y-m-d', strtotime(filter_var($_POST['startyear'], FILTER_SANITIZE_STRING)));
			$endyear = date('Y-m-d', strtotime(filter_var($_POST['endyear'], FILTER_SANITIZE_STRING)));

			try{
				$query = 'INSERT INTO company (companyName, city, county, companyEmail, contactTelephone, companyPin, companyNssf, companyNhif, companyId, yearStart, yearEnd) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($compname, $city, $county, $compemail, $compphone, $companypin, $nssfnumber, $nhifnumber, $_SESSION['companyid'], $startyear, $endyear));
				$msg = "Company Details successfully saved";
				$type = "success";
				$source = $_SERVER['HTTP_REFERER'];
				redirect($msg, $type, $source);
			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}

		break;


		case 'addperiod':
			$periodname = filter_var($_POST['perioddesc'], FILTER_SANITIZE_STRING);
			$periodyear = filter_var($_POST['periodyear'], FILTER_SANITIZE_STRING);
			$periodDescription = $periodname . " " . $periodyear;
			//exit(var_dump(is_int($_SESSION['currentactiveperiod')));
			try{
				//check for replication and create period
				$query = $conn->prepare('SELECT * FROM payperiods WHERE description = ? AND periodYear = ? AND companyId = ?');
				$fin = $query->execute(array($periodname, $periodyear, $_SESSION['companyid']));

				if ($row = $query->fetch()){
					$_SESSION['msg'] = "Selected period values already exist.";
					$_SESSION['alertcolor'] = "danger";
					header('Location: ' . $source);
				} else {
					//Get last id in table
					$payp = $conn->prepare('SELECT periodId, description FROM payperiods WHERE companyId = ? ORDER BY id DESC LIMIT 1');
            		$myperiod = $payp->execute(array($_SESSION['companyid']));
            		$final = $payp->fetch();

					$workperiod = intval($final['periodId']);
					$insertPayId = $workperiod + 1;

					$query = 'INSERT INTO payperiods (periodId, description, periodYear, companyId, active, payrollRun) VALUES (?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($insertPayId, $periodname, $periodyear ,$_SESSION['companyid'], '0', '0'));
					$_SESSION['msg'] = "New Period Succesfully Created";
					$_SESSION['alertcolor'] = "success";
					header('Location: ' . $source);
				}

			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
		break;


		case 'closeActivePeriod':
			try{

				//reset period id
				//reset assigned active period id

				exit('closeActivePeriod');
			}
			catch(PDOEXception $e){
				echo $e->getMessage();
			}
		break;


		case 'activateclosedperiod':
			try{
				$reactivateperiodid = filter_var($_POST['reactivateperiodid'], FILTER_SANITIZE_STRING);
				//exit('activateclosedperiod ' . $reactivateperiodid);

				//Change period session variables
        		$_SESSION['currentactiveperiod'] = $reactivateperiodid;

        			$periodquery = $conn->prepare('SELECT description, periodYear FROM payperiods WHERE periodId = ?');
        			$perres = $periodquery->execute(array($_SESSION['currentactiveperiod']));
        			if ($rowp = $periodquery->fetch()) {
        				$reactivatedperioddesc = $rowp['description'];
        				$reactivatedperiodyear = $rowp['periodYear'];
        			}

        		$_SESSION['activeperiodDescription'] = $reactivatedperioddesc . ' ' . $reactivatedperiodyear;

        			//Ensure all openview status are reset before activating particular one
					$statuschange = $conn->prepare('UPDATE payperiods SET openview = ? ');
					$perres = $statuschange->execute(array('0'));

        			//set openview status
        			$statuschange = $conn->prepare('UPDATE payperiods SET openview = ? WHERE periodId = ?');
        			$perres = $statuschange->execute(array('1', $_SESSION['currentactiveperiod']));
        			$_SESSION['periodstatuschange'] = '1';

        		$_SESSION['msg'] = "You are now viewing data from a closed period. Transactions are not allowed.";
				$_SESSION['alertcolor'] = "success";
				header('Location: ' . $source);

			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
		break;

		
		case 'addNewEmp':
			//check for existing same employee number


			$fname = filter_var($_POST['fname'], FILTER_SANITIZE_STRING);
			$lname = filter_var($_POST['lname'], FILTER_SANITIZE_STRING);
			$gender = ucwords(strtolower(strip_tags(addslashes($_POST['gender']))));
			$idnumber = ucwords(strtolower(strip_tags(addslashes($_POST['idnumber']))));
			$dob = date('Y-m-d', strtotime(filter_var($_POST['dob'], FILTER_SANITIZE_STRING)));
			$citizenship = filter_var($_POST['citizenship'], FILTER_SANITIZE_STRING);	

			$emppin = filter_var($_POST['emppin'], FILTER_SANITIZE_STRING);
			$empnssf = filter_var($_POST['empnssf'], FILTER_SANITIZE_STRING);
			$empnhif = filter_var($_POST['empnhif'], FILTER_SANITIZE_STRING);
			$empbank = ucwords(strtolower(strip_tags(addslashes($_POST['empbank']))));
			$empbankbranch = ucwords(strtolower(strip_tags(addslashes($_POST['empbankbranch']))));

			$empacctnum = ucwords(strtolower(strip_tags(addslashes($_POST['empacctnum']))));
			$empdept = ucwords(strtolower(strip_tags(addslashes($_POST['empdept']))));
			$empcompbranch = ucwords(strtolower(strip_tags(addslashes($_POST['empcompbranch']))));
			$emptype = ucwords(strtolower(strip_tags(addslashes($_POST['emptype']))));
			$empnumber = filter_var($_POST['empnumber'], FILTER_SANITIZE_STRING);
			$employdate = date('Y-m-d', strtotime(filter_var($_POST['employdate'], FILTER_SANITIZE_STRING)));
			$empposition = ucwords(strtolower(strip_tags(addslashes($_POST['empposition']))));

			//validate for empty mandatory fields

			try{
				//check for replication and create period
				$query = $conn->prepare('SELECT * FROM employees WHERE empNumber = ? AND  companyId = ?');
				$fin = $query->execute(array($empnumber, $_SESSION['companyid']));

				if ($row = $query->fetch()){
					$_SESSION['msg'] = "Employee with same Employee Number exists.";
					$_SESSION['alertcolor'] = "danger";
					header('Location: ' . $source);
				} else {

					$query = 'INSERT INTO employees (empNumber, fName, lName, gender, idNumber, companyId, companyDept, companyBranch, empType, dob, citizenship, empTaxPin, empNssf, empNhif, empEmplDate, empPosition, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
				
					$conn->prepare($query)->execute(array($empnumber, $fname, $lname, $gender, $idnumber, $_SESSION['companyid'], $empdept, $empcompbranch, $emptype, $dob, $citizenship, $emppin, $empnssf, $empnhif, $employdate, $empposition, '1'));
					

					$_SESSION['msg'] = $msg = "Employee Successfully added.";
					$_SESSION['alertcolor'] = 'success';
					header('Location: ' . $source);
					//redirect($msg,$type,$source);

				}
			
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'getPreviousEmployee':
			
			$_SESSION['emptrack'] =$_SESSION['emptrack'] - 1;
			header('Location: ' . $source);
		break;


		case 'getNextEmployee':
			
			$_SESSION['emptrack'] =$_SESSION['emptrack'] + 1;
			$_SESSION['empDataTrack'] = 'next';
			header('Location: ' . $source);

		break;


		case 'retrieveLeaveData':

			$leavestate = filter_var($_GET['state'], FILTER_SANITIZE_STRING);
			$_SESSION['leavestate'] = $leavestate;

			header('Location: ' . $source);


		break;

		case 'retrieveSingleEmployeeData':

                $empnumber = filter_var($_POST['empearnings'], FILTER_SANITIZE_STRING);
                $_SESSION['empDataTrack'] = 'option';
                $_SESSION['emptNumTack'] = $empnumber;

                header('Location: ' . $source);

        break;



		case 'vtrans':
			$empRecordId = filter_var($_GET['td'], FILTER_SANITIZE_STRING);
			//exit($empRecordId);
			$_SESSION['empDataTrack'] = 'option';
			$_SESSION['emptNumTack'] = $empRecordId;

			header('Location: ../../empearnings.php');
		break;


		case 'runCurrentEmployeePayroll':
			
			define('TAX_RELIEF', '1280' );

			$thisemployee = filter_var($_POST['thisemployee'], FILTER_SANITIZE_STRING);

			//check if employee has basic salary, if not return error & exit
			$query = $conn->prepare('SELECT earningDeductionCode FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ? ');
			$rerun = $query->execute(array($thisemployee, $_SESSION['companyid'], '200', $_SESSION['currentactiveperiod'], '1'));

			if (!$row = $query->fetch()) {
				$_SESSION['msg'] = $msg = "This employee has no basic salary. Please assign basic salary in order to process employee's earnings.";
				$_SESSION['alertcolor'] = 'danger';
				header('Location: ' . $source);
			} else {

				//check if employee rerun
				try{
					$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ? ');
					$rerun = $query->execute(array($thisemployee, $_SESSION['companyid'], '601', $_SESSION['currentactiveperiod'], '1'));

					if ($row = $query->fetch()) {

						$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
	                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Earning', $_SESSION['currentactiveperiod'], '1'));
	                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
	                    $thisemployeeearnings = 0;

	                    foreach ($res as $row => $link) {
	                    	$thisemployeeearnings = $thisemployeeearnings + $link['amount'];
	                    }

	                    $recordtime = date('Y-m-d H:i:s');
	                    	//Run with an update query
	                    $grossquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
						$conn->prepare($grossquery)->execute(array($thisemployeeearnings, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '601',  $_SESSION['currentactiveperiod'], '1'));

						//NHIF Bands
								if ($thisemployeeearnings > 0 && $thisemployeeearnings < 5999) {
									$thisEmpNhif = 150;
								} elseif ($thisemployeeearnings > 5999 && $thisemployeeearnings <= 7999) {
									$thisEmpNhif = 300;
								} elseif ($thisemployeeearnings > 7999 && $thisemployeeearnings <= 11999) {
									$thisEmpNhif = 400;
								} elseif ($thisemployeeearnings > 11999 && $thisemployeeearnings <= 14999) {
									$thisEmpNhif = 500;
								} elseif ($thisemployeeearnings > 14999 && $thisemployeeearnings <= 19999) {
									$thisEmpNhif = 600;
								} elseif ($thisemployeeearnings > 19999 && $thisemployeeearnings <= 24999) {
									$thisEmpNhif = 750;
								} elseif ($thisemployeeearnings > 24999 && $thisemployeeearnings <= 29999) {
									$thisEmpNhif = 850;
								} elseif ($thisemployeeearnings > 29999 && $thisemployeeearnings <= 34999) {
									$thisEmpNhif = 900;
								} elseif ($thisemployeeearnings > 34999 && $thisemployeeearnings <= 39999) {
									$thisEmpNhif = 950;
								} elseif ($thisemployeeearnings > 39999 && $thisemployeeearnings <= 44999) {
									$thisEmpNhif = 1000;
								} elseif ($thisemployeeearnings > 44999 && $thisemployeeearnings <= 49999) {
									$thisEmpNhif = 1100;
								} elseif ($thisemployeeearnings > 49999 && $thisemployeeearnings <= 59999) {
									$thisEmpNhif = 1200;
								} elseif ($thisemployeeearnings > 59999 && $thisemployeeearnings <= 69999) {
									$thisEmpNhif = 1300;
								} elseif ($thisemployeeearnings > 69999 && $thisemployeeearnings <= 79999) {
									$thisEmpNhif = 1400;
								} elseif ($thisemployeeearnings > 79999 && $thisemployeeearnings <= 89999) {
									$thisEmpNhif = 1500;
								} elseif ($thisemployeeearnings > 89999 && $thisemployeeearnings <= 99999) {
									$thisEmpNhif = 1600;
								} elseif ($thisemployeeearnings > 99999) {
									$thisEmpNhif = 1700;
								}

							$nhifquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($nhifquery)->execute(array($thisEmpNhif, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '481',  $_SESSION['currentactiveperiod'], '1'));

						//NSSF is standard. No recalculation
							$thisemployeeNssfBand1 = 200;
						//Compute Taxable Income
							$thisEmpTaxablePay = $thisemployeeearnings - $thisemployeeNssfBand1;
							$taxpayquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($taxpayquery)->execute(array($thisEmpTaxablePay, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '400',  $_SESSION['currentactiveperiod'], '1'));

						//Compute PAYE
							$employeepayee = 0;
							$taxpay = $thisEmpTaxablePay;
							if ($taxpay > 0 && $taxpay <= 11180) {
								$employeepayee = $taxpay * 0.1;
							} elseif ($taxpay > 11180 && $taxpay <= 21714) {
								$employeepayee = (11180 * 0.1) + (($taxpay - 11180)*0.15);
							} elseif ($taxpay > 21714 && $taxpay <= 32248) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (($taxpay - 11181 - 10533)*0.2);
							} elseif ($taxpay > 32248 && $taxpay <= 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (($taxpay - 11181 - 10533 - 10534)*0.25);
							} elseif ($taxpay > 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (10534 * 0.25) + (($taxpay - 11181 - 10533 - 10534 - 10534)*0.3);
							}

							$taxcharged = $employeepayee;
							$taxchargequery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($taxchargequery)->execute(array($taxcharged, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '399',  $_SESSION['currentactiveperiod'], '1'));


							$finalEmployeePayee = $employeepayee - TAX_RELIEF;

							if ($finalEmployeePayee  <= 0) {
								$finalEmployeePayee = 0;
							}

							$taxpayequery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($taxpayequery)->execute(array($finalEmployeePayee, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '550',  $_SESSION['currentactiveperiod'], '1'));


						//Fetch and populate all deductions and write total
							$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
		                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', $_SESSION['currentactiveperiod'], '1'));
		                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
		                    $thisemployeeearnings = 0;

		                    foreach ($res as $row => $link) {
		                    	$thisemployeedeductions = $thisemployeedeductions + $link['amount'];
		                    }

		                    $recordtime = date('Y-m-d H:i:s');
		                    $deductionsquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($deductionsquery)->execute(array($thisemployeedeductions, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '603',  $_SESSION['currentactiveperiod'], '1'));

						//Calculate Net Salary
							$thisemployeeNet = $thisEmpTaxablePay - $thisemployeedeductions;

							$netquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($netquery)->execute(array($thisemployeeNet, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '600',  $_SESSION['currentactiveperiod'], '1'));


						$_SESSION['msg'] = 'Employee payroll re-run successful';
						$_SESSION['alertcolor'] = 'success';
						//echo $thisemployeeearnings;
						//exit("Re run");
						header('Location: ' . $source);
					}
					else {
						//new; insert records
						//Fetch and populate all taxable earnings and write total
							$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
		                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Earning', $_SESSION['currentactiveperiod'], '1'));
		                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
		                    $thisemployeeearnings = 0;

		                    foreach ($res as $row => $link) {
		                    	$thisemployeeearnings = $thisemployeeearnings + $link['amount'];
		                    }

		                    $recordtime = date('Y-m-d H:i:s');
		                    $grossquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($grossquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '601', $thisemployeeearnings, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//Get initial statutories - NHIF, NSSF, Tax relief
							//NHIF Bands
								if ($thisemployeeearnings > 0 && $thisemployeeearnings < 5999) {
									$thisEmpNhif = 150;
								} elseif ($thisemployeeearnings > 5999 && $thisemployeeearnings <= 7999) {
									$thisEmpNhif = 300;
								} elseif ($thisemployeeearnings > 7999 && $thisemployeeearnings <= 11999) {
									$thisEmpNhif = 400;
								} elseif ($thisemployeeearnings > 11999 && $thisemployeeearnings <= 14999) {
									$thisEmpNhif = 500;
								} elseif ($thisemployeeearnings > 14999 && $thisemployeeearnings <= 19999) {
									$thisEmpNhif = 600;
								} elseif ($thisemployeeearnings > 19999 && $thisemployeeearnings <= 24999) {
									$thisEmpNhif = 750;
								} elseif ($thisemployeeearnings > 24999 && $thisemployeeearnings <= 29999) {
									$thisEmpNhif = 850;
								} elseif ($thisemployeeearnings > 29999 && $thisemployeeearnings <= 34999) {
									$thisEmpNhif = 900;
								} elseif ($thisemployeeearnings > 34999 && $thisemployeeearnings <= 39999) {
									$thisEmpNhif = 950;
								} elseif ($thisemployeeearnings > 39999 && $thisemployeeearnings <= 44999) {
									$thisEmpNhif = 1000;
								} elseif ($thisemployeeearnings > 44999 && $thisemployeeearnings <= 49999) {
									$thisEmpNhif = 1100;
								} elseif ($thisemployeeearnings > 49999 && $thisemployeeearnings <= 59999) {
									$thisEmpNhif = 1200;
								} elseif ($thisemployeeearnings > 59999 && $thisemployeeearnings <= 69999) {
									$thisEmpNhif = 1300;
								} elseif ($thisemployeeearnings > 69999 && $thisemployeeearnings <= 79999) {
									$thisEmpNhif = 1400;
								} elseif ($thisemployeeearnings > 79999 && $thisemployeeearnings <= 89999) {
									$thisEmpNhif = 1500;
								} elseif ($thisemployeeearnings > 89999 && $thisemployeeearnings <= 99999) {
									$thisEmpNhif = 1600;
								} elseif ($thisemployeeearnings > 99999) {
									$thisEmpNhif = 1700;
								}

			                    $nhifquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
								$conn->prepare($nhifquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', '481', $thisEmpNhif, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//NSSF Band Calculation
							$thisemployeeNssfBand1 = 200;

							/*$thisemployeeNssfBand1 = $thisemployeeearnings * 0.06;
							if ($thisemployeeNssfBand1 > 360) {
								$thisemployeeNssfBand1 = 360;
							}*/
							$nssfquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($nssfquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', '482', $thisemployeeNssfBand1, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//Compute Taxable Income
							$thisEmpTaxablePay = $thisemployeeearnings - $thisemployeeNssfBand1;
							$taxpayquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($taxpayquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '400', $thisEmpTaxablePay, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						
						//Compute PAYE
							$employeepayee = 0;
							$taxpay = $thisEmpTaxablePay;
							if ($taxpay > 0 && $taxpay <= 11180) {
								$employeepayee = $taxpay * 0.1;
							} elseif ($taxpay > 11180 && $taxpay <= 21714) {
								$employeepayee = (11180 * 0.1) + (($taxpay - 11180)*0.15);
							} elseif ($taxpay > 21714 && $taxpay <= 32248) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (($taxpay - 11181 - 10533)*0.2);
							} elseif ($taxpay > 32248 && $taxpay <= 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (($taxpay - 11181 - 10533 - 10534)*0.25);
							} elseif ($taxpay > 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (10534 * 0.25) + (($taxpay - 11181 - 10533 - 10534 - 10534)*0.3);
							}

							$taxcharged = $employeepayee;
							$taxchargequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($taxchargequery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '399', $taxcharged, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

							$finalEmployeePayee = $employeepayee - TAX_RELIEF;

							if ($finalEmployeePayee  <= 0) {
								$finalEmployeePayee = 0;
							}

							$taxpayequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($taxpayequery)->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', '550', $finalEmployeePayee, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));


						//Fetch and populate all deductions and write total
							$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
		                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', $_SESSION['currentactiveperiod'], '1'));
		                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
		                    $thisemployeedeductions = 0;

		                    foreach ($res as $row => $link) {
		                    	$thisemployeedeductions = $thisemployeedeductions + $link['amount'];
		                    }

		                    $recordtime = date('Y-m-d H:i:s');
		                    $deductionsquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($deductionsquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '603', $thisemployeedeductions, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//Calculate Net Salary
							$thisemployeeNet = $thisEmpTaxablePay - $thisemployeedeductions;

							$netquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($netquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '600', $thisemployeeNet, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						$_SESSION['msg'] = 'Employee payroll run successful';
						$_SESSION['alertcolor'] = 'success';
						header('Location: ' . $source);

					}	
				}

				catch(PDOException $e){
					echo $e->getMessage();
				}
			}
			
			

		break;


		case 'runGlobalPayroll':
			//Check all employees on missing basic salaries. If return error. --- Check done on submitting page

			//Check if first period run, or its a rerun
				//exit($_SESSION['companyid');
				$query = $conn->prepare('SELECT payrollRun FROM payperiods WHERE periodId = ? AND companyId = ? AND active = ? AND payrollRun = ? ');
				$rerun = $query->execute(array($_SESSION['currentactiveperiod'], $_SESSION['companyid'], '1', '0'));

				if ($row = $query->fetch()) {
					//New run
					//Delete all computed figures for this period and copany
						$globalsql = $conn->prepare('DELETE FROM employee_earnings_deductions WHERE companyId = ? AND transactionType = ? OR transactionType = ? OR earningDeductionCode = ? OR earningDeductionCode = ? OR earningDeductionCode = ? AND payPeriod = ?');
						$globalsql->execute(array($_SESSION['companyid'], 'Calc', 'Notax', '481', '482', '550', $_SESSION['currentactiveperiod']));
					
					//Cycle through employees & execute payroll

							$query = $conn->prepare('SELECT empNumber FROM employees WHERE companyId = ? AND active =? ORDER BY id ASC');
		                    $query->execute(array($_SESSION['companyid'], '1'));
		                    $ftres = $query->fetchAll(PDO::FETCH_COLUMN);
		                    $employeecount = $query->rowCount();
		                    //print($employeecount . "<br />");
		                    //print_r($ftres);
		                    //exit();

		                    $counter = 0;
		                    //$missingbasic = 0;
		                    //$setbasic = 0;

		                    while ($counter < $employeecount) {
		                        //echo $ftres[$counter] . "<br /> ";
		                        $thisemployee = $ftres[$counter];


		                        	//Fetch and populate all taxable earnings and write total
									$equery = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
				                    $fin = $equery->execute(array($thisemployee, $_SESSION['companyid'], 'Earning', $_SESSION['currentactiveperiod'], '1'));
				                    $res = $equery->fetchAll(PDO::FETCH_ASSOC);
				                    $thisemployeeearnings = 0;

				                    foreach ($res as $row => $link) {
				                    	$thisemployeeearnings = $thisemployeeearnings + $link['amount'];
				                    }

				                    $recordtime = date('Y-m-d H:i:s');
				                    $grossquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($grossquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '601', $thisemployeeearnings, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								//Get initial statutories - NHIF, NSSF, Tax relief
									//NHIF Bands
										if ($thisemployeeearnings > 0 && $thisemployeeearnings < 5999) {
											$thisEmpNhif = 150;
										} elseif ($thisemployeeearnings > 5999 && $thisemployeeearnings <= 7999) {
											$thisEmpNhif = 300;
										} elseif ($thisemployeeearnings > 7999 && $thisemployeeearnings <= 11999) {
											$thisEmpNhif = 400;
										} elseif ($thisemployeeearnings > 11999 && $thisemployeeearnings <= 14999) {
											$thisEmpNhif = 500;
										} elseif ($thisemployeeearnings > 14999 && $thisemployeeearnings <= 19999) {
											$thisEmpNhif = 600;
										} elseif ($thisemployeeearnings > 19999 && $thisemployeeearnings <= 24999) {
											$thisEmpNhif = 750;
										} elseif ($thisemployeeearnings > 24999 && $thisemployeeearnings <= 29999) {
											$thisEmpNhif = 850;
										} elseif ($thisemployeeearnings > 29999 && $thisemployeeearnings <= 34999) {
											$thisEmpNhif = 900;
										} elseif ($thisemployeeearnings > 34999 && $thisemployeeearnings <= 39999) {
											$thisEmpNhif = 950;
										} elseif ($thisemployeeearnings > 39999 && $thisemployeeearnings <= 44999) {
											$thisEmpNhif = 1000;
										} elseif ($thisemployeeearnings > 44999 && $thisemployeeearnings <= 49999) {
											$thisEmpNhif = 1100;
										} elseif ($thisemployeeearnings > 49999 && $thisemployeeearnings <= 59999) {
											$thisEmpNhif = 1200;
										} elseif ($thisemployeeearnings > 59999 && $thisemployeeearnings <= 69999) {
											$thisEmpNhif = 1300;
										} elseif ($thisemployeeearnings > 69999 && $thisemployeeearnings <= 79999) {
											$thisEmpNhif = 1400;
										} elseif ($thisemployeeearnings > 79999 && $thisemployeeearnings <= 89999) {
											$thisEmpNhif = 1500;
										} elseif ($thisemployeeearnings > 89999 && $thisemployeeearnings <= 99999) {
											$thisEmpNhif = 1600;
										} elseif ($thisemployeeearnings > 99999) {
											$thisEmpNhif = 1700;
										}

					                    $nhifquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
										$conn->prepare($nhifquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', '481', $thisEmpNhif, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								//NSSF Band Calculation
									$thisemployeeNssfBand1 = 200;

									/*$thisemployeeNssfBand1 = $thisemployeeearnings * 0.06;
									if ($thisemployeeNssfBand1 > 360) {
										$thisemployeeNssfBand1 = 360;
									}*/
									$nssfquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($nssfquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', '482', $thisemployeeNssfBand1, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								//Compute Taxable Income
									$thisEmpTaxablePay = $thisemployeeearnings - $thisemployeeNssfBand1;
									$taxpayquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($taxpayquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '400', $thisEmpTaxablePay, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								
								//Compute PAYE
									$employeepayee = 0;
									$taxpay = $thisEmpTaxablePay;
									if ($taxpay > 0 && $taxpay <= 11180) {
										$employeepayee = $taxpay * 0.1;
									} elseif ($taxpay > 11180 && $taxpay <= 21714) {
										$employeepayee = (11180 * 0.1) + (($taxpay - 11180)*0.15);
									} elseif ($taxpay > 21714 && $taxpay <= 32248) {
										$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (($taxpay - 11181 - 10533)*0.2);
									} elseif ($taxpay > 32248 && $taxpay <= 42782) {
										$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (($taxpay - 11181 - 10533 - 10534)*0.25);
									} elseif ($taxpay > 42782) {
										$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (10534 * 0.25) + (($taxpay - 11181 - 10533 - 10534 - 10534)*0.3);
									}

									$taxcharged = $employeepayee;
									$taxchargequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($taxchargequery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '399', $taxcharged, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));


									$finalEmployeePayee = $employeepayee - TAX_RELIEF;

									if ($finalEmployeePayee  <= 0) {
										$finalEmployeePayee = 0;
									}

									$taxpayequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($taxpayequery)->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', '550', $finalEmployeePayee, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));


								//Fetch and populate all deductions and write total
									$dedquery = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
				                    $fin = $dedquery->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', $_SESSION['currentactiveperiod'], '1'));
				                    $res = $dedquery->fetchAll(PDO::FETCH_ASSOC);
				                    $thisemployeedeductions = 0;

				                    foreach ($res as $row => $link) {
				                    	$thisemployeedeductions = $thisemployeedeductions + $link['amount'];
				                    }

				                    $recordtime = date('Y-m-d H:i:s');
				                    $deductionsquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($deductionsquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '603', $thisemployeedeductions, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));
									//exit($thisemployee . "," . $thisemployeedeductions);

								//Calculate Net Salary
									$thisemployeeNet = $thisEmpTaxablePay - $thisemployeedeductions;									
									$netquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($netquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '600', $thisemployeeNet, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								$counter++;
		                    }
		            //end employee cycle

					//change payroll run flag::::::::::::::::::::::::::::::::::::::::::::::::
		                $periodsql = $conn->prepare('UPDATE payperiods SET payrollRun = ? WHERE periodId = ? AND companyId = ? AND active = ? AND payrollRun = ?');
						$periodsql->execute (array('1', $_SESSION['currentactiveperiod'], $_SESSION['companyid'], '1', '0'));


		                $_SESSION['msg'] = 'Employee payroll run successful';
						$_SESSION['alertcolor'] = 'success';
						header('Location: ' . $source);

					//exit('New run');

				} 
				else {
					//Rerun
						//Delete all computed figures for this period and copany
						$globalsql = $conn->prepare('DELETE FROM employee_earnings_deductions WHERE companyId = ? AND transactionType = ? OR transactionType = ? OR earningDeductionCode = ? OR earningDeductionCode = ? OR earningDeductionCode = ? AND payPeriod = ?');
						$globalsql->execute (array($_SESSION['companyid'], 'Calc', 'Notax', '481', '482', '550', $_SESSION['currentactiveperiod']));

						//Emplyee cycle
						$query = $conn->prepare('SELECT empNumber FROM employees WHERE companyId = ? AND active =? ORDER BY id ASC');
	                    $query->execute (array($_SESSION['companyid'], '1'));
	                    $ftres = $query->fetchAll(PDO::FETCH_COLUMN);
	                    $employeecount = $query->rowCount();
	                    //print($employeecount . "<br />");
	                    //print_r($ftres);

	                    $counter = 0;
	                    //$missingbasic = 0;
	                    //$setbasic = 0;

	                    while ($counter < $employeecount) {
	                        //echo $ftres[$counter] . "<br /> ";
	                    		$thisemployee = $ftres[$counter];


		                        	//Fetch and populate all taxable earnings and write total
									$equery = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
				                    $fin = $equery->execute (array($thisemployee, $_SESSION['companyid'], 'Earning', $_SESSION['currentactiveperiod'], '1'));
				                    $res = $equery->fetchAll(PDO::FETCH_ASSOC);
				                    $thisemployeeearnings = 0;

				                    foreach ($res as $row => $link) {
				                    	$thisemployeeearnings = $thisemployeeearnings + $link['amount'];
				                    }

				                    $recordtime = date('Y-m-d H:i:s');
				                    $grossquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($grossquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '601', $thisemployeeearnings, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								//Get initial statutories - NHIF, NSSF, Tax relief
									//NHIF Bands
										if ($thisemployeeearnings > 0 && $thisemployeeearnings < 5999) {
											$thisEmpNhif = 150;
										} elseif ($thisemployeeearnings > 5999 && $thisemployeeearnings <= 7999) {
											$thisEmpNhif = 300;
										} elseif ($thisemployeeearnings > 7999 && $thisemployeeearnings <= 11999) {
											$thisEmpNhif = 400;
										} elseif ($thisemployeeearnings > 11999 && $thisemployeeearnings <= 14999) {
											$thisEmpNhif = 500;
										} elseif ($thisemployeeearnings > 14999 && $thisemployeeearnings <= 19999) {
											$thisEmpNhif = 600;
										} elseif ($thisemployeeearnings > 19999 && $thisemployeeearnings <= 24999) {
											$thisEmpNhif = 750;
										} elseif ($thisemployeeearnings > 24999 && $thisemployeeearnings <= 29999) {
											$thisEmpNhif = 850;
										} elseif ($thisemployeeearnings > 29999 && $thisemployeeearnings <= 34999) {
											$thisEmpNhif = 900;
										} elseif ($thisemployeeearnings > 34999 && $thisemployeeearnings <= 39999) {
											$thisEmpNhif = 950;
										} elseif ($thisemployeeearnings > 39999 && $thisemployeeearnings <= 44999) {
											$thisEmpNhif = 1000;
										} elseif ($thisemployeeearnings > 44999 && $thisemployeeearnings <= 49999) {
											$thisEmpNhif = 1100;
										} elseif ($thisemployeeearnings > 49999 && $thisemployeeearnings <= 59999) {
											$thisEmpNhif = 1200;
										} elseif ($thisemployeeearnings > 59999 && $thisemployeeearnings <= 69999) {
											$thisEmpNhif = 1300;
										} elseif ($thisemployeeearnings > 69999 && $thisemployeeearnings <= 79999) {
											$thisEmpNhif = 1400;
										} elseif ($thisemployeeearnings > 79999 && $thisemployeeearnings <= 89999) {
											$thisEmpNhif = 1500;
										} elseif ($thisemployeeearnings > 89999 && $thisemployeeearnings <= 99999) {
											$thisEmpNhif = 1600;
										} elseif ($thisemployeeearnings > 99999) {
											$thisEmpNhif = 1700;
										}

					                    $nhifquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
										$conn->prepare($nhifquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', '481', $thisEmpNhif, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								//NSSF Band Calculation
									$thisemployeeNssfBand1 = 200;

									/*$thisemployeeNssfBand1 = $thisemployeeearnings * 0.06;
									if ($thisemployeeNssfBand1 > 360) {
										$thisemployeeNssfBand1 = 360;
									}*/
									$nssfquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($nssfquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', '482', $thisemployeeNssfBand1, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								//Compute Taxable Income
									$thisEmpTaxablePay = $thisemployeeearnings - $thisemployeeNssfBand1;
									$taxpayquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($taxpayquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '400', $thisEmpTaxablePay, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								
								//Compute PAYE
									$employeepayee = 0;
									$taxpay = $thisEmpTaxablePay;
									if ($taxpay < 11181) {
										$employeepayee = $taxpay * 0.1;
									} elseif ($taxpay > 11181 && $taxpay < 21714) {
										$employeepayee = (11181 * 0.1) + (($taxpay - 11181)*0.15);
									} elseif ($taxpay > 21714 && $taxpay < 32248) {
										$employeepayee = (11181 * 0.1) + (10533 * 0.15) + (($taxpay - 11181 - 10533)*0.2);
									} elseif ($taxpay > 32248 && $taxpay < 42782) {
										$employeepayee = (11181 * 0.1) + (10533 * 0.15) + (10534 * 0.2) + (($taxpay - 11181 - 10533 - 10534)*0.25);
									} elseif ($taxpay > 42782) {
										$employeepayee = $taxpay * 0.3;
									}

									$taxcharged = $employeepayee;
									$taxchargequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($taxchargequery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '399', $taxcharged, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));


									$finalEmployeePayee = $employeepayee - 1162;

									if ($finalEmployeePayee  <= 0) {
										$finalEmployeePayee = 0;
									}

									$taxpayequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($taxpayequery)->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', '550', $finalEmployeePayee, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));


								//Fetch and populate all deductions and write total
									$dedquery = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
				                    $fin = $dedquery->execute (array($thisemployee, $_SESSION['companyid'], 'Deduction', $_SESSION['currentactiveperiod'], '1'));
				                    $res = $dedquery->fetchAll(PDO::FETCH_ASSOC);
				                    $thisemployeedeductions = 0;

				                    foreach ($res as $row => $link) {
				                    	$thisemployeedeductions = $thisemployeedeductions + $link['amount'];
				                    }

				                    $recordtime = date('Y-m-d H:i:s');
				                    $deductionsquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($deductionsquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '603', $thisemployeedeductions, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));
									//exit($thisemployee . "," . $thisemployeedeductions);

								//Calculate Net Salary
									$thisemployeeNet = $thisEmpTaxablePay - $thisemployeedeductions;									
									$netquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($netquery)->execute (array($thisemployee, $_SESSION['companyid'], 'Calc', '600', $thisemployeeNet, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

								$counter++;
	                    }

						$_SESSION['msg'] = 'Employee payroll run successful';
						$_SESSION['alertcolor'] = 'success';
						header('Location: ' . $source);
				}

			exit($_SESSION['companyid'] . 'Entire Employee Run');
		break;


		case 'addNewLeave':
			//check for existing same employee number

			$empnumber = filter_var($_POST['empnumber'], FILTER_SANITIZE_STRING);
			$leavetype = filter_var($_POST['leavetype'], FILTER_SANITIZE_STRING);
			$startleave = date('Y-m-d', strtotime(filter_var($_POST['startleave'], FILTER_SANITIZE_STRING)));
				$day1 = strtotime(filter_var($_POST['startleave'], FILTER_SANITIZE_STRING));
			$endleave = date('Y-m-d', strtotime(filter_var($_POST['endleave'], FILTER_SANITIZE_STRING)));
				$day2 = strtotime(filter_var($_POST['endleave'], FILTER_SANITIZE_STRING));

			$days_diff = $day2 - $day1;
    		$numofdays = date('d',$days_diff);

			$currdate = date('Y-m-d');
			//validate for empty mandatory fields

			try{
				//check for same leave request for same staffer
				$leavequery = $conn->prepare('SELECT * FROM hr_leave_requests WHERE employeeNumber = ? AND leaveType = ? AND status = ? OR status = ? AND active = ?');
				$res = $leavequery->execute (array($empnumber, $leavetype, '1', '2', '1'));

				if ($row = $leavequery->fetch()) {
					$_SESSION['msg'] = $msg = "Active / Pending similar leave type for this employee. Please review all approved or pending leave requests.";
					$_SESSION['alertcolor'] = 'danger';
					header('Location: ' . $source);
				} else {
					$query = 'INSERT INTO hr_leave_requests (employeeNumber, leaveType, fromDate, toDate, applicationDate, numberOfDays, status) VALUES (?,?,?,?,?,?,?)';

					$conn->prepare($query)->execute (array($empnumber, $leavetype, $startleave, $endleave, $currdate, $numofdays, '2'));
					
					$_SESSION['msg'] = $msg = "New Leave Successfully added.";
					$_SESSION['alertcolor'] = 'success';
					header('Location: ' . $source);
				}

			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'manageLeave':

			$empalternumber = filter_var($_POST['empalternumber'], FILTER_SANITIZE_STRING);
			$empalterid = filter_var($_POST['empalterid'], FILTER_SANITIZE_STRING);
			$leaveaction = filter_var($_POST['leaveaction'], FILTER_SANITIZE_STRING);
				//exit($empalternumber . ",". $empalterid. "," .$leaveaction);

			try{

				$query = ('UPDATE hr_leave_requests SET status = ? WHERE id = ? AND employeeNumber = ?');
				$conn->prepare($query)->execute (array($leaveaction, $empalterid, $empalternumber));

				$_SESSION['msg'] = $msg = "Leave status successfully amended";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);

			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'deactivateEmployee':
			$empalterid = filter_var($_POST['empalterid'], FILTER_VALIDATE_INT);
			$empalternumber = filter_var($_POST['empalternumber'], FILTER_SANITIZE_STRING);
			$exitdate = date('Y-m-d', strtotime(filter_var($_POST['exitdate'], FILTER_SANITIZE_STRING)));
			$exitreason = filter_var($_POST['exitreason'], FILTER_SANITIZE_STRING);
			$editDate = date('Y-m-d H:i:s');

			//exit($empalternumber . ", " . $empalterid . ", " . $exitdate . ", " . $exitreason);
			$query = 'UPDATE employees SET active = ? WHERE id = ? AND companyId = ? AND active = ?';
			$conn->prepare($query)->execute (array('0', $empalterid, $_SESSION['companyid'], '1'));

				$deactivatequery = 'INSERT INTO hr_exited_employees (employeeId, exitDate, exitReason, editTime, userEditorId) VALUES (?,?,?,?,?)';
				$conn->prepare($deactivatequery)->execute (array($empalternumber, $exitdate, $exitreason, $editDate, $_SESSION['user']));
			
			$_SESSION['msg'] = $msg = "Employee successfully deactivated.";
			$_SESSION['alertcolor'] = 'success';
			header('Location: ' . $source);
		break;


		case 'suspendEmployee':
			$empalterid = filter_var($_POST['empalterid'], FILTER_VALIDATE_INT);
			$empalternumber = filter_var($_POST['empalternumber'], FILTER_SANITIZE_STRING);
			$startsuspension = date('Y-m-d', strtotime(filter_var($_POST['startsuspension'], FILTER_SANITIZE_STRING)));
			$endsuspension = date('Y-m-d', strtotime(filter_var($_POST['endsuspension'], FILTER_SANITIZE_STRING)));
			$suspendreason = filter_var($_POST['suspendreason'], FILTER_SANITIZE_STRING);
			$editDate = date('Y-m-d H:i:s');

			try{
				
				$susquery = $conn->prepare('SELECT * FROM employees WHERE empNumber = ? AND companyId = ? AND active = ? AND suspended = ?');
				$fin = $susquery->execute (array($empalternumber, $_SESSION['companyid'], '1', '1'));

				if ($row = $susquery->fetch()){
					$_SESSION['msg'] = "Selected employee currently on suspension.";
					$_SESSION['alertcolor'] = "danger";
					header('Location: ' . $source);
				} else {
					//exit($empalternumber . ", " . $empalterid . ", " . $exitdate . ", " . $exitreason);
					$query = 'UPDATE employees SET suspended = ? WHERE empNumber = ? AND companyId = ? AND active = ? AND suspended = ?';
					$conn->prepare($query)->execute (array('1', $empalternumber, $_SESSION['companyid'], '1', '0'));

						$deactivatequery = 'INSERT INTO employee_suspensions (employeeId, suspendStartDate, suspendEndDate, suspenReason, editTime, userEditorId) VALUES (?,?,?,?,?,?)';
						$conn->prepare($deactivatequery)->execute (array($empalternumber, $startsuspension, $endsuspension, $suspendreason, $editDate, $_SESSION['user']));
					
					$_SESSION['msg'] = $msg = "Employee successfully suspended.";
					$_SESSION['alertcolor'] = 'success';
					header('Location: ' . $source);
				}

			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'editemployeeearning':
			//exit('Edit Employee Earning');
			$empedit = filter_var($_POST['empeditnum'], FILTER_SANITIZE_STRING);
			$edited = filter_var($_POST['edited'], FILTER_VALIDATE_INT);
			$editname = filter_var($_POST['editname'], FILTER_VALIDATE_INT);
			$editvalue = filter_var($_POST['editvalue'], FILTER_VALIDATE_INT);
			$grossquery = 'UPDATE employee_earnings_deductions SET amount = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
			$conn->prepare($grossquery)->execute (array($editvalue, $empedit, $_SESSION['companyid'], $edited,  $_SESSION['currentactiveperiod'], '1'));

			$_SESSION['msg'] = 'Successfully Edited Earning / Deduction';
			$_SESSION['alertcolor'] = 'success';
			header('Location: ' . $source);
		break;


		case 'deactivateEd':
			$empeditnum = filter_var($_POST['empeditnum'], FILTER_SANITIZE_STRING);
			$edited = filter_var($_POST['edited'], FILTER_VALIDATE_INT);
			//exit($empeditnum . " " . $edited . " " . $_SESSION['currentactiveperiod');
			try{
				$query = 'UPDATE employee_earnings_deductions SET active = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
				$conn->prepare($query)->execute (array('0', $empeditnum, $_SESSION['companyid'], $edited, $_SESSION['currentactiveperiod'], '1'));

				$_SESSION['msg'] = $msg = "E/D successfully deactivated.";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'batchprocess':
			exit('Batch Process');
		break;


		case 'resetpass':
			//exit('reset');

			$title = "Password Reset";
			$resetemail = filter_var((filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);

			//check if account exists with emailaddress
			$query = $conn->prepare('SELECT emailAddress FROM users WHERE emailAddress = ? AND active = ?');
			$fin = $query->execute (array($resetemail, '1'));

			if ($row = $query->fetch()) {

				//Generate update token
				$reset_token = bin2hex(openssl_random_pseudo_bytes(32));
				
				//write token to token table and assign validity state, creation timestamp
				$tokenrecordtime = date('Y-m-d H:i:s');

				//check for any previous tokens and invalidate
					$tokquery = $conn->prepare('SELECT * FROM reset_token WHERE userEmail = ? AND valid = ? AND type = ?');
					$fin = $tokquery->execute (array($resetemail, '1', '1'));
					
					if($row = $tokquery->fetch()){
						$upquery = 'UPDATE reset_token SET valid = ? WHERE userEmail = ? AND valid = ?';
						$conn->prepare($upquery)->execute (array('0', $resetemail, '1'));
					}

				$tokenquery = 'INSERT INTO reset_token (userEmail, token, creationTime, valid, type) VALUES (?,?,?,?,?)';
				$conn->prepare($tokenquery)->execute (array($resetemail, $reset_token, $tokenrecordtime, '1', '1'));
					
				//exit($resetemail . " " . $reset_token);
				
				$sendmessage = "You've recently asked to reset the password for this Redsphere Payroll account: " . $resetemail . "<br /><br />To update your password, click the link below:<br /><br /> " . $sysurl . 'password_reset.php?token=' . $reset_token;
				//generate reset cdde and append to email submitted

				require 'phpmailer/PHPMailerAutoload.php';

				$mail = new PHPMailer;

				$mail->SMTPDebug = 3;                               // Enable verbose debug output

				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = 'smtp.zoho.com';  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = 'noreply@redsphere.co.ke';                 // SMTP username
				$mail->Password = 'redsphere_2017***';                           // SMTP password
				$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 587;                                    // TCP port to connect to

				$mail->setFrom('noreply@redsphere.co.ke', 'Redsphere Payroll');
				$mail->addAddress($resetemail, 'Redsphere Payroll');     // Add a recipient
				//$mail->addAddress('ellen@example.com');               // Name is optional
				$mail->addReplyTo('info@example.com', 'Information');
				$mail->addCC('fgesora@gmail.com');
				//$mail->addBCC('bcc@example.com');

				//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
				$mail->isHTML(true);                                  // Set email format to HTML

				$mail->Subject = $title;
				$mail->Body    = $sendmessage;
				$mail->AltBody = $sendmessage;

				if(!$mail->send()) {
					//exit($mail->ErrorInfo);
				    echo 'Mailer Error: ' . $mail->ErrorInfo;
				    $_SESSION['msg'] = "Failed. Error sending email.";
				    $_SESSION['alertcolor'] = "danger";
				    header("Location: " . $source);
				} else {
				    $status = "Success";
				    $_SESSION['msg'] = "If there is an account associated with this email address, an email has been sent to reset your password.";
				    $_SESSION['alertcolor'] = "success";
				    header("Location: " . $source);
				}

			} else {

				$_SESSION['msg'] = "If there is an account associated with this email address, an email has been sent to reset your password.";
			    $_SESSION['alertcolor'] = "success";
			    header("Location: " . $source);
			}
			
		break;



		case 'deactivateuser':
			$thisuser = filter_var($_POST['thisuser'], FILTER_SANITIZE_STRING);
			$useremail = filter_var($_POST['useremail'], FILTER_SANITIZE_STRING);

			try{
				$query = 'UPDATE users SET active = ? WHERE userId = ? AND emailAddress = ? AND companyId = ? AND active = ?';
				$conn->prepare($query)->execute (array('0', $thisuser, $useremail, $_SESSION['companyid'], '1'));

				$_SESSION['msg'] = $msg = "User successfully deactivated.";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;



		case 'logout':
			$_SESSION['logged_in'] = '0';
			unset($_SESSION['user']);
			unset($_SESSION['email']);
    		unset($_SESSION['first_name']);
    		unset($_SESSION['last_name']);
    		unset($_SESSION['companyid']);
    		unset($_SESSION['emptrack']);
    		unset($_SESSION['currentactiveperiod']);
    		unset($_SESSION['activeperiodDescription']);
    		unset($_SESSION['msg']);
    		unset($_SESSION['alertcolor']);
    		unset($_SESSION['empDataTrack']);
    		unset($_SESSION['emptNumTack']);
    		
    		if (isset($_SESSION['leavestate'])) {
    			unset($_SESSION['leavestate']);
    		}

    		if (isset($_SESSION['periodstatuschange'])) {
    			unset($_SESSION['periodstatuschange']);
    		}

    		//reset global openview status
			$statuschange = $conn->prepare('UPDATE payperiods SET openview = ? ');
			$perres = $statuschange->execute (array('0'));

    		$_SESSION['msg'] = $msg = "Successfully logged out";
    		$_SESSION['alertcolor'] = $type = "success";
    		$page = "../../index.php";
			header('Location: ' . $page);
		break;

		
		default:
			exit('Unexpected route. Please contact administrator.');
		break;
	}