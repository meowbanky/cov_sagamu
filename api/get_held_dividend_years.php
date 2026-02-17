<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
header('Content-Type: application/json');

$query = "
    SELECT DISTINCT h.source_period_id, p.PayrollPeriod 
    FROM tbl_overdue_dividend_held h
    JOIN tbpayrollperiods p ON p.Periodid = h.source_period_id
    WHERE h.status = 'pending'
    ORDER BY h.source_period_id DESC
";

$result = mysqli_query($cov, $query);
$years = [];

while ($row = mysqli_fetch_assoc($result)) {
    $years[] = [
        'period_id' => $row['source_period_id'],
        'name' => $row['PayrollPeriod']
    ];
}

echo json_encode(['years' => $years]);
?>
