<?php
require_once('Connections/cov.php');
session_start(); // Start session if needed

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_GET["id"]) && $_GET["id"] != "-1" && isset($_GET['Amount']) && isset($_GET['periodset'])) {
    $Amount = str_replace(",", "", $_GET['Amount']);
    $specialsavings = str_replace(",", "", $_GET['specialsavings']);
    $periodset = $_GET["periodset"];
    $memberId = $_GET['id'];

    mysqli_select_db($cov, $database_cov);

    // Check if contribution already exists
    $query_checkContribution = sprintf(
        "SELECT membersid, periodid FROM tbl_contributions WHERE membersid = %s AND periodid = %s",
        GetSQLValueString($cov, $memberId, "text"),
        GetSQLValueString($cov, $periodset, "int")
    );

    $checkContribution = mysqli_query($cov, $query_checkContribution) or die(mysqli_error($cov));
    $totalRows_checkContribution = mysqli_num_rows($checkContribution);


        // Insert new contribution
        $updateSQL = sprintf(
            "INSERT INTO tbl_contributions (contribution,special_savings, membersid, periodid) VALUES (%s,%s, %s, %s)",
            GetSQLValueString($cov, $Amount, "double"),
            GetSQLValueString($cov, $specialsavings, "double"),
            GetSQLValueString($cov, $memberId, "text"),
            GetSQLValueString($cov, $periodset, "int")
        );
        $Result1 = mysqli_query($cov, $updateSQL) or die(mysqli_error($cov));


    mysqli_free_result($checkContribution);
}

function saveContribution($coopid, $specialsavings,$period, $amount, $pay_method) {
    global $cov, $database_cov;
    $updateSQL = sprintf(
        "INSERT INTO tbl_contributions (contribution, special_savings,membersid, periodid, pay_method) VALUES (%s,%s, %s, %s, %s)",
        GetSQLValueString($cov, $amount, "double"),
        GetSQLValueString($cov, $specialsavings, "double"),
        GetSQLValueString($cov, $coopid, "text"),
        GetSQLValueString($cov, $period, "int"),
        GetSQLValueString($cov, $pay_method, "int")
    );
    mysqli_select_db($cov, $database_cov);
    $Result1 = mysqli_query($cov, $updateSQL) or die(mysqli_error($cov));
}
?>
