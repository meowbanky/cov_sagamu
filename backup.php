<?php 
//exec("mysqldump -u root -poluwaseyi nasu > my_database_dump.sql");

//exec("C:\Program Files\MySQL\MySQL Server 5.1\bin\mysqldump  --routines -u root -poluwaseyi  hms> hms_oouth%bkupfilename%");

passthru("backup.bat");
?>