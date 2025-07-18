<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_alertsystem = "localhost";
$database_alertsystem = "emmaggic_mhwun";
$username_alertsystem = "emmaggic_root";
$password_alertsystem = "Oluwaseyi";
$alertsystem = mysql_pconnect($hostname_alertsystem, $username_alertsystem, $password_alertsystem) or trigger_error(mysql_error(),E_USER_ERROR); 
?>