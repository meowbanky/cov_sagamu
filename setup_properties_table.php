<?php
require_once('Connections/cov.php');

$sql = "CREATE TABLE IF NOT EXISTS tbl_properties (
    property_id INT(11) NOT NULL AUTO_INCREMENT,
    memberid INT(11) NOT NULL,
    property_name VARCHAR(255) NOT NULL,
    property_description TEXT DEFAULT NULL,
    property_value DECIMAL(15, 2) NOT NULL,
    liquidation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($cov, $sql)) {
    echo "Table tbl_properties created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($cov);
}
?>
