<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('Connections/cov.php'); 
mysqli_select_db($cov, $database_cov);

function curlPost($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error !== '') {
        throw new \Exception($error);
    }
    return $response;
}

// Fetch logo and title (do once for performance)
$logo_query = mysqli_query($cov, "SELECT `value` FROM tbl_globa_settings WHERE setting_id = 2");
$row_logo = mysqli_fetch_assoc($logo_query);
$title_query = mysqli_query($cov, "SELECT `value` FROM tbl_globa_settings WHERE setting_id = 1");
$row_title = mysqli_fetch_assoc($title_query);
$firstname = htmlspecialchars($_SESSION['FirstName'] ?? "User");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($row_title['value']) ?> - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($row_logo['value']) ?>">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.ico">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
    // Simple JS to auto-update the time
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent =
            now.toLocaleString('en-NG', {
                hour12: false
            });
    }
    setInterval(updateClock, 1000);
    window.onload = updateClock;
    </script>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
    <!-- Header -->
    <header class="w-full bg-white shadow sticky top-0 z-30">
        <!-- Top Row: Logo, Title, Clock, User -->
        <div class="flex items-center justify-between px-4 md:px-8 py-2">
            <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($row_logo['value']) ?>" alt="Logo"
                    class="h-12 w-12 rounded-full border border-gray-200 object-contain">
                <span class="text-2xl font-bold text-blue-900"><?= htmlspecialchars($row_title['value']) ?></span>
            </div>
            <div class="text-sm text-gray-500 hidden sm:block mr-8">
                <i class="fa-regular fa-calendar-days mr-1"></i>
                <span id="clock"></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-700 font-medium hidden md:block">Welcome, <?= $firstname ?></span>
                <a href="index.php" title="Logout" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-sign-out-alt fa-lg"></i>
                </a>
            </div>
        </div>
        <!-- Stats Marquee Row -->
        <div class="bg-gray-50 border-t border-gray-200 px-4 md:px-8 py-2">
            <marquee behavior="scroll" direction="left" scrollamount="3" class="text-xs md:text-sm text-gray-600">
                <span class="text-red-600 font-semibold">SMS BALANCE:
                    <?php
                    try{
                        $response = curlPost('https://api.ng.termii.com/api/get-balance?api_key=TLYa2oT5vTpT3X4r3fSv2lSfErDApbmhbOAjOP3ituAA2XnLYMFIqzrq3leU1y');
                        $jsonobj = $response;
                        $obj = json_decode($jsonobj);
                        echo number_format($obj->balance);
                    }
                    catch(Exception $e) {
                        echo '0';
                    }
                    ?>
                </span>
                <span class="mx-4">|</span>
                <span class="text-blue-600 font-semibold">Active Members:
                    <?php
                    $query_activeMembers = "SELECT count(tbl_personalinfo.memberid) FROM tbl_personalinfo WHERE `Status` = 'Active'";
                    $activeMembers = mysqli_query($cov,$query_activeMembers) or die(mysqli_error($cov));
                    $row_activeMembers = mysqli_fetch_assoc($activeMembers);
                    echo $row_activeMembers['count(tbl_personalinfo.memberid)'];
                    ?>
                </span>
                <span class="mx-4">|</span>
                <span class="text-green-600 font-semibold">Savings:
                    <?php
                    $query_contribution = "SELECT SUM(tlb_mastertransaction.savings) as savings FROM tlb_mastertransaction";
                    $contribution = mysqli_query($cov,$query_contribution) or die(mysqli_error($cov));
                    $row_contribution = mysqli_fetch_assoc($contribution);
                    echo number_format($row_contribution['savings'],2);
                    ?>
                </span>
                <span class="mx-4">|</span>
                <span class="text-purple-600 font-semibold">Shares:
                    <?php
                    $query_shares = "SELECT SUM(tlb_mastertransaction.shares) as shares FROM tlb_mastertransaction";
                    $shares = mysqli_query($cov,$query_shares) or die(mysqli_error($cov));
                    $row_shares = mysqli_fetch_assoc($shares);
                    echo number_format($row_shares['shares'],2);
                    ?>
                </span>
                <span class="mx-4">|</span>
                <span class="text-orange-600 font-semibold">Loan Debt:
                    <?php
                    $query_loanDebt = "SELECT (SUM(tlb_mastertransaction.loanAmount))-(SUM(tlb_mastertransaction.loanRepayment)) as 'LoanDebt' FROM tlb_mastertransaction";
                    $loanDebt = mysqli_query($cov,$query_loanDebt) or die(mysqli_error($cov));
                    $row_loanDebt = mysqli_fetch_assoc($loanDebt);
                    echo number_format($row_loanDebt['LoanDebt'],2);
                    ?>
                </span>
            </marquee>
        </div>
    </header>
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg min-h-screen flex flex-col py-6 px-3 hidden md:flex">
            <nav class="flex-1">
                <ul class="space-y-2">
                    <li><a href="dashboard.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 font-medium text-blue-900"><i
                                class="fa fa-house fa-fw mr-2"></i> Dashboard</a></li>
                    <li><a href="registration.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-user-plus fa-fw mr-2"></i> Registration</a></li>
                    <li><a href="process2.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-cogs fa-fw mr-2"></i> Process Transaction</a></li>
                    <li><a href="editContributions.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-pen fa-fw mr-2"></i> Edit Contribution</a></li>
                    <li><a href="addloan.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-hand-holding-usd fa-fw mr-2"></i> Add Loan</a></li>
                    <li><a href="withdrawal_savings.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-money-bill-wave fa-fw mr-2"></i> Withdraw from Savings</a></li>
                    <li><a href="memberlist.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-users fa-fw mr-2"></i> Print Member's List</a></li>
                    <li><a href="transact_period.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-calendar-alt fa-fw mr-2"></i> Create Period</a></li>
                    <li><a href="mastertransaction.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-list-alt fa-fw mr-2"></i> Master Transaction</a></li>
                    <li><a href="status.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-balance-scale fa-fw mr-2"></i> Check Status</a></li>
                    <li><a href="backup2.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-database fa-fw mr-2"></i> Backup</a></li>
                    <li><a href="special_savings_management.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-star fa-fw mr-2"></i> Special Savings</a></li>
                    <li><a href="email_queue_dashboard.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-envelope fa-fw mr-2"></i> Email Queue</a></li>
                    <li><a href="ai_bank_statement_upload.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100"><i
                                class="fa fa-robot fa-fw mr-2"></i> AI Bank Statement Upload</a></li>
                    <!-- Add more menu items as needed -->
                </ul>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 py-8 px-4 md:px-10">
            <h1 class="text-3xl font-bold mb-8 text-blue-900 flex items-center gap-2"><i
                    class="fa fa-dashboard fa-lg"></i> Dashboard</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Example Card -->
                <a href="registration.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-user-plus fa-3x text-blue-500 mb-4"></i>
                    <span class="font-semibold text-lg">New Member</span>
                </a>
                <a href="registration_search.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-user-edit fa-3x text-green-500 mb-4"></i>
                    <span class="font-semibold text-lg">Edit Member's Record</span>
                </a>
                <a href="memberlist.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-users fa-3x text-orange-500 mb-4"></i>
                    <span class="font-semibold text-lg">Print Member's List</span>
                </a>
                <a href="transact_period.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-calendar-alt fa-3x text-indigo-500 mb-4"></i>
                    <span class="font-semibold text-lg">Create Period</span>
                </a>
                <a href="addloan.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-hand-holding-usd fa-3x text-pink-500 mb-4"></i>
                    <span class="font-semibold text-lg">Add Loan</span>
                </a>
                <a href="withdrawal_savings.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-money-bill-wave fa-3x text-green-600 mb-4"></i>
                    <span class="font-semibold text-lg">Withdraw from Savings</span>
                </a>
                <a href="coop_finance.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-money-bill-trend-up fa-3x text-indigo-600 mb-4"></i>
                    <span class="font-semibold text-lg">Income & Expenditure</span>
                </a>
                <a href="process2.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-cogs fa-3x text-teal-500 mb-4"></i>
                    <span class="font-semibold text-lg">Process Transaction</span>
                </a>
                <a href="editContributions.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-pen fa-3x text-red-400 mb-4"></i>
                    <span class="font-semibold text-lg">Edit Contribution</span>
                </a>
                <a href="mastertransaction.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-list-alt fa-3x text-cyan-500 mb-4"></i>
                    <span class="font-semibold text-lg">Master Transaction</span>
                </a>
                <a href="status.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-balance-scale fa-3x text-violet-500 mb-4"></i>
                    <span class="font-semibold text-lg">Check Status</span>
                </a>
                <a href="backup2.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-database fa-3x text-yellow-500 mb-4"></i>
                    <span class="font-semibold text-lg">Backup Database</span>
                </a>
                <a href="special_savings_management.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-star fa-3x text-amber-500 mb-4"></i>
                    <span class="font-semibold text-lg">Special Savings</span>
                </a>
                <a href="email_queue_dashboard.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-envelope fa-3x text-green-500 mb-4"></i>
                    <span class="font-semibold text-lg">Email Queue</span>
                </a>
                <a href="coop_trial_balance.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-balance-scale fa-3x text-indigo-500 mb-4"></i>
                    <span class="font-semibold text-lg">Trial Balance</span>
                </a>
                <a href="coop_financial_statements.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-chart-line fa-3x text-purple-500 mb-4"></i>
                    <span class="font-semibold text-lg">Financial Statements</span>
                </a>
                <a href="AlertSystem/index.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-bell fa-3x text-blue-400 mb-4"></i>
                    <span class="font-semibold text-lg">Send Alert</span>
                </a>
                <a href="registeruser.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-user-shield fa-3x text-purple-500 mb-4"></i>
                    <span class="font-semibold text-lg">Register User</span>
                </a>
                <a href="ai_bank_statement_upload.php"
                    class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center hover:shadow-2xl transition">
                    <i class="fa fa-robot fa-3x text-blue-600 mb-4"></i>
                    <span class="font-semibold text-lg">AI Bank Statement Upload</span>
                </a>
                <!-- Add more feature cards as needed -->
            </div>
        </main>
    </div>
</body>

</html>
<?php
mysqli_free_result($logo_query);
mysqli_free_result($title_query);
?>