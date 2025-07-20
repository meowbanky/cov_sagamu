<?php
require_once('Connections/cov.php');
$periodid = intval($_GET['periodid'] ?? 0);
$admin = isset($_GET['admin']) && $_GET['admin'] === '1' || $_GET['admin'] === 'true';
if (!$periodid) { echo '<div class="text-gray-500 p-4">No period selected.</div>'; exit; }

$sql = "SELECT t.id, t.amount, t.type, t.description, t.created_at, c.name as category
        FROM coop_transactions t
        JOIN coop_categories c ON t.category_id = c.id
        WHERE t.periodid = ?
        ORDER BY t.type, t.created_at DESC";
$stmt = $cov->prepare($sql);
$stmt->bind_param('i', $periodid);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$income = array_filter($rows, fn($r) => $r['type'] === 'income');
$expend = array_filter($rows, fn($r) => $r['type'] === 'expenditure');
$income_total = array_sum(array_column($income, 'amount'));
$expend_total = array_sum(array_column($expend, 'amount'));

function renderTable($rows, $title, $total, $admin) {
    if (!count($rows)) return "<div class='mb-4 text-gray-500'>No $title records for this period.</div>";
    $html = "<div class='mb-4'><h4 class='font-bold text-lg mb-2'>$title</h4><table class='min-w-full bg-white rounded shadow mb-2'><thead><tr><th class='px-3 py-2 text-left'>Date</th><th class='px-3 py-2 text-left'>Category</th><th class='px-3 py-2 text-left'>Description</th><th class='px-3 py-2 text-right'>Amount</th>";
    if ($admin) $html .= "<th class='px-3 py-2 text-center'>Action</th>";
    $html .= "</tr></thead><tbody>";
    foreach ($rows as $r) {
        $html .= "<tr><td class='px-3 py-2'>".htmlspecialchars(date('Y-m-d', strtotime($r['created_at'])))."</td><td class='px-3 py-2'>".htmlspecialchars($r['category'])."</td><td class='px-3 py-2'>".htmlspecialchars($r['description'])."</td><td class='px-3 py-2 text-right'>₦".number_format($r['amount'],2)."</td>";
        if ($admin) {
            $html .= "<td class='px-3 py-2 text-center'><button class='editTransBtn text-xs text-yellow-700 mr-2' data-id='".$r['id']."' data-amount='".$r['amount']."' data-description='".htmlspecialchars($r['description'], ENT_QUOTES)."'>Edit</button><button class='deleteTransBtn text-xs text-red-700' data-id='".$r['id']."'>Delete</button></td>";
        }
        $html .= "</tr>";
    }
    $html .= "<tr class='font-bold bg-gray-100'><td colspan='".($admin?4:3)."' class='px-3 py-2 text-right'>Total</td><td class='px-3 py-2 text-right'>₦".number_format($total,2)."</td>";
    if ($admin) $html .= "<td></td>";
    $html .= "</tr></tbody></table></div>";
    return $html;
}

$html = renderTable($income, 'Income', $income_total, $admin);
$html .= renderTable($expend, 'Expenditure', $expend_total, $admin);
$html .= "<div class='font-bold text-lg mt-4'>Net: <span class='".($income_total-$expend_total>=0?'text-green-700':'text-red-700')."'>₦".number_format($income_total-$expend_total,2)."</span></div>";
echo $html; 