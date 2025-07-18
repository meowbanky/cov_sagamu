<?php require_once('Connections/hms.php'); ?>
<?php session_start();
if (!isset($_SESSION['UserID'])){
header("Location:index.php");} else{
 
}
 
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "eduEntry")) {
	$amount = str_replace(",","",$_POST['txtAmountGranted']);
	
	
  $insertSQL = sprintf("INSERT INTO tbl_loan (memberid, periodid, loanamount, interest) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['txtCoopid'], "text"),
					   GetSQLValueString($_SESSION['period'], "int"),
					   GetSQLValueString(doubleval($amount)+100, "double"),
					   GetSQLValueString($_POST['txtInterest'], "double"));

  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQL, $hms) or die(mysql_error());
    
  $loanID = mysql_insert_id();
  
  
    
  
  
  $insertSQL_MasterTransaction = sprintf("INSERT INTO tlb_mastertransaction (periodid, memberid, loanid,loanAmount,interest) VALUES (%s, %s, %s, %s,%s)",
                      GetSQLValueString($_SESSION['period'], "int"),
					  GetSQLValueString($_POST['txtCoopid'], "text"),
					   GetSQLValueString($loanID, "int"),
                       GetSQLValueString(doubleval($amount)+100, "double"),
                       GetSQLValueString($_POST['txtInterest'], "double"));
					   
  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQL_MasterTransaction, $hms) or die(mysql_error());
  $_POST['id'] = $_POST['PeriodId'];
  
 
 $insertSQLBankSchedule = sprintf("INSERT INTO tbl_bank_schedule (memberid, periodid, loanamount,loanid) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['txtCoopid'], "text"),
					   GetSQLValueString($_SESSION['period'], "int"),
					   GetSQLValueString(doubleval($amount), "double"),
					   GetSQLValueString($loanID, "int"));

  mysql_select_db($database_hms, $hms);
  $Result1 = mysql_query($insertSQLBankSchedule, $hms) or die(mysql_error());
 
   
}

mysql_select_db($database_hms, $hms);
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods ";
$Period = mysql_query($query_Period, $hms) or die(mysql_error());
$row_Period = mysql_fetch_assoc($Period);
$totalRows_Period = mysql_num_rows($Period);

$col_Batch = "-1";
if (isset($_SESSION['period'])) {
  $col_Batch = $_SESSION['period'];
}
mysql_select_db($database_hms, $hms);
$query_Batch = sprintf("SELECT tbl_bank_schedule.loanamount,tbl_bank_schedule.loanid,  tbl_bank_schedule.cheque_no, tbl_bank_schedule.date_on_cheque, tbl_bank_schedule.periodid,
tbl_bank_schedule.memberid, tbl_bank_schedule.`name` FROM tbl_bank_schedule WHERE tbl_bank_schedule.periodid= %s order by loanid", GetSQLValueString($col_Batch, "text"));
$Batch = mysql_query($query_Batch, $hms) or die(mysql_error());
$row_Batch = mysql_fetch_assoc($Batch);
$totalRows_Batch = mysql_num_rows($Batch);




 ?>
 
 <html><head>


<title>MHWUN, OOUTH -  Bank Schedule</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">

<!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004-->
<link href="education_files/oouth.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="education_files/general.js" type="text/javascript"></script>
<script type="text/javascript" src="education_files/popcalendar.js"></script>
<script language="javascript" type="text/javascript">
// Roshan's Ajax dropdown code with php
// This notice must stay intact for legal use
// Copyright reserved to Roshan Bhattarai - nepaliboy007@yahoo.com
// If you have any problem contact me at http://roshanbh.com.np

	
	
	function makeRequest(url,divID,wait) {

                
				//document.getElementById("loading").style.display="block";
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

                http_request.onreadystatechange = function() {  
				alertContents(http_request,divID, wait);
				//document.getElementById("loading").style.display="none";
				 };

                http_request.open('GET', url, true);
				http_request.send(null);

            }
			
			
			function alertContents(http_request,divid, wait) {


			       if (http_request.readyState == 4) {

                    //alert(http_request.status);

                    //alert(divid);

				document.getElementById(divid).innerHTML=http_request.responseText;
				//document.getElementById(divid).innerHTML=req.responseText;	
				document.getElementById(divid).style.visibility = "visible";
				document.getElementById(wait).style.visibility = "hidden";

                    } else { 

                       //document.getElementById(divid).innerHTML=http_request.responseText;
						document.getElementById(wait).style.visibility = "visible";
						document.getElementById(divid).style.visibility = "hidden";
                        //alert("There was a problem with the request");

                    

                }

            }
		
		function popupstartup(){
                //alert("Hi");
				
                var url='printSchedule.php';
                window.open (url,'','top=0,left=0,toolbar=no,resizable=no,status=no,scrollbars=yes');
            }
			
			function addrow(){
				
				var strURL="saveCheque.php?add="+'1'
		
		 makeRequest(strURL,"patSearchResult","wait");
				}
			
		
		
		function deleteRow(loanID, saveType){
			
			var loanID = loanID;
			var saveType = saveType;
	
			var strURL="saveCheque.php?loanID="+loanID+"&saveType="+saveType;
		
		 	makeRequest(strURL,"patSearchResult","wait");
			
			}
		
			
		
		function saveChequeNo(chequeno, loanID, saveType) {		
		//alert("hi");
		
		var chequeno = chequeno;
		var loanID = loanID;
		var saveType = saveType 
		
		//alert(contributions);
		var strURL="saveCheque.php?chequeno="+chequeno+"&loanID="+loanID+"&saveType="+saveType;
		
		 makeRequest(strURL,"patSearchResult","wait");
		}
				

		function reload(){
			location.reload();
			}
		
		function testperiod() {
			alert(document.getElementById('periodset').value);
			}
			
			
function validate(){
	
return( true );
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
	
function getLoanBalance(id) {		
		
		var strURL="loanBalance.php?id="+id;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('loanBalance').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	
	
function deleteLoan(commodityID,periodid) {		
		
		
		if (confirm("Are you sure your want to delete selected item(s)")){
		
	var ln = 0;
	var checkbox = document.getElementsByName('loanID');
	//var checkbox = document.forms['form2'].commodityID;
	var i;
	for (i=0;i<checkbox.length;i++){
			
 		 if (checkbox[i].checked){
			 
			
			//alert("Test1");
						ln++;
					//if (ln == 1){
			
		//var batchcode = document.forms[1].batch.value ;
		var loanID = checkbox[i].value ;
		//alert(coopid);
		
		var strURL="deleteLoan.php?loanID="+loanID;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
					 	//window.location.href="addcommodity.php?id="+periodid;
						//alert ("Delete Successful"); //document.getElementById('BankName').innerHTML=req.responseText;						
						//getCommodity(periodid);
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
	if (ln > 0){alert ("Selected item(s) Deleted Successfully");
	window.location = 'addloan.php';
	}
	if (ln == 0){alert ("Pls Select at least one item(s)  to Delete");}	
	}		
	}
		
	function getLoan(id) {		
		//alert("hi");
		
		document.getElementById('commodity2').style.display="none"; 
		//var id = document.getElementById('periodset').value;
		var strURL="batchLoan.php?id="+id;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('commodity').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}
	
	function getCommodityPrice(commodityId) {		
		var strURL="getCommodityPrice.php?id="+commodityId;
		var req = getXMLHTTP();
		
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('commodityPrice').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}
				
	}
	
	function getInterest(id) {	
	
		var loan = document.getElementsByName('txtAmountGranted').value;
		var interestRate = parseFloat(document.getElementById('interestRate').value);
		document.getElementById('txtInterest').value = id * interestRate;		
		
	}
</script>
<script language="javascript" type="text/javascript">

function commaFormat(inputString) {
   inputString = inputString.toString();
   var decimalPart = "";
   if(inputString.indexOf('.') != -1) {
    //alert("decimal number");
    inputString = inputString.split(".");
    decimalPart = "." + inputString[1];
    inputString = inputString[0];
    //alert(inputString);
    //alert(decimalPart);

    }
    var outputString = "";
var count = 0;
for(var i = inputString.length - 1; i >= 0 && inputString.charAt(i) != '-'; i--) {
    //alert("inside for" + inputString.charAt(i) + "and count=" + count + " and outputString=" + outputString);
    if(count == 3) {
        outputString += ",";
        count = 0;
    }
    outputString += inputString.charAt(i);
    count++;
}
if(inputString.charAt(0) == '-') {
    outputString += "-";
}
//alert(outputString);
//alert(outputString.split("").reverse().join(""));
return outputString.split("").reverse().join("") + decimalPart;

    }
</script>
<script type='text/javascript'>
    function formatNumber(myElement) { // JavaScript function to insert thousand separators
        var myVal = ""; // The number part
        var myDec = ""; // The digits pars
        // Splitting the value in parts using a dot as decimal separator
        var parts = myElement.value.toString().split(".");
        // Filtering out the trash!
        parts[0] = parts[0].replace(/[^0-9]/g,""); 
        // Setting up the decimal part
        if ( ! parts[1] && myElement.value.indexOf(".") > 1 ) { myDec = ".00" }
        if ( parts[1] ) { myDec = "."+parts[1] }
        // Adding the thousand separator
        while ( parts[0].length > 3 ) {
            myVal = "'"+parts[0].substr(parts[0].length-3, parts[0].length )+myVal;
            parts[0] = parts[0].substr(0, parts[0].length-3)
        }
        myElement.value = parts[0]+myVal+myDec;
    }
</script>

<script language="JavaScript">
<!--
/*
 * convert to two digits and currency format
 */
function number_format (number, decimals, dec_point, thousands_sep) {
  // http://kevin.vanzonneveld.net
  // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +     bugfix by: Michael White (http://getsprink.com)
  // +     bugfix by: Benjamin Lupton
  // +     bugfix by: Allan Jensen (http://www.winternet.no)
  // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // +     bugfix by: Howard Yeend
  // +    revised by: Luke Smith (http://lucassmith.name)
  // +     bugfix by: Diogo Resende
  // +     bugfix by: Rival
  // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
  // +   improved by: davook
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Jay Klehr
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Amir Habibi (http://www.residence-mixte.com/)
  // +     bugfix by: Brett Zamir (http://brett-zamir.me)
  // +   improved by: Theriault
  // +      input by: Amirouche
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // *     example 1: number_format(1234.56);
  // *     returns 1: '1,235'
  // *     example 2: number_format(1234.56, 2, ',', ' ');
  // *     returns 2: '1 234,56'
  // *     example 3: number_format(1234.5678, 2, '.', '');
  // *     returns 3: '1234.57'
  // *     example 4: number_format(67, 2, ',', '.');
  // *     returns 4: '67,00'
  // *     example 5: number_format(1000);
  // *     returns 5: '1,000'
  // *     example 6: number_format(67.311, 2);
  // *     returns 6: '67.31'
  // *     example 7: number_format(1000.55, 1);
  // *     returns 7: '1,000.6'
  // *     example 8: number_format(67000, 5, ',', '.');
  // *     returns 8: '67.000,00000'
  // *     example 9: number_format(0.9, 0);
  // *     returns 9: '1'
  // *    example 10: number_format('1.20', 2);
  // *    returns 10: '1.20'
  // *    example 11: number_format('1.20', 4);
  // *    returns 11: '1.2000'
  // *    example 12: number_format('1.2000', 3);
  // *    returns 12: '1.200'
  // *    example 13: number_format('1 000,50', 2, '.', ' ');
  // *    returns 13: '100 050.00'
  // Strip all characters but numerical ones.
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
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
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
</script>
<script>
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
</script>
<script language="JavaScript" type="text/JavaScript">
function fo() {}
function fo2() {}

</script>
<script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
</script>
<script>
function hide(){
	document.getElementById("hide").style.display="none";
}
</script>
<script>
function hide2(){
	document.getElementById("hide2").style.display="none";
}
</script>
<script>
function onSelectedEditAmount(coopid) {
	
	//alert(document.forms[1].batch.value);
	var ln = 0;
	var checkbox = document.getElementsByName('chkcoopid');
	var i;
		for (i=0;i<checkbox.length;i++){
			
 		 if (checkbox[i].checked){
			//alert("Test1");
						ln++;
		 }
		 }
					if (ln == 1){
						
					var m=document.eduEntry.Batch.value

					//alert(m);
					//var m='coop-00004'
                
					var url="editAmount.php?batch="+m+"&coopid="+coopid;

					//alert(url);

					//document.getElementById("patocpdetails").height="500";

					document.getElementById("opatdetails").width="500";

					document.getElementById("opatdetails").height="400";
					document.getElementById("opatdetails").style.overflowY = "hidden";
				
					document.getElementById("opatdetails").src=url;

					document.getElementById("hide").style.display="block";

						

					//makeRequest(url,"patdisplay");
						}
						if (ln > 1 ) {//alert ("error"); 
					//
					for (i=0;i<checkbox.length;i++){
			
						if (checkbox[i].checked){
						//alert("Test1");
						checkbox[i].checked=false;
						var morethanone = "Yes";
						document.getElementById("hide").style.display="none";
					 
					 } 
						}if (morethanone == "Yes"){alert("Select only one item to Edit");}
					 
					  //alert(ln-1);
					 return ;
					 } if (ln == 0 ){document.getElementById("hide").style.display="none";
					 //alert("Select only one item to Edit");
					 return ;}
					 
            }
			
	
function onSelectedEdit() {


                // alert("dddddddddddddddd"+oForm.value);

                var m=document.eduEntry.txtCoopid.value
if (m.length == 0) {
                alert("Enter member's to Edit");}else{
				//var m='coop-00004'
                
                  var url="editAccountNo.php?coopid="+m;

                //alert(url);

                //document.getElementById("patocpdetails").height="500";

                document.getElementById("opatdetails").width="400";

                document.getElementById("opatdetails").height="250";
				document.getElementById("opatdetails").style.overflowY = "hidden";
				document.getElementById("opatdetails").scrolling="no"
				
                document.getElementById("opatdetails").src=url;

                document.getElementById("hide").style.display="block";



                //makeRequest(url,"patdisplay");

            }	}		
function MM_validateForm() { //v4.0
  if (document.getElementById){
    var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
    for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=document.getElementById(args[i]);
      if (val) { nm=val.name; if ((val=val.value)!="") {
        if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
          if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
        } else if (test!='R') { num = parseFloat(val);
          if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
          if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
            min=test.substring(8,p); max=test.substring(p+1);
            if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
      } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
    } if (errors) alert('The following error(s) occurred:\n'+errors);
    document.MM_returnValue = (errors == '');
} }
</script>
<script type="text/javascript" src="jquery-1.2.1.pack.js"></script>
<script type="text/javascript">
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
		getLoanBalance(document.getElementById('txtCoopid').value);
		document.eduEntry.txtAmountGranted.focus();
		
	}
	function fill2(thisValue) {
		$('#CoopName').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
	function fill3(thisValue) {
		$('#txtCommodityBalance').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
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
       <td class="Content" valign="top" width="180"><p>&nbsp;</p>
         <br>
         <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
           <tbody>
             <tr>
               <td class="sidenavtxt" align=""><em><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Welcome,</font></em> <font size="1" face="Verdana, Arial, Helvetica, sans-serif"><span><?php echo ($_SESSION['FirstName']); ?><br>
                 <img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"> <a href="index.php">Logout</a> </span></font></td>
             </tr>
           </tbody>
         </table>
         <br>
         <table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
           <tbody>
             <tr>
               <td colspan="2" class="sidenavtxt" width="100%" align=""><p><br>
               </p></td>
             </tr>
             <tr>
               <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
               <td width="100%" align="" class="sidenavtxt"><a href="dashboard.php">DashBoard</a></td>
             </tr>
             <tr>
               <td align="">&nbsp;</td>
               <td align=""><div id="locationface" class="error"><?php if (isset($_SESSION['period'])){ 
			   mysql_select_db($database_hms, $hms);
$query_Period_session = "SELECT tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods WHERE tbpayrollperiods.Periodid = ".$_SESSION['period'];
$Period_session = mysql_query($query_Period_session, $hms) or die(mysql_error());
$row_Period_session = mysql_fetch_assoc($Period_session);
$totalRows_Period_session = mysql_num_rows($Period_session);
			   
			   
			   echo $row_Period_session['PayrollPeriod'] ;
			   ?> <input name="periodset" type="hidden" id="periodset" value="<?php echo $_SESSION['period']; ?>"> <?php } ?></div></td>
             </tr>
           </tbody>
         </table>
         <br>
          <?php include("marquee.php"); ?> 
         <br>
         <script language="JavaScript1.2" src="skills_files/misc.htm"></script></td>
       <td rowspan="2" class="Content" valign="top"><img src="education_files/addloan.gif" width="350" height="30">
         <hr size="1" width="500" align="left" color="#cccccc">
         <span class="homeContentSmaller"><br>
           </span>
         <table width="500" border="0" cellpadding="0" cellspacing="0">
           <tbody>
             <tr>
               <td class="toplinks2" valign="top"><div align="justify">
                 <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
                   <tbody>
                     <tr>
                       <td valign="top">
                         <fieldset>
                           <legend class="contentHeader1"></legend>
                           <table width="97%" align="center" cellpadding="4" cellspacing="0">
                             <tbody>
                               <tr valign="top" align="left">
                                 <td colspan="7" height="1"><img src="education_files/spacer.gif" width="1" height="1"></td>
                                 </tr>
                               <tr valign="top" align="center">
                                 <th height="35" colspan="4" align="left" valign="middle" class="greyBgd"><div id="wait" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;" class="overlay" align="center"> <img src="images/pageloading.gif" alt="wait" class="area">Please wait... </div>
                                 <div id="patSearchResult"></div>
</th>
                                 <th valign="middle" class="greyBgd"><input name="button" type="button" class="BlueButton" id="button" value="Print Schedule" onClick='popupstartup()' style="opacity:100"></th>
                                 <th valign="middle" class="greyBgd">&nbsp;</th>
                                 <th valign="middle" class="greyBgd">&nbsp;</th>
                               </tr>
                               <tr valign="top" align="center">
                                 <th width="15%" height="35" valign="middle" class="greyBgd">S/N</th>
                                 <th width="15%" valign="middle" class="greyBgd">
                                   DATE ON THE CHEQUE</th>
                                 <th width="50%" valign="middle" class="greyBgd">PAYEE</th>
                                 <th width="20%" valign="middle" class="greyBgd">CHEQUE NO</th>
                                 <th width="39%" align="right" valign="middle" class="greyBgd">AMOUNT</th>
                                 <th width="39%" align="right" valign="middle" class="greyBgd">Delete Row</th>
                                 <th width="39%" align="right" valign="middle" class="greyBgd">Refresh</th>
                                 
                               </tr>
                               <?php if ($totalRows_Batch > 0) { // Show if recordset not empty ?>
                                 <?php $i = 1 ; do { ?>
                                   <tr valign="top" align="left">
                                     <td height="35" align="right" valign="middle" class="greyBgd"><?php echo $i; ?></td>
                                     <td align="left" valign="middle" class="greyBgd"><form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                                       <label for="dateoncheque"></label>
                                       <input name="dateoncheque" type="text" id="dateoncheque" value="<?php echo $row_Batch['date_on_cheque']; ?> " onBlur="saveChequeNo(this.value,document.getElementById('loan_<?php echo $row_Batch['loanid']; ?>').value,1)">
                                       <input name="loanID" type="hidden" id="loanID" value="<?php echo $row_Batch['loanid']; ?>">
                                     </form></td>
                                     <td align="left" valign="middle" class="greyBgd"><form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                                       <label for="name"></label>
                                       <input name="name" type="text" id="name" onBlur="saveChequeNo(this.value,document.getElementById('loan_<?php echo $row_Batch['loanid']; ?>').value,3)" value="<?php echo $row_Batch['name']; ?>">
                                       <input name="loan_<?php echo $row_Batch['loanid']; ?>2" type="hidden" id="loan_<?php echo $row_Batch['loanid']; ?>2" value="<?php echo $row_Batch['loanid']; ?>">
                                     </form></td>
                                     <td align="left" valign="middle" class="greyBgd"><form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                                       <label for="chequeNo"></label>
                                       <input name="chequeNo" type="text" id="chequeNo" onBlur="saveChequeNo(this.value,document.getElementById('loan_<?php echo $row_Batch['loanid']; ?>').value,2)" value="<?php echo $row_Batch['cheque_no']; ?>">
                                       <input name="loan_<?php echo $row_Batch['loanid']; ?>" type="hidden" id="loan_<?php echo $row_Batch['loanid']; ?>" value="<?php echo $row_Batch['loanid']; ?>">
                                     </form></td>
                                     <td align="right" valign="middle" class="greyBgd"><form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                                       <label for="chequeNo3"></label>
                                       <input name="chequeNo2" type="text" id="chequeNo3" onBlur="saveChequeNo(this.value,document.getElementById('loan_<?php echo $row_Batch['loanid']; ?>').value,4)" value="<?php echo number_format($row_Batch['loanamount'],2,'.',','); ?>">
                                       <input name="loan_<?php echo $row_Batch['loanid']; ?>3" type="hidden" id="loan_<?php echo $row_Batch['loanid']; ?>3" value="<?php echo $row_Batch['loanid']; ?>">
                                     </form></td>
                                     <td align="right" valign="middle" class="greyBgd"><form action="<?php echo $editFormAction; ?>" method="POST" name="eduEntry" onSubmit="return(validate()); ">
                                       <label for="chequeNo4"></label>
                                       <input name="button3" type="button" class="BlueButton" id="button3" value="Delete Row" onClick="deleteRow(document.getElementById('loan_<?php echo $row_Batch['loanid']; ?>').value,5)" style="opacity:100">
                                       <input name="loan_<?php echo $row_Batch['loanid']; ?>4" type="hidden" id="loan_<?php echo $row_Batch['loanid']; ?>4" value="<?php echo $row_Batch['loanid']; ?>">
                                     </form></td>
                                     <td align="right" valign="middle" class="greyBgd"><input name="button4" type="button" class="BlueButton" id="button4" value="Refresh Page" onClick="reload()" style="opacity:100"></td>
                                   </tr>
                                    <?php $i=$i+1;} while ($row_Batch = mysql_fetch_assoc($Batch)); ?>
                                 <?php } // Show if recordset not empty ?>
                                   <tr valign="top" align="left">
                                     <td height="35" colspan="7" align="center" valign="middle" class="greyBgd"><input name="button2" type="button" class="BlueButton" id="button2" value="Add Row" onClick='addrow()' style="opacity:100"></td>
                                     </tr>
                                  
                             </tbody>
                             </table>
                           </fieldset>
                      
                         
                         <br>
                         
                           <script language="JavaScript" type="text/JavaScript">
<!--
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
//-->
                          </script>
                        
                         <p>&nbsp;</p>
                         <p><br>
                           </p></td>
                       </tr>
                     </tbody>
                   </table>
                 </div></td>
               </tr>
             </tbody>
           </table>
         <br>
         <br>
         <br></td>
    </tr>
    <tr>
      <td class="Content" valign="top">&nbsp;</td>
    </tr>
  </tbody>
</table>
</body></html>
<?php
mysql_free_result($Period);


mysql_free_result($Batch);


?>
