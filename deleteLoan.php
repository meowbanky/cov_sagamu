<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($cov, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      
      $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($cov, $theValue) : mysqli_escape_string($cov, $theValue);

      switch ($theType) {
        case "text":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;    
        case "long":
        case "int":
          $theValue = ($theValue != "") ? intval($theValue) : "NULL";
          break;
        case "double":
          $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
          break;
        case "date":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
        case "defined":
          $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
          break;
      }
      return $theValue;
    }
   
}


$col_loanID = "-1";
if (isset($_GET['loanID'])) {
  $col_loanID = $_GET['loanID'];
}
mysqli_select_db($cov,$database_cov);
$query_loan = sprintf("SELECT tbl_loan.memberid FROM tbl_loan WHERE tbl_loan.loanid = %s ", GetSQLValueString($cov,$col_loanID, "int"));
$loan = mysqli_query($cov,$query_loan) or die(mysqli_error($cov));
$row_loan = mysqli_fetch_assoc($loan);
$totalRows_loan = mysqli_num_rows($loan);


if ((isset($_GET['loanID'])) && ($_GET['loanID'] != "")) {
  $deleteSQL = sprintf("DELETE FROM tbl_loan WHERE loanid=%s",
                       GetSQLValueString($cov,$_GET['loanID'], "int"));
$deleteSQL_masterTransact = sprintf("DELETE FROM tlb_mastertransaction WHERE loanid=%s",
                       GetSQLValueString($cov,$_GET['loanID'], "int"));		
					   
$deleteShedule = sprintf("DELETE FROM tbl_bank_schedule WHERE loanid=%s",
                       GetSQLValueString($cov,$_GET['loanID'], "int"));					   						   			   

  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$deleteSQL) or die(mysqli_error($cov));
  $Result2 = mysqli_query($cov,$deleteSQL_masterTransact) or die(mysqli_error($cov));
  $Result3 = mysqli_query($cov,$deleteShedule) or die(mysqli_error($cov));
    
	$deleteGoTo = 'addloan.php';
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<script language="javascript" type="text/javascript">
// Roshan's Ajax dropdown code with php
// This notice must stay intact for legal use
// Copyright reserved to Roshan Bhattarai - nepaliboy007@yahoo.com
// If you have any problem contact me at http://roshanbh.com.np
function getXMLHTTP() { //fuction to return the xml http object
		var xmlhttp=false;	
		try{
			xmlhttp=new XMLHttpRequest();
		}
		catch(e)	{		
			try{			
				xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch(e1){
					xmlhttp=false;
				}
			}
		}
		 	
		return xmlhttp;
    }
	
	function getName(coopid) {		
		
		var strURL="BankCode1.php?id="+coopid;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('BankAccountNo').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	function getBankName(coopid) {		
		
		var strURL="bankName.php?id="+coopid;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('BankName').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	
	function getAccountNo(id) {		
		var strURL="BankCode.php?id="+id;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('BankAccountNo').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
				
	}
</script>
</head>

<body>
<?php //echo ($_GET['beneficiaryCode']) ; ?>

</body>
</html>
<?php
//mysql_free_result($memberid);
?>
