<?php
header('Content-Type: application/json');

require_once('../Connections/cov.php');

// Get all members with their email addresses
$query = "SELECT 
    memberid, 
    CONCAT(Lname, ', ', Fname, ' ', IFNULL(Mname, '')) as name,
    EmailAddress as email
FROM tbl_personalinfo 
WHERE EmailAddress IS NOT NULL AND EmailAddress != ''
ORDER BY Lname, Fname";

$result = mysqli_query($cov, $query);

$members = [];
while ($row = mysqli_fetch_assoc($result)) {
    $members[] = [
        'memberid' => (int)$row['memberid'],
        'name' => $row['name'],
        'email' => $row['email']
    ];
}

echo json_encode($members);
?>
