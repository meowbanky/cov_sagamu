<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once('../Connections/cov.php');
require_once('../libs/services/AccountingEngine.php');

try {
    $memberid = intval($_POST['memberid'] ?? 0);
    $periodid = intval($_POST['periodid'] ?? 0);
    
    if ($memberid <= 0 || $periodid <= 0) {
        throw new Exception('Invalid member or period ID');
    }
    
    $engine = new AccountingEngine($cov, $database_cov);
    
    // Find all journal entries for this member/period that are NOT already reversed
    $sql = "SELECT id, entry_number, source_document, description, total_amount, is_reversed
            FROM coop_journal_entries
            WHERE periodid = ? 
            AND (source_document LIKE ? OR source_document LIKE ?)
            AND status = 'posted'
            ORDER BY id ASC";
    
    $stmt = mysqli_prepare($cov, $sql);
    $contrib_pattern = "CONTRIB-{$memberid}-%";
    $loan_pattern = "LOAN-{$memberid}-%";
    mysqli_stmt_bind_param($stmt, "iss", $periodid, $contrib_pattern, $loan_pattern);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $entries_to_reverse = [];
    $already_reversed_count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        // Only add entries that are not already reversed (handle MySQL boolean 0/1)
        if ($row['is_reversed'] == 1 || $row['is_reversed'] === true || strtolower($row['is_reversed']) === '1') {
            $already_reversed_count++;
        } else {
            $entries_to_reverse[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    
    // If all entries are already reversed, that's fine - we can proceed with deletion
    if (empty($entries_to_reverse)) {
        echo json_encode([
            'success' => true,
            'message' => $already_reversed_count > 0 
                ? "All {$already_reversed_count} journal entries are already reversed" 
                : 'No accounting entries found to reverse',
            'entries_reversed' => 0,
            'already_reversed' => $already_reversed_count
        ]);
        exit;
    }
    
    // Reverse each entry that needs reversal
    $reversed_count = 0;
    $errors = [];
    
    foreach ($entries_to_reverse as $entry) {
        $result = $engine->reverseEntry($entry['id'], $_SESSION['UserID'], "Reversal for transaction correction");
        
        if ($result['success']) {
            $reversed_count++;
        } else {
            // If entry is already reversed, treat as success (might have been reversed by another process)
            if (strpos($result['error'], 'already been reversed') !== false) {
                $reversed_count++;
                $already_reversed_count++;
            } else {
                // Real error - add to error list
                $errors[] = "Entry {$entry['entry_number']}: {$result['error']}";
            }
        }
    }
    
    // Success if all entries were reversed (either now or already were)
    if (empty($errors)) {
        $total_handled = $reversed_count + $already_reversed_count;
        echo json_encode([
            'success' => true,
            'message' => "All journal entries handled successfully ({$reversed_count} reversed, {$already_reversed_count} were already reversed)",
            'entries_reversed' => $reversed_count,
            'already_reversed' => $already_reversed_count
        ]);
    } else {
        // Some entries failed to reverse (not because they were already reversed)
        echo json_encode([
            'success' => false,
            'error' => "Only {$reversed_count} of " . count($entries_to_reverse) . " entries reversed. Errors: " . implode('; ', $errors)
        ]);
    }
    
} catch (Exception $e) {
    error_log("Transaction reversal error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>