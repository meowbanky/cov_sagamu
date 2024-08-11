<?php
	include_once('Connections/cov.php'); 

	
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function retrieveDescDualFilter($table, $basevar, $filter1,$val1,  $filter2, $val2){
		global $conn;

		try{
			$query = $conn->prepare("SELECT SUM( " . $basevar . ") as  {$basevar} FROM " . $table ." WHERE " . $filter1 .  " = ? AND " . $filter2 . " = ?");
            $res = $query->execute(array($val1, $val2));
            if ($row = $query->fetch()) {
                return($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function retrieveDescDualFilterequalToLessThan($table, $basevar, $val1, $filter1, $filter2, $val2)
{
	global $conn;

	try {
		$query = $conn->prepare('SELECT sum(' . $basevar . ') as ' . $basevar . ' FROM ' . $table . ' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' <= ?');
		$res = $query->execute(array($val1, $val2));
		if ($row = $query->fetch()) {
			return ($row['' . $basevar . '']);
		}
	} catch (PDOException $e) {
		echo $e->getMessage();
	}
}

function retrieveDescDualFilterequalTo($table, $basevar, $val1, $filter1, $filter2, $val2)
{
	global $conn;

	try {
		$query = $conn->prepare('SELECT sum(' . $basevar . ') as ' . $basevar . ' FROM ' . $table . ' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' = ?');
		$res = $query->execute(array($val1, $val2));
		if ($row = $query->fetch()) {
			return ($row['' . $basevar . '']);
		}
	} catch (PDOException $e) {
		echo $e->getMessage();
	}
}
	
	function retrieveDescDualFilterLessThan($table, $basevar, $filter1,$val1,  $filter2, $val2){
		global $conn;

		try{
			$query = $conn->prepare("SELECT SUM( " . $basevar . ") as  {$basevar} FROM " . $table ." WHERE " . $filter1 .  " = ? AND " . $filter2 . " <= ?");
            $res = $query->execute(array($val1, $val2));
            if ($row = $query->fetch()) {
                return($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}



	function retrieveSingFilterString($table, $basevar, $filter1,$val1){
		global $conn;

		try{
			$query = $conn->prepare("SELECT " . $basevar . " FROM " . $table ." WHERE " . $filter1 .  " = ?");
            $res = $query->execute(array($val1));
            if ($row = $query->fetch()) {
                return ($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

?>