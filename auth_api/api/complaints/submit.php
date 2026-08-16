<?php
// api/complaints/submit.php
//
// Records a complaint raised from the mobile app. Complaints are private
// between the member and the cooperative office — they are never shown to
// other members — so this is not user-generated content in the Guideline 1.2
// sense and needs no report/block tooling.

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../utils/MobileAuth.php';

MobileAuth::applyCors('POST, OPTIONS');

const COMPLAINT_CATEGORIES = [
    'Contributions',
    'Loans',
    'Dividends',
    'Account Access',
    'Other',
];

const MAX_SUBJECT_LENGTH = 160;
const MAX_BODY_LENGTH = 4000;

try {
    $memberId = MobileAuth::requireMemberId();
    $body = MobileAuth::jsonBody();

    $category = isset($body['category']) ? trim($body['category']) : '';
    $subject = isset($body['subject']) ? trim($body['subject']) : '';
    $message = isset($body['body']) ? trim($body['body']) : '';

    if (!in_array($category, COMPLAINT_CATEGORIES, true)) {
        throw new Exception('Please choose a valid category', 400);
    }
    if ($subject === '' || mb_strlen($subject) > MAX_SUBJECT_LENGTH) {
        throw new Exception('Subject must be between 1 and ' . MAX_SUBJECT_LENGTH . ' characters', 400);
    }
    if ($message === '' || mb_strlen($message) > MAX_BODY_LENGTH) {
        throw new Exception('Message must be between 1 and ' . MAX_BODY_LENGTH . ' characters', 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare(
        'INSERT INTO tbl_complaints (memberid, category, subject, body, status, created_at)
         VALUES (:member_id, :category, :subject, :body, :status, NOW())'
    );
    $status = 'open';
    $stmt->bindParam(':member_id', $memberId);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':body', $message);
    $stmt->bindParam(':status', $status);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Your complaint has been sent to the cooperative office.',
        'data' => ['id' => (int) $db->lastInsertId()],
    ]);
} catch (Exception $e) {
    error_log('complaints/submit: ' . $e->getMessage());
    MobileAuth::fail($e);
}
