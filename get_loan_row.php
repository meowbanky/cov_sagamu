<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once('Connections/cov.php');
header('Content-Type: application/json');
mysqli_select_db($cov, $database_cov);
$loanid = intval($_GET['loanid'] ?? -1);
$row = [];
if ($loanid) {
    $res = $cov->query("SELECT l.*, CONCAT(p.Lname, ' ', p.Fname, ' ', IFNULL(p.Mname,'')) AS membername FROM tbl_loan l JOIN tbl_personalinfo p ON l.memberid=p.memberid WHERE l.loanid = $loanid LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
    }
}
echo json_encode($row ?: null);