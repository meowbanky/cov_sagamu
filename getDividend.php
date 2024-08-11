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

mysqli_select_db($cov,$database_cov );
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods order by Periodid desc";
$Period =  mysqli_query($cov,$query_Period ) or die(mysqli_error($cov));
$row_Period = mysqli_fetch_assoc($Period);
$totalRows_Period = mysqli_num_rows($Period);

if (isset($_GET['period'])){
	$period = $_GET['period'];
	}else{
		$period = -1;
		}

if (isset($_GET['percentage'])){
	$percentage = $_GET['percentage'];
	}else{
		$percentage = -1;
		}
mysqli_select_db($cov,$database_cov );
$query_status = sprintf("SELECT
((Sum(tlb_mastertransaction.Shares)+Sum(tlb_mastertransaction.savings))) AS `Share&Savings`,
((Sum(tlb_mastertransaction.Shares)+Sum(tlb_mastertransaction.savings))) * %s AS dividend,
concat(ifnull(tbl_personalinfo.Lname,''),' ',ifnull(tbl_personalinfo.Mname,''),' ',ifnull(tbl_personalinfo.Fname,'')) AS `name`,
tlb_mastertransaction.memberid,
tblbankcode.bank,
tblaccountno.AccountNo,
tblbankcode.bankcode
FROM
tlb_mastertransaction
INNER JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
LEFT JOIN tblaccountno ON tblaccountno.COOPNO = tbl_personalinfo.memberid
left JOIN tblbankcode ON tblbankcode.bankcode = tblaccountno.bankcode
WHERE periodid <= %s  and status = 'Active' GROUP BY tlb_mastertransaction.memberid", 
GetSQLValueString($cov,$percentage, "double"),GetSQLValueString($cov,$period, "int"));	
	
$status =  mysqli_query($cov,$query_status ) or die(mysqli_error($cov));
$row_status = mysqli_fetch_assoc($status);
$totalRows_status = mysqli_num_rows($status);

mysqli_select_db($cov,$database_cov );
$query_sumDividend = sprintf("SELECT
((Sum(tlb_mastertransaction.Shares)+Sum(tlb_mastertransaction.savings))) AS `Share&Savings`,
((Sum(tlb_mastertransaction.Shares)+Sum(tlb_mastertransaction.savings)))* %s AS dividend
FROM tlb_mastertransaction
INNER JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tlb_mastertransaction.memberid
WHERE periodid <= %s and status = 'Active'", 
GetSQLValueString($cov,$percentage, "double"),GetSQLValueString($cov,$period, "int"));	
	
$sumDividend =  mysqli_query($cov,$query_sumDividend ) or die(mysqli_error($cov));
$row_sumDividend = mysqli_fetch_assoc($sumDividend);
$totalRows_sumDividend = mysqli_num_rows($sumDividend);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="100%" border="1" cellpadding="1" cellspacing="0" class="greyBgdHeader">
                                 <tr class="table_header_new">
                                   <th scope="col">&nbsp;</th>
                                   <th colspan="2" scope="col"><select name="dividendPeriod" id="dividendPeriod">
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
                                   </select></th>
                                   <th scope="col">&nbsp;</th>
                                   <th scope="col"><input name="button2" type="button" class="formbutton" id="button2" onclick="getDeleteDividend()" value="Delete Dividend" /></th>
                                   <th scope="col">&nbsp;</th>
                                 <th scope="col">&nbsp;</th>
                                 <th scope="col">&nbsp;</th>
                                 <th scope="col">&nbsp;</th>
                                 <th scope="col"><input name="button" type="button" class="formbutton" id="button" onclick="getCalcDividend()" value="Post Dividend" /></th>
                                 </tr>
                                 <tr class="table_header_new">
                                   <th width="7%" scope="col">&nbsp;</th>
                                   <th width="24%" scope="col">S/N</th>
                                   
                                   <th width="24%" scope="col"><strong>Member's Id</strong></th>
                                   <th width="24%" scope="col">Name</th>
                                   <th width="14%" scope="col">Share and Savings</th>
                                   <th width="15%" scope="col">Percentage</th>
                                   <th width="16%" scope="col">Dividend</th>
                                   <th width="16%" scope="col">Acct No.</th>
                                   <th width="16%" scope="col">Bank</th>
                                   <th width="16%" scope="col">Bank Code</th>
                                   </tr>
                                <?php $i = 1 ; $sum = 0; do { ?>   <tr>
                                  <th align="left" scope="col"><input name="memberid" type="checkbox" id="memberid" value="<?php echo $row_status['memberid']; ?>,<?php echo $_GET['percentage']; ?>" checked="checked" /></th>
                                  <th align="left" scope="col"><?php echo $i ;?></th>
                                  
                                   <th align="left" scope="col"><?php echo $row_status['memberid']; ?></th>
                                   <th align="left" scope="col"><?php echo $row_status['name']; ?></th>
                                  <th align="right" scope="col"><?php echo number_format($row_status['Share&Savings'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($_GET['percentage'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo number_format($row_status['dividend'] ,2,'.',','); ?></th>
                                   <th align="right" scope="col"><?php echo $row_status['AccountNo']; ?></th>
                                   <th align="right" scope="col"><?php echo $row_status['bank']; ?></th>
                                   <th align="right" scope="col"><?php echo $row_status['bankcode']; ?></th>
                                   </tr>
								   <?php $sum = $sum + $row_status['dividend'];$i=$i+1;} while ($row_status = mysqli_fetch_assoc($status)); ?>
                                    <tr>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="left" scope="col">&nbsp;</th>
                                      <th align="left" scope="col">Total</th>
                                      <th align="right" scope="col"><?php echo number_format($row_sumDividend['Share&Savings'] ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php echo number_format($sum ,2,'.',','); ?></th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col">&nbsp;</th>
                                      <th align="right" scope="col"><?php //echo number_format($row_sumDividend['dividend'] ,2,'.',','); ?></th>
                                    </tr> 
                                    
                                 
                               </table>
</body>
</html>
<?php
mysqli_free_result($Period);


mysqli_free_result($status);
?>
