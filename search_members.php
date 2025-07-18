<?php
header('Content-Type: application/json');
require_once('Connections/cov.php');

mysqli_select_db($cov,$database_cov);
// Secure database connection (mysqli)
if (!$cov) {
    echo json_encode([]);
    exit;
}

$queryString = trim($_POST['term'] ?? $_GET['term'] ?? '');
if (strlen($queryString) < 2) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT memberid, Fname, Mname, Lname, MobilePhone
        FROM tbl_personalinfo
        WHERE memberid LIKE CONCAT('%', ?, '%')
           OR Fname LIKE CONCAT('%', ?, '%')
           OR Mname LIKE CONCAT('%', ?, '%')
           OR Lname LIKE CONCAT('%', ?, '%')
           OR MobilePhone LIKE CONCAT('%', ?, '%')
        LIMIT 10";
$stmt = $cov->prepare($sql);
$stmt->bind_param('sssss', $queryString,$queryString, $queryString, $queryString, $queryString);
$stmt->execute();
$res = $stmt->get_result();

$results = [];
while ($row = $res->fetch_assoc()) {
    $label = $row['memberid'] . " - " . trim($row['Lname'] . " " . $row['Fname'] . " " . $row['Mname']);
    $results[] = [
        'label' => $label,
        'value' => $row['memberid'],
        'membername' => trim($row['Lname'] . " " . $row['Fname'] . " " . $row['Mname']),
        'memberid' => $row['memberid'],
        'mobile' => $row['MobilePhone']
    ];
}
echo json_encode($results);
exit;
