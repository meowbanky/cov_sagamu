<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

$sql = "ALTER TABLE tbl_properties ADD COLUMN transaction_id INT(11) NOT NULL DEFAULT 0 AFTER memberid";

if (mysqli_query($cov, $sql)) {
    echo "Table tbl_properties updated successfully. Column 'transaction_id' added.";
} else {
    echo "Error updating table: " . mysqli_error($cov);
}
?>
