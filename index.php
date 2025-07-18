<?php
require_once('Connections/cov.php');
session_start();
session_destroy(); // log out user if any session remains

mysqli_select_db($cov, $database_cov);
$query_global_settings = "SELECT * FROM tbl_globa_settings WHERE setting_id = 1";
$global_settings = mysqli_query($cov, $query_global_settings);
$row_global_settings = mysqli_fetch_assoc($global_settings);

$query_logo = "SELECT `value` FROM tbl_globa_settings WHERE setting_id = 2";
$logo = mysqli_query($cov, $query_logo);
$row_logo = mysqli_fetch_assoc($logo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($row_global_settings['value']) ?> - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">
    <link rel="stylesheet" href="registration_files/oouth.css">
    <style>
        body {
            background: #f4f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 30px 0 rgba(0,0,0,0.12);
            padding: 32px 26px 24px 26px;
            width: 100%;
            max-width: 380px;
            box-sizing: border-box;
            margin: 32px auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .login-header img {
            max-width: 100px;
            margin-bottom: 10px;
        }
        .login-header h2 {
            margin: 0;
            font-size: 1.5em;
            color: #183b5b;
        }
        .login-form label {
            font-weight: 500;
            color: #444;
            display: block;
            margin-top: 20px;
            margin-bottom: 6px;
        }
        .login-form input[type="text"], .login-form input[type="password"] {
            width: 100%;
            padding: 12px 10px;
            font-size: 1em;
            border: 1px solid #c6d0df;
            border-radius: 5px;
            margin-bottom: 8px;
            box-sizing: border-box;
            background: #f9fbfc;
            transition: border 0.2s;
        }
        .login-form input:focus {
            border-color: #4d91f1;
            outline: none;
        }
        .login-form .login-btn {
            width: 100%;
            padding: 12px 0;
            background: #4d91f1;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            margin-top: 12px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .login-form .login-btn:hover {
            background: #2562be;
        }
        .login-footer {
            margin-top: 18px;
            text-align: center;
            color: #888;
            font-size: 0.95em;
        }
        @media (max-width: 500px) {
            .login-container {
                padding: 16px 6vw 18px 6vw;
            }
            .login-header img {
                max-width: 75px;
            }
        }
    </style>
    <script>
        function validateForm() {
            var u = document.getElementById("uname").value.trim();
            var p = document.getElementById("passwd").value;
            if (u === "") {
                alert("Username must be filled out");
                document.getElementById("uname").focus();
                return false;
            }
            if (p === "") {
                alert("Password must be filled out");
                document.getElementById("passwd").focus();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <?php if (!empty($row_logo['value'])): ?>
            <img src="<?= htmlspecialchars($row_logo['value']) ?>" alt="Logo">
        <?php endif; ?>
        <h2><?= htmlspecialchars($row_global_settings['value']) ?></h2>
    </div>
    <form class="login-form" method="POST" action="login_auth.php" onsubmit="return validateForm()">
        <label for="uname">Username</label>
        <input type="text" id="uname" name="uname" placeholder="Enter username" autocomplete="username">
        <label for="passwd">Password</label>
        <input type="password" id="passwd" name="passwd" placeholder="Enter password" autocomplete="current-password">
        <button type="submit" class="login-btn">Login</button>
    </form>
    <div class="login-footer">
        &copy; <?= date('Y') ?> BankSoft Solutions
        <?php if (isset($_GET['Expired'])): ?>
        <script>
            alert("License Expired. Please Contact your Administrator");
        </script>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
<?php
mysqli_free_result($global_settings);
mysqli_free_result($logo);
?>
