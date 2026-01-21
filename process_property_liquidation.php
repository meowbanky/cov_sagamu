<?php
require_once('Connections/cov.php');
session_start();

// Ensure JSON response
header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

mysqli_select_db($cov, $database_cov);

// Handle GET requests (e.g., getting recent liquidations)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_recent') {
        fetchRecentLiquidations($cov);
        exit;
    }
}

// Handle POST requests (Processing Liquidation)
// Handle POST requests (Processing Liquidation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? 'create';

    // DELETE Action
    if($action === 'delete') {
        $propertyId = intval($_POST['property_id'] ?? 0);
        
        if($propertyId <= 0) {
            echo json_encode(['error' => 'Invalid property ID.']);
            exit;
        }

        mysqli_begin_transaction($cov);
        try {
            // Get transaction_id first
            $stmtGet = $cov->prepare("SELECT transaction_id FROM tbl_properties WHERE property_id = ?");
            $stmtGet->bind_param("i", $propertyId);
            $stmtGet->execute();
            $resGet = $stmtGet->get_result();
            if($row = $resGet->fetch_assoc()) {
                $transactionId = $row['transaction_id'];
                
                // Delete from Master Transaction
                if($transactionId > 0) {
                    $stmtDelTrans = $cov->prepare("DELETE FROM tlb_mastertransaction WHERE transactionid = ?");
                    $stmtDelTrans->bind_param("i", $transactionId);
                    $stmtDelTrans->execute();
                    $stmtDelTrans->close();
                }
            }
            $stmtGet->close();

            // Delete from Properties
            $stmtDelProp = $cov->prepare("DELETE FROM tbl_properties WHERE property_id = ?");
            $stmtDelProp->bind_param("i", $propertyId);
            if(!$stmtDelProp->execute()) {
                throw new Exception("Failed to delete property record.");
            }
            $stmtDelProp->close();

            mysqli_commit($cov);
            echo json_encode(['success' => true, 'message' => "Liquidation deleted successfully."]);

        } catch (Exception $e) {
            mysqli_rollback($cov);
            echo json_encode(['error' => 'Delete failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // CREATE / UPDATE Actions
    $memberId = intval($_POST['member_id'] ?? 0);
    $periodId = intval($_POST['period_id'] ?? 0);
    $propertyName = trim($_POST['property_name'] ?? '');
    $propertyDesc = trim($_POST['property_description'] ?? '');
    $propertyValue = floatval(str_replace(",", "", $_POST['property_value'] ?? 0));
    $propertyId = intval($_POST['property_id'] ?? 0); // For updates

    if($memberId <= 0) {
        echo json_encode(['error' => 'Invalid member selected.']);
        exit;
    }
    
    if($periodId <= 0) {
        echo json_encode(['error' => 'Invalid period.']);
        exit;
    }
    
    if(empty($propertyName)) {
        echo json_encode(['error' => 'Property name is required.']);
        exit;
    }
    
    if($propertyValue <= 0) {
        echo json_encode(['error' => 'Property value must be greater than zero.']);
        exit;
    }

    mysqli_begin_transaction($cov);

    try {
        if ($action === 'update' && $propertyId > 0) {
            // UPDATE Logic
            // 1. Get existing transaction_id
            $stmtGet = $cov->prepare("SELECT transaction_id FROM tbl_properties WHERE property_id = ?");
            $stmtGet->bind_param("i", $propertyId);
            $stmtGet->execute();
            $resGet = $stmtGet->get_result();
            $transactionId = 0;
            if($row = $resGet->fetch_assoc()) {
                $transactionId = $row['transaction_id'];
            }
            $stmtGet->close();

            // 2. Update Property
            $stmtProp = $cov->prepare("UPDATE tbl_properties SET property_name=?, property_description=?, property_value=? WHERE property_id=?");
            $stmtProp->bind_param("ssdi", $propertyName, $propertyDesc, $propertyValue, $propertyId);
            if(!$stmtProp->execute()) throw new Exception("Failed to update property.");
            $stmtProp->close();

            // 3. Update Master Transaction (Loan Repayment)
            if($transactionId > 0) {
                $stmtMaster = $cov->prepare("UPDATE tlb_mastertransaction SET loanRepayment=?, periodid=? WHERE transactionid=?");
                $stmtMaster->bind_param("dii", $propertyValue, $periodId, $transactionId);
                if(!$stmtMaster->execute()) throw new Exception("Failed to update transaction.");
                $stmtMaster->close();
            }

            $message = "Liquidation updated successfully!";

        } else {
            // CREATE Logic
            // 1. Insert into tlb_mastertransaction FIRST to get ID
            $loanRepayment = $propertyValue;
            $payMethod = 3; 
            $completed = 1;

            $stmtMaster = $cov->prepare("INSERT INTO tlb_mastertransaction (periodid, memberid, loanRepayment, pay_method, completed) VALUES (?, ?, ?, ?, ?)");
            $stmtMaster->bind_param("iidii", $periodId, $memberId, $loanRepayment, $payMethod, $completed);
            
            if(!$stmtMaster->execute()) {
                throw new Exception("Failed to create transaction record: " . $stmtMaster->error);
            }
            $transactionId = $stmtMaster->insert_id;
            $stmtMaster->close();

            // 2. Insert into tbl_properties with transaction_id
            $stmtProp = $cov->prepare("INSERT INTO tbl_properties (memberid, property_name, property_description, property_value, liquidation_date, transaction_id) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmtProp->bind_param("isssi", $memberId, $propertyName, $propertyDesc, $propertyValue, $transactionId);
            
            if(!$stmtProp->execute()) {
                throw new Exception("Failed to save property record: " . $stmtProp->error);
            }
            $stmtProp->close();
            
            $message = "Liquidation processed successfully! Property saved and loan balance reduced by ₦" . number_format($propertyValue, 2);
        }

        mysqli_commit($cov);
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (Exception $e) {
        mysqli_rollback($cov);
        error_log("Liquidation Error: " . $e->getMessage());
        echo json_encode(['error' => 'Transaction failed: ' . $e->getMessage()]);
    }

    exit;
}

// Helper: Fetch Recent Liquidations
function fetchRecentLiquidations($cov) {
    header('Content-Type: text/html');
    
    // We need transaction_id, periodid, description as well for editing
    // Assuming periodid is in tlb_mastertransaction? Or we just pick from property?
    // Wait, periodid is in master transaction. property doesn't store periodid directly in standard design, 
    // but we need it for the form.
    // Let's JOIN with master transaction to get periodid.
    
    $query = "
        SELECT 
            p.property_id,
            p.property_name,
            p.property_description,
            p.property_value,
            p.liquidation_date,
            p.transaction_id,
            pi.memberid,
            pi.Lname, pi.Fname,
            pi.MobilePhone,
            mt.periodid
        FROM tbl_properties p
        JOIN tbl_personalinfo pi ON p.memberid = pi.memberid
        LEFT JOIN tlb_mastertransaction mt ON p.transaction_id = mt.transactionid
        ORDER BY p.property_id DESC
        LIMIT 10
    ";
    
    $result = mysqli_query($cov, $query);
    
    if(!$result || mysqli_num_rows($result) === 0) {
        echo '<div class="text-center text-gray-400 py-4">No recent liquidations found.</div>';
        return;
    }

    echo '<ul class="divide-y divide-gray-100">';
    while($row = mysqli_fetch_assoc($result)) {
        $name = htmlspecialchars($row['Lname'] . ' ' . $row['Fname']);
        $mobile = htmlspecialchars($row['mobile']);
        $prop = htmlspecialchars($row['property_name']);
        $desc = htmlspecialchars($row['property_description']);
        $val = number_format($row['property_value'], 2);
        $date = date('M d, Y', strtotime($row['liquidation_date']));
        $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
        
        echo "
        <li class='py-3 flex justify-between items-center group'>
            <div>
                <p class='text-sm font-medium text-gray-800'>{$prop}</p>
                <p class='text-xs text-gray-500'>{$name}</p>
            </div>
            <div class='flex items-center space-x-4'>
                <div class='text-right'>
                    <p class='text-sm font-bold text-green-600'>₦{$val}</p>
                    <p class='text-xs text-gray-400'>{$date}</p>
                </div>
                <div class='flex space-x-1 opacity-10 group-hover:opacity-100 transition-opacity'>
                    <button type='button' class='p-1 text-blue-600 hover:bg-blue-50 rounded edit-btn' data-liquidation='{$json}' title='Edit'>
                        <i class='fa fa-pencil'></i>
                    </button>
                    <button type='button' class='p-1 text-red-600 hover:bg-red-50 rounded delete-btn' data-id='{$row['property_id']}' title='Delete'>
                        <i class='fa fa-trash'></i>
                    </button>
                </div>
            </div>
        </li>
        ";
    }
    echo '</ul>';
}
?>
