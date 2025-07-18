<?php
session_start();
if (!isset($_SESSION['UserID'])){
    header("Location:index.php");
    exit();
}

require_once('Connections/cov.php');

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
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

$query_status = "SELECT
    ANY_VALUE(CONCAT(tbl_personalinfo.Lname,' , ', tbl_personalinfo.Fname,' ', IFNULL(tbl_personalinfo.Mname,''))) AS namess,
    IFNULL(SUM(tlb_mastertransaction.loanAmount),0) AS Loan,
    IFNULL(SUM(tlb_mastertransaction.loanAmount) - SUM(tlb_mastertransaction.loanRepayment),0) AS Loanbalance,
    IFNULL(SUM(tlb_mastertransaction.withdrawal),0) AS withdrawal,
    IFNULL(SUM(tlb_mastertransaction.interest),0) AS interest,
    IFNULL(SUM(tlb_mastertransaction.interestPaid),0) AS interestpaid,
    IFNULL(SUM(tlb_mastertransaction.loanRepayment),0) AS loanRepayment,
    IFNULL(SUM(tlb_mastertransaction.entryFee),0) AS entryfee
FROM
    tlb_mastertransaction
RIGHT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
GROUP BY tlb_mastertransaction.memberid";

$status = mysqli_query($cov, $query_status) or die(mysqli_error($cov));
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);

$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods ORDER BY periodid DESC";
$Period = mysqli_query($cov, $query_Period) or die(mysqli_error($cov));
$row_Period = mysqli_fetch_assoc($Period);
$totalRows_Period = mysqli_num_rows($Period);

$query_Period2 = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods ORDER BY periodid DESC";
$Period2 = mysqli_query($cov, $query_Period2) or die(mysqli_error($cov));
$row_Period2 = mysqli_fetch_assoc($Period2);
$totalRows_Period2 = mysqli_num_rows($Period2);

$query_title = "SELECT tbl_globa_settings.value FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov, $query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);

$query_logo = "SELECT tbl_globa_settings.value FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov, $query_logo) or die(mysqli_error($cov));
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $row_title['value']; ?> - Members Status</title>
    <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="skills_files/oouth.css" rel="stylesheet" type="text/css">
    <link href="css/output.css" rel="stylesheet" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Helvetica;
            font-size: 11px;
            color: #000;
        }
        h3 {
            margin: 0;
            padding: 0;
        }
        .suggestionsBox {
            position: relative;
            left: -30px;
            margin: 10px 0 0 0;
            width: 200px;
            background-color: #212427;
            border-radius: 7px;
            border: 2px solid #000;
            color: #fff;
        }
        .suggestionList {
            margin: 0;
            padding: 0;
        }
        .suggestionList li {
            margin: 0 0 3px 0;
            padding: 3px;
            cursor: pointer;
        }
        .suggestionList li:hover {
            background-color: #659CD8;
        }
    </style>
    <script>

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        async function getMasterTransaction() {
            const fromPeriod = parseInt(document.getElementById('fromPeriodI').value);
            const toPeriodId = parseInt(document.getElementById('toPeriodId').value);

            if (document.getElementById('fromPeriodI').value === "na") {
                alert("Please select from Period");
                document.getElementById('fromPeriodI').focus();
            } else if (document.getElementById('toPeriodId').value === "na") {
                alert("Please select To Period");
                document.getElementById('toPeriodId').focus();
            } else if (fromPeriod > toPeriodId) {
                alert("From Period cannot be Greater Than To Period");
            } else {
                const id = document.getElementById('memberid').value;
                const strURL = `getMasterTransaction.php?id=${id}&periodTo=${toPeriodId}&periodfrom=${fromPeriod}`;

                try {
                    document.getElementById('wait').style.visibility = "visible";
                    document.getElementById('status').style.visibility = "hidden";

                    const response = await fetch(strURL);
                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.text();
                    document.getElementById('status').innerHTML = data;
                    document.getElementById('status').style.visibility = "visible";
                } catch (error) {
                    console.error('There was a problem with the fetch operation:', error);
                    alert("There was a problem retrieving the data.");
                } finally {
                    document.getElementById('wait').style.visibility = "hidden";
                }
            }
        }


        function clearBox() {
            document.forms[0].CoopName.value = "";
            document.forms[0].memberid.value = "";
        }

        function getXMLHTTP() {
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

        function lookup(inputString) {
            if(inputString.length == 0) {
                $('#suggestions').hide();
            } else {
                $.post("rpc.php", {queryString: "" + inputString + ""}, function(data){
                    if(data.length > 0) {
                        $('#suggestions').show();
                        $('#autoSuggestionsList').html(data);
                    }
                });
            }
        }

        function fill(thisValue) {
            $('#memberid').val(thisValue);
            setTimeout("$('#suggestions').hide();", 200);
        }
        function fill2(thisValue) {
            $('#CoopName').val(thisValue);
            setTimeout("$('#suggestions').hide();", 200);
        }
        function deletetrans(){

            if (confirm("Are you sure your want to delete selected item(s)")){
                var ln = 0;
                var checkbox  = document.getElementsByName('memberid');
                var i;
                for (i=0;i<checkbox.length;i++){

                    if (checkbox[i].checked){
                        ln++;
                        var transactionid = checkbox[i].value ;


                        var info = document.getElementById('memberid').value;
                        var info_array = transactionid.split(",");

                        var periodid1 = info_array[1];
                        var memberid1 = info_array[0];

                        var strURL="deletetransaction.php?periodid="+periodid1+"&memberid="+memberid1;

                        var req = getXMLHTTP();

                        if (req) {

                            req.onreadystatechange = function() {
                                if (req.readyState == 4) {
                                    // only if "OK"
                                    if (req.status == 200) {
                                        window.location.href="mastertransaction.php";
                                        //alert ("Delete Successful"); //document.getElementById('BankName').innerHTML=req.responseText;

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
                if (ln > 0){alert ("Selected item(s) Deleted Successfully");}
                if (ln == 0){alert ("Pls Select at least one item(s)  to Delete");}
            }
        }


        function toPeriod() {
            var fromSelect = document.getElementById("fromPeriodI");
            var toSelect = document.getElementById("toPeriodId");

            // Get the selected value from the fromSelect element
            var selectedValue = fromSelect.options[fromSelect.selectedIndex].value;

            // Find and select the corresponding option in the toSelect element
            for (var i = 0; i < toSelect.options.length; i++) {
                if (toSelect.options[i].value == selectedValue) {
                    toSelect.options[i].selected = true;
                    break;
                }
            }
        }


    </script>
</head>
<body>


<table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
    <tr>
        <td></td>
    </tr>
    <tr>
        <td class="centerAligned" valign="top" height="100">
            <div align="center"></div>
            <table width="750" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="4" rowspan="4" align="center">
                        <img src="<?php echo $row_logo['value']; ?>" alt="Logo">
                    </td>
                    <td>&nbsp;</td>
                    <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="17"></td>
                </tr>
                <tr>
                    <td rowspan="3"><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="1"></td>
                    <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="37"></td>
                </tr>
                <tr>
                    <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="25"></td>
                </tr>
                <tr>
                    <td><img src="skills_files/spacer.gif" alt="" width="1" border="0" height="11"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="mainNav" valign="top" height="21">
            <table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
                <tr>
                    <td class="mainNavTxt" valign="bottom">&nbsp;</td>
                    <td class="leftAligned" width="12">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="dividerCenterAligned" valign="top" height="1"><img src="skills_files/index_r3_c1.jpg" alt="" width="750" border="0" height="1"></td>
    </tr>
    <tr>
        <td class="globalNav" valign="top" height="25">
            <table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
                <tr>
                    <td class="rightAligned" width="10"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                    <td><img src="skills_files/spacer.gif" width="6"></td>
                    <td class="leftAligned" width="12"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="dividerCenterAligned" valign="top" height="1"><img src="skills_files/index_r5_c1.jpg" alt="" width="750" border="0" height="1"></td>
    </tr>
    <tr>
        <td class="innerPg" valign="top">
            <table width="900" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td rowspan="2" width="8"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                    <td colspan="2" class="breadcrumbs" valign="bottom" height="20"></td>
                    <td rowspan="2" width="12"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                </tr>
                <tr>
                    <td class="Content" valign="top" width="180">
                        <p>&nbsp;</p>
                        <br>
                        <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
                            <tr>
                                <td class="sidenavtxt" align="">
                                    <em><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Welcome,</font></em>
                                    <font size="1" face="Verdana, Arial, Helvetica, sans-serif"><span><?php if(isset($_SESSION['FirstName'])){ echo ($_SESSION['FirstName']);} ?><br>
              <img src="skills_files/spacer.gif" width="1" border="0" height="8"><img src="skills_files/arrow_bullets2.gif" border="0">
              <a href="index.php">Logout</a>
              </span></font>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
                            <tr>
                                <td colspan="2" class="sidenavtxt" width="100%" align=""><p><br></p></td>
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
                        </table>
                        <?php include("marquee.php"); ?>
                        <br>
                    </td>
                    <td rowspan="2" class="Content" valign="top">
                        <hr size="1" width="500" align="left" color="#cccccc">
                        <table width="700" border="0" align="right" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="toplinks2" valign="top">

                                    <div class="bg-gray-200 grid grid-cols-1 gap-4 p-4 rounded-lg shadow-lg">
                                        <!-- Search Field -->
                                        <div class="flex justify-between items-center relative">
                                            <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 w-full rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <button class="absolute left-0 inset-y-0 flex items-center px-3 text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.299 4.3a1 1 0 11-1.414 1.414l-4.3-4.299zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Name Field -->
                                        <div class="flex justify-between items-center">
                                            <div class="text-gray-500 font-bold w-1/3">Name</div>
                                            <div class="w-2/3"><input type="text" name="name" id="name" class="w-full rounded-md border border-gray-300 p-2"></div>
                                        </div>

                                        <!-- Member ID Field -->
                                        <div class="flex justify-between items-center">
                                            <div class="text-gray-500 font-bold w-1/3">Member ID</div>
                                            <div class="w-2/3"><input type="text" name="memberid" id="memberid" class="w-full rounded-md border border-gray-300 p-2"></div>
                                        </div>

                                        <!-- Period From Field -->
                                        <div class="flex justify-between items-center">
                                            <div class="text-gray-500 font-bold w-1/3">Period From</div>
                                            <div class="relative h-10 w-72 w-2/3 mt-2">
                                                <select name="fromPeriodId" id="fromPeriodId"
                                                        class="peer h-full w-full rounded-[7px] border border-blue-gray-200 border-t-transparent bg-white px-3 py-2.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 empty:!bg-gray-900 focus:border-2 focus:border-gray-900 focus:border-t-transparent focus:outline-0 disabled:border-0 disabled:bg-blue-gray-50">
                                                    <?php
                                                    do {
                                                        echo "<option value=\"{$row_Period2['Periodid']}\">{$row_Period2['PayrollPeriod']}</option>";
                                                    } while ($row_Period2 = mysqli_fetch_assoc($Period2));
                                                    mysqli_data_seek($Period2, 0);
                                                    ?>

                                                </select>
                                                <label
                                                        class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px] before:mr-1 before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px] after:ml-1 after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
                                                    Select Period-To
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Period To Field -->
                                        <div class="flex justify-between items-center">
                                            <div class="text-gray-500 font-bold w-1/3">Period To</div>
                                            <div class="relative h-10 w-72 w-2/3 mt-2">
                                                <select name="toPeriodId" id="toPeriodId"
                                                        class="peer h-full w-full rounded-[7px] border border-blue-gray-200 border-t-transparent bg-white px-3 py-2.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 empty:!bg-gray-900 focus:border-2 focus:border-gray-900 focus:border-t-transparent focus:outline-0 disabled:border-0 disabled:bg-blue-gray-50">
                                                    <?php
                                                    do {
                                                        echo "<option value=\"{$row_Period2['Periodid']}\">{$row_Period2['PayrollPeriod']}</option>";
                                                    } while ($row_Period2 = mysqli_fetch_assoc($Period2));
                                                    mysqli_data_seek($Period2, 0);
                                                    ?>

                                                </select>
                                                <label
                                                        class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px] before:mr-1 before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px] after:ml-1 after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
                                                    Select Period-To
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>

</table>

<div id="wait" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;" class="overlay">
    <img src="images/pageloading.gif" class="area">Please wait...
</div>
<script type="text/javascript">
    $(document).ready(function() {
        console.log('Script is running');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded and parsed');

            var element = document.querySelector('#fromPeriodI');
            if (element) {
                console.log('Element exists!');
            } else {
                console.log('Element does not exist.');
            }
        });


        $("#fromPeriodI").change(function () {

            alert('ok');
            var selectedValue = $(this).val(); // Get the selected value from the fromPeriodI select element

            // Set the corresponding value in the toPeriodId select element
            $("#toPeriodId").val(selectedValue);
        });

    })

</script>
</body>
</html>

<?php
mysqli_free_result($status);
mysqli_free_result($Period);
mysqli_free_result($title);
mysqli_free_result($logo);
?>
