<?php
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);
$q = trim($_POST['q'] ?? '');
if(!$q) exit;
$res = $cov->query("SELECT memberid, CONCAT(Lname, ', ', Fname, ' ', IFNULL(Mname, '')) as name FROM tbl_personalinfo WHERE status='Active' AND (Lname LIKE '%$q%' OR Fname LIKE '%$q%' OR Mname LIKE '%$q%') LIMIT 10");
echo '<ul class="suggestionList">';
while($row = $res->fetch_assoc()){
    echo "<li data-id='{$row['memberid']}' style='cursor: pointer;'>".htmlspecialchars($row['name'])." - {$row['memberid']}</li>";
}
echo '</ul>';
