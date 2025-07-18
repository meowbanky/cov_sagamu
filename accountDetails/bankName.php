<?php require_once('Connections/cov.php'); ?>
<?php
$coop_accountNo = "-1";
if (isset($_GET['id'])) {
  $coop_accountNo = (get_magic_quotes_gpc()) ? $_GET['id'] : addslashes($_GET['id']);
}
mysql_select_db($database_cov, $cov);
$query_accountNo = sprintf("SELECT tbl_personalinfo.memberid, concat(tbl_personalinfo.Fname,' , ',tbl_personalinfo.Mname,' ',tbl_personalinfo.Lname) as name, 
tblaccountno.Bank, tblaccountno.AccountNo, tblaccountno.BankCode 
FROM tbl_personalinfo LEFT JOIN tblaccountno ON tblaccountno.COOPNO = tbl_personalinfo.memberid WHERE memberid = '%s'", $coop_accountNo);
$accountNo = mysql_query($query_accountNo, $cov) or die(mysql_error());
$row_accountNo = mysql_fetch_assoc($accountNo);
$totalRows_accountNo = mysql_num_rows($accountNo);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
		
		var strURL="accountNo1.php?id="+coopid;
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
		var strURL="accountNo.php?id="+id;
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

<label><form id="eduEntry" name="eduEntry" method="post" action="">

<div id="BankName"><input name ='txtBankName' type="text" class="innerBox" id="txtBankName" value="<?php echo $row_accountNo['Bank']; ?>" size="60" readonly="true" onMouseOver="getAccountNo(document.getElementById('hiddenField').value)"> 
  <input name="hiddenField" type="hidden" value="<?php echo $row_accountNo['memberid']; ?>" / id="hiddenField"> 
</div></form>
</label>
</body>
</html>
<?php
mysql_free_result($accountNo);
?>
