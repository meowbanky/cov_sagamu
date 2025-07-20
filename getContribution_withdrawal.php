<?php
require_once('Connections/cov.php');

// Sanitize and get member id
$Id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Query only savings as contribution
$query = "SELECT SUM(tlb_mastertransaction.savings) + SUM(tlb_mastertransaction.shares) AS contribution FROM tlb_mastertransaction WHERE memberid = ?";
$stmt = $cov->prepare($query);
$stmt->bind_param('i', $Id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$contribution = isset($row['contribution']) ? floatval($row['contribution']) : 0.00;
$stmt->close();

// Output as Naira currency
?>
<strong>&#8358;<?= number_format($contribution, 2) ?></strong>
<input id="contribution" name="contribution" type="hidden" value="<?= $contribution ?>" />