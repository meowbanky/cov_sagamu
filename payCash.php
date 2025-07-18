<?php require_once('Connections/hms.php'); ?>
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
$saved = '';
mysql_select_db($database_hms, $hms);
//$query_status = "SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) as namess, sum(tlb_mastertransaction.Contribution) as Contribution,  (sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) as Loan, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- sum(tlb_mastertransaction.loanRepayment)) as Loanbalance, sum(tlb_mastertransaction.withdrawal) as withdrawal FROM tlb_mastertransaction INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid group by patientid";
//$status = mysql_query($query_status, $hms) or die(mysql_error());
//$row_status = mysql_fetch_assoc($status);
//$totalRows_status = mysql_num_rows($status);

mysql_select_db($database_hms, $hms);
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods order by periodid desc";
$Period = mysql_query($query_Period, $hms) or die(mysql_error());
$row_Period = mysql_fetch_assoc($Period);
$totalRows_Period = mysql_num_rows($Period);

mysql_select_db($database_hms, $hms);
$query_memberlist = "SELECT tbl_personalinfo.sfxname,tbl_personalinfo.patientid, concat(ifnull(tbl_personalinfo.Lname,''),', ',ifnull(tbl_personalinfo.Fname,''),' ',ifnull(tbl_personalinfo.Mname,'')) AS namee,ifnull(tbl_personalinfo.gender,'Male') as gender, tbl_personalinfo.DOB, tbl_personalinfo.Address, tbl_personalinfo.Address2, tbl_personalinfo.City, tbl_personalinfo.State, tbl_personalinfo.MobilePhone, tbl_personalinfo.passport, tbl_nok.NOkName, tbl_nok.NOKRelationship, tbl_nok.NOKPhone, tbl_nok.NOKAddress, tbl_personalinfo.dept FROM tbl_personalinfo LEFT JOIN tbl_nok ON tbl_nok.patientId = tbl_personalinfo.patientid WHERE `Status` = 'Active' ORDER BY patientid";
$memberlist = mysql_query($query_memberlist, $hms) or die(mysql_error());
$row_memberlist = mysql_fetch_assoc($memberlist);
$totalRows_memberlist = mysql_num_rows($memberlist);
 session_start();
if (!isset($_SESSION['UserID'])){
header("Location:index.php");} else{
 
}
 ?>
<?php
$finalfile = '';
if (isset($_SERVER['QUERY_STRING'])) {
 
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "eduEntry")) {
 
if (isset ($_FILES["file"]["name"])){
$validextensions = array("jpeg", "jpg", "png");
    $temporary = explode(".", $_FILES["file"]["name"]);
    $file_extension = end($temporary);

    if ((($_FILES["file"]["type"] == "image/png") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/jpeg")
            ) //&& ($_FILES["file"]["size"] < 100000)//Approx. 100kb files can be uploaded.
            && in_array($file_extension, $validextensions)) {

        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br/><br/>";
        } else {
            
            echo "<span>Your File Uploaded Succesfully...!!</span><br/>";
            //echo "<br/><b>File Name:</b> " . $_FILES["file"]["name"] . "<br>";
            //echo "<b>Type:</b> " . $_FILES["file"]["type"] . "<br>";
            //echo "<b>Size:</b> " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
            //echo "<b>Temp file:</b> " . $_FILES["file"]["tmp_name"] . "<br>";


            if (file_exists("teller/" . $_FILES["file"]["name"])) {
                echo $_FILES["file"]["name"] . " <b>already exists.</b> ";
            } else {
				$filetime = date('dmyhis');
				$target_dir = "teller/";
				$target_file = $target_dir . basename($_FILES["file"]["name"]);
				$target_size = (((100*1000) / $_FILES["file"]["size"])*100);
				
				if ($_FILES["file"]["type"] == "image/jpeg"){
				$image = imagecreatefromjpeg(($_FILES["file"]["tmp_name"]));
				imagejpeg($image, "teller/". $filetime.".".$file_extension, $target_size);
				}elseif ($_FILES["file"]["type"] == "image/png") {
				$image = imagecreatefrompng(($_FILES["file"]["tmp_name"]));
				imagepng($image, "teller/". $filetime.".".$file_extension, 0.9);
				}
            //$finalfile = move_uploaded_file($target_file, $target_dir . $target_file.".".$file_extension);
				
				 $finalfile = "teller/". $filetime.".".$file_extension;				
				//$imageLocation = "teller/" . $_FILES["file"]["name"];
				
				//$insertSQL = sprintf("UPDATE tbl_personalinfo SET passport = %s WHERE patientid = %s",
                  //GetSQLValueString($imageLocation, "text"),
				  //GetSQLValueString($_GET['patientid'], "text"));
					   
					   
				  //mysql_select_db($database_hms, $hms);
				  //$Result1 = mysql_query($insertSQL, $hms) or die(mysql_error());
				
				
				
				
               // $imgFullpath = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/'. "passport/" . $_FILES["file"]["name"];
				
				//$uploadedForm = "../edit_registration.php?patientid=".$_GET['patientid'];
				
				//echo "<b>Stored in:</b><a href = '$imgFullpath' target='_blank'> " .$imgFullpath.'<a><br>';
				//echo "<b>View Uploaded Form in:</b><a href = '$uploadedForm' target='_blank'> " ."here".'<a>';
            }
        
        }
    
			}
			
	 $insertSQL = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, repayment_bank,teller_upload) VALUES (%s, %s, %s,%s)",
                       GetSQLValueString($_POST['PeriodId'], "int"),
                       GetSQLValueString($_POST['txtCoopid'], "int"),
                       GetSQLValueString($_POST['cashAmount'], "double"),
					   GetSQLValueString($finalfile, "text"));

  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQL, $hms) or die(mysql_error());
  $saved = 'success';
	
	
	$insertSQL = sprintf("INSERT INTO tbl_teller (periodid, memberid, repayment_bank,teller_upload) VALUES (%s, %s, %s,%s)",
                       GetSQLValueString($_POST['PeriodId'], "int"),
                       GetSQLValueString($_POST['txtCoopid'], "int"),
                       GetSQLValueString($_POST['cashAmount'], "double"),
					   GetSQLValueString($finalfile, "text"));

  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQL, $hms) or die(mysql_error());
  $saved = 'success';
  
	} else {
        echo "<span>***Invalid file Size or Type***<span>";
    }


}
?>
<html><head>


<title>MHWUN -  Cash Payment</title>
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
function getcontribution(id) {		
		
		if(id > 0){
		document.getElementById('Save').style.display="none";
		var strURL="getLoanBalCashpayment.php?id="+id;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('information').innerHTML=req.responseText;						
					} else {
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
		
		if (document.getElementById('txtCoopid').value.trim() == ""){
			alert("Please Search Member's ID to get Status");
			document.getElementById('txtCoopid').focus();
			}else if (document.getElementById('PeriodId').value == "na"){
			alert("Please Select Period to get Status");
			document.getElementById('PeriodId').focus();
				}else{
		document.getElementById('status_old').style.display="none"; 
		var period = document.getElementById('PeriodId').value;
		var strURL="getStatus.php?id="+id+"&period="+period;
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
						document.getElementById('wait').style.visibility = "visible";
						document.getElementById('status').style.visibility = "hidden";
					}
				}				
						
			req.open("GET", strURL, true);
			req.send(null);
		}		
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
		left: -30px;
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
.suggestionsBox1 {		position: relative;
		left: 30px;
		margin: 10px 0px 0px 0px;
		width: 200px;
		background-color: #212427;
		-moz-border-radius: 7px;
		-webkit-border-radius: 7px;
		border: 2px solid #000;	
		color: #fff;
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
   <td colspan="4" rowspan="4" align="center"><img name="top_r2_c1" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"><img src="images/mhwun_logo_web.jpg" width="499" height="95"><img name="top_r4_c4" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"></td>
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
  
</tbody></table>
<?php include("marquee.php"); ?> 
  
</td>
       <td rowspan="2" class="Content" valign="top"><hr size="1" width="500" align="left" color="#cccccc">
         <table width="700" border="0" align="right" cellpadding="0" cellspacing="0">
           <tbody><tr>
             <td class="toplinks2" valign="top"><div align="justify">
                 <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
                   <tbody><tr>
                     <td valign="top"><span class="homeContentSmaller">
                       
                       </span>
                       <form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" enctype="multipart/form-data">
                         <fieldset>
                           <div class="error" id="Save"></div>
                           <table width="97%" align="center" cellpadding="4" cellspacing="0">
                             <tbody>
                               <tr valign="top" align="left">
                                 <td colspan="3" height="1"><img src="education_files/spacer.gif" alt="" width="1" height="1"><?php if ($saved == 'success'){?><div id="saved" class="globalNav">Cash Payment Saved Successfully</div><?php } ?></td>
                               </tr>
                               <tr valign="top" align="left">
                                 <td height="35" align="right" valign="middle" class="greyBgd">Period:</td>
                                 <td valign="middle" class="greyBgd"><select name="PeriodId" id="PeriodId" onChange="setvariablechat()" >
                                   <option value="na">Select Period</option>
                                   <?php
do {  
?>
                                   <option value="<?php echo $row_Period['Periodid']?>"><?php echo $row_Period['PayrollPeriod']?></option>
                                   <?php
} while ($row_Period = mysql_fetch_assoc($Period));
  $rows = mysql_num_rows($Period);
  if($rows > 0) {
      mysql_data_seek($Period, 0);
	  $row_Period = mysql_fetch_assoc($Period);
  }
?>
                                 </select></td>
                               </tr>
                               <tr valign="top" align="left">
                                 <td width="31%" height="35" align="right" valign="middle" class="greyBgd">Name:</td>
                                 <td width="69%" valign="middle" class="greyBgd"><input name="CoopName" type="text" class="innerBox" id="CoopName" onBlur="fill();" onKeyUp="lookup(this.value);" value="" size="30" / autocomplete="off">
                                   <div class="suggestionsBox1" id="suggestions" style="display: none;" > <img src="upArrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                                     <div class="suggestionList" id="autoSuggestionsList" style="position: relative; top: 5px; "></div>
                                   </div>
                                   <input type="button" class="formbutton" onClick="javascript:clearBox()" value="X"></td>
                               </tr>
                               <tr valign="top" align="left">
                                 <td class="greyBgd" valign="middle" align="right" height="35"><p>Staff No:</p></td>
                                 <td class="greyBgd" valign="middle"><input name="txtCoopid" type="text" class="innerBox" id="txtCoopid" readonly onMouseOver="getcontribution(this.value)"></td>
                               </tr>
                               <tr valign="top" align="left">
                                 <td height="35" align="right" valign="middle" class="greyBgd">Loan Balance:</td>
                                 <td height="35" align="left" valign="middle" class="greyBgd"><strong>
                                   <div id="information"></div>
                                 </strong></td>
                               </tr>
                               <tr valign="top" align="left">
                                 <td height="35" align="right" valign="middle" class="greyBgd">Cash Amount:</td>
                                 <td height="35" align="left" valign="middle" class="greyBgd"><input name="cashAmount" type="text" class="innerBox" id="cashAmount" onKeyUp="return checknum(this)"  autocomplete="off"></td>
                               </tr> 
                               <tr valign="top" align="left">
                                 <td height="35" align="right" valign="middle" class="greyBgd">Upload Teller:</td>
                                 <td height="35" align="left" valign="middle" class="greyBgd"><div id="upload">
                            <input type="file" name="file" id="file"/>
                        </div></td>
                               </tr>
                               <tr valign="top" align="left">
                                 <td colspan="3" valign="middle" align="center" height="10"><input name="Submit2" type="submit" class="formbutton" value="Update">
                                   <!-- <input name="Submit" onClick="location.href='editAccountNo.php'" class="formbutton" value="Edit Account No." type="button"></td>
                         -->
                                 </tr>
                               <tr valign="top" align="left">
                                 <td colspan="3" height="3"><img src="education_files/spacer.gif" alt="" width="1" height="1"></td>
                               </tr>
                             </tbody>
                           </table>
                         </fieldset>
                         <input type="hidden" name="MM_update" >
                         <input type="hidden" name="MM_insert" value="eduEntry">
                         <input name="Batch" type="hidden" id="Batch">
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
mysql_free_result($Period);

mysql_free_result($memberlist);



//mysql_free_result($maxVisit);
?>
