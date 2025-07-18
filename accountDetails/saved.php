<?php require_once('Connections/cov.php'); ?>
<?php
session_start();
mysql_select_db($database_cov, $cov);
$query_saved = "SELECT
tbl_accounting.session_id,
tbl_accounting.account_id,
tbl_accounting.post_to,
tbl_accounting.post_date,
tbl_accounting.details,
tbl_accounting.amount,
tbl_account_type.type,
tbpayrollperiods.PayrollPeriod
FROM
tbl_accounting
INNER JOIN tbl_account_type ON tbl_account_type.type_id = tbl_accounting.type
INNER JOIN tbpayrollperiods ON tbpayrollperiods.Periodid = tbl_accounting.period
 WHERE session_id = '".$_SESSION['SESS_INVOICE']."'";
$saved = mysql_query($query_saved, $cov) or die(mysql_error());
$row_saved = mysql_fetch_assoc($saved);
$totalRows_saved = mysql_num_rows($saved);

$query_session = "SELECT sum(tbl_accounting.amount) as amount FROM tbl_accounting WHERE session_id = '".$_SESSION['SESS_INVOICE']."'";
$session = mysql_query($query_session, $cov) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);




?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title></title>
</head>
<body>
    
   <?php if ($totalRows_saved > 0) { ?> <table width="200" border="1">
        <thead>
			<tr>
  
   <th scope="col">Session</th>
      <th scope="col">ID</th>
      <th scope="col">Type</th>
      <th scope="col">Gl Account</th>
      <th scope="col">Date</th>
      <th scope="col">Period</th>
      <th scope="col">Details</th>
      <th scope="col">Amount</th>
            </tr></thead>
        <tbody>
    <?php   do { ?><tr>
      
      <td><?php echo $row_saved['session_id'] ?></td>
      <td><?php echo $row_saved['account_id'] ?></td>
      <td><?php echo $row_saved['type'] ?></td>
      <td><?php echo $row_saved['post_to'] ?></td>
      <td><?php echo date("d-m-Y",strtotime($row_saved['post_date'])) ?></td>
      <td><?php echo $row_saved['PayrollPeriod'] ?></td>
      <td><?php echo $row_saved['details'] ?></td>
      <td><?php echo number_format($row_saved['amount'],2); ?></td>
    </tr><?php } while ($row_saved = mysql_fetch_assoc($saved)); ?>
        <tr>
          <td colspan="7" align="right"><strong>Total:</strong></td>
          <td><strong><?php echo number_format($row_session['amount'],2); ?></strong></td>
        </tr>
      
            
            
  </tbody>
</table>
<?php } ?>
</body>
</html>