<?php require_once('Connections/cov.php'); ?>
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

//mysqli_select_db($cov, $database_cov);
//$query_status = "SELECT tbl_personalinfo.patientid, concat(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', ifnull( tbl_personalinfo.Mname,'')) as namess, (sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest)) as Loan, ((sum(tlb_mastertransaction.loanAmount)+ sum(tlb_mastertransaction.interest))- sum(tlb_mastertransaction.loanRepayment)) as Loanbalance, sum(tlb_mastertransaction.withdrawal) as withdrawal FROM tlb_mastertransaction INNER JOIN tbl_personalinfo ON tbl_personalinfo.patientid = tlb_mastertransaction.memberid group by patientid";
//$status = mysqli_query($cov,$query_status) or die(mysql_error());
//$row_status = mysqli_fetch_assoc($status);
//$totalRows_status = mysqli_num_rows($status);

mysqli_select_db($cov, $database_cov);
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods order by periodid desc";
$Period = mysqli_query($cov,$query_Period) or die(mysql_error());
$row_Period = mysqli_fetch_assoc($Period);
$totalRows_Period = mysqli_num_rows($Period);

mysqli_select_db($cov, $database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysql_error());
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

mysqli_select_db($cov, $database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov,$query_logo) or die(mysqli_error($cov));
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);
session_start();
if (!isset($_SESSION['UserID'])){
    header("Location:index.php");
} else{

}
?>
<?php









//if ((isset($_POST['fromDate']))&& (isset($_POST['fromDate']))){

//}
$editFormAction = $_SERVER['PHP_SELF'];
?>
<html><head>


    <title><?php echo $row_title['value']; ?> -  Members Status</title>
    <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="skills_files/oouth.css" rel="stylesheet" type="text/css">
    <link href="css/output.css" rel="stylesheet" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>


    <script>

        var isNS4=(navigator.appName ==="Netscape")?1:0;

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
    </style>
</head>
<body>
<div onClick="bShow=true" id="calendar" style="z-index: 999; position: absolute; visibility: hidden;">
    <table style="border: 1px solid rgb(160, 160, 160); font-size: 11px; font-family: arial;" width="220" bgcolor="#ffffff">
        <tbody><tr bgcolor="#0000aa"><td><table width="218">
                    <tbody><tr><td style="padding: 2px; font-family: arial; font-size: 11px;"><font color="#ffffff"><b><span id="caption"><span id="spanLeft" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeLeft","left2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to previous month. Hold mouse button to scroll automatically."' onClick="javascript:decMonth()" onmouseout='clearInterval(intervalID1);swapImage("changeLeft","left1.gif");this.style.borderColor="#3366FF";window.status=""' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartDecMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeLeft" src="skills_files/left1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanRight" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeRight","right2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to next month. Hold mouse button to scroll automatically."' onmouseout='clearInterval(intervalID1);swapImage("changeRight","right1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="incMonth()" onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartIncMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeRight" src="skills_files/right1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanMonth" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeMonth","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a month."' onmouseout='swapImage("changeMonth","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpMonth()"></span>&nbsp;<span id="spanYear" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeYear","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a year."' onmouseout='swapImage("changeYear","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpYear()"></span>&nbsp;</span></b></font></td><td align="right"><a href="javascript:hideCalendar()"><img src="skills_files/close.gif" alt="Close the Calendar" width="15" border="0" height="13"></a></td></tr></tbody></table></td></tr><tr><td style="padding: 5px;" bgcolor="#ffffff"><span id="content"></span></td></tr><tr bgcolor="#f0f0f0"><td style="padding: 5px;" align="center"><span id="lblToday">Today is <a onmousemove='window.status="Go To Current Month"' onmouseout='window.status=""' title="Go To Current Month" style="text-decoration: none; color: black;" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">Wed, 8 Jun	2011</a></span></td></tr></tbody></table></div><div id="selectMonth" style="z-index: 999; position: absolute; visibility: hidden;"></div><div id="selectYear" style="z-index: 999; position: absolute; visibility: hidden;"></div>



<table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
    <!-- fwtable fwsrc="MTN4U.png" fwbase="index.jpg" fwstyle="Dreamweaver" fwdocid = "1226677029" fwnested="0" -->
    <tbody>


    <tr>
        <td class="centerAligned" valign="top" height="100"><div align="center"></div>
            <table width="750" border="0" cellpadding="0" cellspacing="0">
                <!-- fwtable fwsrc="Untitled" fwbase="top.gif" fwstyle="Dreamweaver" fwdocid = "2000728079" fwnested="0" -->
                <tbody>
                <tr>
                    <td colspan="4" rowspan="4" align="center"><img name="top_r2_c1" src="skills_files/spacer.gif" alt="" width="1" border="0" height="1">
                        <img src="<?php echo $row_logo['value']; ?>">
                    </td>
                    <td>&nbsp;</td>
                    <td></td>
                </tr>
                <tr>
                    <td rowspan="3"></td>
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
                                                    <form action="" method="POST" name="eduEntry">
                                                        <fieldset>
                                                            <legend class="contentHeader1"> Status<a name="top"></a></legend>
                                                            <table width="66%" align="center" cellpadding="4" cellspacing="0">
                                                                <tbody><tr valign="top" align="left">
                                                                    <td colspan="2" height="1"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                                                                </tr>

                                                                <tr valign="top" align="center">
                                                                    <div class="bg-gray-200 grid grid-cols-1 gap-4 p-4 rounded-lg shadow-lg text-sm">
                                                                        <!-- Search Field -->
                                                                        <div class="flex justify-between items-center relative">
                                                                            <input type="text" name="search" id="search" placeholder="Search..." class="pl-10 pr-4 py-2 w-full rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                            <button class="absolute left-0 inset-y-0 flex items-center px-3 text-gray-500">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                                    <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.299 4.3a1 1 0 11-1.414 1.414l-4.3-4.299zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>

                                                                        <!-- Name Field -->
                                                                        <div class="flex justify-between items-center">
                                                                            <div class="text-gray-500 font-bold w-1/3">Name</div>
                                                                            <div class="w-2/3"><input readonly type="text" name="name" id="name" class="w-full rounded-md border border-gray-300 p-2"></div>
                                                                        </div>

                                                                        <!-- Member ID Field -->
                                                                        <div class="flex justify-between items-center">
                                                                            <div class="text-gray-500 font-bold w-1/3">Member ID</div>
                                                                            <div class="w-2/3"><input readonly type="text" name="memberid" id="memberid" class="w-full rounded-md border border-gray-300 p-2"></div>
                                                                        </div>
                                                                        <!-- Period To Field -->
                                                                        <div class="flex justify-between items-center">
                                                                            <div class="text-gray-500 font-bold w-1/3">As At</div>
                                                                            <div class="relative h-10 w-72 w-2/3 mt-2">
                                                                                <select name="PeriodId" id="PeriodId"
                                                                                        class="peer h-full w-full rounded-[7px] border border-blue-gray-200 border-t-transparent bg-white px-3 py-2.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 empty:!bg-gray-900 focus:border-2 focus:border-gray-900 focus:border-t-transparent focus:outline-0 disabled:border-0 disabled:bg-blue-gray-50">
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
                                                                                <label
                                                                                        class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px] before:mr-1 before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px] after:ml-1 after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">

                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex justify-center items-center">
                                                                            <button id="getResult" name="getResult" class="bg-red-500 text-white font-bold py-2 px-4 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                                                Get Status
                                                                            </button>
                                                                        </div>
                                                                    </div>



                                                                </tr>
                                                                <tr valign="top" align="center">
                                                                    <td colspan="6" valign="middle" height="10"><div id="wait" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;" class="overlay">
                                                                            <img src="images/pageloading.gif" class="area">Please wait...
                                                                        </div></td>
                                                                </tr>
                                                                <tr valign="top" align="center">
                                                                    <td colspan="2" valign="middle" height="10"><div id="status"></div></td>
                                                                </tr>

                                                                <tr valign="top" align="left">
                                                                    <td colspan="2" height="3"><img src="skills_files/spacer.gif" width="1" height="1">
                                                                        <div id="status_old"></div></td>
                                                                </tr>

                                                                <tr valign="top" align="left">
                                                                    <td colspan="2" height="3">&nbsp;</td>
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


<script type="text/javascript">
    $(document).ready(function() {


        $('#getResult').click(function(e) {
            e.preventDefault();
            var id = $('#memberid').val().trim();  // Trim to remove any accidental spaces

// Check if the id is empty
            if (id === '') {
                alert('Please search for a member');
                return false;  // Stop the function execution if id is empty
            }

// If id is not empty, call the getStatus function
            getStatus(id);
        })

        function getStatus(id) {
            // Check if the period is not selected
            if ($('#PeriodId').val() === "na") {
                alert("Please Select Period to get Status");
                $('#PeriodId').focus();
            }
            else {
                // Hide the old status section
                $('#status_old').hide();

                // Get the period value
                var period = $('#PeriodId').val();

                // Construct the URL for the request
                var strURL = "getStatus_reducing.php?id=" + id + "&period=" + period;

                // Make the AJAX request
                $.ajax({
                    url: strURL,
                    type: "GET",
                    beforeSend: function() {
                        // Show the loading indicator
                        $('#wait').css('visibility', 'visible');
                        $('#status').css('visibility', 'hidden');
                    },
                    success: function(response) {
                        // Insert the response into the status element and show it
                        $('#status').html(response);
                        $('#status').css('visibility', 'visible');
                        $('#wait').css('visibility', 'hidden');
                    },
                    error: function() {
                        // Handle error, if needed
                        alert('An error occurred while fetching the status.');
                    }
                });
            }
        }


        $("#search").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_member.php", // The server-side script that will return the data
                    type: "POST",
                    dataType: "json",
                    data: {
                        term: request.term // The term being typed in the input field
                    },
                    success: function(data) {
                        response(data); // Pass the data to the response function
                    }
                });
            },
            minLength: 2, // Minimum number of characters before starting the search
            select: function(event, ui) {
                $('#memberid').val(ui.item.value);
                $('#name').val(ui.item.name);
                $("#search").val('');
            }
        });













    })

</script>
</body></html>
<?php

mysqli_free_result($Period);

mysqli_free_result($title);

mysqli_free_result($logo);

//mysql_free_result($maxVisit);
?>
