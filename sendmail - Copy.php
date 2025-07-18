<?php
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
include_once('Connections/cov.php');
include_once('model.php');

function sendmail($memberid,$period){

$savings =   retrieveDescDualFilter("tlb_mastertransaction","savings","memberid",$memberid,"periodid",$period);
$savings= number_format($savings,2); 

$savingsB =   retrieveDescDualFilterLessThan("tlb_mastertransaction","savings","memberid",$memberid,"periodid",$period);
$savingsB= number_format($savingsB,2); 

$shares = retrieveDescDualFilter("tlb_mastertransaction","shares","memberid",$memberid,"periodid",$period);
$shares = number_format($shares,2);

$sharesB = retrieveDescDualFilterLessThan("tlb_mastertransaction","shares","memberid",$memberid,"periodid",$period);
$sharesB = number_format($sharesB,2);


$loanRepayment = retrieveDescDualFilter("tlb_mastertransaction","loanRepayment","memberid",$memberid,"periodid",$period);
$loanRepayment = number_format($loanRepayment,2);


$loanAmount = retrieveDescDualFilter("tlb_mastertransaction","loanAmount","memberid",$memberid,"periodid",$period);
$loanAmount = number_format($loanAmount,2);


$loanAmountB = retrieveDescDualFilterLessThan("tlb_mastertransaction","loanAmount","memberid",$memberid,"periodid",$period);

$loanrRepaymentB = retrieveDescDualFilterLessThan("tlb_mastertransaction","loanRepayment","memberid",$memberid,"periodid",$period);


$loanBalance = $loanAmountB -$loanrRepaymentB ;
$loanBalance = number_format($loanBalance,2);


$interest = retrieveDescDualFilter("tlb_mastertransaction","interest","memberid",$memberid,"periodid",$period);
$interest = number_format($interest,2);

$interestB = retrieveDescDualFilterLessThan("tlb_mastertransaction","interest","memberid",$memberid,"periodid",$period);


$interestPaid = retrieveDescDualFilter("tlb_mastertransaction","interestPaid","memberid",$memberid,"periodid",$period);
$interestPaid = number_format($interestPaid,2);

$interestPaidB = retrieveDescDualFilterLessThan("tlb_mastertransaction","interestPaid","memberid",$memberid,"periodid",$period);
$unpaid_interest = $interestB - $interestPaidB;  

$unpaid_interest = number_format($unpaid_interest,2);

$withdrawal = retrieveDescDualFilter("tlb_mastertransaction","withdrawal","memberid",$memberid,"periodid",$period);

$withdrawal = number_format($withdrawal,2);

$total = str_replace(",", "", $shares)+str_replace(",", "", $savings)+str_replace(",", "", $interestPaid)+str_replace(",", "", $loanRepayment); 

$total = number_format(round($total),2); 


$emaddress = retrieveSingFilterString("tbl_personalinfo", "EmailAddress", "memberid",$memberid);


$first_name = retrieveSingFilterString("tbl_personalinfo", "Lname", "memberid",$memberid);

$period = retrieveSingFilterString("tbpayrollperiods", "PayrollPeriod", "Periodid",$period);

$tody = date("d/m/Y");

$message = "<table cellspacing='0' cellpadding='0' width='689' border='0'>
  <tbody>
    <tr>
      <td colspan='7' height='97'>
        <div align='left'>
         
        </div>
      </td>
      <td colspan='3' height='97'>
        <div align='right'>
          <img height='87' src='https://cov.emmaggi.com/source/logo.jpg' width='445'>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan='2'>
        &nbsp;
      </td>
      <td colspan='8'>
        <div align='right'>
          {$tody}
        </div>
      </td>
    </tr>
    <tr>
      <td colspan='10'>
        Dear &nbsp; <strong>{$first_name}</strong>
      </td>
    </tr>
    <tr>
      <td colspan='10'></td>
    </tr>
    <tr>
      <td colspan='10'>
        <h4>
          <u>VCMS eLectronic Notification Service
          (VeNS)</u>
        </h4>
      </td>
    </tr>
    <tr>
      <td colspan='10'></td>
    </tr>
    <tr>
      <td colspan='10'>
        We wish to inform you that a transaction
        occurred on your cooperative account.
      </td>
    </tr>
    <tr>
      <td colspan='10'>
        &nbsp;
      </td>
    </tr>
    <tr>
      <td colspan='10'>
        The details of this transaction are shown below:
      </td>
    </tr>
    <tr>
      <td colspan='10'></td>
    </tr>
    <tr>
      <td colspan='10'>
        <h4>
          <u>Transaction Notification</u>
        </h4>
      </td>
    </tr>
    <tr>
	<td>
	<table width='720px' border='0'>
	<tbody><tr>
	 <td width='130'>
        COOP Number
      </td>
      <td width='10'>
        :
      </td>
      <td colspan='8' width='549'>
        {$memberid}
      </td>
    </tr>
   <tr>
      <td width='130' height='19'>
        Savings
      </td>
      <td>
        :
      </td>
      <td colspan='8'>
        NGN &nbsp; {$savings}
      </td>
    </tr>
    <tr>
      <td width='130' height='19'>
        <strong>Savings Balance</strong>
      </td>
      <td>
        :
      </td>
      <td colspan='8'>
        NGN &nbsp; {$savingsB}
      </td>
    </tr>
    
    <tr>
      <td width='130'>
        Shares
      </td>
      <td>
        :
      </td>
      <td colspan='8'>
        NGN &nbsp; {$shares}
      </td>
    </tr>
    <tr>
      <td width='130'>
       <strong> Shares Balance </strong>
      </td>
      <td>
        :
      </td>
      <td colspan='8'>
        NGN &nbsp; {$sharesB}
      </td>
    </tr>
    
	<tr>
	      <td width='130'>
	        Loan Amount
	      </td>
	      <td>
	        :
	      </td>
	      <td colspan='8'>
	        NGN &nbsp; {$loanAmount}
	      </td>
	    </tr>

    <tr>
		<tr>
			      <td width='130'>
			        Interest - Charge
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$interest}
			      </td>
			    </tr>

		    <tr>

		    <tr>
		<tr>
			      <td width='130'>
			        Interest Paid
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$interestPaid}
			      </td>
			    </tr>

		    <tr>
		    
		    <tr>
			      <td width='130'>
			        Unpaid-Interest
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$unpaid_interest}
			      </td>
			    </tr>

		    <tr>
		    
		    <tr>
			      <td width='130'>
			        Loan Repayment
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$loanRepayment}
			      </td>
			    </tr>

		    <tr>
		    <tr>
			      <td width='130'>
			       <strong> Loan Balance </strong>
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$loanBalance}
			      </td>
			    </tr>

		    <tr>

		    <tr>
			      <td width='130'>
			        Withdrawal from Savings
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$withdrawal}
			      </td>
			    </tr>

		    <tr>
		    <tr>
			      <td width='130'>
			        <strong>Total</strong>
			      </td>
			      <td>
			        :
			      </td>
			      <td colspan='8'>
			        NGN &nbsp; {$total} 
			      </td>
			    </tr>

		    <tr>




      <td width='130'>
        Value Date
      </td>
      <td>
        :
      </td>
      <td colspan='8'>
        {$period}
      </td>
    </tr>
    
	
	
	</tbody></table>
	
	</td></tr>
    <tr>
      <td colspan='7'>
        You can login into the mobile App to view transaction details;
      </td>
    </tr>
<tr>
      <td colspan='10'>
        &nbsp;
      </td>
    </tr>
    <tr>
      <td height='25' colspan='10'>
        Thank you for choosing Victory Sagamu CMS Limited
      </td>
    </tr>

    
    <tr>
      <td colspan='10'>
        &nbsp;
      </td>
    </tr>
    <tr>
      <td height='19' colspan='10'>
        <table width='650px' border='0'>
          <tbody><tr>
            <td width='185' rowspan='3'>
              </td>

        
    </tr>
  </tbody>
</table>



</td></tr></tbody></table>";


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
	$mail->Username = "no-reply@emmaggi.com";

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
	$mail->addAddress($emaddress, $first_name);
	$mail ->addBCC('bankole.adesoji@gmail.com');
	
	//Set the subject line
	$mail->Subject = "VCMS Transaction";

	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), __DIR__);

	//Replace the plain text body with one created manually
	$mail->AltBody = "This is a plain-text message body";

	$mail->Body = $message;

	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');

	//send the message, check for errors
	
	if (!$mail->send()) {
		echo "Mailer Error: " . $mail->ErrorInfo;
	} else {
		echo "Mail Sent";
	}
}


?>