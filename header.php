<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('Connections/cov.php');
mysqli_select_db($cov, $database_cov);
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
                now.toLocaleString('en-GB', { hour12: false });
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
    <div class="flex items-center justify-between px-4 md:px-8 py-2 relative">
        <div class="flex items-center gap-3">
            <!-- Hamburger: mobile only -->
            <button id="menu-btn" class="text-2xl md:hidden mr-2">
                <i class="fa fa-bars"></i>
            </button>
            <img src="<?= htmlspecialchars($row_logo['value']) ?>" alt="Logo" class="h-12 w-12 rounded-full border border-gray-200 object-contain">
            <span class="text-2xl font-bold text-blue-900"><?= htmlspecialchars($row_title['value']) ?></span>
        </div>
        <div class="text-sm text-gray-500 hidden sm:block">
            <i class="fa-regular fa-calendar-days mr-1"></i>
            <span id="clock"></span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-gray-700 font-medium hidden md:block">Welcome, <?= $firstname ?></span>
            <a href="index.php" title="Logout" class="text-red-600 hover:text-red-800"><i class="fas fa-sign-out-alt fa-lg"></i></a>
        </div>
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
                <li><a href="dashboard.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 font-medium text-blue-900 <?= $current=='dashboard.php'?'sidebar-active':'' ?>"><i class="fa fa-house fa-fw mr-2"></i> Dashboard</a></li>
                <li><a href="registration.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='registration.php'?'sidebar-active':'' ?>"><i class="fa fa-user-plus fa-fw mr-2"></i> Registration</a></li>
                <li><a href="process2.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='process2.php'?'sidebar-active':'' ?>"><i class="fa fa-cogs fa-fw mr-2"></i> Process Transaction</a></li>
                <li><a href="editContributions.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='editContributions.php'?'sidebar-active':'' ?>"><i class="fa fa-pen fa-fw mr-2"></i> Edit Contribution</a></li>
                <li><a href="addloan.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='addloan.php'?'sidebar-active sidebar-active':'' ?>"><i class="fa fa-hand-holding-usd fa-fw mr-2"></i> Add Loan</a></li>
                <li><a href="memberlist.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='memberlist.php'?'sidebar-active':'' ?>"><i class="fa fa-users fa-fw mr-2"></i> Print Member's List</a></li>
                <li><a href="transact_period.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='transact_period.php'?'sidebar-active':'' ?>"><i class="fa fa-calendar-alt fa-fw mr-2"></i> Create Period</a></li>
                <li><a href="mastertransaction.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='mastertransaction.php'?'sidebar-active':'' ?>"><i class="fa fa-list-alt fa-fw mr-2"></i> Master Transaction</a></li>
                <li><a href="status.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='status.php'?'sidebar-active':'' ?>"><i class="fa fa-balance-scale fa-fw mr-2"></i> Check Status</a></li>
                <li><a href="backup2.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-100 <?= $current=='backup2.php'?'sidebar-active':'' ?>"><i class="fa fa-database fa-fw mr-2"></i> Backup</a></li>
            </ul>
        </nav>
    </aside>
    <!-- PAGE CONTENT -->
    <main class="flex-1 py-8 px-4 md:px-10">
