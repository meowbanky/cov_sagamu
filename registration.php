<?php require_once('Connections/cov.php'); ?>
<?php session_start();
if (!isset($_SESSION['UserID'])) {
  header("Location:index.php");
} else {
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
  function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
  {


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

mysqli_select_db($cov, $database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov, $query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);


mysqli_select_db($cov, $database_cov);
$query_coopNo = "SELECT MAX(memberid)+1 as coopNo FROM tbl_personalinfo";
$coopNo = mysqli_query($cov, $query_coopNo) or die(mysqli_error($cov));
$row_coopNo = mysqli_fetch_assoc($coopNo);
$totalRows_coopNo = mysqli_num_rows($coopNo);

mysqli_select_db($cov, $database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov, $query_logo) or die(mysqli_error($cov));
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Save")) {


  //$insetTogetMrn = "INSERT INTO tbl_getMrn(no)values(NULL)";
  //	mysqlii_select_db($cov,$database_cov);
  // 	$Result_1 = mysqli_query($insetTogetMrn, $cov) or die(mysqli_error());
  //	$MRN = mysqli_insert_id();
  //	$getDate = date("y/m/");
  //	$MRN = $getDate.$MRN;


  if (isset($_POST['status'])) {
    $statusCheck = 'Active';
  } else {
    $statusCheck = 'In-Active';
  }

  $insertSQL = sprintf(
    "INSERT INTO tbl_personalinfo (sfxname, Fname, Mname, Lname, gender, DOB, Address, Address2, City, `State`, MobilePhone, EmailAddress,DateOfReg,status) VALUES (%s,%s, %s, %s, %s, %s, %s, %s, %s, %s, %s,%s,NOW(),%s)",
    // GetSQLValueString($_POST['new_mrn'], "text"),
    GetSQLValueString($_POST['sfxname'], "text"),
    GetSQLValueString($_POST['Fname'], "text"),
    GetSQLValueString($_POST['Mname'], "text"),
    GetSQLValueString($_POST['Lname'], "text"),
    GetSQLValueString($_POST['gender'], "text"),
    GetSQLValueString($_POST['DOB'], "date"),
    GetSQLValueString($_POST['Address'], "text"),
    GetSQLValueString($_POST['Address2'], "text"),
    GetSQLValueString($_POST['City'], "text"),
    GetSQLValueString($_POST['State'], "text"),
    GetSQLValueString($_POST['MobilePhone'], "text"),
    GetSQLValueString($_POST['EmailAddress'], "text"),
    GetSQLValueString($statusCheck, "text")
  );

  mysqli_select_db($cov, $database_cov);
  $Result1 = mysqli_query($cov, $insertSQL) or die(mysqli_error($cov));

  $id = mysqli_insert_id($cov);
  $_POST['id'] = $id;

  $plainPassword = $_POST['passwordGen'];
  $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

  $insertSQL_login = sprintf(
    "INSERT INTO tblusers (UserID, firstname, middlename, lastname, Username,UPassword,CPassword,PlainPassword,dateofRegistration) VALUES (%s, %s, %s, %s,%s, %s, %s, %s,now())",
    GetSQLValueString($id, "text"),
    GetSQLValueString($_POST['Fname'], "text"),
    GetSQLValueString($_POST['Mname'], "text"),
    GetSQLValueString($_POST['Lname'], "text"),
    GetSQLValueString($id, "text"),
    GetSQLValueString($hashedPassword, "text"),
    GetSQLValueString($hashedPassword, "text"),
    GetSQLValueString($_POST['passwordGen'], "text")
  );
  mysqli_select_db($cov, $database_cov);
  $Result3 = mysqli_query($cov, $insertSQL_login) or die(mysqli_error($cov));


  $insertSQL_NOK = sprintf(
    "INSERT INTO tbl_nok (memberid, NOkName, NOKRelationship, NOKPhone, NOKAddress) VALUES (%s, %s, %s, %s,%s)",
    GetSQLValueString($id, "text"),
    GetSQLValueString($_POST['NOkName'], "text"),
    GetSQLValueString($_POST['NOKRelationship'], "text"),
    GetSQLValueString($_POST['NOKPhone'], "text"),
    GetSQLValueString($_POST['NOKAddress'], "text")
  );
  mysqli_select_db($cov, $database_cov);
  $Result3 = mysqli_query($cov, $insertSQL_NOK) or die(mysqli_error($cov));


  mysqli_select_db($cov, $database_cov);
  $query_settings_contri = "SELECT tbl_settings.contribution FROM tbl_settings";
  $settings_contri = mysqli_query($cov, $query_settings_contri) or die(mysqli_error($cov));
  $row_settings_contri = mysqli_fetch_assoc($settings_contri);
  $totalRows_settings_contri = mysqli_num_rows($settings_contri);



  //$insertSQL_contribution = sprintf("INSERT INTO tbl_contributions (membersid, contribution) VALUES (%s, %s)",
  //                       GetSQLValueString($id, "text"),
  //					   GetSQLValueString($row_settings_contri['contribution'], "int"));
  //						mysqli_select_db($cov,$database_cov);
  //  $Result34 = mysql_query($insertSQL_contribution, $cov) or die(mysqli_error($cov));


  if (isset($_POST['EmailAddress'])) {

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    $from = "noreply@covcoop.com";
    $to = $_POST['EmailAddress'];
    $subject = "Login Credential";
    $message = "Details of your log-in \n Username = " . $id . "\n Password:" . $_POST['passwordGen'];
    $headers = "From:" . $from;
    mail($to, $subject, $message, $headers);
    // echo "The email message was sent.";


  }


  $insertGoTo = "registration.php?success=ok&id=" . $id;
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";

    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

if (!function_exists("GetSQLValueString")) {
  function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
  {
    global $cov;
    $theValue = function_exists("mysql_real_escape_string") ? mysqli_real_escape_string($cov, $theValue) : mysqli_escape_string($cov, $theValue);

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

mysqli_select_db($cov, $database_cov);
$query_tribe = "SELECT tribe.tribe FROM tribe";
$tribe = mysqli_query($cov, $query_tribe) or die(mysqli_error($cov));
$row_tribe = mysqli_fetch_assoc($tribe);
$totalRows_tribe = mysqli_num_rows($tribe);

mysqli_select_db($cov, $database_cov);
$query_nokRelationship = "SELECT nok_relationship.relationship FROM nok_relationship";
$nokRelationship = mysqli_query($cov, $query_nokRelationship) or die(mysqli_error($cov));
$row_nokRelationship = mysqli_fetch_assoc($nokRelationship);
$totalRows_nokRelationship = mysqli_num_rows($nokRelationship);

mysqli_select_db($cov, $database_cov);
$query_state2 = "SELECT * FROM state_nigeria";
$state2 = mysqli_query($cov, $query_state2) or die(mysqli_error($cov));
$row_state2 = mysqli_fetch_assoc($state2);
$totalRows_state2 = mysqli_num_rows($state2);

if ((isset($_POST["ButtonSearch"])) && ($_POST["ButtonSearch"] == "Search")) {
}
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
{

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
?>
<html>

<head>


  <title><?php echo $row_title['value']; ?> - Member's Registration</title>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">

  <!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004-->
  <link href="resource/oouth.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" src="resource/general.js" type="text/javascript"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script type="text/javascript" language="javascript">
    function getXMLHTTP() { //fuction to return the xml http object
      var xmlhttp = false;
      try {
        xmlhttp = new XMLHttpRequest();
      } catch (e) {
        try {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
          try {
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
          } catch (e1) {
            xmlhttp = false;
          }
        }
      }

      return xmlhttp;
    }

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


    function sendsms() {
      //alert("hi");

      //document.getElementById('status_old').style.display="none"; 



      var equality = document.getElementById('equal').value;;
      var staffid = document.getElementById("memberid").value;;
      //var period = id3;

      alert(equality + staffid);

      //window.location = 'mobileapp.php?equality='+equality+'&staffid='+staffid;

      //var period2 = document.getElementById('PeriodId2').value;
      var strURL = 'mobileapp.php?equality=' + equality + '&staffid=' + staffid;
      var req = getXMLHTTP();

      if (req) {

        req.onreadystatechange = function() {
          if (req.readyState == 4) {
            // only if "OK"
            //if (req.status == 200) {						
            //document.getElementById('status').innerHTML=req.responseText;	
            //document.getElementById('status').style.visibility = "visible";
            //document.getElementById('wait').style.visibility = "hidden";						
          } else {
            //document.getElementById('wait').style.width = "100%";
            //document.getElementById('wait').style.visibility = "visible";
            //document.getElementById('status').style.visibility = "hidden";
          }
        }

        req.open("GET", strURL, true);
        req.send(null);
      }
    }

    function sendss() {

      alert("am workin");
    }

    function getStatus(id) {
      //alert("hi");

      //document.getElementById('status_old').style.display="none"; 
      var period = document.getElementById('Period').value;

      if (period == 'na') {
        period = '-1';
      }
      //var period2 = document.getElementById('PeriodId2').value;
      var strURL = "selectedMonth.php?period=" + period;
      var req = getXMLHTTP();

      if (req) {

        req.onreadystatechange = function() {
          if (req.readyState == 4) {
            // only if "OK"
            //if (req.status == 200) {						
            document.getElementById('status').innerHTML = req.responseText;
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
        //document.getElementById("new_mrn").readOnly = false;
      }
    }

    function randomPassword(length) {
      var chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
      var pass = "";
      for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
      }
      return pass;
    }

    function generate() {
      document.getElementById('passwordGen').value = randomPassword(5);
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

      if (document.eduEntry.new_mrn.value.trim() == "") {
        alert("Please provide Staff No.!");
        document.eduEntry.new_mrn.focus();
        return false;
      }
      if (document.eduEntry.sfxname.value == "na") {
        alert("Please provide your Title!");
        document.eduEntry.sfxname.focus();
        return false;
      }

      if (document.eduEntry.Fname.value.trim() == "") {
        alert("Please provide your First Name!");
        document.eduEntry.Fname.focus();
        return false;
      }
      if (document.eduEntry.Lname.value.trim() == "") {
        alert("Please provide your Last Name!");
        document.eduEntry.Lname.focus();
        return false;
      }

      if (document.eduEntry.Address.value.trim() == "") {
        alert("Please provide Patient House No!");
        document.eduEntry.Address.focus();
        return false;
      }

      if (document.eduEntry.City.value.trim() == "") {
        alert("Please provide Patient City Address!");
        document.eduEntry.City.focus();
        return false;
      }
      if (document.eduEntry.State.value.trim() == "") {
        alert("Please provide State!");
        document.eduEntry.State.focus();
        return false;
      }
      if (document.eduEntry.MobilePhone.value.trim() == "") {
        alert("Please provide Mobile Phone No!");
        document.eduEntry.MobilePhone.focus();
        return false;
      }
      if (document.eduEntry.NOkName.value.trim() == "") {
        alert("Please provide Next of Kin Name!");
        document.eduEntry.NOkName.focus();
        return false;
      }
      if (document.eduEntry.NOKRelationship.value == "na") {
        alert("Please provide Next of Kin Relationship!");
        document.eduEntry.NOKRelationship.focus();
        return false;
      }

      if (document.eduEntry.NOKPhone.value == "") {
        alert("Please provide Next of Kin Phone No!");
        document.eduEntry.NOKPhone.focus();
        return false;
      }
      if (document.eduEntry.NOKAddress.value == "") {
        alert("Please provide Next of Kin Address!");
        document.eduEntry.NOKAddress.focus();
        return false;
      }
      if (document.getElementById("mrnExist2").checked == true) {
        alert("Duplicate Staff No Entered!");
        document.eduEntry.new_mrn.focus();
        return false;
      }
      return (true);
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
    <table style="border: 1px solid rgb(160, 160, 160); font-size: 11px; font-family: arial;" width="220" bgcolor="#ffffff">
      <tbody>
        <tr bgcolor="#0000aa">
          <td>
            <table width="218">
              <tbody>
                <tr>
                  <td style="padding: 2px; font-family: arial; font-size: 11px;">
                    <font color="#ffffff"><b><span id="caption"><span id="spanLeft" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeLeft","left2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to previous month. Hold mouse button to scroll automatically."' onClick="javascript:decMonth()" onmouseout='clearInterval(intervalID1);swapImage("changeLeft","left1.gif");this.style.borderColor="#3366FF";window.status=""' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartDecMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeLeft" src="resource/left1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanRight" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeRight","right2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to next month. Hold mouse button to scroll automatically."' onmouseout='clearInterval(intervalID1);swapImage("changeRight","right1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="incMonth()" onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartIncMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeRight" src="resource/right1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanMonth" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeMonth","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a month."' onmouseout='swapImage("changeMonth","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpMonth()"></span>&nbsp;<span id="spanYear" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeYear","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a year."' onmouseout='swapImage("changeYear","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpYear()"></span>&nbsp;</span></b></font>
                  </td>
                  <td align="right"><a href="javascript:hideCalendar()"><img src="resource/close.gif" alt="Close the Calendar" width="15" border="0" height="13"></a></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding: 5px;" bgcolor="#ffffff"><span id="content"></span></td>
        </tr>
        <tr bgcolor="#f0f0f0">
          <td style="padding: 5px;" align="center"><span id="lblToday">Today is <a onmousemove='window.status="Go To Current Month"' onmouseout='window.status=""' title="Go To Current Month" style="text-decoration: none; color: black;" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">Wed,
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
                <td colspan="5"><img name="top_r1_c1" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
                <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
              </tr>
              <tr>
                <td rowspan="4"><img name="top_r2_c1" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
                <td colspan="3" rowspan="4" align="center"><img src="<?php echo $row_logo['value']; ?>"><img name="top_r4_c4" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
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
                  </table>
                </td>
                <td class="leftAligned" width="12">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r3_c1" src="resource/index_r3_c1.jpg" alt="" width="750" border="0" height="1"></td>
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

                    </tbody><?php include("marquee.php"); ?>
                  </table>
                  <br>
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
                            <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
                              <tbody>
                                <tr>
                                  <td valign="top">
                                    <input name="equal" id="equal" type="hidden" value="="><input name="memberid" id="memberid" type="hidden" value="<?php if (isset($_POST['id'])) {
                                                                                                                                                        echo $_POST['id'];
                                                                                                                                                      } ?>"><span class="homeContentSmaller">
                                      <?php if ((isset($_GET['success'])) && (($_GET['success']) == "ok")) {
                                        echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">
  <tbody><tr>
    <td>Record Inserted Successfully</td>
  </tr>
</tbody></table>";
                                      } ?>
                                      <?php if (isset($_GET['id'])) { ?> <script language="javascript" type="text/javascript">
                                          sendsms();
                                        </script><?php } ?>
                                      <br>
                                    </span>
                                    <form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                                      <p>

                                      <div id="patnc" name="patnc" style="display: block; margin-left: 0em;">
                                        <fieldset>
                                          <legend class="contentHeader1">
                                            Personal Information </legend>
                                          <table width="96%" align="center" cellpadding="4" cellspacing="0">
                                            <tbody>
                                              <tr valign="top" align="left">
                                                <td colspan="2" height="1"><img src="resource/spacer.gif" width="1" height="1"></td>
                                              </tr>
                                              <tr>
                                                <td class="greyBgd" align="right" height="35">Coop No:
                                                </td>
                                                <td class="greyBgd">
                                                  <input name="new_mrn" type="text" class="innerBox" id="new_mrn" onBlur="Javascript:onSelectedSearchMRN(this.value)" readonly="readonly" value="<?php echo $row_coopNo['coopNo']; ?>">
                                                </td>
                                              </tr>
                                              <tr>
                                                <script language="javascript">
                                                  //document.getElementById("new_mrn").focus();
                                                </script>
                                                <td class="greyBgd" align="right" height="35">Title:
                                                  <font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd">
                                                  <select name="sfxname" class="innerBox" style="width:145px">
                                                    <option value="na">
                                                      -Select-
                                                    </option>
                                                    <option value="Mr">
                                                      Mr</option>
                                                    <option value="Miss">
                                                      Miss
                                                    </option>
                                                    <option value="Mrs">
                                                      Mrs</option>
                                                    <option value="Dr">
                                                      Dr</option>
                                                    <option value="Baby">
                                                      Baby
                                                    </option>
                                                    <option value="Master">
                                                      Master
                                                    </option>
                                                  </select>
                                                </td>

                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">First
                                                  Name: <font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="Fname" type="text" class="innerBox" id="Fname">
                                                  *</td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">Middle
                                                  Name: </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="Mname" type="text" class="innerBox" id="Mname"></td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">Last
                                                  Name:<font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="Lname" type="text" class="innerBox" id="Lname">
                                                  *</td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">Gender:
                                                  <font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left">
                                                  <p>
                                                    <label>
                                                      <input name="gender" type="radio" value="Male" checked="CHECKED">
                                                      Male</label>
                                                    <label>
                                                      <input type="radio" name="gender" value="Female">
                                                      Female</label>
                                                    <br>
                                                  </p>
                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">Date of
                                                  Birth [mm/dd/yyyy]:
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="DOB" type="text" class="innerBox" id="DOB" readonly>
                                                  <input src="resource/ew_calendar.gif" alt="Pick a Date" onClick="popUpCalendar(this, this.form.DOB,'yyyy-mm-dd');return false;" type="image">
                                                  *
                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">House
                                                  No.:<font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="Address" type="text" class="innerBox" id="Address">
                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">Address
                                                  2: </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="Address2" type="text" class="innerBox" id="Address2">
                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">City:
                                                  <font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="City" type="text" class="innerBox" id="City"></td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">State:
                                                  <font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><select name="State" class="innerBox" id="State">
                                                    <option value="">
                                                      Select State
                                                      ...</option>
                                                    <?php
                                                    do {
                                                    ?>
                                                      <option value="<?php echo $row_state2['State'] ?>" <?php if (!(strcmp("Ogun State", $row_state2['State']))) {
                                                                                                          echo "selected=\"selected\"";
                                                                                                        } ?>>
                                                        <?php echo $row_state2['State'] ?>
                                                      </option>
                                                    <?php
                                                    } while ($row_state2 = mysqli_fetch_assoc($state2));
                                                    $rows = mysqli_num_rows($state2);
                                                    if ($rows > 0) {
                                                      mysqli_data_seek($state2, 0);
                                                      $row_state2 = mysqli_fetch_assoc($state2);
                                                    }
                                                    ?>
                                                  </select>

                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">Mobile
                                                  Phone:<font color="red">*
                                                  </font>
                                                </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="MobilePhone" type="text" class="innerBox" id="MobilePhone">
                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" width="43%" align="right" height="35">E-mail
                                                  Address: </td>
                                                <td class="greyBgd" width="57%" align="left"><input name="EmailAddress" type="text" class="innerBox" id="EmailAddress">
                                                </td>
                                              </tr>
                                              <tr valign="middle" align="left">
                                                <td class="greyBgd" align="right" height="35">Status:
                                                </td>
                                                <td class="greyBgd" align="left"><input name="status" type="checkbox" id="status" value="Active" checked="CHECKED">
                                                  <label for="status"></label>
                                                </td>
                                              </tr>
                                              <tr valign="top" align="left">
                                                <td colspan="2" valign="middle" align="center" height="10">
                                                  <p>
                                                  <fieldset>
                                                    <legend class="contentHeader1">
                                                      Next of Kin
                                                      <script language="JavaScript" type="text/JavaScript">
                                                        <!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                                                      </script>
                                                    </legend>
                                                    <table width="96%" align="center" cellpadding="4" cellspacing="0">
                                                      <tbody>
                                                        <tr valign="top">
                                                          <td width="36%" height="35" align="right" valign="middle" class="greyBgd">
                                                            Next
                                                            of
                                                            Kin:
                                                            <font color="red">
                                                              *
                                                            </font>
                                                          </td>
                                                          <td class="greyBgd" valign="middle" width="64%">
                                                            <input name="NOkName" type="text" class="innerBox" id="NOkName">
                                                          </td>
                                                        </tr>
                                                        <tr valign="top">
                                                          <td height="35" align="right" valign="middle" class="greyBgd">
                                                            Relationship
                                                            to
                                                            Patient:
                                                            <font color="red">
                                                              *
                                                            </font>
                                                          </td>
                                                          <td class="greyBgd" valign="middle">
                                                            <select name="NOKRelationship" class="innerBox" id="NOKRelationship">
                                                              <option value="na" selected>
                                                                Select
                                                                ...
                                                              </option>
                                                              <?php
                                                              do {
                                                              ?>
                                                                <option value="<?php echo $row_nokRelationship['relationship'] ?>">
                                                                  <?php echo $row_nokRelationship['relationship'] ?>
                                                                </option>
                                                              <?php
                                                              } while ($row_nokRelationship = mysqli_fetch_assoc($nokRelationship));
                                                              $rows = mysqli_num_rows($nokRelationship);
                                                              if ($rows > 0) {
                                                                mysqli_data_seek($nokRelationship, 0);
                                                                $row_nokRelationship = mysqli_fetch_assoc($nokRelationship);
                                                              }
                                                              ?>
                                                            </select>
                                                          </td>
                                                        </tr>
                                                        <tr valign="top">
                                                          <td height="35" align="right" valign="middle" class="greyBgd">
                                                            Next
                                                            of
                                                            Kin
                                                            Phone
                                                            No:
                                                            <font color="red">
                                                              *
                                                            </font>
                                                          </td>
                                                          <td class="greyBgd" valign="middle">
                                                            <input name="NOKPhone" type="text" class="innerBox" id="NOKPhone">
                                                          </td>
                                                        </tr>
                                                        <tr valign="top">
                                                          <td height="35" align="right" valign="middle" class="greyBgd">
                                                            Next
                                                            of
                                                            Kin
                                                            Address:
                                                            <font color="red">
                                                              *
                                                            </font>
                                                          </td>
                                                          <td class="greyBgd" valign="middle">
                                                            <input name="NOKAddress" type="text" class="innerBox" id="NOKAddress">
                                                            <input name="same" type="checkbox" id="same" onChange="javascript:sameasabove()" value="checked">
                                                            <label for="same">
                                                              Same
                                                              as
                                                              above</label>
                                                          </td>
                                                        </tr>
                                                        <tr valign="top" align="right">
                                                          <td colspan="2" class="Content" height="3">
                                                            <img src="workhistory_files/spacer.gif" width="1" height="1">
                                                          </td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                  </fieldset>

                                                  <fieldset>
                                                    <br>
                                                    <legend class="contentHeader1">
                                                      Generate
                                                      User's
                                                      Password
                                                    </legend>
                                                    <table width="96%" align="center" cellpadding="4" cellspacing="0" class="greyBgd">
                                                      <tr>
                                                        <th scope="col">
                                                          <span class="greyBgd">Generate
                                                            Password</span>
                                                        </th>
                                                        <th scope="col">
                                                          <span class="greyBgd">
                                                            <input name="passwordGen" type="text" class="innerBox" id="passwordGen">
                                                          </span>
                                                        </th>
                                                      </tr>
                                                      <tr>
                                                        <td>&nbsp;
                                                        </td>
                                                        <td align="center">
                                                          <input name="Submit2" id="submit2" class="formbutton" value="Generate" type="button">
                                                        </td>
                                                      </tr>
                                                    </table>
                                                    <div id="returnmessage" class="errorBox">
                                                    </div>
                                                  </fieldset>
                                                  <p>
                                                    <input name="Submit" class="formbutton" value="Save" type="submit">
                                                  </p>
                                                </td>
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
                                        <script>
                                          $(document).ready(function() {





                                            function randomPassword(
                                              length) {

                                              var chars =
                                                "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";

                                              var pass = "";

                                              for (var x = 0; x <
                                                length; x++) {

                                                var i = Math.floor(
                                                  Math
                                                  .random() *
                                                  chars.length
                                                );

                                                pass += chars
                                                  .charAt(i);

                                              }

                                              return pass;

                                            }



                                            $("#submit2").click(
                                              function() {

                                                $("#submit2")
                                                  .attr(
                                                    'disabled',
                                                    true);

                                                $("#submit2")
                                                  .val(
                                                    'Sending...'
                                                  );

                                                var randomPassw =
                                                  randomPassword(
                                                    5);

                                                var password =
                                                  $(
                                                    "#passwordGen"
                                                  )
                                                  .val(
                                                    randomPassw
                                                  );

                                                var new_mrn = $(
                                                  "#new_mrn"
                                                ).val();

                                                var SentMessageDisplayname =
                                                  'COV';

                                                var pages = 2;

                                                var SentMessageRecipient =
                                                  $(
                                                    "#MobilePhone"
                                                  )
                                                  .val();



                                                var SentMessageMessage =
                                                  'Please download the COV COOP android mobile App via http://emmaggi.com/cov/download/cov_coop.apk Your username is:-' +
                                                  new_mrn +
                                                  ' and Password:  ' +
                                                  randomPassw +
                                                  ' You will be prompted to change your password on your first login. Kindly use password you can easily remember.';

                                                $("#returnmessage")
                                                  .empty();

                                                if (SentMessageDisplayname ==
                                                  ''
                                                ) { //||  || ) {

                                                  alert(
                                                    "Please Fill Display Field"
                                                  );

                                                  $("#SentMessageDisplayname")
                                                    .focus();

                                                  $("#submit2")
                                                    .attr(
                                                      'disabled',
                                                      false
                                                    );

                                                  $("#submit2")
                                                    .val(
                                                      'Send Message'
                                                    );

                                                } else if (
                                                  SentMessageRecipient ==
                                                  '') {

                                                  alert(
                                                    "Please Fill Phone No. Field"
                                                  );

                                                  $("#MobilePhone")
                                                    .focus();

                                                  $("#submit2")
                                                    .attr(
                                                      'disabled',
                                                      false
                                                    );

                                                  $("#submit2")
                                                    .val(
                                                      'Send Message'
                                                    );

                                                } else if (
                                                  new_mrn ==
                                                  '') {

                                                  alert(
                                                    "Please Fill Coop No. Field"
                                                  );

                                                  $("#new_mrn")
                                                    .focus();

                                                  $("#submit2")
                                                    .attr(
                                                      'disabled',
                                                      false
                                                    );

                                                  $("#submit2")
                                                    .val(
                                                      'Send Message'
                                                    );

                                                } else {

                                                  // Returns successful data submission message when the entered information is stored in database.

                                                  $.post("send_all.php", {

                                                      SentMessageMessage: SentMessageMessage,

                                                      SentMessageDisplayname: SentMessageDisplayname,

                                                      pages: pages,

                                                      SentMessageRecipient: SentMessageRecipient



                                                      //message1: message,

                                                      //contact1: contact

                                                    },
                                                    function(
                                                      data
                                                    ) {

                                                      $("#submit2")
                                                        .attr(
                                                          'disabled',
                                                          false
                                                        );

                                                      $("#submit2")
                                                        .val(
                                                          'Send Message'
                                                        );

                                                      $("#returnmessage")
                                                        .append(
                                                          data
                                                        ); // Append returned message to message paragraph.



                                                      if (data !=
                                                        ""
                                                      ) {

                                                        //$("#MessageIndexForm")[0].reset(); // To reset form fields on success.

                                                      }

                                                    });

                                                }

                                              });

















                                          })
                                        </script>
                                      <div id="patoc" name="patoc" style="display: none; margin-left: 0em;">
                                        <fieldset>
                                          <legend class="contentHeader1">
                                            Search Existing Patient</legend>
                                          <table width="96%" align="center" cellpadding="4" cellspacing="0">
                                            <tbody>
                                              <tr valign="top">
                                                <td width="36%" height="35" align="right" valign="middle" class="greyBgd">
                                                  Enter Search
                                                  Criteria e.g. First
                                                  Name,Last Name,
                                                  Telephone No, MRN:
                                                </td>
                                                <td class="greyBgd" valign="middle" width="64%"><input name="SearchMRN" type="text" class="innerBox" id="SearchMRN">
                                                  <span class="errorBox"><a onClick="javascript:clearbox()" href="#">X</a></span>
                                                </td>
                                              </tr>
                                              <tr valign="top">
                                                <td height="35" align="right" valign="middle" class="greyBgd">
                                                  Date of Birth :
                                                </td>
                                                <td class="greyBgd" valign="middle">
                                                  <input name="SDOb" type="text" class="innerBox" id="SDOb" readonly>
                                                  <input src="resource/ew_calendar.gif" alt="Pick a Date" onClick="popUpCalendar(this, this.form.SearchMRN,'yyyy-mm-dd');return false;" type="image">
                                                  *
                                                </td>
                                              </tr>
                                              <tr valign="top" align="right">
                                                <td height="3" colspan="2" align="center" class="Content"><img src="workhistory_files/spacer.gif" width="1" height="1">
                                                  <input type="hidden" name="MM_insert" value="eduEntry">
                                                  <input name="ButtonSearch" type="button" class="formbutton" id="ButtonSearch" value="Search" onClick="javascript:onSelectedSearch()">
                                                  &nbsp;&nbsp;<input name="ButtonSearch2" type="button" class="formbutton" id="ButtonSearch2" value="Reset" onClick="javascript:reset()">
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
        <td class="innerPg" valign="top" height="1"><img name="index_r7_c1" src="resource/index_r7_c1.jpg" alt="" width="750" border="0" height="1"></td>
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
        <td class="innerPg" valign="top" height="1"><img name="index_r9_c1.jpg" alt="" width="750" border="0" height="1"></td>
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
mysqli_free_result($tribe);

mysqli_free_result($nokRelationship);

mysqli_free_result($state2);
if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Save")) {
  mysqli_free_result($settings_contri);
}
?>