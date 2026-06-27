<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('Connections/cov.php');

// Stream the backup SQL directly to the browser as a download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_backup'])) {
    $tables = [];
    $result = mysqli_query($cov, 'SHOW TABLES');
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $sql = '';
    foreach ($tables as $table) {
        $row2 = mysqli_query($cov, "SHOW CREATE TABLE `$table`")->fetch_row();
        $sql .= "DROP TABLE IF EXISTS `$table`;\n\n" . $row2[1] . ";\n\n";

        $rows = mysqli_query($cov, "SELECT * FROM `$table`");
        $num_fields = mysqli_num_fields($rows);
        while ($row = mysqli_fetch_row($rows)) {
            $values = [];
            for ($j = 0; $j < $num_fields; $j++) {
                $val = isset($row[$j]) ? addslashes($row[$j]) : '';
                $val = str_replace("\n", "\\n", $val);
                $values[] = '"' . $val . '"';
            }
            $sql .= "INSERT INTO `$table` VALUES(" . implode(',', $values) . ");\n";
        }
        $sql .= "\n\n";
    }

    $filename = 'db-backup-' . date('Y-m-d-His') . '.sql';
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Content-Length: ' . strlen($sql));
    echo $sql;
    exit;
}

$current = 'backup2.php';
include('header.php');
?>

<div class="p-6 max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow p-8 text-center">
        <div class="mb-4 text-blue-600">
            <i class="fas fa-database text-5xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Database Backup</h2>
        <p class="text-gray-500 mb-6">
            Downloads a full SQL dump of <strong><?= htmlspecialchars($_ENV['DB_NAME']) ?></strong>
            to your computer. All tables and data are included.
        </p>
        <form method="POST">
            <input type="hidden" name="run_backup" value="1">
            <button type="submit"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-download"></i> Download Backup
            </button>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>
