<?php
if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set all required CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 1728000');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'));

    if (!isset($data->fromPeriod) || !isset($data->toPeriod)) {
        throw new Exception('From and To periods are required');
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT 
    tbpayrollperiods.PayrollPeriod,
    SUM(tlb_mastertransaction.entryFee) as entryFee,
    SUM(tlb_mastertransaction.savings) as savingsAmount,
    SUM(tlb_mastertransaction.shares) as sharesAmount,
    SUM(tlb_mastertransaction.interestPaid) as InterestPaid,
    SUM(tlb_mastertransaction.interest) as interest,
    SUM(tlb_mastertransaction.loanAmount) as loan,
    SUM(tlb_mastertransaction.loanRepayment) as loanRepayment,
    (
        SELECT 
            SUM(m2.interest) - SUM(m2.interestPaid)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as interestBalance,
    (
        SELECT 
            SUM(m2.loanAmount) - SUM(m2.loanRepayment)
        FROM tlb_mastertransaction m2
        WHERE m2.memberid = tlb_mastertransaction.memberid
        AND m2.periodid <= tlb_mastertransaction.periodid
    ) as loanBalance,
    SUM(tlb_mastertransaction.entryFee + 
        tlb_mastertransaction.savings + 
        tlb_mastertransaction.shares + 
        tlb_mastertransaction.interestPaid + 
        tlb_mastertransaction.loanRepayment + 
        tlb_mastertransaction.repayment_bank ) as total
FROM tlb_mastertransaction 
LEFT JOIN tbpayrollperiods ON tlb_mastertransaction.periodid = tbpayrollperiods.Periodid 
WHERE tlb_mastertransaction.memberid = :coopId
AND tlb_mastertransaction.periodid BETWEEN :fromPeriod AND :toPeriod
GROUP BY tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod
ORDER BY tbpayrollperiods.Periodid DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':coopId', $data->coopId);
    $stmt->bindParam(':fromPeriod', $data->fromPeriod);
    $stmt->bindParam(':toPeriod', $data->toPeriod);
    $stmt->execute();

    $summaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $summaries
    ]);
    error_log(print_r($summaries, true));
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}