<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_info = "localhost";
$database_info = "YOUR_DATABASE_NAME";
$username_info = "YOUR_DB_USERNAME";
$password_info = "YOUR_DB_PASSWORD";
$info = mysqli_connect($hostname_info, $username_info, $password_info) or trigger_error(mysqli_error($info),E_USER_ERROR); 


	try {
			$conn = new PDO("mysql:host=$hostname_info;dbname=$database_info", $username_info, $password_info, array(PDO::ATTR_PERSISTENT=>true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	catch(PDOException $e)
		{
			echo "Failed Connection: " . $e->getMessage();
		}
?>
