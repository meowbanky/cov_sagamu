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
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods order by Periodid desc";
$Period = mysqli_query($cov,$query_Period) or die(mysql_error());
$row_Period = mysqli_fetch_assoc($Period);
$totalRows_Period = mysqli_num_rows($Period);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysql_error());
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- DW6 -->
<head>
<!-- Copyright 2005 Macromedia, Inc. All rights reserved. -->
<title>..:<?php echo $row_title['value']; ?> SMS ALERT:..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="mm_health_nutr.css" type="text/css" />
<script language="JavaScript" type="text/javascript">
//--------------- LOCALIZEABLE GLOBALS ---------------
var d=new Date();
var monthname=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
//Ensure correct for language. English is "January 1, 2004"
var TODAY = monthname[d.getMonth()] + " " + d.getDate() + ", " + d.getFullYear();
//---------------   END LOCALIZEABLE   ---------------
</script>
<script language="JavaScript" type="text/javascript">
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
	
			function makeRequest(url,divID) {

                //alert("ajax code");

                // alert(divID);

                //alert(url);

                var http_request = false;

                if (window.XMLHttpRequest) { // Mozilla, Safari, ...

                    http_request = new XMLHttpRequest();

                    if (http_request.overrideMimeType) {

                        http_request.overrideMimeType('text/xml');

                        // See note below about this line

                    }

                }

                else

                    if (window.ActiveXObject) { // IE

                        //alert("fdsa");

                        try {

                            http_request = new ActiveXObject("Msxml2.XMLHTTP");

                        } catch (e) {

                            lgErr.error("this is exception1 in his_secpatientreg.jsp"+e);

                            try {

                                http_request = new ActiveXObject("Microsoft.XMLHTTP");

                            } catch (e) {

                                lgErr.error("this is exception2 in his_secpatientreg.jsp"+e);

                            }

                    }

                }

                if (!http_request) {

                    alert('Giving up :( Cannot create an XMLHTTP instance');

                    return false;

                }

                http_request.onreadystatechange = function() {  alertContents(http_request,divID); };

                http_request.open('GET', url, true);

                http_request.send(null);

            }
			function alertContents(http_request,divid) {

                if (http_request.readyState == 4) {

                    //alert(http_request.status);

                    //alert(divid);

                    if (http_request.status == 200) {

                        document.getElementById(divid).innerHTML=http_request.responseText;

                    } else {

                        //document.getElementById(divid).innerHTML=http_request.responseText;

                        alert("There was a problem with the request");

                    }

                }

            }
			
			
			
function sendsms(id1,id2,id3){
	var equality = id1;
	var staffid = id2;
	var period = id3;
	
	
	
window.location = '../manualMail_Sms.php?equality='+equality+'&staffid='+staffid+'&period='+period;
	
	
	
	}			
				
function getStatus(id) {		
		//alert("hi");
		
		//document.getElementById('status_old').style.display="none"; 
		var period = document.getElementById('Period').value;
		
		if (period == 'na'){
			period = '-1';
			}
		//var period2 = document.getElementById('PeriodId2').value;
		var strURL="selectedMonth.php?period="+period;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					//if (req.status == 200) {						
						document.getElementById('status').innerHTML=req.responseText;	
						document.getElementById('status').style.visibility = "visible";
						document.getElementById('wait').style.visibility = "hidden";						
					} else {
						//document.getElementById('wait').style.width = "100%";
						document.getElementById('wait').style.visibility = "visible";
						document.getElementById('status').style.visibility = "hidden";
					}
				}				
						
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}

</script>

<style type="text/css">
<!--
.style5 {font-size: 14px; color: #000000; }
.style6 {color: #000000; font-weight: bold; font-size: 14px; }
.style8 {color: #000000; font-size: 15px; }
-->
</style>
</head>
<body bgcolor="#F4FFE4">
<form id="form1" name="form1" method="post" action= "sendsms.php">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr bgcolor="#D5EDB3">
    <td colspan="2" rowspan="2"><img src="mm_health_photo.jpg" alt="Header image" width="382" height="101" border="0" /></td>
    <td width="652" height="50" id="logo" valign="bottom" align="center" nowrap="nowrap"><?php echo $row_title['value']; ?> SMS ALERT SYSTEM </td>
    <td width="64">&nbsp;</td>
  </tr>

  <tr bgcolor="#D5EDB3">
    <td height="51" id="tagline" valign="top" align="center">..sms system </td>
	<td width="64">&nbsp;</td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#5C743D"><img src="mm_spacer.gif" alt="" width="1" height="2" border="0" /></td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#99CC66" background="mm_dashed_line.gif"><img src="mm_dashed_line.gif" alt="line decor" width="4" height="3" border="0" /></td>
  </tr>

  <tr bgcolor="#99CC66">
  <td>&nbsp;</td>
  	<td colspan="3" id="dateformat" height="20"><a href="javascript:;">HOME</a>&nbsp;&nbsp;::&nbsp;&nbsp;<script language="JavaScript" type="text/javascript">
      document.write(TODAY);	</script>	</td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#99CC66" background="mm_dashed_line.gif"><img src="mm_dashed_line.gif" alt="line decor" width="4" height="3" border="0" /></td>
  </tr>

  <tr>
    <td colspan="4" bgcolor="#5C743D"><img src="mm_spacer.gif" alt="" width="1" height="2" border="0" /></td>
  </tr>
 <tr>
    <td width="40">&nbsp;</td>
    <td colspan="2" valign="top" align="center">&nbsp;<br />
    &nbsp;<br />
   <table border="1" cellspacing="0" cellpadding="2" width="50%">
        <tr>
          <td width="989" colspan="2" class="pageName">SELECT PERIOD</td>
        </tr>
        <tr>
          <td class="bodyText" align="center"><p>Period</p></td>
          <td class="bodyText" align="center"><select name="Period" id="Period" onchange="getStatus(this.value)">
            <option value="na">Select Period</option>
            <?php
do {  
?>
            <option value="<?php echo $row_Period['Periodid']?>"><?php echo $row_Period['PayrollPeriod']?></option>
            <?php
} while ($row_Period = mysqli_fetch_assoc($Period));
  $rows = mysqli_num_rows($Period);
  if($rows > 0) {
      mysqli_data_seek($Period, 0);
	  $row_Period = mysqli_fetch_assoc($Period);
  }
?>
          </select></td>
        </tr>
      </table>
	  
<div align="center"></div></td>
    <td width="64">&nbsp;</td>
  </tr>

 <tr>
    <td width="40"></td>
   
	<td colspan="2"><div id="status"></div></td>
    </tr>
</table>
<div id="wait" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;" class="overlay">
 <img src="../images/pageloading.gif" class="area">Please wait...
 </div>
</form>
</body>
</html>
<?php
mysqli_free_result($Period);

mysqli_free_result($title);

?>
