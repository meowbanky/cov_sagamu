<?php
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);

$periodid = intval($_POST['periodid'] ?? ($_GET['periodid'] ?? 0));
$action = $_REQUEST['action'] ?? 'list';

if (!$periodid && $action != 'get_one') {
    echo "No period selected.";
    exit;
}

// Action: GET ONE (for editing)
if ($action == 'get_one') {
    $loanid = intval($_GET['loanid'] ?? 0);
    $query = "SELECT l.loanid, l.memberid, l.periodid, l.loanamount, l.interest, l.loan_date, 
                     CONCAT(p.Lname, ' ', p.Fname) as membername 
              FROM tbl_special_loan l
              JOIN tbl_personalinfo p ON l.memberid = p.memberid
              WHERE l.loanid = ?";
    $stmt = $cov->prepare($query);
    $stmt->bind_param('i', $loanid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }
    exit;
}

// Action: LIST (for table)
$query = "SELECT l.loanid, l.loanamount, l.interest, l.loan_date, 
                 CONCAT(p.Lname, ' ', p.Fname) as membername 
          FROM tbl_special_loan l
          JOIN tbl_personalinfo p ON l.memberid = p.memberid
          WHERE l.periodid = ?
          ORDER BY l.loanid DESC";

$stmt = $cov->prepare($query);
$stmt->bind_param('i', $periodid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0): ?>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border text-left">Member</th>
                <th class="p-2 border text-right">Loan Amount</th>
                <th class="p-2 border text-center">Date</th>
                <th class="p-2 border text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): 
                $total = $row['loanamount'] + $row['interest'];
            ?>
            <tr class="hover:bg-gray-50">
                <td class="p-2 border"><?= htmlspecialchars($row['membername']) ?></td>
                <td class="p-2 border text-right"><?= number_format($row['loanamount'], 2) ?></td>
                <td class="p-2 border text-center"><?= htmlspecialchars($row['loan_date']) ?></td>
                <td class="p-2 border text-center">
                    <button class="edit-loan-btn text-blue-600 hover:text-blue-800 mr-2" data-loanid="<?= $row['loanid'] ?>">Edit</button>
                    <button class="delete-loan-btn text-red-600 hover:text-red-800" data-loanid="<?= $row['loanid'] ?>">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="p-4 bg-yellow-50 text-yellow-700 rounded">No special loans found for this period.</div>
<?php endif; ?>
