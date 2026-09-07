<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

try {
    // This returned name, email and mobile number for any staff ID with no token
    // at all. Callers now get their own record, identified by the token.
    $staff_id = MobileAuth::requireStaffId();

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT staff_id, EMAIL, MOBILE_NO, NAME FROM employee WHERE staff_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$staff_id]);

    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        echo json_encode([
            'success' => true,
            'data' => $employee
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found'
        ]);
    }

} catch (Exception $e) {
    $code = $e->getCode();
    http_response_code(is_int($code) && $code >= 400 && $code <= 599 ? $code : 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
