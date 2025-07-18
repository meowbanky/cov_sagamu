<?php require_once('Connections/cov.php'); ?>
<?php session_start();
if (!isset($_SESSION['UserID'])){
header("Location:index.php");} else{
 
}
?>
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


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Save")) {
  $insertSQL = sprintf("INSERT INTO tblusers (UserID, username, UPassword, CPassword, firstname, middlename, lastname,dateofRegistration) VALUES (%s, %s, password(%s), password(%s), %s, %s, %s, Now())",
                       GetSQLValueString($cov,$_POST['username'], "text"),
					   GetSQLValueString($cov,$_POST['username'], "text"),
                       GetSQLValueString($cov,$_POST['UPassword'], "text"),
					   GetSQLValueString($cov,$_POST['CPassword'], "text"),
                       GetSQLValueString($cov,$_POST['Firstname'], "text"),
                       GetSQLValueString($cov,$_POST['middlename'], "text"),
                       GetSQLValueString($cov,$_POST['lastname'], "text"));
                      

  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$insertSQL) or die(mysqli_error($cov));
  $save = "insert";
}

if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Update")) {
  $updateSQL = sprintf("UPDATE tblusers SET Username=%s, UPassword= password(%s), CPassword=password(%s), firstname=%s, middlename=%s, lastname=%s WHERE userid=%s",
                       GetSQLValueString($cov,$_POST['username'], "text"),
                       GetSQLValueString($cov,$_POST['UPassword'], "text"),
					   GetSQLValueString($cov,$_POST['CPassword'], "text"),
                       GetSQLValueString($cov,$_POST['Firstname'], "text"),
                       GetSQLValueString($cov,$_POST['middlename'], "text"),
                       GetSQLValueString($cov,$_POST['lastname'], "text"),
                       GetSQLValueString($cov,$_POST['hduserid'], "int"));

  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$updateSQL) or die(mysqli_error($cov));
$save = "update";
}

if ((isset($_GET['deleteid'])) && ($_GET['deleteid'] != "")) {
  $deleteSQL = sprintf("DELETE FROM tblusers WHERE userid=%s",
                       GetSQLValueString($cov,$_GET['deleteid'], "int"));

  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$deleteSQL) or die(mysqli_error($cov));
 $save = "delete"; 
  
}

$maxRows_username = 10;
$pageNum_username = 0;
if (isset($_GET['pageNum_username'])) {
  $pageNum_username = $_GET['pageNum_username'];
}
$startRow_username = $pageNum_username * $maxRows_username;

mysqli_select_db($cov,$database_cov);
$query_username = "SELECT tblusers.UserID, tblusers.PlainPassword,tblusers.firstname, tblusers.middlename, tblusers.lastname, tblusers.Username, tblusers.dateofRegistration FROM tblusers ";
$query_limit_username = sprintf("%s LIMIT %d, %d", $query_username, $startRow_username, $maxRows_username);
$username = mysqli_query($cov,$query_limit_username) or die(mysqli_error($cov));
$row_username = mysqli_fetch_assoc($username);

if (isset($_GET['totalRows_username'])) {
  $totalRows_username = $_GET['totalRows_username'];
} else {
  $all_username = mysqli_query($cov,$query_username);
  $totalRows_username = mysqli_num_rows($all_username);
}
$totalPages_username = ceil($totalRows_username/$maxRows_username)-1;

$col_editUsername = "-1";
if (isset($_GET['userid'])) {
  $col_editUsername = $_GET['userid'];
}
mysqli_select_db($cov,$database_cov);
$query_editUsername = sprintf("SELECT tblusers.UserID, tblusers.firstname, tblusers.middlename, tblusers.lastname, tblusers.Username FROM tblusers WHERE userid = %s", GetSQLValueString($cov,$col_editUsername, "int"));
$editUsername = mysqli_query($cov,$query_editUsername) or die(mysqli_error($cov));
$row_editUsername = mysqli_fetch_assoc($editUsername);
$totalRows_editUsername = mysqli_num_rows($editUsername);

mysqli_select_db($cov,$database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov,$query_logo) or die(mysqli_error($cov));
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

$queryString_username = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_username") == false && 
        stristr($param, "totalRows_username") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_username = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_username = sprintf("&totalRows_username=%d%s", $totalRows_username, $queryString_username);



$currentPage = $_SERVER["PHP_SELF"];

if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Search")) {

}

?>
<html>
<head>


<title><?php echo $row_title['value']; ?> -User's Registration</title>
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
	
	window.location="schedule.php?patientid="+oForm.value;
	
	
	}
	
function checkedd (){
		
		if((document.getElementById('CPassword').value) !=(document.getElementById('UPassword').value)){
			alert("Password in Password field does not Match Password in Confirm Password Field");
			}
		//
	}
				  
	
       function onSelectedSearchMRN(searchM) {
			
			
			
			//
                //options[document.form.profile.selectedIndex].value

                //var s=oForm.selectedIndex;

                //var mrnSearch = document.getElementById("mrn").value;
				//var dob = document.getElementById("SDOb").value;

                 
				

                var url="uploadSearch_editRecords.php?SearchMRN="+searchM;
				
				//var url="patSearch.php?SearchMRN="+searchM;
				//alert(searchM);
                //alert(mrn+lastname+Firstname+phoneno+dob);

                makeRequest(url,"UploadSearchResult");
				//document.getElementsById("mrnExist").checked = "true";
				if (document.getElementById("mrnExist2").checked == true){
				document.getElementById("new_mrn").focus();
				document.getElementById("new_mrn").readOnly = false;
				}
            }    
			                
function onSelectedSearch() {

                //options[document.form.profile.selectedIndex].value

                //var s=oForm.selectedIndex;

                var mrn = document.getElementById("SearchMRN").value;
				var dob = document.getElementById("SDOb").value;

                 
				

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
	
	function mrn(){
		alert(document.getElementById("initial_mrn").value);
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

   if (document.eduEntry.Firstname.value.trim() == "" )
   {
     alert( "Please provide your First Name!" );
     document.eduEntry.Firstname.focus() ;
     return false;
   }
    
   if(document.eduEntry.lastname.value.trim() == "" )
   {
     alert( "Please provide your Last Name!" );
     document.eduEntry.lastname.focus() ;
     return false;
   }
   if( document.eduEntry.username.value.trim() == "" )
   {
     alert( "Please provide Username!" );
     document.eduEntry.username.focus() ;
     return false;
   }
   cansubmit=isSpace(document.eduEntry.UPassword,"Space not allowed")
   if( document.eduEntry.UPassword.value.trim() == "" )
   {
     alert( "Please provide Password!" );
     document.eduEntry.UPassword.focus() ;
     return false;
   }
      cansubmit=isSpace(document.eduEntry.CPassword,"Space not allowed")
   if( document.eduEntry.CPassword.value.trim() == "" )
   {
     alert( "Please Confirm Password" );
     document.eduEntry.CPassword.focus() ;
     return false;
   }
  
   if((document.getElementById('CPassword').value) !=(document.getElementById('UPassword').value)){
			alert("Password in Password field does not Match Password in Confirm Password Field");
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
<script type="text/javascript" src="jquery-1.8.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#username').keyup(function(){
        var username = $(this).val(); // Get username textbox using $(this)
        var Result = $('#result'); // Get ID of the result DIV where we display the results
        if(username.length > 2) { // if greater than 2 (minimum 3)
            Result.html('Loading...'); // you can use loading animation here
            var dataPass = 'action=availability&username='+username;
            $.ajax({ // Send the username val to available.php
            type : 'POST',
            data : dataPass,
            url  : 'available.php',
            success: function(responseText){ // Get the result
                if(responseText == 0){
                    Result.html('<span class="success">Available</span>');
                }
                else if(responseText > 0){
                    Result.html('<span class="error">Taken</span>');
                }
                else{
                    alert('Problem with sql query');
                }
            }
            });
        }else{
            Result.html('Enter atleast 3 characters');
        }
        if(username.length == 0) {
            Result.html('');
        }
    });
});
</script>
    <style type="text/css">
        .success
        {
            color: green;
        }
        .error
        {
            color: red;
        }
        
        #username
        {
            width:120px;
            border:solid 1px #000;
            
            font-size:11px;
        }
    </style>
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
              <td></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td></td>
              <td><img src="resource/spacer.gif" alt="" width="8" border="0" height="8"></td>
              <td></a></td>
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
    <td rowspan="2" width="6"><img src="resource/spacer.gif" width="1" height="1"></td>
    <td colspan="2" class="breadcrumbs" valign="bottom" height="20">&nbsp;</td>
    <td rowspan="2" width="4"><img src="resource/spacer.gif" width="1" height="1"></td>
  </tr>
  <tr>
  
  <td class="Content" valign="top" width="150"><p>&nbsp;</p>
    <br>
    <table class="innerWhiteBox" width="100%" border="0" cellpadding="4" cellspacing="0">
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
    <br>
    <br>
    <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
      <tbody>
        
      </tbody>
  </table>
    <br>
    <script language="JavaScript1.2" src="resource/misc.htm"></script></td>
  <td width="590" rowspan="2" valign="top" class="error">
  <img src="resource/register_user.gif" width="350" height="30">
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
          <span class="homeContentSmaller"> 
              <?php if ((isset($save)) && ($save == "insert")){ echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Records Inserted successful</td>
  </tr>
</tbody></table>" ;} ?>
<?php if ((isset($save)) && ($save == "update")){ echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Records Updated successful</td>
  </tr>
</tbody></table>" ;} ?>
<?php if ((isset($save)) && ($save == "delete")){ echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Records Deleted successful</td>
  </tr>
</tbody></table>" ;} ?>
              <br>
            </span>
          <form autocomplete="off" action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate());" >
            <p>
            
            <div id="patnc" name="patnc" style="display: block; margin-left: 0em;">
              <fieldset>
                <legend class="contentHeader1">Personal Information </legend>
                <table width="86%" align="center" cellpadding="4" cellspacing="0">
                  <tbody>
                    <tr valign="top" align="left">
                      <td colspan="2" height="1"><img src="resource/spacer.gif" width="1" height="1"></td>
                    </tr>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" width="43%" align="right" height="35">First Name: <font color="red">*</font></td>
                      <td class="greyBgd" width="57%" align="left"><input name="Firstname" type="text" class="innerBox" id="Firstname" value="<?php if($totalRows_editUsername> 0) {echo $row_editUsername['firstname'];} ?>">
                        *
                          <input name="hduserid" type="hidden" id="hduserid" value="<?php if($totalRows_editUsername> 0) {echo $row_editUsername['UserID'];} ?>"></td>
                    </tr>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" width="43%" align="right" height="35">Middle Name: </td>
                      <td class="greyBgd" width="57%" align="left"><input name="middlename" type="text" class="innerBox" id="middlename" value="<?php if($totalRows_editUsername> 0) {echo $row_editUsername['middlename'];} ?>"></td>
                    </tr>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" width="43%" align="right" height="35">Last Name:<font color="red">*</font></td>
                      <td class="greyBgd" width="57%" align="left"><input name="lastname" type="text" class="innerBox" id="lastname" value="<?php if($totalRows_editUsername> 0) {echo $row_editUsername['lastname'];} ?>">
                        *</td>
                    </tr>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" align="right" height="35">Username Name</td>
                      <td class="greyBgd" align="left"><input name="username" type="text" class="innerBox" id="username" value="<?php if($totalRows_editUsername> 0) {echo $row_editUsername['Username'];} ?>" placeholder="Username" autocomplete="off" ><div class="result" id="result"></div></td>
                    </tr>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" align="right" height="35">Password:</td>
                      <td class="greyBgd" align="left"><input name="UPassword" type="password" class="innerBox" id="UPassword"></td>
                    </tr>
                    <tr valign="middle" align="left">
                      <td class="greyBgd" align="right" height="35">Confirm Password:</td>
                      <td class="greyBgd" align="left"><input name="CPassword" type="password" class="innerBox" id="CPassword" onBlur="Javascript:checkedd()"></td>
                    </tr>
                  <tr valign="top" align="left">
                    <td colspan="2" valign="middle" align="center" height="10"><p>
                      
                      <input name="Submit" class="formbutton" value=<?php if (isset($_GET['userid'])){echo "Update";}else {echo "Save";} ?> type="submit">
                      <input name="MM_insert" type="hidden" id="MM_insert" value="eduEntry">
                      </p></td>
                  </tr>
                  <tr valign="top" align="left">
                    <td colspan="2" height="3"><img src="resource/spacer.gif" width="1" height="1"></td>
                  </tr>
                  </tbody>
                  </table>
              </fieldset>
                      
                      <p>  
                      
            </div>
            <br>
            <p>
            
          </form>
          </p><fieldset>
                       <legend class="contentHeader1">Registered Users
                       <script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                      </script>
                      
					  
					    </legend><table width="96%" align="center" cellpadding="4" cellspacing="0">
					       <tbody>
					         <tr valign="top" align="right">
					           <td class="content" align="left" height="1"></td>
                                   <td colspan="11" class="content" height="1"><a href="#top">Top</a></td>
                                </tr>
					          <tr valign="top">
					            <td class="greyBgdHeader" valign="middle" height="35"><strong>Username</strong></td>
					            <td class="greyBgdHeader" valign="middle"><strong>Surname</strong></td>
					            <td class="greyBgdHeader" valign="middle"><strong>First Name</strong></td>
					            <td class="greyBgdHeader" valign="middle"><strong>Middle Name</strong></td>
					            <td class="greyBgdHeader" valign="middle"><strong>Password</strong></td>
                                  <td class="greyBgdHeader" valign="middle" height="35"><strong>Edit</strong></td> 
                               <td class="greyBgdHeader" valign="middle" height="35"><strong>Delete</strong></td>
                                </tr>
                              <?php do { ?>
                                <tr valign="top">
                                  <td class="greyBgd" valign="middle" height="35"><?php echo $row_username['Username']; ?></td>
                                  <td class="greyBgd" valign="middle"><?php echo $row_username['lastname']; ?></td>
                                  <td class="greyBgd" valign="middle"><?php echo $row_username['firstname']; ?></td>
                                  <td class="greyBgd" valign="middle"><?php echo $row_username['middlename']; ?></td>
                                  <td class="greyBgd" valign="middle"><?php echo $row_username['PlainPassword']; ?></td>
                                  <td class="greyBgd" valign="middle"><a href="registeruser.php?userid=<?php echo $row_username['UserID']; ?>">Edit</a></td>
                                  <td class="greyBgd" valign="middle"><a href="registeruser.php?deleteid=<?php echo $row_username['UserID']; ?>" onClick="GP_popupConfirmMsg('Are you sure you want to delete this entry?\rTo continue, click \'Ok\' otherwise, click \'Cancel\'');return document.MM_returnValue">Delete</a></td>
                                </tr><?php } while ($row_username = mysqli_fetch_assoc($username)); ?>
                                <tr valign="top">
                                  <td class="greyBgd" valign="middle" height="35"><a href="<?php printf("%s?pageNum_username=%d%s", $currentPage, max(0, $pageNum_username - 1), $queryString_username); ?>">Last</a>&nbsp;</td>
                                  <td class="greyBgd" valign="middle"><a href="<?php printf("%s?pageNum_username=%d%s", $currentPage, max(0, $pageNum_username - 1), $queryString_username); ?>">Previous</a></td>
                                  <td class="greyBgd" valign="middle"></td>
                                  <td class="greyBgd" valign="middle"></td>
                                  <td class="greyBgd" valign="middle">&nbsp;</td>
                                  <td class="greyBgd" valign="middle"><a href="<?php printf("%s?pageNum_username=%d%s", $currentPage, min($totalPages_username, $pageNum_username + 1), $queryString_username); ?>">Next</a></td>
                                  <td class="greyBgd" valign="middle"><a href="<?php printf("%s?pageNum_username=%d%s", $currentPage, 0, $queryString_username); ?>">First</a></td>
                                </tr>
                                
<tr valign="top" align="left">
  <td colspan="12" height="3"><img src="education_files/spacer.gif" width="1" height="1"></td>
                            </tr>
					             </tbody>
					      </table>
					   </fieldset>
          <p><br>
      </p><script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                      </script>
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
  <td class="innerPg" valign="top" height="1"><img name="index_r7_c1" src="resource/index_r7_c1.jpg" alt="" width="750" border="0" height="1"></td>
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
  <td class="innerPg" valign="top" height="1"><img name="index_r9_c1.jpg" alt="" width="750" border="0" height="1"></td>
</tr>
<tr>
    <td class = "innerPg" valign="top">&nbsp;

  
    </td>
</tr>
</tbody>
</table>
</body></html>
<?php
mysqli_free_result($username);

mysqli_free_result($editUsername);

mysqli_free_result($logo);

mysqli_free_result($title);

?>
