<?php
session_start();
header('Content-Type: application/json');
require_once('Connections/cov.php');

function respond($arr) {
    echo json_encode($arr);
    exit;
}

if (!isset($_SESSION['UserID'])) {
    respond(['error' => 'Session expired. Please log in again.']);
}

$memberid = isset($_POST['memberid']) ? intval($_POST['memberid']) : 0;
if (!$memberid) {
    respond(['error' => 'Invalid member ID.']);
}

// Get current status
$stmt = $cov->prepare("SELECT Status FROM tbl_personalinfo WHERE memberid = ?");
$stmt->bind_param('i', $memberid);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    respond(['error' => 'Member not found.']);
}

// Toggle status: Active -> In-Active, In-Active -> Active
$newStatus = ($member['Status'] === 'Active') ? 'In-Active' : 'Active';
$updatedBy = $_SESSION['FirstName'] ?? 'Admin';

// Update status
$stmt = $cov->prepare("UPDATE tbl_personalinfo SET Status = ?, UpdatedBy = ? WHERE memberid = ?");
$stmt->bind_param('ssi', $newStatus, $updatedBy, $memberid);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    respond(['error' => 'Failed to update member status.']);
}

respond([
    'success' => true,
    'message' => 'Member status updated successfully.',
    'newStatus' => $newStatus,
    'oldStatus' => $member['Status']
]);