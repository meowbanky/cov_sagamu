<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_cov = "localhost";
$database_cov = "emmaggic_cofv";
$username_cov = "emmaggic_root";
$password_cov = "Oluwaseyi";
$cov = mysqli_connect($hostname_cov, $username_cov, $password_cov);
if (!$cov) {
    trigger_error(mysqli_connect_error(), E_USER_ERROR);
}
mysqli_select_db($cov, $database_cov);

try {
			$conn = new PDO("mysql:host=$hostname_cov;dbname=$database_cov", $username_cov, $password_cov, array(PDO::ATTR_PERSISTENT=>true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
		}
	catch(PDOException $e)
		{
			echo "Failed Connection: " . $e->getMessage();
		}