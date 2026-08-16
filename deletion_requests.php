<?php
// deletion_requests.php
//
// Officer queue for member-initiated account deletion requests raised from the
// mobile app (App Store Guideline 5.1.1(v)).
//
// A member may not erase their own record while indebted to the society, so
// the app only ever files a request; approval happens here. Approving runs the
// same anonymisation the mobile code path would have, via MemberBalance so the
// two can never drift apart.

session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('header.php');
require_once('Connections/cov.php');
require_once('auth_api/utils/MemberBalance.php');

mysqli_select_db($cov, $database_cov);

/**
 * MemberBalance works on PDO; the admin side is mysqli. One short-lived PDO
 * handle for the approval path keeps the anonymisation logic in a single place
 * rather than reimplementing it here in mysqli and letting the two drift.
 */
function adminPdo()
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

$flash = null;
$flashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $requestId = (int) $_POST['request_id'];
    $action = $_POST['action'];
    $reviewNote = trim($_POST['review_note'] ?? '');
    $officer = (string) $_SESSION['UserID'];

    try {
        $pdo = adminPdo();

        $stmt = $pdo->prepare(
            'SELECT memberid, status FROM tbl_account_deletions WHERE id = :id'
        );
        $stmt->execute([':id' => $requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            throw new Exception('Request not found.');
        }
        if (!in_array($request['status'], ['pending', 'pending_settlement'], true)) {
            throw new Exception('That request has already been actioned.');
        }

        if ($action === 'approve') {
            // Re-check the balance at approval time — it may have been settled
            // (or newly incurred) since the member filed the request.
            $outstanding = MemberBalance::outstandingLoan($pdo, $request['memberid']);
            if ($outstanding > 0) {
                throw new Exception(
                    'Cannot approve: member still owes '
                    . MemberBalance::formatNaira($outstanding) . '.'
                );
            }

            $pdo->beginTransaction();
            try {
                MemberBalance::anonymise($pdo, $request['memberid'], $officer);

                $stmt = $pdo->prepare(
                    "UPDATE tbl_account_deletions
                        SET status = 'approved', reviewed_at = NOW(),
                            reviewed_by = :officer, review_note = :note
                      WHERE id = :id"
                );
                $stmt->execute([
                    ':officer' => $officer,
                    ':note' => $reviewNote !== '' ? $reviewNote : null,
                    ':id' => $requestId,
                ]);
                $pdo->commit();
            } catch (Exception $inner) {
                $pdo->rollBack();
                throw $inner;
            }

            $flash = 'Account ' . htmlspecialchars($request['memberid'])
                . ' has been closed and anonymised.';
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare(
                "UPDATE tbl_account_deletions
                    SET status = 'rejected', reviewed_at = NOW(),
                        reviewed_by = :officer, review_note = :note
                  WHERE id = :id"
            );
            $stmt->execute([
                ':officer' => $officer,
                ':note' => $reviewNote !== '' ? $reviewNote : null,
                ':id' => $requestId,
            ]);
            $flash = 'Request declined. The member keeps their account.';
        } else {
            throw new Exception('Unknown action.');
        }
    } catch (Exception $e) {
        error_log('deletion_requests: ' . $e->getMessage());
        $flash = $e->getMessage();
        $flashType = 'error';
    }
}

// Open requests, with today's balance rather than the one captured at request
// time, so an officer never approves against a stale figure.
$rows = [];
try {
    $pdo = adminPdo();
    $stmt = $pdo->query(
        "SELECT d.id, d.memberid, d.requested_at, d.status,
                d.outstanding_at_request, d.member_note,
                p.Fname, p.Lname
           FROM tbl_account_deletions d
           LEFT JOIN tbl_personalinfo p ON p.memberid = d.memberid
          WHERE d.status IN ('pending', 'pending_settlement')
          ORDER BY d.requested_at ASC"
    );
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $row['outstanding_now'] = MemberBalance::outstandingLoan($pdo, $row['memberid']);
        $rows[] = $row;
    }
} catch (Exception $e) {
    error_log('deletion_requests list: ' . $e->getMessage());
    $flash = 'Could not load requests: ' . $e->getMessage();
    $flashType = 'error';
}
?>

<div class="p-4 md:p-6">
  <h1 class="text-2xl font-semibold mb-1">Account Deletion Requests</h1>
  <p class="text-sm text-gray-600 mb-4">
    Members request account closure from the mobile app. A member with an
    outstanding loan cannot be approved until the balance is settled.
  </p>

  <?php if ($flash): ?>
    <div class="mb-4 px-4 py-3 rounded <?= $flashType === 'error'
        ? 'bg-red-100 text-red-800 border border-red-300'
        : 'bg-green-100 text-green-800 border border-green-300' ?>">
      <?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
    <div class="bg-white rounded shadow p-6 text-gray-500">
      No pending deletion requests.
    </div>
  <?php else: ?>
    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left">
          <tr>
            <th class="px-4 py-3">Member</th>
            <th class="px-4 py-3">Requested</th>
            <th class="px-4 py-3">Owed now</th>
            <th class="px-4 py-3">Member note</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y">
        <?php foreach ($rows as $row): ?>
          <?php $blocked = $row['outstanding_now'] > 0; ?>
          <tr>
            <td class="px-4 py-3">
              <div class="font-medium">
                <?= htmlspecialchars(trim(($row['Fname'] ?? '') . ' ' . ($row['Lname'] ?? ''))) ?>
              </div>
              <div class="text-gray-500"><?= htmlspecialchars($row['memberid']) ?></div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <?= htmlspecialchars(date('d M Y', strtotime($row['requested_at']))) ?>
            </td>
            <td class="px-4 py-3 whitespace-nowrap <?= $blocked ? 'text-red-700 font-semibold' : 'text-green-700' ?>">
              <?= htmlspecialchars(MemberBalance::formatNaira($row['outstanding_now'])) ?>
            </td>
            <td class="px-4 py-3 text-gray-600 max-w-xs">
              <?= $row['member_note'] ? htmlspecialchars($row['member_note']) : '—' ?>
            </td>
            <td class="px-4 py-3">
              <?php if ($blocked): ?>
                <span class="text-gray-500 italic">Blocked — balance outstanding</span>
                <form method="post" class="mt-2 flex gap-2">
                  <input type="hidden" name="request_id" value="<?= (int) $row['id'] ?>">
                  <input type="hidden" name="action" value="reject">
                  <input type="text" name="review_note" placeholder="Reason (optional)"
                         class="border rounded px-2 py-1 text-xs">
                  <button class="px-3 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300">
                    Decline
                  </button>
                </form>
              <?php else: ?>
                <form method="post" class="flex flex-wrap gap-2 items-center">
                  <input type="hidden" name="request_id" value="<?= (int) $row['id'] ?>">
                  <input type="text" name="review_note" placeholder="Note (optional)"
                         class="border rounded px-2 py-1 text-xs">
                  <!-- Confirm only on approve; declining is reversible. -->
                  <button name="action" value="approve"
                          onclick="return confirm('Permanently close and anonymise this member\'s record? This cannot be undone.');"
                          class="px-3 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                    Approve &amp; close
                  </button>
                  <button name="action" value="reject"
                          class="px-3 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300">
                    Decline
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once('footer.php'); ?>
