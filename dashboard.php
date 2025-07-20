<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location:index.php");
    exit;
}
require_once('Connections/cov.php'); 
mysqli_select_db($cov, $database_cov);

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
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
    // Simple JS to auto-update the time
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent =
            now.toLocaleString('en-GB', {
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
        <div class="flex items-center justify-between px-4 md:px-8 py-2">
            <div class="flex items-center gap-3">
                <img src="<?= htmlspecialchars($row_logo['value']) ?>" alt="Logo"
                    class="h-12 w-12 rounded-full border border-gray-200 object-contain">
                <span class="text-2xl font-bold text-blue-900"><?= htmlspecialchars($row_title['value']) ?></span>
            </div>
            <div class="text-sm text-gray-500 hidden sm:block">
                <i class="fa-regular fa-calendar-days mr-1"></i>
                <span id="clock"></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-700 font-medium hidden md:block">Welcome, <?= $firstname ?></span>
                <a href="index.php" title="Logout" class="text-red-600 hover:text-red-800"><i
                        class="fas fa-sign-out-alt fa-lg"></i></a>
            </div>
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
                <!-- Add more feature cards as needed -->
            </div>
            <!-- Optionally add your <marquee> or info banner here -->
            <div class="mt-8">
                <?php include("marquee.php"); ?>
            </div>
        </main>
    </div>
</body>

</html>
<?php
mysqli_free_result($logo_query);
mysqli_free_result($title_query);
?>