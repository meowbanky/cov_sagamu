<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_cov = "localhost";
$database_cov = "emmaggic_cofv";
$username_cov = "root";
$password_cov = "oluwaseyi";
$cov = mysql_pconnect($hostname_cov, $username_cov, $password_cov) or trigger_error(mysql_error(),E_USER_ERROR); 
?>