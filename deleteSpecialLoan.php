<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

$loanid = intval($_GET['loanID'] ?? -1);

if ($loanid > 0) {
    // 1. Delete from tbl_special_loan
    $stmt1 = $cov->prepare("DELETE FROM tbl_special_loan WHERE loanid=?");
    $stmt1->bind_param('i', $loanid);
    $stmt1->execute();
    $stmt1->close();

    echo "OK";
}
?>
