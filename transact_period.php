<?php require_once('Connections/cov.php'); ?>
<?php session_start();
if (!isset($_SESSION['UserID'])){
header("Location:index.php");} else{
 
}
?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Save")) {
	
mysqli_select_db($cov,$database_cov);
$query_check_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods WHERE PayrollPeriod='".$_POST['DOB']."'";
$check_Period = mysqli_query($cov,$query_check_Period) or die(mysql_error());
$row_check_Period = mysqli_fetch_assoc($check_Period);
$totalRows_check_Period = mysqli_num_rows($check_Period);

if ($totalRows_check_Period > 0){
	echo "0";
	$insertGoTo = "transact_period.php?success=duplicate";
 
  header(sprintf("Location: %s", $insertGoTo));
	}else{

$sArrary = explode("-",$_POST['DOB']);
$PhysicalYear = $sArrary[1];
$PhysicalMonth = $sArrary[0];	
$insertSQL = sprintf("INSERT INTO tbpayrollperiods (PayrollPeriod,PhysicalYear, PhysicalMonth, InsertedBy, DateInserted) VALUES (%s,%s,%s, %s,NOW())",
                       GetSQLValueString($cov,$_POST['DOB'], "text"),
                       GetSQLValueString($cov,$PhysicalYear, "text"),
                       GetSQLValueString($cov,$PhysicalMonth, "text"),
                       GetSQLValueString($cov,$_SESSION['FirstName'], "text"));

  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$insertSQL) or die(mysql_error());
  
  
	$insertGoTo = "transact_period.php?success=ok";
 
  header(sprintf("Location: %s", $insertGoTo));

}
}

mysqli_select_db($cov,$database_cov);
$query_Existing_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods";
$Existing_Period = mysqli_query($cov,$query_Existing_Period) or die(mysql_error());
$row_Existing_Period = mysqli_fetch_assoc($Existing_Period);
$totalRows_Existing_Period = mysqli_num_rows($Existing_Period);

mysqli_select_db($cov,$database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov,$query_logo) or die(mysql_error());
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysql_error());
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Search")) {

}

?>
<html>
<head>


<title>MHWUN, OOUTH - Create Transaction Period</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">

<!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004-->
<link href="resource/oouth.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="resource/general.js" type="text/javascript"></script>
<script type="text/javascript" language="javascript">

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
						
                        
                        function onSelected(oForm) {

                //options[document.form.profile.selectedIndex].value

                //var s=oForm.selectedIndex;

                var s1=oForm.value;

                //  alert("ddddddddddddd"+s+s1);

                var url="state.php?Country="+s1;

                // alert(url);

                makeRequest(url,"state");

            }
			
function jumptoURL(oForm){
	
	window.location="edit_registration.php?patientid="+oForm.value;
	
	
	}
                        
function onSelectedSearch() {

                var mrn = document.getElementById("SearchMRN").value;
			
                var url="patSearch.php?SearchMRN="+mrn;

                //alert(mrn+lastname+Firstname+phoneno+dob);

                makeRequest(url,"patSearchResult");

            }

function reset(){
				 document.getElementById("SearchMRN").value = "";
				 document.getElementById("SLastName").value= "";
				  document.getElementById("SFirstName").value= "";
				document.getElementById("SphoneNo").value= "";
				document.getElementById("SDOb").value= "";

	}
function clearbox(){
	document.getElementById("SearchMRN").focus();
	document.getElementById("SearchMRN").value = "";
	 }
function Ipopcases1(oForm) {

                //options[document.form.profile.selectedIndex].value

                var s=oForm.selectedIndex;

                var s1=oForm.options[oForm.selectedIndex].value;

                // alert("ddddddddddddd"+s+s1);

                var url="config/patientype.php?apptype="+s1;

                // alert(url);

                makeRequest(url,"patcategory");



            }
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
	
function insertPeriod(period) {		
				
		var str = period;
    	var res = str.split("-");
		var PhysicalYear = res[1];
		var PhysicalMonth= res[0];
		
			
		var strURL="insertTransactPeriod.php?PayrollPeriod="+period+"&PhysicalYear="+PhysicalYear+"&PhysicalMonth="+PhysicalMonth;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if ((req.readyState == 4) || (req.status == 200)){
					alert("Period created Successfully");						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	//alert(PhysicalYear+ " "+PhysicalMonth+" "+period);
	}

function Expand90(itemm){

                //alert(itemm.value);

            

                    if(itemm.value=="NC"){

                       // alert("in new");

                        //document.getElementById('patocpdetailsiframe').style.display="none";

                        //document.getElementById("modeofpay").style.display="none";

                        document.getElementById("patnc").style.display="block";

                        //document.getElementById("patnp").style.display="block";

                        document.getElementById("patoc").style.display="none";
						document.getElementById("patSearchResult").style.display="none";

                        //document.getElementById("patop").style.display="none";

                        //document.getElementById("patdisplay").style.display="none";

                        //document.getElementById("patocpdetails").style.display="none";



                        //document.getElementById("patientolddetails").style.display="none";

                        //document.getElementById("patocpdetails").width="0";

                        //document.getElementById("patocpdetails").height="0";



                        //document.getElementById("Temporrarayappointment").style.display="none";

                        //document.getElementById("paylater").style.display="none";

                        //document.getElementById('fname').value="";

                        //document.getElementById('lname').value="";











                    }else{

                        //alert("in old");

                        //document.getElementById("modeofpay").style.display="block";

                        document.getElementById("patnc").style.display="none";

                        document.getElementById("patoc").style.display="block";
						document.getElementById("patSearchResult").style.display="block"
                        //document.getElementById("patnp").style.display="none";

                        //document.getElementById("patop").style.display="block";

                        //document.getElementById("patocpdetails").style.display="none";

                        //document.getElementById("patientolddetails").style.display="none";

                        //document.getElementById("patocpdetails").width="0";

                        //document.getElementById("patocpdetails").height="0";

                        //document.getElementById("Temporrarayappointment").style.display="none";

                        //document.getElementById("companynamesss").style.display="none";

                        //document.getElementById("paylater").style.display="none";

                        //document.getElementById('patfirstname').value="";

                        //document.getElementById('patlastname').value="";

                    }
}



function ischecked(oFormEle,msg)

                                {

                                var s=oFormEle.value

                                if (s=="na"){

                                alert(msg);

                                oFormEle.focus()

                                return false;

                                }

                                return true;

                                }
function UserFeedback(oFormEle)

        {

        oFormEle.focus();

		}
function isSpace(s,message)
                {
					

                ss=s.value;

                var length=ss.length;

                var c = ss.charAt(0);

                var d=ss.charAt(length-1);

                //    var regexpr =/[A-Za-z0-9]/;

                //     result= regexpr.test(c)

                //	if (!result)
				
				
                if(c == " " || d == " ")

                {

                UserFeedback(s);

                s.value = ss.trim();

                alert(message);

               return false;

                }

                return true;

                }

//function cansubmit(){ can (cansubmit=isSpace(document.eduEntry.Fname,"Space not allowed"));}
//cansubmit=isSpace(document.eduEntry.Fname.value,"Space not allowed");
function sameasabove(){
	if (document.eduEntry.same.checked){
	document.eduEntry.NOKAddress.value = document.eduEntry.Address.value +" "+document.eduEntry.Address2.value+" "+document.eduEntry.City.value+" "+document.eduEntry.State.value;
		}else{ document.eduEntry.NOKAddress.value = "";}
}

function validate(){
//var cansubmit=false

   if (document.eduEntry.DOB.value.trim() == "" )
   {
     alert( "Please fill Period!" );
     document.eduEntry.DOB.focus() ;
     return false;
   }
 
return( true );
}



</script>
                        




<script type="text/javascript" src="resource/popcalendar.js"></script>
<script>

                    var isNS4=(navigator.appName=="Netscape")?1:0;

                    function auto_logout(iSessionTimeout,iSessTimeOut,sessiontimeout)

                    {

                             window.setTimeout('', iSessionTimeout);

                              window.setTimeout('winClose()', iSessTimeOut);

                    }

                    function winClose() {

                        //alert("Your Application session is expired.");

                   if(!isNS4)

	           {

		          window.navigate("index.php");

	           }

                  else

	          {

		        window.location="index.php";

	           }

             }

            auto_logout(1440000,1500000,1500)

</script>
</head>
<body><div onClick="bShow=true" id="calendar" style="z-index: 999; position: absolute; visibility: hidden;"><table style="border: 1px solid rgb(160, 160, 160); font-size: 11px; font-family: arial;" width="220" bgcolor="#ffffff"><tbody><tr bgcolor="#0000aa"><td><table width="218"><tbody><tr><td style="padding: 2px; font-family: arial; font-size: 11px;"><font color="#ffffff"><b><span id="caption"><span id="spanLeft" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeLeft","left2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to previous month. Hold mouse button to scroll automatically."' onClick="javascript:decMonth()" onmouseout='clearInterval(intervalID1);swapImage("changeLeft","left1.gif");this.style.borderColor="#3366FF";window.status=""' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartDecMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeLeft" src="resource/left1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanRight" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeRight","right2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to next month. Hold mouse button to scroll automatically."' onmouseout='clearInterval(intervalID1);swapImage("changeRight","right1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="incMonth()" onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartIncMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeRight" src="resource/right1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanMonth" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeMonth","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a month."' onmouseout='swapImage("changeMonth","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpMonth()"></span>&nbsp;<span id="spanYear" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeYear","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a year."' onmouseout='swapImage("changeYear","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpYear()"></span>&nbsp;</span></b></font></td><td align="right"><a href="javascript:hideCalendar()"><img src="resource/close.gif" alt="Close the Calendar" width="15" border="0" height="13"></a></td></tr></tbody></table></td></tr><tr><td style="padding: 5px;" bgcolor="#ffffff"><span id="content"></span></td></tr><tr bgcolor="#f0f0f0"><td style="padding: 5px;" align="center"><span id="lblToday">Today is <a onmousemove='window.status="Go To Current Month"' onmouseout='window.status=""' title="Go To Current Month" style="text-decoration: none; color: black;" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">Wed, 8 Jun	2011</a></span></td></tr></tbody></table></div><div id="selectMonth" style="z-index: 999; position: absolute; visibility: hidden;"></div><div id="selectYear" style="z-index: 999; position: absolute; visibility: hidden;"></div>



<table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
<!-- fwtable fwsrc="MTN4U.png" fwbase="index.jpg" fwstyle="Dreamweaver" fwdocid = "1226677029" fwnested="0" -->
<tbody>
<tr>
  <td><img src="resource/spacer.gif" alt="" width="750" border="0" height="1"></td>
</tr>
<tr>
  <td class="centerAligned" valign="top" height="100"><div align="center"></div>
    <table width="750" border="0" cellpadding="0" cellspacing="0">
      <!-- fwtable fwsrc="Untitled" fwbase="top.gif" fwstyle="Dreamweaver" fwdocid = "2000728079" fwnested="0" -->
      <tbody>
        <tr>
          <td><img src="resource/spacer.gif" alt="" width="7" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="78" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="491" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="153" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="21" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
        </tr>
        <tr>
          <td colspan="5"><img name="top_r1_c1" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
        </tr>
        <tr>
          <td rowspan="4"><img name="top_r2_c1" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
          <td colspan="3" rowspan="4" align="center"><img src="<?php echo $row_logo['value']; ?>" width="499" height="95"><img name="top_r4_c4" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
          <td>&nbsp;</td>
          <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="17"></td>
        </tr>
        <tr>
          <td rowspan="3"><img name="top_r3_c5" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
          <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="37"></td>
        </tr>
        <tr>
          <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="25"></td>
        </tr>
        <tr>
          <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
        </tr>
      </tbody>
    </table></td>
</tr>
<tr>
  <td class="mainNav" valign="top" height="21"><table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
    <tbody>
      <tr>
        <td class="rightAligned" width="10">&nbsp;</td>
        <td class="mainNavTxt" valign="bottom"><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <!-- fwtable fwsrc="Untitled" fwbase="nav.gif" fwstyle="Dreamweaver" fwdocid = "1284367442" fwnested="0" -->
          <tbody>
            <tr>
              <td><a href="http://careers.mtnonline.com/index.asp"></a></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td><a href="http://careers.mtnonline.com/departments.asp"></a></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td><a href="http://careers.mtnonline.com/vacancies.asp"></a></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td><a href="http://careers.mtnonline.com/lifeatmtn.asp"></a></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td><a href="http://careers.mtnonline.com/mycv.asp"></a></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td><a href="http://careers.mtnonline.com/logout.asp"></a></td>
            </tr>
          </tbody>
        </table></td>
        <td class="leftAligned" width="12">&nbsp;</td>
      </tr>
    </tbody>
  </table></td>
</tr>
<tr>
  <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r3_c1" src="resource/index_r3_c1.jpg" alt="" width="750" border="0" height="1"></td>
</tr>
<tr>
  <td class="globalNav" valign="top" height="25"><table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
    <tbody>
      <tr>
        <td class="rightAligned" width="10"><img src="resource/spacer.gif" width="1" height="1"></td>
        <td><img src="resource/spacer.gif" width="6"></td>
        <td class="leftAligned" width="12"><img src="resource/spacer.gif" width="1" height="1"></td>
      </tr>
    </tbody>
  </table></td>
</tr>
<tr>
  <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r5_c1" src="resource/index_r5_c1.jpg" alt="" width="750" border="0" height="1"></td>
</tr>
<tr>

<td class="innerPg" valign="top">
<table width="750" border="0" cellpadding="0" cellspacing="0">
  <tbody>
  <tr>
    <td rowspan="2" width="8"><img src="resource/spacer.gif" width="1" height="1"></td>
    <td colspan="2" class="breadcrumbs" valign="bottom" height="20">&nbsp;</td>
    <td rowspan="2" width="12"><img src="resource/spacer.gif" width="1" height="1"></td>
  </tr>
  <tr>
  
  <td class="Content" valign="top" width="180"><p>&nbsp;</p>
    <br>
    <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
      <tbody>
        <tr>
          <td class="sidenavtxt" align=""><p><em><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Welcome,</font></em> <font size="1" face="Verdana, Arial, Helvetica, sans-serif"><span><?php echo ($_SESSION['FirstName']); ?></p>
            <p><a href="dashboard.php">DashBoard</a><br>
            </p>
            </tr>
      </tbody>
    </table>
    <br>
    <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
      <tbody>
        
      </tbody>
    </table>
     <?php include("marquee.php"); ?> 
    <br>
    <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
      <tbody>
        
      </tbody>
    </table>
    <br>
    <script language="JavaScript1.2" src="resource/misc.htm"></script></td>
  <td rowspan="2" valign="top" class="error">
  <img src="images/transaction_period.gif" width="350" height="30">
  <hr size="1" width="500" align="left" color="#cccccc">
  <table width="500" border="0" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
    
    <td class="toplinks2" valign="top">
    <div align="justify">
      <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
        <tbody>
        <tr>
          <td valign="top">
          <p class="homeContentSmaller">
            <?php if ((isset($_GET['success'])) && (($_GET['success'])== "ok")){ echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Record Inserted Successfully</td>
  </tr>
</tbody></table>" ;} ?>
            <?php if ((isset($_GET['success'])) && (($_GET['success'])== "duplicate")){ echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Record is Already Existing</td>
  </tr>
</tbody></table>" ;} ?>
          </p>
          <p class="homeContentSmaller">           
          </p>
          <form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
            
            <div id="patoc"  name="patoc" style="display: block; margin-left: 0em;">
              <fieldset>
                <legend class="contentHeader1"> Create Transaction Period
                </legend>
                <table width="96%" align="center" cellpadding="4" cellspacing="0">
                  <tbody>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" align="right" height="35">Select Date Period<font color="red">*</font></td>
                      <td class="greyBgd" align="left"><input name="DOB" type="text" class="innerBox" id="DOB" value="" readonly>
                        <input src="resource/ew_calendar.gif" alt="Pick a Date" onClick="popUpCalendar(this, this.form.DOB,'mmm - yyyy');return false;" type="image">
                        * </td>
                    </tr>
                    <tr valign="top" align="right">
                      <td width="100%" height="3" colspan="2" align="center" class="Content"><img src="workhistory_files/spacer.gif" width="1" height="1"> <input type="hidden" name="MM_insert" value="eduEntry">
                        <input name="ButtonSearch" type="submit" class="formbutton" id="ButtonSearch" value="Save"> &nbsp;&nbsp;<input name="ButtonSearch2" type="button" class="formbutton" id="ButtonSearch2" value="Reset" onClick="javascript:reset()"></td>
                    </tr>
                  </tbody>
                </table>
              </fieldset>
            </div>
      </form>
          </p>
          <?php if ($totalRows_Existing_Period > 0) { // Show if recordset not empty ?>
  <table width="100%" border="1" class="greyBgd">
    <tr class="homeBox">
     
      <th align="left" scope="col">Period</th>
    </tr>
    <?php do { ?>
      <tr>
        
        <th align="left" scope="col"><?php echo $row_Existing_Period['PayrollPeriod']; ?></th>
      </tr>
      <?php } while ($row_Existing_Period = mysqli_fetch_assoc($Existing_Period)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p><br>
  </p>
        </td>
        </tr>
        
        </tbody>
        </table>
    </div>
    </td>
    
    </tr>
    
    </tbody>
    </table>
  <br>
  <br>
  <br>
  </td>
  
  </tr>
  
  <tr>
    <td class="Content" valign="top">&nbsp;</td>
  </tr>
  </tbody>
</table>
</td>

</tr>

<tr>
  <td class="innerPg" valign="top" height="1"><img name="index_r7_c1" src="resource/index_r7_c1.jpg" alt="" border="0"></td>
</tr>
<tr>
  <td class="innerPg" valign="top" height="21"><table class="contentHeader1" width="750" border="0" cellpadding="0" cellspacing="0" height="21">
    <tbody>
      <tr>
        <td class="rightAligned" width="10">&nbsp;</td>
        <td class="baseNavTxt">&nbsp;</td>
        <td class="leftAligned" width="12">&nbsp;</td>
      </tr>
    </tbody>
  </table></td>
</tr>
<tr>
  <td class="innerPg" valign="top" height="1"><img name="index_r9_c1"mysql_free_result($country);9_c1.jpg" alt="" width="750" border="0" height="1"></td>
</tr>
<tr>
  <td class=

mysqli_free_result($SearchResult);

mysqli_free_result($SearchResult);"innerPg" valign=

mysqli_free_result($state2);"top">&nbsp;</td>
</tr>
</tbody>
</table>
</body></html>
<?php
mysqli_free_result($Existing_Period);

mysqli_free_result($logo);

mysqli_free_result($title);
?>
