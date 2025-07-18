<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
$res = $cov->query("SELECT Periodid, PayrollPeriod FROM tbpayrollperiods ORDER BY Periodid DESC");
$out = [];
while($row = $res->fetch_assoc()) $out[] = $row;
echo json_encode($out);
