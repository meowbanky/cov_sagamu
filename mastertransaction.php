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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="skills_files/oouth.css" rel="stylesheet" type="text/css">
    <script src="excel/tableExport.js"></script>
    
    <script src="excel/main.js"></script>

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

        function getMasterTransaction() {
            var fromPeriod = parseInt(document.getElementById('fromPeriodI').value);
            var toPeriodId = parseInt(document.getElementById('toPeriodId').value);

            if (document.getElementById('fromPeriodI').value == "na") {
                alert("Please select from Period");
                document.getElementById('fromPeriodI').focus();
            } else if (document.getElementById('toPeriodId').value == "na") {
                alert("Please select To Period");
                document.getElementById('toPeriodId').focus();
            } else if (fromPeriod > toPeriodId) {
                alert("From Period can not be Greater Than To Period");
            } else {
                var id = document.getElementById('memberid').value;
                var strURL = "getMasterTransaction.php?id=" + id + "&periodTo=" + toPeriodId + "&periodfrom=" + fromPeriod;
                var req = getXMLHTTP();

                if (req) {
                    req.onreadystatechange = function() {
                        if ((req.readyState == 4)) {
                            document.getElementById('status').innerHTML = req.responseText;
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
<div id="calendar" style="z-index: 999; position: absolute; visibility: hidden;"></div>
<div id="selectMonth" style="z-index: 999; position: absolute; visibility: hidden;"></div>
<div id="selectYear" style="z-index: 999; position: absolute; visibility: hidden;"></div>

<table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
    <tr>
        <td><img src="skills_files/spacer.gif" alt="" width="750" border="0" height="1"></td>
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
                                    <div align="justify">
                                        <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
                                            <tr>
                                                <td valign="top">
                                                    <?php
                                                    if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Save")) {
                                                        echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\"><tr><td>Record Saved successful</td></tr></table>";
                                                    }
                                                    if ((isset($_POST["Submit"])) && ($_POST["Submit"] == "Update")) {
                                                        echo "<table class=\"errorBox\" width=\"500\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\"><tr><td>Records Updated successful</td></tr></table>";
                                                    }
                                                    ?>
                                                    <form action="" method="POST" name="eduEntry">
                                                        <fieldset>
                                                            <legend class="contentHeader1"> Status<a name="top"></a></legend>
                                                            <table width="66%" align="center" cellpadding="4" cellspacing="0">
                                                                <tr valign="top" align="left">
                                                                    <td colspan="6" height="1"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                                                                </tr>
                                                                <tr valign="top" align="left">
                                                                    <td height="27" colspan="6" align="center" class="greyBgd">
                                                                        <strong class="tableHeaderContentDarkBlue">Search Member</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr valign="top" align="left">
                                                                    <td width="36%" height="35" align="right" valign="middle" class="greyBgd">Name</td>
                                                                    <td colspan="5" align="left" valign="middle" class="greyBgd">
                                                                        <input name="CoopName" type="text" class="innerBox" id="CoopName" onblur="fill();" onkeyup="lookup(this.value);" value="" size="30" autocomplete="off">
                                                                        <input type="button" class="formbutton" onclick="javascript:clearBox()" value="X">
                                                                        <br>
                                                                        <div class="suggestionsBox" id="suggestions" style="display: none;">
                                                                            <img src="upArrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                                                                            <div class="suggestionList" id="autoSuggestionsList" style="position: relative; top: -12px;"> &nbsp; </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr valign="top" align="left">
                                                                    <td class="greyBgd" valign="middle" align="right" height="35">Member ID:</td>
                                                                    <td colspan="5" align="left" valign="middle" class="greyBgd">
                                                                        <input name="memberid" type="text" class="innerBox" id="memberid"
                                                                               readonly>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center" valign="top" class="greyBgd">
                                                                    <td height="10" align="right" valign="middle" class="greyBgd">Period:</td>
                                                                    <td width="6%" height="10" align="right" valign="middle">From:</td>
                                                                    <td width="12%" align="left" valign="middle">
                                                                        <select name="fromPeriodI" id="fromPeriodI" onchange="toPeriod()">
                                                                            <option value="na">Select Period</option>
                                                                            <?php
                                                                            do {
                                                                                echo "<option value=\"{$row_Period['Periodid']}\">{$row_Period['PayrollPeriod']}</option>";
                                                                            } while ($row_Period = mysqli_fetch_assoc($Period));
                                                                            mysqli_data_seek($Period, 0);
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                    <td width="9%" valign="middle">&nbsp;</td>
                                                                    <td width="9%" align="right" valign="middle">To:</td>
                                                                    <td width="28%" align="left" valign="middle">
                                                                        <select name="toPeriodId" id="toPeriodId">
                                                                            <option value="na">Select Period</option>
                                                                            <?php
                                                                            do {
                                                                                echo "<option value=\"{$row_Period2['Periodid']}\">{$row_Period2['PayrollPeriod']}</option>";
                                                                            } while ($row_Period2 = mysqli_fetch_assoc($Period2));
                                                                            mysqli_data_seek($Period2, 0);
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr valign="top" align="center">
                                                                    <td colspan="6" valign="middle" height="10">
                                                                        <input name="Search" type="button" class="formbutton" id="Submit" value="Search" onclick="getMasterTransaction()">
                                                                    </td>
                                                                </tr>
                                                                <tr valign="top" align="center">
                                                                    <td colspan="6" valign="middle" height="10">
                                                                        <div id="wait" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;" class="overlay">
                                                                            <img src="images/pageloading.gif" class="area">Please wait...
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr valign="top" align="center">
                                                                    <td colspan="6" valign="middle" height="10">
                                                                        <div id="status"></div>
                                                                    </td>
                                                                </tr>
                                                                <tr valign="top" align="left">
                                                                    <td colspan="6" height="3"><img src="skills_files/spacer.gif" width="1" height="1"></td>
                                                                </tr>
                                                                <tr valign="top" align="left">
                                                                    <td colspan="6" height="3">&nbsp;</td>
                                                                </tr>
                                                            </table>
                                                        </fieldset>
                                                        <input type="hidden" name="MM_insert" value="eduEntry">
                                                        <input type="hidden" name="MM_update" value="eduEntry">
                                                    </form>
                                                    <p>&nbsp;</p>
                                                    <p><br></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="Content" valign="top">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="innerPg" valign="top" height="1"><img src="skills_files/index_r7_c1.jpg" alt="" width="750" border="0" height="1"></td>
    </tr>
    <tr>
        <td class="innerPg" valign="top" height="21">
            <table class="contentHeader1" width="750" border="0" cellpadding="0" cellspacing="0" height="21">
                <tr>
                    <td class="rightAligned" width="10">&nbsp;</td>
                    <td class="baseNavTxt">&nbsp;</td>
                    <td class="leftAligned" width="12">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="innerPg" valign="top" height="1"><img src="skills_files/index_r9_c1.jpg" alt="" width="750" border="0" height="1"></td>
    </tr>
    <tr>
        <td class="innerPg" valign="top">&nbsp;</td>
    </tr>
</table>
    <script>

        function exportPDF() {
            // Get the table HTML
            var table = document.getElementById('sample_1').outerHTML;

            var selectTo = document.getElementById('toPeriodId');
            var selectedToFilename = selectTo.options[selectTo.selectedIndex].text;

            var selectFr = document.getElementById('fromPeriodI');
            var selectedFrFilename = selectTo.options[selectFr.selectedIndex].text;

            var filename = selectedToFilename+'_'+selectedFrFilename;

            $.ajax({
                url: 'export_pdf.php',
                type: 'POST',
                data: {html: table, filename: filename},
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (data) {
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    a.download = filename + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                },
                error: function () {
                    alert('Failed to export table');
                }
            });

            // console.log(table);
        }


        function exportTable() {
        // Get the table HTML
        var table = document.getElementById('sample_1').outerHTML;
            var selectTo = document.getElementById('toPeriodId');
            var selectedToFilename = selectTo.options[selectTo.selectedIndex].text;

            var selectFr = document.getElementById('fromPeriodI');
            var selectedFrFilename = selectTo.options[selectFr.selectedIndex].text;

            var filename =selectedToFilename+'_'+selectedFrFilename;


            $.ajax({
        url: 'export.php',
        type: 'POST',
        data: {html: table},
        xhrFields: {
        responseType: 'blob'
    },
        success: function (data) {
        var a = document.createElement('a');
        var url = window.URL.createObjectURL(data);
        a.href = url;
            a.download = filename + '.xlsx';
        document.body.append(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
    },
        error: function () {
        alert('Failed to export table');
    }
    });

        console.log(table);
    }
</script>
</body>
</html>

<?php
mysqli_free_result($status);
mysqli_free_result($Period);
mysqli_free_result($title);
mysqli_free_result($logo);
?>
