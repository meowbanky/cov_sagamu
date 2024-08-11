<?php
   require_once('Connections/cov.php');  // include_once('db.php');
   // if(isset($_POST['action']) && $_POST['action'] == 'availability')
   // {
	   	mysqli_select_db( $hms,$database_hms);
        $username       =  $_POST['username']; // Get the username values
        $query  = "select Username from tblusers where Username = '".$username."'";
		$res    = mysqli_query( $hms,$query);
        $count  = mysqli_num_rows($res);
        echo $count;
   // }
?>