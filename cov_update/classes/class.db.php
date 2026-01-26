<?php

    // Load environment variables (relative path to root cov_admin)
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../vendor/autoload.php';
    }
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
    }

	$db_server = $_ENV['DB_HOST'];
	$db_user = 	 $_ENV['DB_USERNAME'];
	$db_passwd = $_ENV['DB_PASSWORD'];
	$db_name =   $_ENV['DB_NAME'];

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
