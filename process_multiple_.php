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
$query_status = "SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) AS namess, tbl_contributions.contribution, tbl_contributions.loan, (tbl_contributions.contribution +tbl_contributions.loan) as total FROM tbl_contributions INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tbl_contributions.membersid ";
$status = mysqli_query($cov,$query_status) or die(mysqli_error($cov));
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);

mysqli_select_db($cov,$database_cov);
$query_grandtotal = "SELECT sum(tbl_contributions.loan)+ sum(tbl_contributions.contribution) FROM tbl_contributions";
$grandtotal = mysqli_query($cov,$query_grandtotal) or die(mysqli_error($cov));
$row_grandtotal = mysqli_fetch_assoc($grandtotal);
$totalRows_grandtotal = mysqli_num_rows($grandtotal);

mysqli_select_db($cov,$database_cov);
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods order by periodid desc";
$Period = mysqli_query($cov,$query_Period) or die(mysqli_error($cov));
$row_Period = mysqli_fetch_assoc($Period);
$totalRows_Period = mysqli_num_rows($Period);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

mysqli_select_db($cov,$database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov,$query_logo) or die(mysqli_error($cov));
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);
 session_start();
if (!isset($_SESSION['UserID'])){
header("Location:index.php");} else{
 
}
 ?>
<?php
if (isset($_SERVER['QUERY_STRING'])) {
 
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}




$editFormAction = $_SERVER['PHP_SELF'];
?>
<html><head>


<title><?php echo $row_title['value']; ?> -  Members Status</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">

<!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004-->
<link href="skills_files/oouth.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="skills_files/general.js" type="text/javascript"></script>
<script type="text/javascript" src="skills_files/popcalendar.js"></script>
<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript" language="javascript">
function clearBox()
{
	//alert("hi")
fo();
document.forms[0].CoopName.value = ""	
document.forms[0].txtCoopid.value = ""	
document.getElementById('txtLoanBalance').value = "";	
document.forms[0].txtAmount.value = ""
document.forms[0].txtBankName.value = ""
document.forms[0].txNarration.value = ""
document.forms[0].txtbankcode.value = ""
document.forms[0].txtAccountNo_hidden.value = ""
document.getElementById('txtLoanBalance').value = ""
document.forms[0].txtLoanBalance.value = ""

fo();
}

function getContribution(id) {		
		
		
		//document.getElementById('status_old').style.display="none"; 
		
		
		if (document.getElementById('PeriodId').value == "na"){
		alert("Please select from Period");
			document.getElementById('PeriodId').focus();
		}else {

		var periodid = id;//document.getElementById('PeriodId').value;
		
		var strURL="getContributionList.php?periodid="+periodid;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('status').innerHTML=req.responseText;	
						document.getElementById('wait').style.visibility = "hidden";
						//alert("hi");					
					} else {document.getElementById('wait').style.visibility = "visible";
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
			}
	}
	
function getStatus(id) {		
		//alert("hi");
		
		document.getElementById('status_old').style.display="none"; 
		var strURL="getStatus.php?id="+id;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('status').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
function fo() {document.eduEntry.CoopName.focus();}
function fo2() {document.eduEntry.txtAmount.focus();
}

function lookup(inputString) {
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions').hide();
		} else {
			//alert("msg");
			$.post("rpc.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function fill(thisValue) {
		$('#txtCoopid').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
	function fill2(thisValue) {
		$('#CoopName').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
	function fill3(thisValue) {
		$('#txtCommodityBalance').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}

            function checknum(oFormEle){





                var sValue=oFormEle.value;

     

                if(isNaN(sValue) == true){



                    alert("Please Enter Numeric Value only");

                    oFormEle.value="";

                    oFormEle.focus()

                    return false;

                }

                return true;

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

                var url="doctor_dept.php?departmentid="+s1;

                // alert(url);

                makeRequest(url,"doctor");

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


function Expand90(itemm){

                //alert(itemm.value);

            

                    if(itemm.value=="NC"){

                       // alert("in new");

                        //document.getElementById('patocpdetailsiframe').style.display="none";

                        //document.getElementById("modeofpay").style.display="none";

                        document.getElementById("patnc").style.display="block";

                        //document.getElementById("patnp").style.display="block";

                        document.getElementById("patoc").style.display="none";

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

function process2(){
	//alert(document.getElementById('PeriodId2').value);
	
	if (document.getElementById('PeriodFrom').value == "na"){
		alert("Please select a Period to Process Transaction")} else{
	 var PeriodFrom = document.getElementById('PeriodFrom').value;
	 var PeriodTo = document.getElementById('PeriodTo').value;
	 window.location.href="processMulti.php?PeriodFrom="+PeriodFrom+"&PeriodTo="+PeriodTo;
	}
}
function process(){
	//alert(document.getElementById('PeriodId').value);
	if (document.forms['eduEntry'].PeriodTo.value == "na"){
		alert("Please select a Period to Process Transaction")} else{
	var PeriodFrom = document.getElementById('PeriodFrom').value;
	 var PeriodTo = document.getElementById('PeriodTo').value;
	 window.location.href="processMulti.php?PeriodFrom="+PeriodFrom+"&PeriodTo="+PeriodTo;
	}
}
function validate(){
//var cansubmit=false
     //alert(document.eduEntry.userid.value);
	  if (document.eduEntry.items.value.trim() == ""){
	 alert( "Pls Input Item!" );
     document.eduEntry.items.focus() ;
     return false;}
	 if (document.eduEntry.costPrice.value.trim() == ""){
	 alert( "Pls Input Cost Price!" );
     document.eduEntry.costPrice.focus() ;
     return false;}
	  if (document.eduEntry.SellingPrice.value.trim() == ""){
	 alert( "Pls Input Selling Price!" );
     document.eduEntry.SellingPrice.focus() ;
     return false;}
	 return( true );
}


</script>
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
<style type="text/css">
	body {
		font-family: Helvetica;
		font-size: 11px;
		color: #000;
	}
	
	h3 {
		margin: 0px;
		padding: 0px;	
	}

	.suggestionsBox {
		position: relative;
		left: 30px;
		margin: 10px 0px 0px 0px;
		width: 200px;
		background-color: #212427;
		-moz-border-radius: 7px;
		-webkit-border-radius: 7px;
		border: 2px solid #000;	
		color: #fff;
	}
	
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	
	.suggestionList li {
		
		margin: 0px 0px 3px 0px;
		padding: 3px;
		cursor: pointer;
	}
	
	.suggestionList li:hover {
		background-color: #659CD8;
	}
</style>
</head>
<body>
<div onClick="bShow=true" id="calendar" style="z-index: 999; position: absolute; visibility: hidden;">
<table style="border: 1px solid rgb(160, 160, 160); font-size: 11px; font-family: arial;" width="220" bgcolor="#ffffff">
<tbody><tr bgcolor="#0000aa"><td><table width="218">
<tbody><tr><td style="padding: 2px; font-family: arial; font-size: 11px;"><font color="#ffffff"><b><span id="caption"><span id="spanLeft" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeLeft","left2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to previous month. Hold mouse button to scroll automatically."' onClick="javascript:decMonth()" onmouseout='clearInterval(intervalID1);swapImage("changeLeft","left1.gif");this.style.borderColor="#3366FF";window.status=""' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartDecMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeLeft" src="skills_files/left1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanRight" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeRight","right2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to next month. Hold mouse button to scroll automatically."' onmouseout='clearInterval(intervalID1);swapImage("changeRight","right1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="incMonth()" onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartIncMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeRight" src="skills_files/right1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanMonth" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeMonth","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a month."' onmouseout='swapImage("changeMonth","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpMonth()"></span>&nbsp;<span id="spanYear" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeYear","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a year."' onmouseout='swapImage("changeYear","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpYear()"></span>&nbsp;</span></b></font></td><td align="right"><a href="javascript:hideCalendar()"><img src="skills_files/close.gif" alt="Close the Calendar" width="15" border="0" height="13"></a></td></tr></tbody></table></td></tr><tr><td style="padding: 5px;" bgcolor="#ffffff"><span id="content"></span></td></tr><tr bgcolor="#f0f0f0"><td style="padding: 5px;" align="center"><span id="lblToday">Today is <a onmousemove='window.status="Go To Current Month"' onmouseout='window.status=""' title="Go To Current Month" style="text-decoration: none; color: black;" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">Wed, 8 Jun	2011</a></span></td></tr></tbody></table></div><div id="selectMonth" style="z-index: 999; position: absolute; visibility: hidden;"></div><div id="selectYear" style="z-index: 999; position: absolute; visibility: hidden;"></div>



<table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
<!-- fwtable fwsrc="MTN4U.png" fwbase="index.jpg" fwstyle="Dreamweaver" fwdocid = "1226677029" fwnested="0" -->
  <tbody><tr>
   <td><img src="skills_files/spacer.gif" alt="" width="750" border="0" height="1"></td>
  </tr>

  <tr>
   <td class="centerAligned" valign="top" height="100"><div align="center"></div>
<table width="750" border="0" cellpadding="0" cellspacing="0">
<!-- fwtable fwsrc="Untitled" fwbase="top.gif" fwstyle="Dreamweaver" fwdocid = "2000728079" fwnested="0" -->
  <tbody><tr>
   <td><img src="skills_files/spacer.gif" alt="" width="7" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="78" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="491" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="153" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="21" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"></td>
  </tr>

  <tr>
   <td colspan="5"><img name="top_r1_c1" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="11"></td>
  </tr>
  <tr>
   <td colspan="4" rowspan="4" align="center"><img name="top_r2_c1" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"><img src="<?php echo $row_logo['value']; ?>"><img name="top_r4_c4" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"></td>
    <td>&nbsp;</td>
   <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="17"></td>
  </tr>
  <tr>
   <td rowspan="3"><img name="top_r3_c5" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"></td>
   <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="37"></td>
  </tr>
  <tr>
   <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="25"></td>
  </tr>
  <tr>
   <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="11"></td>
  </tr>
</tbody></table>

</td>
  </tr>
  <tr>
   <td class="mainNav" valign="top" height="21"><table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
     <tbody><tr>
       <td class="mainNavTxt" valign="bottom">&nbsp;</td>
       <td class="leftAligned" width="12">&nbsp;</td>
     </tr>
   </tbody></table>
</td>
  </tr>
  <tr>
   <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r3_c1" src="skills_files/index_r3_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="globalNav" valign="top" height="25"><table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
     <tbody><tr>
       <td class="rightAligned" width="10"><img src="skills_files/spacer.gif" width="1" height="1"></td>
       <td><img src="skills_files/spacer.gif" width="6"></td>
       <td class="leftAligned" width="12"><img src="skills_files/spacer.gif" width="1" height="1"></td>
     </tr>
   </tbody></table>

</td>
  </tr>
  <tr>
   <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r5_c1" src="skills_files/index_r5_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top"><table width="900" border="0" cellpadding="0" cellspacing="0">
     <tbody><tr>
       <td rowspan="2" width="8"><img src="skills_files/spacer.gif" width="1" height="1"></td>
       <td colspan="2" class="breadcrumbs" valign="bottom" height="20"><a href="http://careers.mtnonline.com/index.asp"> </a></td>
       <td rowspan="2" width="12"><img src="skills_files/spacer.gif" width="1" height="1"></td>
     </tr>
     <tr>
       <td class="Content" valign="top" width="180">

<p>&nbsp;</p><br>

<table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
  <tbody><tr> 
    <td class="sidenavtxt" align=""> <em><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Welcome,</font></em> 
      <font size="1" face="Verdana, Arial, Helvetica, sans-serif"><span><?php echo ($_SESSION['FirstName']); ?><br>
      <img src="skills_files/spacer.gif" width="1" border="0" height="8"><img src="skills_files/arrow_bullets2.gif" border="0">		  
<a href="index.php">Logout</a>
      </span></font> </td>
  </tr>
</tbody></table>
<br>
<table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
  <tbody><tr>
    <td colspan="2" class="sidenavtxt" width="100%" align=""><p><br>
      </p></td>
  </tr>
  
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td width="100%" align="" class="sidenavtxt"><a href="registration.php">Registration</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="dashboard.php">DashBoard</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="process2.php">Process Transaction</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="editContributions.php">Edit Contribution</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="addloan.php">Add Loan</a></td>
    </tr>
  
</tbody></table>
<br>
 <?php include("marquee.php"); ?> 
<br>

</td>
       <td rowspan="2" class="Content" valign="top"><hr size="1" width="500" align="left" color="#cccccc">
         <table width="700" border="0" align="right" cellpadding="0" cellspacing="0">
           <tbody><tr>
             <td class="toplinks2" valign="top"><div align="justify">
                 <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
                   <tbody><tr>
                     <td valign="top"><span class="homeContentSmaller">
                       
                       </span><?php if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Save")) {echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Record Saved successful</td>
  </tr>
</tbody></table>" ; ;}if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Update")) {echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Records Updated successful</td>
  </tr>
</tbody></table>" ; ;}?>
                        <form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                         <fieldset>
                         <legend class="contentHeader1"> Process<a name="top"></a></legend>
                         <table width="66%" align="center" cellpadding="4" cellspacing="0">
                           <tbody><tr valign="top" align="left">
                           <td width="100%" height="1"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                         </tr>
                             <tr valign="top" align="left">
                               <td height="27" align="center" class="greyBgd"> <strong class="tableHeaderContentDarkBlue">Member's Deductions</strong></td>
                             </tr>
                       
                           <tr valign="top" align="left">
                           	<td>From:</td>
                             <td height="3" align="center"><p><img src="skills_files/spacer.gif" width="1" height="1"></p>
                               <p>
                                 <select name="PeriodFrom" id="PeriodFrom" onChange="getContribution(this.value)" >
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
                                 </select>
                                 <input name="Submit" type="button" class="formbutton" id="Submit2" value="Process" onClick="javascript:process2()"><br>
                               <div id="wait" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;" class="overlay"> <img src="images/pageloading.gif" alt="" class="area">Please wait... </div>
                         
                       
                     <div id="status"></div></td>
                           </tr>
                          
                         <tr valign="top" align="left">
                         	<td>To Period</td>
                           <td height="3" align="center"><select name="PeriodTo" id="PeriodTo" onChange="getContribution(this.value)" >
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
                           </select>                             <input name="Search" type="button" class="formbutton" id="Submit" value="Process" onClick="javascript:process()"></td>
                       </tr>
                           </tbody>																													
                         </table>
                       </fieldset>
                         <input type="hidden" name="MM_insert" value="eduEntry">
                         <input type="hidden" name="MM_update" value="eduEntry">
                       
                        </form>
                        <p>&nbsp;</p>
                         
                       
  <p><br>
                       </p></td></tr>
                 </tbody></table>
             </div></td>
           </tr>
         </tbody></table>
         <br>         <br>            <br>          </td>
       </tr>
     <tr>
       <td class="Content" valign="top">&nbsp;</td>
     </tr>
   </tbody></table></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top" height="1"><img name="index_r7_c1" src="skills_files/index_r7_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top" height="21"><table class="contentHeader1" width="750" border="0" cellpadding="0" cellspacing="0" height="21">
  <tbody><tr>
    <td class="rightAligned" width="10">&nbsp;</td>
    <td class="baseNavTxt">&nbsp;</td>
    <td class="leftAligned" width="12">&nbsp;</td>
  </tr>
</tbody></table>
</td>
  </tr>
  <tr>
   <td class="innerPg" valign="top" height="1"><img name="index_r9_c1" src="skills_files/index_r9_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top">&nbsp;</td>
  </tr>
</tbody></table>
</body></html>
<?php
mysqli_free_result($status);

mysqli_free_result($grandtotal);

mysqli_free_result($Period);

mysqli_free_result($title);

mysqli_free_result($logo);

//mysqli_free_result($maxVisit);
?>
