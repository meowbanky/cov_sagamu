<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

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

mysql_select_db($database_cov, $cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysql_query($query_title, $cov) or die(mysql_error());
$row_title = mysql_fetch_assoc($title);
$totalRows_title = mysql_num_rows($title);

mysql_select_db($database_cov, $cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysql_query($query_logo, $cov) or die(mysql_error());
$row_logo = mysql_fetch_assoc($logo);
$totalRows_logo = mysql_num_rows($logo);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	mysql_select_db($database_cov, $cov);
$query_Checkcoopid = sprintf("SELECT * FROM tblaccountno WHERE tblaccountno.COOPNO = %s", GetSQLValueString($_POST['txtCoopid'], "text"));
$Checkcoopid = mysql_query($query_Checkcoopid, $cov) or die(mysql_error());
$row_Checkcoopid = mysql_fetch_assoc($Checkcoopid);
$totalRows_Checkcoopid = mysql_num_rows($Checkcoopid);

if ($totalRows_Checkcoopid > 0) {
	
	
  $updateSQL = sprintf("UPDATE tblaccountno SET Bank=%s, AccountNo=%s WHERE COOPNO=%s",
                       GetSQLValueString($_POST['txtBank'], "text"),
                       GetSQLValueString($_POST['txtAccountNo'], "text"),
                       GetSQLValueString($_POST['txtCoopid'], "text"));
}elseif ($totalRows_Checkcoopid == 0){
	$updateSQL = sprintf("INSERT INTO tblaccountno (Bank, AccountNo, coopno) VALUES(%s,%s,%s)",
                       GetSQLValueString($_POST['txtBank'], "text"),
                       GetSQLValueString($_POST['txtAccountNo'], "text"),
					   GetSQLValueString($_POST['txtCoopid'], "text"));
}

  mysql_select_db($database_cov, $cov);
  $Result1 = mysql_query($updateSQL, $cov) or die(mysql_error());
}

$col_coopID = "-1";
if (isset($_GET['coopid'])) {
  $col_coopID = $_GET['coopid'];
}
mysql_select_db($database_cov, $cov);
$query_coopID = sprintf("SELECT concat (memberid, ' - ', Lname, ' , ', fName, '  ', mName) as coopname, memberid, Bank, AccountNo 
FROM tblaccountno RIGHT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tblaccountno.COOPNO WHERE memberid = %s", GetSQLValueString($col_coopID, "text"));
$coopID = mysql_query($query_coopID, $cov) or die(mysql_error());
$row_coopID = mysql_fetch_assoc($coopID);
$totalRows_coopID = mysql_num_rows($coopID);

mysql_select_db($database_cov, $cov);
$query_Bank = "SELECT tblbankcode.bank, tblbankcode.bankcode  FROM tblbankcode ";
$Bank = mysql_query($query_Bank, $cov) or die(mysql_error());
$row_Bank = mysql_fetch_assoc($Bank);
$totalRows_Bank = mysql_num_rows($Bank);


//session_start();
//if(!isset($_SESSION['UserID'])){
//	header("Location:index.php"); 
//}
?>
<?php

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["CreateBatch"])) && ($_POST["CreateBatch"] == "Create Batch")) {
  $insertSQL = sprintf("INSERT INTO tbl_batch (batch) VALUES (%s)",
                       GetSQLValueString($_POST['batch'], "text"));

  mysql_select_db($database_cov, $cov);
  $Result1 = mysql_query($insertSQL, $cov) or die(mysql_error());
  
  $insertGoTo = "batchcreate.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

if ((isset($_GET['id'])) && ($_GET['id'] != "") && ($_POST['action']="deleteProfCert")) {
  $deleteSQL = sprintf("DELETE FROM tbl_proffcert WHERE ProfCertID=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_conn_career, $conn_career);
  $Result1 = mysql_query($deleteSQL, $conn_career) or die(mysql_error());
}

if ((isset($_GET['id'])) && ($_GET['id'] != "") && ($_POST['action']="deleteSkill")) {
  $deleteSQL = sprintf("DELETE FROM tbl_skills WHERE SkillID=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_conn_career, $conn_career);
  $Result1 = mysql_query($deleteSQL, $conn_career) or die(mysql_error());
}

if ((isset($_GET['id'])) && ($_GET['id'] != "") && ($_POST['action']="deleteWExp")) {
  $deleteSQL = sprintf("DELETE FROM tbl_workexperience WHERE WEID=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_conn_career, $conn_career);
  $Result1 = mysql_query($deleteSQL, $conn_career) or die(mysql_error());
}

if ((isset($_GET['id'])) && ($_GET['id'] != "") && ($_POST['action']="deleteEdu")) {
  $deleteSQL = sprintf("DELETE FROM tbl_education WHERE EducationID=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_conn_career, $conn_career);
  $Result1 = mysql_query($deleteSQL, $conn_career) or die(mysql_error());
}
?>
<HTML><HEAD><TITLE>Edit Account Info</TITLE>
<META content="text/html; charset=windows-1252" http-equiv=Content-Type><!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004--><LINK 
rel=stylesheet type=text/css 
href="education_files/oouth.css">
<link href="../SpryAssets/jquery.ui.core.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.theme.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.button.min.css" rel="stylesheet" type="text/css">
<SCRIPT language=JavaScript type=text/javascript 
src="education_files/general.js"></SCRIPT>
<script src="../SpryAssets/jquery-1.11.1.min.js" type="text/javascript"></script>
<script src="../SpryAssets/jquery.ui-1.10.4.button.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
<!--
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
	
	function getBatch() {		
		
		var strURL="generateBatch.php";
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('batch').innerHTML=req.responseText;						
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

function MM_validateForm() { //v4.0
  var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
  for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=MM_findObj(args[i]);
    if (val) { nm=val.name; if ((val=val.value)!="") {
      if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
        if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
      } else if (test!='R') { num = parseFloat(val);
        if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
        if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
          min=test.substring(8,p); max=test.substring(p+1);
          if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
    } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
  } if (errors) alert('The following error(s) occurred:\n'+errors);
  document.MM_returnValue = (errors == '');
}
//-->
</script>

<script>
function clearBox()
{
document.forms[0].txtBank.value = "Bank"
document.forms[0].txtAccountNo.value = ""
}
</script>

<META name=GENERATOR content="MSHTML 8.00.7600.16385"></HEAD>
<BODY>
      <TABLE border=0 cellSpacing=0 cellPadding=0 width=500 bgcolor="#FFFFFF">
        <TBODY>
        <TR>
          <TD width=8><IMG src="mycv_files/spacer.gif" width=1 
            height=1></TD>
          <TD class=breadcrumbs height=20 vAlign=bottom colSpan=2>&nbsp;</TD>
          <TD width=10><IMG src="mycv_files/spacer.gif" width=1 
            height=1></TD></TR>
        <TR>
          <TD height="369" vAlign=top class=Content>&nbsp;</TD>
          <TD width="501" vAlign=top class=Content id=top><img src="../<?php echo $row_logo['value']; ?>" height="50%">
            <HR align=left color=#cccccc SIZE=1 width=500>
            <TABLE border=0 cellSpacing=0 cellPadding=0 width=444>
              <TBODY>
                <TR>
                  <TD width="444" vAlign=top class=toplinks2>
                    <DIV align=justify>
                      <TABLE class=Content border=0 cellSpacing=0 cellPadding=4 
                  width="100%">
                        <TBODY>
                          <TR>
                            <TD vAlign=top>
                              <?php if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) { echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Bank Account No Update Successful</td>
  </tr>
</tbody></table>" ;} ?>
                              <FIELDSET>
                                <LEGEND class=contentHeader1>Edt Account No</LEGEND>
                                <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" onSubmit="MM_validateForm('txtAccountNo','','RisNum');return document.MM_returnValue">
                                <table width="97%" align="center" cellpadding="4" cellspacing="0">
                                  <tr valign="top" align="left">
                                      <td class="greyBgd" valign="middle" width="31%" align="right" height="35">Coop id </td>
                                      <td class="greyBgd" valign="middle" width="86%" align="left">
                                        <select name="txtCoopid" class="innerBox" id="txtCoopid" onChange=clearBox(); >
                                          <?php
do {  
?>
                                          <option value="<?php echo $row_coopID['memberid']?>"><?php echo $row_coopID['coopname']?></option>
                                          <?php
} while ($row_coopID = mysql_fetch_assoc($coopID));
  $rows = mysql_num_rows($coopID);
  if($rows > 0) {
      mysql_data_seek($coopID, 0);
	  $row_coopID = mysql_fetch_assoc($coopID);
  }
?>
                                        </select>
                                        <input name="coopid" type="hidden" value="<?php echo $row_coopID['memberid']; ?>"></td>
                                  </tr>
                                    <tr valign="top" align="left">
                                      <td class="greyBgd" valign="middle" align="right" height="35">Bank</td>
                                      <td class="greyBgd" valign="middle" align="left"><label>
                                        <select name="txtBank" class="innerBox" id="txtBank">
                                          <option value="-1" <?php if (!(strcmp(-1, $row_coopID['Bank']))) {echo "selected=\"selected\"";} ?>>Bank</option>
                                          <?php
do {  
?>
                                          <option value="<?php echo $row_Bank['bank']?>"<?php if (!(strcmp($row_Bank['bank'], $row_coopID['Bank']))) {echo "selected=\"selected\"";} ?>><?php echo $row_Bank['bank']?></option>
                                          <?php
} while ($row_Bank = mysql_fetch_assoc($Bank));
  $rows = mysql_num_rows($Bank);
  if($rows > 0) {
      mysql_data_seek($Bank, 0);
	  $row_Bank = mysql_fetch_assoc($Bank);
  }
?>
                                        </select>
                                        </label></td>
                                  </tr>
                                    <tr valign="top" align="left">
                                      <td class="greyBgd" valign="middle" align="right" height="35">Account No.: </td>
                                      <td class="greyBgd" valign="middle" align="left"><label>
                                        <input name="txtAccountNo" type="text" class="innerBox" id="txtAccountNo" value="<?php echo $row_coopID['AccountNo']; ?>" maxlength="10">
                                        </label></td>
                                  </tr>
                                    <tr valign="top" align="left">
                                      <td height="35" colspan="2" align="right" valign="middle" class="greyBgd"><div align="center">
                                        <button id="Save" onClick="submith()">Save</button>&nbsp;
                                           
                                        <button id="Close" class="ui-button" onClick="parent.hide(); parent.location.refresh(true)">Close</button>
                                      </div></p></td>
                                  </tr>
                                  </table>
                                  <input type="hidden" name="MM_insert" value="form1">
                                  <SCRIPT language=JavaScript type=text/JavaScript>
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                        </SCRIPT>
                                  <input type="hidden" name="MM_update" value="form1">
                                  </form>
                                </FIELDSET>
                              <SCRIPT language=JavaScript type=text/JavaScript>
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</SCRIPT>
                              <FILDSET><LEGEND class=contentHeader1></LEGEND>
                              </FIELDSET> 
                          <P><BR></P></TD></TR></TBODY></TABLE>
      </DIV></TD></TR></TBODY></TABLE><BR><BR><BR></TD></TR></TBODY></TABLE></TD></TR>
  <script type="text/javascript">
$(function() {
	$( "#Button1" ).button(); 
});
      </script>
</BODY></HTML>
<?php
mysql_free_result($coopID);

mysql_free_result($Bank);


?>
