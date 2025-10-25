<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
$logo_query = mysqli_query($cov, "SELECT `value` FROM tbl_globa_settings WHERE setting_id = 2");
$row_logo = mysqli_fetch_assoc($logo_query);
$title_query = mysqli_query($cov, "SELECT `value` FROM tbl_globa_settings WHERE setting_id = 1");
$row_title = mysqli_fetch_assoc($title_query);
$firstname = htmlspecialchars($_SESSION['FirstName'] ?? "User");
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($row_title['value']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($row_logo['value']) ?>">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- jQuery & jQuery UI (for autocomplete, datepicker, etc) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.min.css" />
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- SweetAlert2 for alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
    <style>
    .sidebar-active {
        background: #e0e7ff;
        color: #1d4ed8;
    }
    </style>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
    <!-- HEADER -->
    <header class="w-full bg-white shadow sticky top-0 z-30">
        <!-- Top Row: Logo, Title, Clock, User -->
        <div class="flex items-center justify-between px-4 md:px-8 py-2 relative">
            <div class="flex items-center gap-3">
                <!-- Hamburger: mobile only -->
                <button id="menu-btn" class="text-2xl md:hidden mr-2">
                    <i class="fa fa-bars"></i>
                </button>
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
                <a href="index.php" title="Logout" class="text-red-600 hover:text-red-800"><i
                        class="fas fa-sign-out-alt fa-lg"></i></a>
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
    <!-- SIDEBAR + MAIN -->
    <div class="flex min-h-screen">
        <!-- SIDEBAR: mobile & desktop -->
        <aside id="sidebar"
            class="fixed top-0 left-0 z-40 w-64 h-full bg-white shadow-lg flex flex-col py-6 px-3 transition-transform duration-200 -translate-x-full md:static md:translate-x-0 md:flex md:w-64">
            <div class="flex justify-between mb-6 md:hidden">
                <span class="text-lg font-bold text-blue-900"><?= htmlspecialchars($row_title['value']) ?></span>
                <button id="close-sidebar" class="text-gray-500 text-2xl focus:outline-none">&times;</button>
            </div>
            <nav class="flex-1">
                <ul class="space-y-2">
                    <li><a href="dashboard.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 font-medium text-blue-900 <?= $current=='dashboard.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-house fa-fw mr-2"></i> Dashboard</a></li>
                    <li><a href="registration.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='registration.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-user-plus fa-fw mr-2"></i> Registration</a></li>
                    <li><a href="process2.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='process2.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-cogs fa-fw mr-2"></i> Process Transaction</a></li>
                    <li><a href="editContributions.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='editContributions.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-pen fa-fw mr-2"></i> Edit Contribution</a></li>
                    <li><a href="addloan.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='addloan.php'?'sidebar-active sidebar-active':'' ?>"><i
                                class="fa fa-hand-holding-usd fa-fw mr-2"></i> Add Loan</a></li>
                    <li><a href="withdrawal_savings.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='withdrawal_savings.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-money-bill-wave fa-fw mr-2"></i> Withdraw from Savings</a></li>
                    <li><a href="coop_finance.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='coop_finance.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-money-bill-trend-up fa-fw mr-2"></i> Income & Expenditure</a></li>
                    <li><a href="memberlist.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='memberlist.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-users fa-fw mr-2"></i> Print Member's List</a></li>
                    <li><a href="transact_period.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='transact_period.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-calendar-alt fa-fw mr-2"></i> Create Period</a></li>
                    <li><a href="mastertransaction.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='mastertransaction.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-list-alt fa-fw mr-2"></i> Master Transaction</a></li>
                    <li><a href="status.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='status.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-balance-scale fa-fw mr-2"></i> Check Status</a></li>
                    <li><a href="backup2.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='backup2.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-database fa-fw mr-2"></i> Backup</a></li>
                    <li><a href="special_savings_management.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='special_savings_management.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-star fa-fw mr-2"></i> Special Savings</a></li>
                    <li><a href="email_queue_dashboard.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='email_queue_dashboard.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-envelope fa-fw mr-2"></i> Email Queue</a></li>
                    
                    <!-- Accounting Section -->
                    <li class="mt-4 pt-4 border-t border-gray-200">
                        <span class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accounting</span>
                    </li>
                    <li><a href="coop_chart_of_accounts.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='coop_chart_of_accounts.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-list-alt fa-fw mr-2"></i> Chart of Accounts</a></li>
                    <li><a href="coop_journal_entries.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='coop_journal_entries.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-book fa-fw mr-2"></i> Journal Entries</a></li>
                    <li><a href="coop_trial_balance.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='coop_trial_balance.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-balance-scale fa-fw mr-2"></i> Trial Balance</a></li>
                    <li><a href="coop_financial_statements.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='coop_financial_statements.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-chart-line fa-fw mr-2"></i> Financial Statements</a></li>
                    <li><a href="coop_member_statement.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='coop_member_statement.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-user-circle fa-fw mr-2"></i> Member Statement</a></li>
                    
                    <li><a href="queue_members_email.php"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='queue_members_email.php'?'sidebar-active':'' ?>"><i
                                class="fa fa-paper-plane fa-fw mr-2"></i> Queue Members Email</a></li>
                </ul>
            </nav>
        </aside>
        <!-- PAGE CONTENT -->
        <main class="flex-1 py-8 px-4 md:px-10">