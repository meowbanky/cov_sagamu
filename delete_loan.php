<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);
$loanid = intval($_POST['loanid'] ?? -1);
if ($loanid) {
    $stmt = $cov->prepare("DELETE FROM tbl_loan WHERE loanid=?");
    $stmt->bind_param('i', $loanid);
    $stmt->execute();
    $stmt->close();
    echo "OK";
}
?>
