<?php
require_once('Connections/cov.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Query to get available periods
    $query = "SELECT periodid as id, payrollperiod as name FROM tbpayrollperiods ORDER BY id DESC LIMIT 20";
    $result = mysqli_query($cov, $query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($cov));
    }
    
    $periods = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $periods[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'periods' => $periods
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>