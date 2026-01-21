<?php require_once('Connections/cov.php'); ?>
<?php
session_start();
if (!isset($_SESSION['UserID'])){
    header("Location:index.php");
    exit;
}

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($cov, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
        $theValue = mysqli_real_escape_string($cov, $theValue);

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

$periodID = isset($_GET['periodid']) ? $_GET['periodid'] : '';

// Total Sum Query
$query_totalsum = sprintf("SELECT tbl_specialcontributions.membersid, (sum(tbl_specialcontributions.contribution)) as contribu 
FROM tbl_specialcontributions INNER JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tbl_specialcontributions.membersid 
WHERE `Status` = 'Active' AND tbl_specialcontributions.periodid = %s", GetSQLValueString($cov, $periodID, "text"));
$totalsum = mysqli_query($cov, $query_totalsum) or die(mysqli_error($cov));
$row_totalsum = mysqli_fetch_assoc($totalsum);
$totalRows_totalsum = mysqli_num_rows($totalsum);

// Compare List Query
$query_compare = sprintf("SELECT
tbl_specialcontributions.membersid,
((tbl_specialcontributions.contribution)) AS contribu,
concat(ifnull(tbl_personalinfo.Lname,''),' ',ifnull(tbl_personalinfo.Fname,''),' ',ifnull(tbl_personalinfo.Mname,'')) AS namee,
tbpayrollperiods.PayrollPeriod, contriId
FROM
tbl_specialcontributions
LEFT JOIN tbl_personalinfo ON tbl_personalinfo.memberid = tbl_specialcontributions.membersid
LEFT JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tbl_specialcontributions.periodid
WHERE `Status` = 'Active' AND tbl_specialcontributions.periodid = %s ORDER BY contriId DESC", GetSQLValueString($cov, $periodID, "text"));

$compare = mysqli_query($cov, $query_compare) or die(mysqli_error($cov));
$row_compare = mysqli_fetch_assoc($compare);
$totalRows_compare = mysqli_num_rows($compare);
?>

<?php if ($totalRows_compare > 0) { ?>
<table width="80%" border="1" align="center" cellpadding="4" cellspacing="1">
  <tbody>
    <tr valign="top" align="center">
      <td width="8%" valign="middle" class="greyBgd"><strong>S/N</strong></td>
      <td width="25%" height="35" valign="middle" class="greyBgd"><strong>Membership ID.</strong></td>
      <td colspan="5" valign="middle" class="greyBgd"><strong >Name</strong></td>
      <td width="17%" valign="middle" class="greyBgd"><strong>Loan Balance</strong></td>
      <td width="17%" valign="middle" class="greyBgd"><strong>Deduction</strong></td>
      <td width="35%" valign="middle" class="greyBgd"><strong>Delete</strong></td>
      <td width="35%" valign="middle" class="greyBgd"><strong>Period</strong></td>
    </tr>
    <?php  
    $i = 1; 
    do { 
        // Loan Balance Query for each member
        $query_loanBalance = sprintf("SELECT tlb_mastertransactionspecial.memberid, 
        ((sum(tlb_mastertransactionspecial.loanAmount))- (sum(tlb_mastertransactionspecial.loanRepayment))) as 'balance'
        FROM tlb_mastertransactionspecial WHERE memberid = %s", GetSQLValueString($cov, $row_compare['membersid'], "int"));
        
        $loanBalance = mysqli_query($cov, $query_loanBalance) or die(mysqli_error($cov));
        $row_loanBalance = mysqli_fetch_assoc($loanBalance);
    ?>
    
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="left"><?php echo $i; ?></td>
      <td class="greyBgd" valign="middle" align="left" height="35"><?php echo $row_compare['membersid']; ?></td>
      <td colspan="5" align="left" valign="middle" class="greyBgd"><?php echo $row_compare['namee']; ?></td>
      <td align="right" valign="middle" class="greyBgd"><?php echo number_format($row_loanBalance['balance'] ?? 0, 2); ?></td>
      <td align="right" valign="middle" class="greyBgd"><?php echo number_format($row_compare['contribu'], 2); ?></td>
      <td align="center" valign="middle" class="greyBgd"><a href="editSpecialContributions.php?deleteid=<?php echo $row_compare['contriId']?>">Delete</a></td>
      <td align="right" valign="middle" class="greyBgd"><?php echo $row_compare['PayrollPeriod']; ?></td>
    </tr>
    <?php 
        $i++; 
    } while ($row_compare = mysqli_fetch_assoc($compare)); 
    ?>
    <tr valign="top" align="left">
      <td class="greyBgd" valign="middle" align="right">&nbsp;</td>
      <td class="greyBgd" valign="middle" align="right" height="35">&nbsp;</td>
      <td colspan="5" align="left" valign="middle" class="greyBgd"><strong>Total</strong></td>
      <td align="right" valign="middle" class="greyBgd">&nbsp;</td>
      <td align="right" valign="middle" class="greyBgd"><strong><?php echo number_format($row_totalsum['contribu'], 2); ?></strong></td>
      <td align="right" valign="middle" class="greyBgd">&nbsp;</td>
      <td align="right" valign="middle" class="greyBgd">&nbsp;</td>
    </tr>
  </tbody>
</table>
<?php } else { ?>
  <p align="center"><strong><font color="#FF0000"> No Matching Record !!!</font></strong></p>
<?php } ?>
<?php
// Free result sets
if ($totalsum) mysqli_free_result($totalsum);
if ($compare) mysqli_free_result($compare);
?>
