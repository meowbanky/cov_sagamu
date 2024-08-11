<?php

	$db_server = "localhost";
	$db_user = 	 "emmaggic_root";
	$db_passwd = "Oluwaseyi";
	$db_name = "emmaggic_cofv" ;

	try {
			$conn = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user, $db_passwd, array(PDO::ATTR_PERSISTENT=>true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	catch(PDOException $e)
		{
			echo "Failed Connection: " . $e->getMessage();
		}

	$conn2 = mysqli_connect($db_server, $db_user, $db_passwd, $db_name); 



?>
