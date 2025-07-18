<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
        $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn_vote, $theValue) : mysqli_escape_string($conn_vote, $theValue);

        switch ($theType) {
            case "text":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double":
                $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
                break;
            case "date":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "defined":
                $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
                break;
        }
        return $theValue;
    }
}

// Fetching data from database
mysqli_select_db($cov, $database_cov);
$query_Period = "SELECT tbpayrollperiods.Periodid, tbpayrollperiods.PayrollPeriod FROM tbpayrollperiods order by periodid desc";
$Period = mysqli_query($cov, $query_Period) or die(mysqli_error($cov));
$row_Period = mysqli_fetch_assoc($Period);
$totalRows_Period = mysqli_num_rows($Period);

mysqli_select_db($cov, $database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov, $query_title) or die(mysqli_error($cov));
$row_title = mysqli_fetch_assoc($title);

mysqli_select_db($cov, $database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov, $query_logo) or die(mysqli_error($cov));
$row_logo = mysqli_fetch_assoc($logo);
session_start();
if (!isset($_SESSION['UserID'])){
    header("Location:index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $row_title['value']; ?> - Members Status</title>
    <link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/output.css" rel="stylesheet" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style type="text/css">
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header, .footer {
            padding: 10px 0;
            text-align: center;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 10px;
        }
        .content {
            margin-top: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .content form {
            display: flex;
            flex-direction: column;
        }
        .content label {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .content input[type="text"], .content select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<header class="header">
    <img src="<?php echo $row_logo['value']; ?>" alt="Logo">
</header>
<main class="container">
    <div class="content">
        <h1>Status</h1>
        <form action="" method="POST">
            <label for="PeriodId">As At:</label>
            <select name="PeriodId" id="PeriodId">
                <option value="na">Select Period</option>
                <?php
                do {
                    echo "<option value='{$row_Period['Periodid']}'>{$row_Period['PayrollPeriod']}</option>";
                } while ($row_Period = mysqli_fetch_assoc($Period));
                ?>
            </select>
            <!-- Add other form fields here -->
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</main>
<footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> Your Company. All Rights Reserved.</p>
</footer>
</body>
</html>
