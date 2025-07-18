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
	

	//$insetTogetMrn = "INSERT INTO tbl_getMrn(no)values(NULL)";
//	mysql_select_db($database_cov, $cov);
// 	$Result_1 = mysql_query($insetTogetMrn, $cov) or die(mysqli_error($cov));
//	$MRN = mysql_insert_id();
//	$getDate = date("y/m/");
//	$MRN = $getDate.$MRN;

if (isset($_POST['status'])){
	$statusCheck = 'Active';
	}else{
		$statusCheck = 'In-Active';
  }
  if (isset($_POST['interest'])){
	$interest = '1';
	}else{
		$interest = '0';
  }


	
  $insertSQL = sprintf("UPDATE tbl_personalinfo SET sfxname = %s, Fname = %s, Mname = %s, 
  						Lname = %s, gender = %s, DOB = %s, Address = %s, Address2 = %s, City = %s, `State` = %s, 
						 MobilePhone = %s,EmailAddress = %s, `status` = '".$statusCheck ."',interest=%s WHERE memberid = %s",
                       GetSQLValueString($cov,$_POST['sfxname'], "text"),
                       GetSQLValueString($cov,$_POST['Fname'], "text"),
                       GetSQLValueString($cov,$_POST['Mname'], "text"),
                       GetSQLValueString($cov,$_POST['Lname'], "text"),
                       GetSQLValueString($cov,$_POST['gender'], "text"),
                       GetSQLValueString($cov,$_POST['DOB'], "date"),
                       GetSQLValueString($cov,$_POST['Address'], "text"),
                       GetSQLValueString($cov,$_POST['Address2'], "text"),
                       GetSQLValueString($cov,$_POST['City'], "text"),
                       GetSQLValueString($cov,$_POST['State'], "text"),
                       GetSQLValueString($cov,$_POST['MobilePhone'], "text"),
                       GetSQLValueString($cov,$_POST['EmailAddress'], "text"),
                       GetSQLValueString($cov,$interest, "int"),             
					             GetSQLValueString($cov,$_POST['initial_mrn'], "text"));
					  
					   

  mysqli_select_db($cov,$database_cov);
  $Result1 = mysqli_query($cov,$insertSQL) or die(mysqli_error($cov));
  

 
 mysqli_select_db($cov,$database_cov);
$query_nokCheck = sprintf("SELECT * FROM tbl_nok WHERE memberid = %s",GetSQLValueString($cov,$_POST['initial_mrn'], "int"));
$nokCheck = mysqli_query($cov,$query_nokCheck); //or die(mysqli_error($cov));


if (!$nokCheck) {
        $message  = 'Invalid query: ' . mysqli_error($cov) . "\n";
        $message .= 'Whole query: ' . $query_nokCheck;
        die($message);
    }

//echo $query_nokCheck;

$row_nokCheck = mysqli_fetch_assoc($nokCheck);
$totalRows_nokCheck = mysqli_num_rows($nokCheck);

//echo $totalRows_nokCheck;
 
 if ($totalRows_nokCheck > 0 ){
 
 $insertSQL_NOK = sprintf("UPDATE tbl_nok SET memberid = %s , NOkName = %s, NOKRelationship = %s, NOKPhone = %s, NOKAddress = %s WHERE memberid = %s",
                       GetSQLValueString($cov,$_POST['new_mrn'], "text"),
					   GetSQLValueString($cov,$_POST['NOkName'], "text"),
                       GetSQLValueString($cov,$_POST['NOKRelationship'], "text"),
					   GetSQLValueString($cov,$_POST['NOKPhone'], "text"),
					    GetSQLValueString($cov,$_POST['NOKAddress'], "text"),
					   GetSQLValueString($cov,$_POST['initial_mrn'], "int"));
						mysqli_select_db($cov,$database_cov);
  $Result3 = mysqli_query($cov,$insertSQL_NOK) or die(mysqli_error($cov));
 }else {
	 
	 $insertSQL_NOK = sprintf("INSERT INTO tbl_nok (memberid, NOkName, NOKRelationship, NOKPhone, NOKAddress) VALUES (%s, %s, %s, %s,%s)",
                       GetSQLValueString($cov,$_POST['new_mrn'], "text"),
					   GetSQLValueString($cov,$_POST['NOkName'], "text"),
                       GetSQLValueString($cov,$_POST['NOKRelationship'], "text"),
					   GetSQLValueString($cov,$_POST['NOKPhone'], "text"),
					    GetSQLValueString($cov,$_POST['NOKAddress'], "text"));
						mysqli_select_db($cov,$database_cov);
  $Result3 = mysqli_query($cov,$insertSQL_NOK) or die(mysqli_error($cov));
	 
	 
	 }

$insertGoTo = "edit_registration.php?success=ok";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));

}

mysqli_select_db($cov, $database_cov);
$query_nokRelationship = "SELECT nok_relationship.relationship FROM nok_relationship";
$nokRelationship = mysqli_query($cov, $query_nokRelationship) or die(mysqli_error($cov));
$row_nokRelationship = mysqli_fetch_assoc($nokRelationship);
$totalRows_nokRelationship = mysqli_num_rows($nokRelationship);

mysqli_select_db($cov,$database_cov);
$query_state2 = "SELECT * FROM state_nigeria";
$state2 = mysqli_query($cov,$query_state2) or die(mysqli_error($cov));
$row_state2 = mysqli_fetch_assoc($state2);
$totalRows_state2 = mysqli_num_rows($state2);

$col_editRecords = "-1";
if (isset($_GET['memberid'])) {
  $col_editRecords = $_GET['memberid'];
}
mysqli_select_db($cov,$database_cov);
$query_editRecords = sprintf("SELECT tbl_personalinfo.memberid, tbl_personalinfo.sfxname, tbl_personalinfo.Fname, tbl_personalinfo.Mname, tbl_personalinfo.passport,tbl_personalinfo.Lname,tbl_personalinfo.interest, tbl_personalinfo.MaidenName, tbl_personalinfo.Mothersname, tbl_personalinfo.gender, tbl_personalinfo.bloodGroup, tbl_personalinfo.Status, tbl_personalinfo.DOB, tbl_personalinfo.Address, tbl_personalinfo.Address2, tbl_personalinfo.State, tbl_personalinfo.City, tbl_personalinfo.countryOrigin, tbl_personalinfo.StateOfOrigin, tbl_personalinfo.Tribe, tbl_personalinfo.EducationLevel, tbl_personalinfo.Occupation, tbl_personalinfo.Religion, tbl_personalinfo.MobilePhone, tbl_personalinfo.EmailAddress, tbl_personalinfo.DateOfReg, tbl_nok.NOkName, tbl_nok.NOKRelationship, tbl_nok.NOKPhone, tbl_nok.NOKAddress FROM tbl_personalinfo left JOIN tbl_nok ON tbl_nok.memberid = tbl_personalinfo.memberid WHERE tbl_personalinfo.memberid = %s", GetSQLValueString($cov,$col_editRecords, "text"));
$editRecords = mysqli_query($cov,$query_editRecords) or die(mysqli_error($cov));
$row_editRecords = mysqli_fetch_assoc($editRecords);
$totalRows_editRecords = mysqli_num_rows($editRecords);

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

if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Search")) {

}

?>
<html>

<head>


    <title><?php echo $row_title['value']; ?> - Edit Member's Registration</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">

    <!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004-->
    <link href="resource/oouth.css" rel="stylesheet" type="text/css">
    <script language="JavaScript" src="resource/general.js" type="text/javascript"></script>
    <script type="text/javascript" language="javascript">
    function makeRequest(url, divID) {

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

        } else

        if (window.ActiveXObject) { // IE

            //alert("fdsa");

            try {

                http_request = new ActiveXObject("Msxml2.XMLHTTP");

            } catch (e) {

                lgErr.error("this is exception1 in his_secpatientreg.jsp" + e);

                try {

                    http_request = new ActiveXObject("Microsoft.XMLHTTP");

                } catch (e) {

                    lgErr.error("this is exception2 in his_secpatientreg.jsp" + e);

                }

            }

        }

        if (!http_request) {

            alert('Giving up :( Cannot create an XMLHTTP instance');

            return false;

        }

        http_request.onreadystatechange = function() {
            alertContents(http_request, divID);
        };

        http_request.open('GET', url, true);

        http_request.send(null);

    }

    function alertContents(http_request, divid) {

        if (http_request.readyState == 4) {

            //alert(http_request.status);

            //alert(divid);

            if (http_request.status == 200) {

                document.getElementById(divid).innerHTML = http_request.responseText;

            } else {

                //document.getElementById(divid).innerHTML=http_request.responseText;

                alert("There was a problem with the request");

            }

        }

    }


    function onSelected(oForm) {

        //options[document.form.profile.selectedIndex].value

        //var s=oForm.selectedIndex;

        var s1 = oForm.value;

        //  alert("ddddddddddddd"+s+s1);

        var url = "state.php?Country=" + s1;

        // alert(url);

        makeRequest(url, "state");

    }

    function jumptoURL(oForm) {

        window.location = "schedule.php?patientid=" + oForm.value;


    }

    function checkedd() {

        document.getElementById("new_mrn").readOnly = false;
        document.getElementById("new_mrn").focus();
        //alert("s");
    }


    function onSelectedSearchMRN(searchM) {



        //
        //options[document.form.profile.selectedIndex].value

        //var s=oForm.selectedIndex;

        //var mrnSearch = document.getElementById("mrn").value;
        //var dob = document.getElementById("SDOb").value;




        var url = "uploadSearch_editRecords.php?SearchMRN=" + searchM;

        //var url="patSearch.php?SearchMRN="+searchM;
        //alert(searchM);
        //alert(mrn+lastname+Firstname+phoneno+dob);

        makeRequest(url, "UploadSearchResult");
        //document.getElementsById("mrnExist").checked = "true";
        if (document.getElementById("mrnExist2").checked == true) {
            document.getElementById("new_mrn").focus();
            document.getElementById("new_mrn").readOnly = false;
        }
    }

    function onSelectedSearch() {

        //options[document.form.profile.selectedIndex].value

        //var s=oForm.selectedIndex;

        var mrn = document.getElementById("SearchMRN").value;
        var dob = document.getElementById("SDOb").value;




        var url = "patSearch.php?SearchMRN=" + mrn;

        //alert(mrn+lastname+Firstname+phoneno+dob);

        makeRequest(url, "patSearchResult");

    }

    function reset() {
        document.getElementById("SearchMRN").value = "";
        document.getElementById("SLastName").value = "";
        document.getElementById("SFirstName").value = "";
        document.getElementById("SphoneNo").value = "";
        document.getElementById("SDOb").value = "";

    }

    function mrn() {
        alert(document.getElementById("initial_mrn").value);
    }

    function clearbox() {
        document.getElementById("SearchMRN").focus();
        document.getElementById("SearchMRN").value = "";
    }

    function Ipopcases1(oForm) {

        //options[document.form.profile.selectedIndex].value

        var s = oForm.selectedIndex;

        var s1 = oForm.options[oForm.selectedIndex].value;

        // alert("ddddddddddddd"+s+s1);

        var url = "config/patientype.php?apptype=" + s1;

        // alert(url);

        makeRequest(url, "patcategory");



    }


    function Expand90(itemm) {

        //alert(itemm.value);



        if (itemm.value == "NC") {

            // alert("in new");

            //document.getElementById('patocpdetailsiframe').style.display="none";

            //document.getElementById("modeofpay").style.display="none";

            document.getElementById("patnc").style.display = "block";

            //document.getElementById("patnp").style.display="block";

            document.getElementById("patoc").style.display = "none";
            document.getElementById("patSearchResult").style.display = "none";

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











        } else {

            //alert("in old");

            //document.getElementById("modeofpay").style.display="block";

            document.getElementById("patnc").style.display = "none";

            document.getElementById("patoc").style.display = "block";
            document.getElementById("patSearchResult").style.display = "block"
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



    function ischecked(oFormEle, msg)

    {

        var s = oFormEle.value

        if (s == "na") {

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

    function isSpace(s, message) {


        ss = s.value;

        var length = ss.length;

        var c = ss.charAt(0);

        var d = ss.charAt(length - 1);

        //    var regexpr =/[A-Za-z0-9]/;

        //     result= regexpr.test(c)

        //	if (!result)


        if (c == " " || d == " ")

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
    function sameasabove() {
        if (document.eduEntry.same.checked) {
            document.eduEntry.NOKAddress.value = document.eduEntry.Address.value + " " + document.eduEntry.Address2
                .value + " " + document.eduEntry.City.value + " " + document.eduEntry.State.value;
        } else {
            document.eduEntry.NOKAddress.value = "";
        }
    }

    function validate() {
        //var cansubmit=false
        if (document.eduEntry.Submit.value == "Save") {
            if (document.eduEntry.sfxname.value == "na") {
                alert("Please provide your Title!");
                document.eduEntry.sfxname.focus();
                return false;
            }

            cansubmit = isSpace(document.eduEntry.Fname, "Space not allowed")
            if (document.eduEntry.Fname.value == "") {
                alert("Please provide your First Name!");
                document.eduEntry.Fname.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.Lname, "Space not allowed");
            if (document.eduEntry.Lname.value == "") {
                alert("Please provide your Last Name!");
                document.eduEntry.Lname.focus();
                return false;
            }
            if (document.eduEntry.patCategory.value == "na") {
                alert("Please provide Patient Category!");
                document.eduEntry.patCategory.focus();
                return false;
            }
            // if( document.eduEntry.DOB.value == "" )
            //   {
            //     alert( "Please provide Patient Date of Birth!" );
            //     document.eduEntry.DOB.focus() ;
            //     return false;
            //   }
            cansubmit = isSpace(document.eduEntry.Address, "Space not allowed")
            if (document.eduEntry.Address.value == "") {
                alert("Please provide Patient House No!");
                document.eduEntry.Address.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.City, "Space not allowed")
            if (document.eduEntry.City.value == "") {
                alert("Please provide Patient City Address!");
                document.eduEntry.City.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.State, "Space not allowed")
            if (document.eduEntry.State.value == "") {
                alert("Please provide State!");
                document.eduEntry.State.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.MobilePhone, "Space not allowed")
            if (document.eduEntry.MobilePhone.value == "") {
                alert("Please provide Mobile Phone No!");
                document.eduEntry.MobilePhone.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.NOkName, "Space not allowed")
            if (document.eduEntry.NOkName.value == "") {
                alert("Please provide Next of Kin Name!");
                document.eduEntry.NOkName.focus();
                return false;
            }
            if (document.eduEntry.NOKRelationship.value == "na") {
                alert("Please provide Next of Kin Relationship!");
                document.eduEntry.NOKRelationship.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.NOKPhone, "Space not allowed")
            if (document.eduEntry.NOKPhone.value == "") {
                alert("Please provide Next of Kin Phone No!");
                document.eduEntry.NOKPhone.focus();
                return false;
            }
            cansubmit = isSpace(document.eduEntry.NOKAddress, "Space not allowed")
            if (document.eduEntry.NOKAddress.value == "") {
                alert("Please provide Next of Kin Address!");
                document.eduEntry.NOKAddress.focus();
                return false;
            }
            return (true);
        }
    }
    </script>





    <script type="text/javascript" src="resource/popcalendar.js"></script>
    <script>
    var isNS4 = (navigator.appName == "Netscape") ? 1 : 0;

    function auto_logout(iSessionTimeout, iSessTimeOut, sessiontimeout)

    {

        window.setTimeout('', iSessionTimeout);

        window.setTimeout('winClose()', iSessTimeOut);

    }

    function winClose() {

        //alert("Your Application session is expired.");

        if (!isNS4)

        {

            window.navigate("index.php");

        } else

        {

            window.location = "index.php";

        }

    }

    auto_logout(1440000, 1500000, 1500)
    </script>
</head>

<body>
    <div onClick="bShow=true" id="calendar" style="z-index: 999; position: absolute; visibility: hidden;">
        <table style="border: 1px solid rgb(160, 160, 160); font-size: 11px; font-family: arial;" width="220"
            bgcolor="#ffffff">
            <tbody>
                <tr bgcolor="#0000aa">
                    <td>
                        <table width="218">
                            <tbody>
                                <tr>
                                    <td style="padding: 2px; font-family: arial; font-size: 11px;">
                                        <font color="#ffffff"><b><span id="caption"><span id="spanLeft"
                                                        style="border: 1px solid rgb(51, 102, 255); cursor: pointer;"
                                                        onmouseover='swapImage("changeLeft","left2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to previous month. Hold mouse button to scroll automatically."'
                                                        onClick="javascript:decMonth()"
                                                        onmouseout='clearInterval(intervalID1);swapImage("changeLeft","left1.gif");this.style.borderColor="#3366FF";window.status=""'
                                                        onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartDecMonth()",500)'
                                                        onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img
                                                            id="changeLeft" src="resource/left1.gif" width="10"
                                                            border="0" height="11">&nbsp;</span>&nbsp;<span
                                                        id="spanRight"
                                                        style="border: 1px solid rgb(51, 102, 255); cursor: pointer;"
                                                        onmouseover='swapImage("changeRight","right2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to next month. Hold mouse button to scroll automatically."'
                                                        onmouseout='clearInterval(intervalID1);swapImage("changeRight","right1.gif");this.style.borderColor="#3366FF";window.status=""'
                                                        onClick="incMonth()"
                                                        onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartIncMonth()",500)'
                                                        onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img
                                                            id="changeRight" src="resource/right1.gif" width="10"
                                                            border="0" height="11">&nbsp;</span>&nbsp;<span
                                                        id="spanMonth"
                                                        style="border: 1px solid rgb(51, 102, 255); cursor: pointer;"
                                                        onmouseover='swapImage("changeMonth","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a month."'
                                                        onmouseout='swapImage("changeMonth","drop1.gif");this.style.borderColor="#3366FF";window.status=""'
                                                        onClick="popUpMonth()"></span>&nbsp;<span id="spanYear"
                                                        style="border: 1px solid rgb(51, 102, 255); cursor: pointer;"
                                                        onmouseover='swapImage("changeYear","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a year."'
                                                        onmouseout='swapImage("changeYear","drop1.gif");this.style.borderColor="#3366FF";window.status=""'
                                                        onClick="popUpYear()"></span>&nbsp;</span></b></font>
                                    </td>
                                    <td align="right"><a href="javascript:hideCalendar()"><img src="resource/close.gif"
                                                alt="Close the Calendar" width="15" border="0" height="13"></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px;" bgcolor="#ffffff"><span id="content"></span></td>
                </tr>
                <tr bgcolor="#f0f0f0">
                    <td style="padding: 5px;" align="center"><span id="lblToday">Today is <a
                                onmousemove='window.status="Go To Current Month"' onmouseout='window.status=""'
                                title="Go To Current Month" style="text-decoration: none; color: black;"
                                href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">Wed,
                                8 Jun 2011</a></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="selectMonth" style="z-index: 999; position: absolute; visibility: hidden;"></div>
    <div id="selectYear" style="z-index: 999; position: absolute; visibility: hidden;"></div>



    <table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
        <!-- fwtable fwsrc="MTN4U.png" fwbase="index.jpg" fwstyle="Dreamweaver" fwdocid = "1226677029" fwnested="0" -->
        <tbody>
            <tr>
                <td><img src="resource/spacer.gif" alt="" width="750" border="0" height="1"></td>
            </tr>
            <tr>
                <td class="centerAligned" valign="top" height="100">
                    <div align="center"></div>
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
                                <td colspan="5"><img name="top_r1_c1" src="resource/spacer.gif" alt="" width="1"
                                        border="0" height="1"></td>
                                <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
                            </tr>
                            <tr>
                                <td rowspan="4"><img name="top_r2_c1" src="resource/spacer.gif" alt="" width="1"
                                        border="0" height="1"></td>
                                <td colspan="3" rowspan="4" align="center"><img
                                        src="<?php echo $row_logo['value']; ?>"><img name="top_r4_c4"
                                        src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
                                <td>&nbsp;</td>
                                <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="17"></td>
                            </tr>
                            <tr>
                                <td rowspan="3"><img name="top_r3_c5" src="resource/spacer.gif" alt="" width="1"
                                        border="0" height="1"></td>
                                <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="37"></td>
                            </tr>
                            <tr>
                                <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="25"></td>
                            </tr>
                            <tr>
                                <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="mainNav" valign="top" height="21">
                    <table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
                        <tbody>
                            <tr>
                                <td class="rightAligned" width="10">&nbsp;</td>
                                <td class="mainNavTxt" valign="bottom">
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <!-- fwtable fwsrc="Untitled" fwbase="nav.gif" fwstyle="Dreamweaver" fwdocid = "1284367442" fwnested="0" -->
                                        <tbody>
                                            <tr>
                                                <td><a href="http://careers.mtnonline.com/index.asp"></a></td>
                                                <td><img src="resource/spacer.gif" alt="" width="8" border="0"
                                                        height="8"></td>
                                                <td><a href="http://careers.mtnonline.com/departments.asp"></a></td>
                                                <td><img src="resource/spacer.gif" alt="" width="8" border="0"
                                                        height="8"></td>
                                                <td><a href="http://careers.mtnonline.com/vacancies.asp"></a></td>
                                                <td><img src="resource/spacer.gif" alt="" width="8" border="0"
                                                        height="8"></td>
                                                <td><a href="http://careers.mtnonline.com/lifeatmtn.asp"></a></td>
                                                <td><img src="resource/spacer.gif" alt="" width="8" border="0"
                                                        height="8"></td>
                                                <td><a href="http://careers.mtnonline.com/mycv.asp"></a></td>
                                                <td><img src="resource/spacer.gif" alt="" width="8" border="0"
                                                        height="8"></td>
                                                <td><a href="http://careers.mtnonline.com/logout.asp"></a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td class="leftAligned" width="12">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r3_c1"
                        src="resource/index_r3_c1.jpg" alt="" width="750" border="0" height="1"></td>
            </tr>
            <tr>
                <td class="globalNav" valign="top" height="25">
                    <table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
                        <tbody>
                            <tr>
                                <td class="rightAligned" width="10"><img src="resource/spacer.gif" width="1" height="1">
                                </td>
                                <td><img src="resource/spacer.gif" width="6"></td>
                                <td class="leftAligned" width="12"><img src="resource/spacer.gif" width="1" height="1">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r5_c1"
                        src="resource/index_r5_c1.jpg" alt="" width="750" border="0" height="1"></td>
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

                                <td class="Content" valign="top" width="180">
                                    <p>&nbsp;</p>
                                    <br>
                                    <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td class="sidenavtxt" align="">
                                                    <p><em>
                                                            <font size="1" face="Verdana, Arial, Helvetica, sans-serif">
                                                                Welcome,</font>
                                                        </em>
                                                        <font size="1" face="Verdana, Arial, Helvetica, sans-serif">
                                                            <span><?php echo ($_SESSION['FirstName']); ?>
                                                    </p>
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
                                    <script language="JavaScript1.2" src="resource/misc.htm"></script>
                                </td>
                                <td rowspan="2" valign="top" class="error">
                                    <img src="resource/editPatientInfo.gif" width="350" height="30">
                                    <hr size="1" width="500" align="left" color="#cccccc">
                                    <table width="500" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>

                                                <td class="toplinks2" valign="top">
                                                    <div align="justify">
                                                        <table class="Content" width="100%" border="0" cellpadding="4"
                                                            cellspacing="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td valign="top">
                                                                        <span class="homeContentSmaller">
                                                                            <?php if ((isset($_POST['dtp'])) && (($_POST['dtp'])== ($_SESSION['UserID']))){ echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Your update was successful</td>
  </tr>
</tbody></table>" ;} ?>

                                                                            <br><?php if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Update")) { echo "Medical Record No = ". $MRN ;}?>
                                                                        </span>
                                                                        <form action="<?php echo $editFormAction; ?>"
                                                                            method="POST" name="eduEntry"
                                                                            onSubmit="return(validate()); ">
                                                                            <p>

                                                                            <div id="patnc" name="patnc"
                                                                                style="display: block; margin-left: 0em;">
                                                                                <fieldset>
                                                                                    <legend class="contentHeader1">
                                                                                        Personal Information </legend>
                                                                                    <table width="96%" align="center"
                                                                                        cellpadding="4" cellspacing="0">
                                                                                        <tbody>
                                                                                            <tr valign="top"
                                                                                                align="left">
                                                                                                <td colspan="3"
                                                                                                    height="1"><img
                                                                                                        src="resource/spacer.gif"
                                                                                                        width="1"
                                                                                                        height="1"></td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    align="right"
                                                                                                    height="35">
                                                                                                    Membership No:</td>
                                                                                                <td class="greyBgd"
                                                                                                    align="left"><label
                                                                                                        for="new_mrn"></label>
                                                                                                    <input
                                                                                                        name="new_mrn"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="new_mrn"
                                                                                                        value="<?php echo $row_editRecords['memberid']; ?>"
                                                                                                        readonly>
                                                                                                    <input
                                                                                                        name="initial_mrn"
                                                                                                        type="hidden"
                                                                                                        id="initial_mrn"
                                                                                                        value="<?php echo $row_editRecords['memberid']; ?>">
                                                                                                    </p>

                                                                                                    <div
                                                                                                        id="UploadSearchResult">
                                                                                                    </div>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <script
                                                                                                    language="javascript">
                                                                                                //document.getElementById("new_mrn").focus();
                                                                                                </script>
                                                                                                <td class="greyBgd"
                                                                                                    align="right"
                                                                                                    height="35">Title:
                                                                                                    <font color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd">
                                                                                                    <select
                                                                                                        name="sfxname"
                                                                                                        class="innerBox"
                                                                                                        style="width:145px">
                                                                                                        <option
                                                                                                            value="na"
                                                                                                            <?php if (!(strcmp("na", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            -Select-
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Mr"
                                                                                                            <?php if (!(strcmp("Mr", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            Mr</option>
                                                                                                        <option
                                                                                                            value="Miss"
                                                                                                            <?php if (!(strcmp("Miss", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            Miss
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Mrs"
                                                                                                            <?php if (!(strcmp("Mrs", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            Mrs</option>
                                                                                                        <option
                                                                                                            value="Dr"
                                                                                                            <?php if (!(strcmp("Dr", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            Dr</option>
                                                                                                        <option
                                                                                                            value="Baby"
                                                                                                            <?php if (!(strcmp("Baby", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            Baby
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Master"
                                                                                                            <?php if (!(strcmp("Master", $row_editRecords['sfxname']))) {echo "selected=\"selected\"";} ?>>
                                                                                                            Master
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </td>
                                                                                                <td width="57%"
                                                                                                    rowspan="4"
                                                                                                    class="greyBgd"><img
                                                                                                        src="<?php echo $row_editRecords['passport']; ?>"
                                                                                                        alt="passport"
                                                                                                        width="150"
                                                                                                        height="150" />
                                                                                                </td>

                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">First
                                                                                                    Name: <font
                                                                                                        color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="Fname"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="Fname"
                                                                                                        value="<?php echo $row_editRecords['Fname']; ?>">
                                                                                                    *</td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">Middle
                                                                                                    Name: </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="Mname"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="Mname"
                                                                                                        value="<?php echo $row_editRecords['Mname']; ?>">
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">Last
                                                                                                    Name:<font
                                                                                                        color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="Lname"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="Lname"
                                                                                                        value="<?php echo $row_editRecords['Lname']; ?>">
                                                                                                    *</td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">Gender:
                                                                                                    <font color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">
                                                                                                    <p>
                                                                                                        <label>
                                                                                                            <input
                                                                                                                type="radio"
                                                                                                                name="gender"
                                                                                                                value="Male"
                                                                                                                <?php if($row_editRecords['gender']=="Male"){echo "checked=checked";} ?>>
                                                                                                            Male</label>
                                                                                                        <label>
                                                                                                            <input
                                                                                                                type="radio"
                                                                                                                name="gender"
                                                                                                                value="Female"
                                                                                                                <?php if($row_editRecords['gender']=="Female"){echo "checked=checked";} ?>>
                                                                                                            Female</label>
                                                                                                        <br>
                                                                                                    </p>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">Date of
                                                                                                    Birth [mm/dd/yyyy]:
                                                                                                    <font color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="DOB"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="DOB"
                                                                                                        value="<?php echo $row_editRecords['DOB']; ?>"
                                                                                                        readonly>
                                                                                                    <input
                                                                                                        src="resource/ew_calendar.gif"
                                                                                                        alt="Pick a Date"
                                                                                                        onClick="popUpCalendar(this, this.form.DOB,'yyyy-mm-dd');return false;"
                                                                                                        type="image">
                                                                                                    *
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">House
                                                                                                    No.:<font
                                                                                                        color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="Address"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="Address"
                                                                                                        value="<?php echo $row_editRecords['Address']; ?>">
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">Address
                                                                                                    2: </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="Address2"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="Address2"
                                                                                                        value="<?php echo $row_editRecords['Address2']; ?>">
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">City:
                                                                                                    <font color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="City"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="City"
                                                                                                        value="<?php echo $row_editRecords['City']; ?>">
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">State:
                                                                                                    <font color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><select
                                                                                                        name="State"
                                                                                                        class="innerBox"
                                                                                                        id="State">
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Select State
                                                                                                            ...</option>
                                                                                                        <?php
do {  
?>
                                                                                                        <option
                                                                                                            value="<?php echo $row_state2['State']?>"
                                                                                                            <?php if($totalRows_editRecords > 0) {if (!(strcmp($row_state2['State'], $row_editRecords['State']))) {echo "selected=\"selected\"";} }?>>
                                                                                                            <?php echo $row_state2['State']?>
                                                                                                        </option>
                                                                                                        <?php
} while ($row_state2 = mysqli_fetch_assoc($state2));
  $rows = mysqli_num_rows($state2);
  if($rows > 0) {
      mysqli_data_seek($state2, 0);
	  $row_state2 = mysqli_fetch_assoc($state2);
  }
?>
                                                                                                    </select>

                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">Mobile
                                                                                                    Phone:<font
                                                                                                        color="red">*
                                                                                                    </font>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="MobilePhone"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="MobilePhone"
                                                                                                        value="<?php echo $row_editRecords['MobilePhone']; ?>">
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    width="43%"
                                                                                                    align="right"
                                                                                                    height="35">E-mail
                                                                                                    Address: </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left"><input
                                                                                                        name="EmailAddress"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="EmailAddress"
                                                                                                        value="<?php echo $row_editRecords['EmailAddress']; ?>">
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    width="57%"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    align="right"
                                                                                                    height="35">Status
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    align="left"><input
                                                                                                        <?php if (!(strcmp($row_editRecords['Status'],"Active"))) {echo "checked=\"checked\"";} ?>
                                                                                                        type="checkbox"
                                                                                                        name="status"
                                                                                                        id="status">
                                                                                                    Active
                                                                                                    <label
                                                                                                        for="status"></label>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="middle"
                                                                                                align="left">
                                                                                                <td class="greyBgd"
                                                                                                    align="right"
                                                                                                    height="35">Charge Interest?
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    align="left"><input
                                                                                                        <?php if (!(strcmp($row_editRecords['interest'],"1"))) {echo "checked=\"checked\"";} ?>
                                                                                                        type="checkbox"
                                                                                                        name="interest"
                                                                                                        id="interest">
                                                                                                    Yes
                                                                                                    <label
                                                                                                        for="interest"></label>
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    align="left">&nbsp;
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="top"
                                                                                                align="left">
                                                                                                <td colspan="3"
                                                                                                    valign="middle"
                                                                                                    align="center"
                                                                                                    height="10">
                                                                                                    <p>
                                                                                                    <fieldset>
                                                                                                        <legend
                                                                                                            class="contentHeader1">
                                                                                                            Next of Kin
                                                                                                            <script
                                                                                                                language="JavaScript"
                                                                                                                type="text/JavaScript">
                                                                                                                <!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                                                                                                            </script>
                                                                                                        </legend>
                                                                                                        <table
                                                                                                            width="96%"
                                                                                                            align="center"
                                                                                                            cellpadding="4"
                                                                                                            cellspacing="0">
                                                                                                            <tbody>
                                                                                                                <tr
                                                                                                                    valign="top">
                                                                                                                    <td width="36%"
                                                                                                                        height="35"
                                                                                                                        align="right"
                                                                                                                        valign="middle"
                                                                                                                        class="greyBgd">
                                                                                                                        Next
                                                                                                                        of
                                                                                                                        Kin:
                                                                                                                        <font
                                                                                                                            color="red">
                                                                                                                            *
                                                                                                                        </font>
                                                                                                                    </td>
                                                                                                                    <td class="greyBgd"
                                                                                                                        valign="middle"
                                                                                                                        width="64%">
                                                                                                                        <input
                                                                                                                            name="NOkName"
                                                                                                                            type="text"
                                                                                                                            class="innerBox"
                                                                                                                            id="NOkName"
                                                                                                                            value="<?php echo $row_editRecords['NOkName']; ?>">
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                                <tr
                                                                                                                    valign="top">
                                                                                                                    <td height="35"
                                                                                                                        align="right"
                                                                                                                        valign="middle"
                                                                                                                        class="greyBgd">
                                                                                                                        Relationship
                                                                                                                        to
                                                                                                                        Member:
                                                                                                                        <font
                                                                                                                            color="red">
                                                                                                                            *
                                                                                                                        </font>
                                                                                                                    </td>
                                                                                                                    <td class="greyBgd"
                                                                                                                        valign="middle">
                                                                                                                        <select
                                                                                                                            name="NOKRelationship"
                                                                                                                            class="innerBox"
                                                                                                                            id="NOKRelationship"
                                                                                                                            title="<?php echo $row_editRecords['NOKRelationship']; ?>">
                                                                                                                            <option
                                                                                                                                selected="selected"
                                                                                                                                value=""
                                                                                                                                <?php if (!(strcmp("", "Parent"))) {echo "selected=\"selected\"";} ?>>
                                                                                                                                Select
                                                                                                                                ...
                                                                                                                            </option>
                                                                                                                            <?php
do {  
?>
                                                                                                                            <option
                                                                                                                                value="<?php echo $row_nokRelationship['relationship']?>"
                                                                                                                                <?php if($totalRows_editRecords > 0 ) {if (!(strcmp($row_nokRelationship['relationship'], $row_editRecords['NOKRelationship']))) {echo "selected=\"selected\"";} } ?>>
                                                                                                                                <?php echo $row_nokRelationship['relationship']?>
                                                                                                                            </option>
                                                                                                                            <?php
} while ($row_nokRelationship = mysqli_fetch_assoc($nokRelationship));
  $rows = mysqli_num_rows($nokRelationship);
  if($rows > 0) {
      mysqli_data_seek($nokRelationship, 0);
	  $row_nokRelationship = mysqli_fetch_assoc($nokRelationship);
  }
?>
                                                                                                                        </select>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                                <tr
                                                                                                                    valign="top">
                                                                                                                    <td height="35"
                                                                                                                        align="right"
                                                                                                                        valign="middle"
                                                                                                                        class="greyBgd">
                                                                                                                        Next
                                                                                                                        of
                                                                                                                        Kin
                                                                                                                        Phone
                                                                                                                        No:
                                                                                                                        <font
                                                                                                                            color="red">
                                                                                                                            *
                                                                                                                        </font>
                                                                                                                    </td>
                                                                                                                    <td class="greyBgd"
                                                                                                                        valign="middle">
                                                                                                                        <input
                                                                                                                            name="NOKPhone"
                                                                                                                            type="text"
                                                                                                                            class="innerBox"
                                                                                                                            id="NOKPhone"
                                                                                                                            value="<?php echo $row_editRecords['NOKPhone']; ?>">
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                                <tr
                                                                                                                    valign="top">
                                                                                                                    <td height="35"
                                                                                                                        align="right"
                                                                                                                        valign="middle"
                                                                                                                        class="greyBgd">
                                                                                                                        Next
                                                                                                                        of
                                                                                                                        Kin
                                                                                                                        Address:
                                                                                                                        <font
                                                                                                                            color="red">
                                                                                                                            *
                                                                                                                        </font>
                                                                                                                    </td>
                                                                                                                    <td class="greyBgd"
                                                                                                                        valign="middle">
                                                                                                                        <input
                                                                                                                            name="NOKAddress"
                                                                                                                            type="text"
                                                                                                                            class="innerBox"
                                                                                                                            id="NOKAddress"
                                                                                                                            value="<?php echo $row_editRecords['NOKAddress']; ?>">
                                                                                                                        <input
                                                                                                                            name="same"
                                                                                                                            type="checkbox"
                                                                                                                            id="same"
                                                                                                                            onChange="javascript:sameasabove()"
                                                                                                                            value="checked">
                                                                                                                        <label
                                                                                                                            for="same">
                                                                                                                            Same
                                                                                                                            as
                                                                                                                            above</label>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                                <tr valign="top"
                                                                                                                    align="right">
                                                                                                                    <td colspan="2"
                                                                                                                        class="Content"
                                                                                                                        height="3">
                                                                                                                        <img src="workhistory_files/spacer.gif"
                                                                                                                            width="1"
                                                                                                                            height="1">
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                        <br>
                                                                                                    </fieldset>
                                                                                                    </p>
                                                                                                    <?php if ((isset($_GET['success'])) && (($_GET['success'])== "ok")){  ?>
                                                                                                    <script
                                                                                                        language="javascript">
                                                                                                    alert(
                                                                                                        "Record Updated Successfully"
                                                                                                        );
                                                                                                    window.location
                                                                                                        .href =
                                                                                                        'dashboard.php';
                                                                                                    </script>


                                                                                                    <?php }?>
                                                                                                    <p>
                                                                                                        <input
                                                                                                            name="Submit"
                                                                                                            class="formbutton"
                                                                                                            value="Save"
                                                                                                            type="submit">
                                                                                                    </p>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="top"
                                                                                                align="left">
                                                                                                <td colspan="3"
                                                                                                    height="3"><img
                                                                                                        src="resource/spacer.gif"
                                                                                                        width="1"
                                                                                                        height="1"></td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </fieldset>

                                                                                <script language="JavaScript"
                                                                                    type="text/JavaScript">
                                                                                    <!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                                                                                </script><br>

                                                                                <p>

                                                                            </div>
                                                                            <br>
                                                                            <p>
                                                                            <div id="patoc" name="patoc"
                                                                                style="display: none; margin-left: 0em;">
                                                                                <fieldset>
                                                                                    <legend class="contentHeader1">
                                                                                        Search Existing Patient</legend>
                                                                                    <table width="96%" align="center"
                                                                                        cellpadding="4" cellspacing="0">
                                                                                        <tbody>
                                                                                            <tr valign="top">
                                                                                                <td width="36%"
                                                                                                    height="35"
                                                                                                    align="right"
                                                                                                    valign="middle"
                                                                                                    class="greyBgd">
                                                                                                    Enter Search
                                                                                                    Criteria e.g. First
                                                                                                    Name,Last Name,
                                                                                                    Telephone No, MRN:
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    valign="middle"
                                                                                                    width="64%"><input
                                                                                                        name="SearchMRN"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="SearchMRN">
                                                                                                    <span
                                                                                                        class="errorBox"><a
                                                                                                            onClick="javascript:clearbox()"
                                                                                                            href="#">X</a></span>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="top">
                                                                                                <td height="35"
                                                                                                    align="right"
                                                                                                    valign="middle"
                                                                                                    class="greyBgd">
                                                                                                    Date of Birth :
                                                                                                </td>
                                                                                                <td class="greyBgd"
                                                                                                    valign="middle">
                                                                                                    <input name="SDOb"
                                                                                                        type="text"
                                                                                                        class="innerBox"
                                                                                                        id="SDOb"
                                                                                                        readonly>
                                                                                                    <input
                                                                                                        src="resource/ew_calendar.gif"
                                                                                                        alt="Pick a Date"
                                                                                                        onClick="popUpCalendar(this, this.form.SearchMRN,'yyyy-mm-dd');return false;"
                                                                                                        type="image">
                                                                                                    *
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr valign="top"
                                                                                                align="right">
                                                                                                <td height="3"
                                                                                                    colspan="2"
                                                                                                    align="center"
                                                                                                    class="Content"><img
                                                                                                        src="workhistory_files/spacer.gif"
                                                                                                        width="1"
                                                                                                        height="1">
                                                                                                    <input type="hidden"
                                                                                                        name="MM_insert"
                                                                                                        value="eduEntry">
                                                                                                    <input
                                                                                                        name="ButtonSearch"
                                                                                                        type="button"
                                                                                                        class="formbutton"
                                                                                                        id="ButtonSearch"
                                                                                                        value="Search"
                                                                                                        onClick="javascript:onSelectedSearch()">
                                                                                                    &nbsp;&nbsp;<input
                                                                                                        name="ButtonSearch2"
                                                                                                        type="button"
                                                                                                        class="formbutton"
                                                                                                        id="ButtonSearch2"
                                                                                                        value="Reset"
                                                                                                        onClick="javascript:reset()">
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </fieldset>
                                                                            </div>
                                                                        </form>
                                                                        </p>

                                                                        <div id="patSearchResult"></div>
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
                <td class="innerPg" valign="top" height="1"><img name="index_r7_c1" src="resource/index_r7_c1.jpg"
                        alt="" width="750" border="0" height="1"></td>
            </tr>
            <tr>
                <td class="innerPg" valign="top" height="21">
                    <table class="contentHeader1" width="750" border="0" cellpadding="0" cellspacing="0" height="21">
                        <tbody>
                            <tr>
                                <td class="rightAligned" width="10">&nbsp;</td>
                                <td class="baseNavTxt">&nbsp;</td>
                                <td class="leftAligned" width="12">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="innerPg" valign="top" height="1"><img name="index_r9_c1.jpg" alt="" width="750" border="0"
                        height="1"></td>
            </tr>
            <tr>
                <td class="innerPg" valign="top">&nbsp;


                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
<?php
mysqli_free_result($nokRelationship);

mysqli_free_result($editRecords);

mysqli_free_result($title);

mysqli_free_result($logo);

mysqli_free_result($state2);
?>