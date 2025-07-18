<?php
require_once('../Connections/cov.php');
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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
if (isset($_POST['submit'])) {

    $validextensions = array("jpeg", "jpg", "png");
    $temporary = explode(".", $_FILES["file"]["name"]);
    $file_extension = end($temporary);

    if ((($_FILES["file"]["type"] == "image/png") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/jpeg")
            ) && ($_FILES["file"]["size"] < 100000)//Approx. 100kb files can be uploaded.
            && in_array($file_extension, $validextensions)) {

        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br/><br/>";
        } else {
            
            echo "<span>Your File Uploaded Succesfully...!!</span><br/>";
            echo "<br/><b>File Name:</b> " . $_FILES["file"]["name"] . "<br>";
            echo "<b>Type:</b> " . $_FILES["file"]["type"] . "<br>";
            echo "<b>Size:</b> " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
            echo "<b>Temp file:</b> " . $_FILES["file"]["tmp_name"] . "<br>";


            if (file_exists("../passport/" . $_FILES["file"]["name"])) {
                echo $_FILES["file"]["name"] . " <b>already exists.</b> ";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], "../passport/" . $_FILES["file"]["name"]);
				
				$imageLocation = "passport/" . $_FILES["file"]["name"];
				$insertSQL = sprintf("UPDATE tbl_personalinfo SET passport = %s WHERE memberid = %s",
                  GetSQLValueString($imageLocation, "text"),
				  GetSQLValueString($_GET['memberid'], "text"));
					   
					   
				  mysql_select_db($database_cov, $cov);
				  $Result1 = mysql_query($insertSQL, $cov) or die(mysql_error());
				
				
				
				
                $imgFullpath = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/'. "passport/" . $_FILES["file"]["name"];
				
				$uploadedForm = "../edit_registration.php?memberid=".$_GET['memberid'];
				
				echo "<b>Stored in:</b><a href = '$imgFullpath' target='_blank'> " .$imgFullpath.'<a><br>';
				echo "<b>View Uploaded Form in:</b><a href = '$uploadedForm' target='_blank'> " ."here".'<a>';
            }
        
        }
    } else {
        echo "<span>***Invalid file Size or Type***<span>";
    }
}
?>