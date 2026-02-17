<?php
header('Content-Type: application/json');
require_once('../Connections/cov.php');
mysqli_select_db($cov, $database_cov);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$items = json_decode($_POST['items'] ?? '[]', true);

if (empty($items)) {
    echo json_encode(['error' => 'No items selected for deletion']);
    exit;
}

$successCount = 0;
$failCount = 0;
$errors = [];

foreach ($items as $item) {
    $id = isset($item['id']) ? intval($item['id']) : 0;
    $type = isset($item['type']) ? $item['type'] : 'regular';
    
    if ($id <= 0) continue;

    $table = ($type === 'special_repayment') 
        ? 'tbl_specialcontributions' 
        : 'tbl_contributions';
        
    $pk = ($type === 'special_repayment') ? 'id' : 'contriId';

    $query = "DELETE FROM $table WHERE $pk = $id";
    
    if (mysqli_query($cov, $query)) {
        $successCount++;
    } else {
        $failCount++;
        $errors[] = "Failed to delete ID $id: " . mysqli_error($cov);
    }
}

if ($successCount > 0) {
    if ($failCount > 0) {
        echo json_encode(['success' => "Deleted $successCount records. Failed to delete $failCount records.", 'partial' => true]);
    } else {
        echo json_encode(['success' => "Successfully deleted $successCount records."]);
    }
} else {
    echo json_encode(['error' => 'Failed to delete any selected records.']);
}
?>
