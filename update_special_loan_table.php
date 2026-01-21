<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

// Add loan_date column if it doesn't exist
$query = "SHOW COLUMNS FROM tbl_special_loan LIKE 'loan_date'";
$result = mysqli_query($cov, $query);
if (mysqli_num_rows($result) == 0) {
    $alter = "ALTER TABLE tbl_special_loan ADD COLUMN loan_date DATE NULL";
    if (mysqli_query($cov, $alter)) {
        echo "Column 'loan_date' added successfully to tbl_special_loan.<br>";
    } else {
        echo "Error adding column loan_date: " . mysqli_error($cov) . "<br>";
    }
} else {
    echo "Column 'loan_date' already exists.<br>";
}

// Check/Add columns to tlb_mastertransaction
$cols = ['specialLoanRepayment', 'specialInterest', 'specialLoanAmount', 'specialInterestPaid'];
foreach ($cols as $col) {
    $query = "SHOW COLUMNS FROM tlb_mastertransaction LIKE '$col'";
    $result = mysqli_query($cov, $query);
    if (mysqli_num_rows($result) == 0) {
        $alter = "ALTER TABLE tlb_mastertransaction ADD COLUMN $col DOUBLE DEFAULT 0";
        if (mysqli_query($cov, $alter)) {
            echo "Column '$col' added successfully to tlb_mastertransaction.<br>";
        } else {
            echo "Error adding column $col: " . mysqli_error($cov) . "<br>";
        }
    } else {
        echo "Column '$col' already exists in tlb_mastertransaction.<br>";
    }
}


// Check if tlb_mastertransaction has special_loan columns
// We might need to store special loan repayment in master transaction or separate?
// The user said "add modification for deleting transaction in getMasterTransaction.php".
// This implies special loan repayments might end up in tlb_mastertransaction or we need to delete from tbl_specialcontributions.
// Let's assume for now we just need the date column.
?>
