<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

      $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn_vote, $theValue) : mysqli_escape_string($conn_vote, $theValue);

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

mysqli_select_db($cov,$database_cov);
$query_global_settings = "SELECT * FROM tbl_globa_settings where setting_id = 1";
$global_settings = mysqli_query($cov,$query_global_settings) or die(mysql_error());
$row_global_settings = mysqli_fetch_assoc($global_settings);
$totalRows_global_settings = mysqli_num_rows($global_settings);

mysqli_select_db($cov,$database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov,$query_logo) or die(mysql_error());
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);
session_start();
session_destroy();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<!-- saved from url=(0038)http://careers.mtnonline.com/login.asp -->
<HTML xmlns="http://www.w3.org/1999/xhtml"><HEAD><TITLE><?php echo $row_global_settings['value']; ?> - LOGIN</TITLE>
<META content="text/html; charset=iso-8859-1" http-equiv=Content-Type>
<link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">
<LINK 
rel=stylesheet type=text/css href="index_files/siteCSS.css">

<link href="registration_files/oouth.css" rel="stylesheet" type="text/css">


<SCRIPT>
function clearBox()
{
document.forms[0].uname.value = ""
document.forms[0].passwd.value = ""

}

function validation (){
	
	var x = document.forms["form1"]["uname"].value.trim();
	var y = document.forms["form1"]["passwd"].value;
	
	if (x == ""){
		document.forms["form1"]["uname"].focus()
		alert("User name must be filled out");
		return false
		}
		if (y == ""){
		document.forms["form1"]["passwd"].focus()
		alert("Password must be filled out");
		return false
		}
	
	}


function resetBox()
{
document.forms[0].uname.value = "-Email Address-";
document.forms[0].passwd.value = "password";
}
</SCRIPT>


<META name=GENERATOR content="MSHTML 8.00.6001.18702"></HEAD>
<BODY class=SB 
leftMargin=0>
<CENTER>
<TABLE border=0 cellSpacing=0 cellPadding=0 width=742 bgcolor="#FFFFFF"><!--DWLayoutTable-->
  <TBODY>
  <TR>
    <TD height=758 vAlign=top width=742>
      <TABLE id=bodyTb-border border=0 cellSpacing=0 cellPadding=0 width="50%"><!--DWLayoutTable-->
        <TBODY>
        <TR>
          <TD height=119 vAlign=top colSpan=3>
            <TABLE border=0 cellSpacing=0 cellPadding=0 width=736><!--DWLayoutTable-->
              <TBODY>
              <TR>
                <TD vAlign=top 
                width=32><!--DWLayoutEmptyCell-->&nbsp;</TD>
                <TD height="22" align="center" vAlign=top><A 
                  href="#/"><img src="<?php echo $row_logo['value']; ?>" alt=""></A></TD>
              </TR>
              </TBODY></TABLE></TD></TR>
        <TR>
          <TD height=242 vAlign=top colSpan=3>
            <TABLE style="BORDER-TOP: #fecb00 1px solid" border=0 cellSpacing=0 
            cellPadding=0 width=736><!--DWLayoutTable-->
              <TBODY>
              </TBODY></TABLE></TD></TR>
        <TR>
          <TD height=34 vAlign=top colSpan=3>
            <FIELDSET><FORM method="POST" name="form1" onsubmit="return validation()" action="login_auth.php" ><LEGEND class=contentHeader1>Login</LEGEND> <TABLE width=281 border=0 cellPadding=0 cellSpacing=0 class="greyBgd"><!--DWLayoutTable-->
              <TBODY>
              <TR>
                <TD height="34" 
                  align=left vAlign=baseline noWrap>Username</TD>
                <TD 
                  align=left vAlign=baseline noWrap><input id=uname class=TextBox placeholder="-Username-" 
                  type=text name=uname></TD>
              </TR>
              <TR>
                <TD height="34" 
                  align=left vAlign=baseline noWrap>Password</TD>
                <TD height="34" 
                  align=left vAlign=baseline noWrap><input 
                  name=passwd type=password class="TextBox" id=passwd placeholder="Password" autocomplete="false"></TD>
              </TR>
              <TR>
                
                  <TD width=281 height="34" colspan="2" 
                  align=left vAlign=baseline noWrap>&nbsp;&nbsp;
                    <input 
                  src="index_files/btn-login.jpg" type=image 
                  name=Submit></TD>
              </TR></TBODY></TABLE></FORM></FIELDSET></TD></TR>
        <TR>
          <TD height=25 vAlign=top 
           colSpan=3><!--DWLayoutEmptyCell-->&nbsp;</TD></TR>
        <TR>
          <TD vAlign=top rowSpan=2 width=30><!--DWLayoutEmptyCell-->&nbsp;</TD>
          <TD height=212 vAlign=top width=672><!--DWLayoutEmptyCell-->&nbsp;</TD>
          <TD vAlign=top width=34><!--DWLayoutEmptyCell-->&nbsp;</TD></TR>
        <TR>
      <TD height=126 vAlign=top colSpan=2><?php   if (isset($_GET['Expired'])){?>
                              <script language="javascript">
							  alert("License Expired Please Contact your Administrator");
							  </script>
                              <?php }?>&nbsp;</TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE>
<TABLE id=bottom-bg border=0 cellSpacing=0 cellPadding=0 width="100%" 
align=left><!--DWLayoutTable-->
  <TBODY>
  <TR id=copyright-txt>
    <TD height=26 vAlign=top width=66><!--DWLayoutEmptyCell-->&nbsp;</TD>
    <TD vAlign=center width=278>© 2019 BankSoft Solutions </TD>
    <TD vAlign=center width=407 align=right></TD></TR></TBODY></TABLE>
</CENTER></BODY></HTML>
<?php
mysqli_free_result($global_settings);

mysqli_free_result($logo);
?>
