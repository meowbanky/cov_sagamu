<?php
// Call the function with the parameters from the error log as defaults, or usage example
// Note: This script is designed to be called or included. If run directly, be careful.
// Based on the error: backup_tables('localhost', 'emmaggic_root', 'Oluwaseyi', 'emmaggic_cofv')
// We will keep the function definition.

// If standalone execution is needed similar to the error trace:
// backup_tables('localhost', 'root', '', 'cov'); // Example usage

function backup_tables($host, $user, $pass, $name, $tables = '*')
{
    // CONNECT TO THE DATABASE
    $mysqli = new mysqli($host, $user, $pass, $name);
    
    if ($mysqli->connect_errno) {
        die("Connect failed: " . $mysqli->connect_error);
    }
    
    // GET ALL TABLES
    if($tables == '*')
    {
        $tables = array();
        $result = $mysqli->query('SHOW TABLES');
        while($row = $result->fetch_row())
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
    
    $return = "";
    
    // CYCLE THROUGH
    foreach($tables as $table)
    {
        $result = $mysqli->query('SELECT * FROM '.$table);
        $num_fields = $result->field_count;
        
        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = $mysqli->query('SHOW CREATE TABLE '.$table)->fetch_row();
        $return.= "\n\n".$row2[1].";\n\n";
        
        for ($i = 0; $i < $num_fields; $i++) 
        {
            while($row = $result->fetch_row())
            {
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j < $num_fields; $j++) 
                {
                    $row[$j] = isset($row[$j]) ? addslashes($row[$j]) : '';
                    $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j < ($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }
    
    // SAVE THE FILE
    $handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
    fwrite($handle,$return);
    fclose($handle);
}
?>
