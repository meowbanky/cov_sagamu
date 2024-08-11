<?php require_once('Connections/cov.php'); ?>
<?php
session_start();
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

if (!isset($_SESSION['period'])){
	$_SESSION['period'] = -1;
	}

$col_contributions = "-1";
if (isset($_GET['id'])) {
  $col_contributions = $_GET['id'];
}
mysqli_select_db($cov,$database_cov);
$query_contributions = sprintf("SELECT sum(tbl_specialcontributions.contribution) as total, tbl_specialcontributions.loan, tbl_specialcontributions.membersid  FROM tbl_specialcontributions WHERE tbl_specialcontributions.membersid = %s and periodid = %s group by membersid, periodid ", GetSQLValueString($cov,$col_contributions, "text"),GetSQLValueString($cov,$_SESSION['period'], "text"));
$contributions = mysqli_query($cov,$query_contributions ) or die(mysqli_error($cov));
$row_contributions = mysqli_fetch_assoc($contributions);
$totalRows_contributions = mysqli_num_rows($contributions);

mysqli_select_db($cov,$database_cov);
$query_grandTotal = sprintf("SELECT (sum(tbl_specialcontributions.contribution)) as total
FROM tbl_specialcontributions WHERE periodid = %s",GetSQLValueString($cov,$_SESSION['period'], "text"));
$grand_total = mysqli_query($cov,$query_grandTotal ) or die(mysqli_error($cov));
$row_grand_total = mysqli_fetch_assoc($grand_total);
$totalRows_grand_total = mysqli_num_rows($grand_total);



$col_balances = "-1";
if (isset($_GET['id'])) {
  $col_balances = $_GET['id'];
}
mysqli_select_db($cov,$database_cov);
$query_balances = sprintf("SELECT ((sum(tlb_mastertransaction.loanAmount)) - sum(tlb_mastertransaction.loanRepayment)) as loanbalance FROM tlb_mastertransaction WHERE memberid = %s ", GetSQLValueString($cov,$col_balances, "text"));
$balances = mysqli_query($cov,$query_balances ) or die(mysqli_error($cov));
$row_balances = mysqli_fetch_assoc($balances);
$totalRows_balances = mysqli_num_rows($balances);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script language="javascript">
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
</head>

<body>
<table width="97%" align="center" cellpadding="4" cellspacing="0">
  <tbody>
  </tbody>
  <?php if ($totalRows_contributions > 0) { // Show if recordset not empty ?>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right" height="35">Contribution</td>
        <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_contributions['total'] ,2,'.',','); ?></strong></td>
    </tr>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right" height="35">Loan Balance:</td>
      <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_balances['loanbalance'] ,2,'.',','); ?>
        <input name="memberid" type="hidden" id="memberid" value="<?php echo $row_contributions['membersid']; ?>" />
      </strong></td>
    </tr>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right" height="35"><strong>Grand Total:</strong></td>
      <td class="greyBgd" valign="middle" align="left"><strong><?php echo number_format($row_grand_total['total'] ,2,'.',','); ?></strong></td>
    </tr>
    
    <?php } // Show if recordset not empty ?>
</table>
</body>
</html>
<?php
mysqli_free_result($contributions);

mysqli_free_result($balances);
?>
