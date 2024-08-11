<?php
	//session_start();
	/*if (!defined('DIRECTACC')) {
        header('Status: 200');
        header('Location: ../../index.php');
	}*/

	include_once('class.db.php');
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	function retrieveDescSingleFilter($table, $basevar, $filter1, $val1){
		global $conn;
		
		try{
			$query = $conn->prepare('SELECT ' . $basevar .' FROM ' . $table . ' WHERE ' . $filter1 . ' = ?');
	        $res = $query->execute(array($val1));
	        if ($row = $query->fetch()) {
	            echo($row[''. $basevar .'']);
				
	        }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function &returnDescSingleFilter($table, $basevar, $filter1, $val1){
		global $conn;
		
		try{
			$query = $conn->prepare('SELECT ' . $basevar .' FROM ' . $table . ' WHERE ' . $filter1 . ' = ?');
	        $res = $query->execute(array($val1));
	        if ($row = $query->fetch()) {
	            return $row[''. $basevar .''];
	        }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retrieveCompanyDepartment($table, $basevar, $val1, $filter1){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $basevar . ' FROM ' . $table .' WHERE ' . $filter1 .  ' = ?');
            $res = $query->execute(array($val1));
            if ($row = $query->fetch()) {
                echo($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retrieveDescDualFilter($table, $basevar, $val1, $filter1, $filter2, $val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $basevar . ' FROM ' . $table .' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' = ?');
            $res = $query->execute(array($val1, $val2));
            if ($row = $query->fetch()) {
                echo($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function retrieveDescDualFilterLessThan($table, $basevar, $val1, $filter1, $filter2, $val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $basevar . ' FROM ' . $table .' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' <= ?');
            $res = $query->execute(array($val1, $val2));
            if ($row = $query->fetch()) {
                echo($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	
	function retrieveDescQuadFilter($table, $basevar, $val1, $filter1, $filter2, $val2, $filter3, $val3, $filter4, $val4){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $basevar . ' FROM ' . $table .' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' = ? AND ' . $filter3 . ' = ? AND ' . $filter4 . ' = ?');
            $res = $query->execute(array($val1, $val2, $val3, $val4));
            if ($row = $query->fetch()) {
                echo(number_format($row[''.$basevar.'']));
            } else {
            	echo '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}


	function retrieveDescPentaFilter($table, $basevar, $val1, $filter1, $filter2, $val2, $filter3, $val3, $filter4, $val4, $filter5, $val5){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $basevar . ' FROM ' . $table .' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' = ? AND ' . $filter3 . ' = ? AND ' . $filter4 . ' = ? AND ' . $filter5 . ' = ?');
            $res = $query->execute(array($val1, $val2, $val3, $val4, $val5));
            if ($row = $query->fetch()) {
                echo(number_format($row[''.$basevar.'']));
            } else {
            	echo '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}


	function &returnDescPentaFilter($table, $basevar, $val1, $filter1, $filter2, $val2, $filter3, $val3, $filter4, $val4, $filter5, $val5){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $basevar . ' FROM ' . $table .' WHERE ' . $filter1 .  ' = ? AND ' . $filter2 . ' = ? AND ' . $filter3 . ' = ? AND ' . $filter4 . ' = ? AND ' . $filter5 . ' = ?');
            $res = $query->execute(array($val1, $val2, $val3, $val4, $val5));
            if ($row = $query->fetch()) {
                return $row[''.$basevar.''];
            } else {
            	echo '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	
	function styleLabelColor($labelType){
		global $conn;

		try{
			if ($labelType == 'Earning') {				
				return "success";
			} elseif ($labelType == 'Deduction') {
				return "danger";
			}elseif ($labelType == 'Union Deduction') {
				return "warning";
			}elseif ($labelType == 'Loan') {
				return "info";
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	
	function retrieveSelect($table, $filter1, $filter2, $basevar, $sortvar){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' WHERE ' . $filter2 . ' = ? AND status = ? ORDER BY ' . $sortvar .'');
			$res = $query->execute(array($basevar, 'Active'));
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row['ed_id'] .'">' . $row['edDesc'] . ' - ' . $row['ed_id'] . '</option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function retrieveSelectAll($table, $filter1, $filter2, $basevar, $sortvar){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' WHERE ' . $filter2 . ' <> ? AND status = ? ORDER BY ' . $sortvar .'');
			$res = $query->execute(array($basevar, 'Active'));
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row['ed_id'] .'">' . $row['edDesc'] . ' - ' . $row['ed_id'] . '</option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	function retrieveSelectwithoutFilter($table, $filter1, $filter2, $basevar, $sortvar){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' WHERE ' . $filter2 . ' = ? AND status = ? ORDER BY ' . $sortvar .'');
			$res = $query->execute(array($basevar, 'Active'));
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row['ed_id'] .'">' . $row['edDesc'] . ' - ' . $row['ed_id'] . '</option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function retrieveSelectwithoutWhere($table, $filter1,  $sortvar,$value1,$value2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' ORDER BY ' . $sortvar .'');
			$res = $query->execute(array());
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row[$value1] .'">' . $row[$value2] . ' - ' . $row[$value1] . '</option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retrievePayrollSubTotal($basevar, $table, $filter1, $filter2, $filter3, $filter4, $var1, $var2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' .$basevar. ' FROM '.$table.' WHERE '.$filter1.' = ? AND '.$filter2.' = ? AND '.$filter3.' = ? AND '.$filter4.' = ?');
			$ans = $query->execute(array($var1, $var2, $_SESSION['currentactiveperiod'], '1'));
			
	        if ($row = $query->fetch()) {
                echo number_format($row[''.$basevar.'']);
            }
		}
		catch(PDOException $e){
			echo $e-getMessage();
		}
	}

	function retrieveEmployees($table, $filter1, $filter2, $basevar, $sortvar){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' WHERE ' . $filter2 . ' = ? order by Name');
			$res = $query->execute(array($basevar));
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row['staff_id'] .'">' . $row['NAME'] . ' - ' . $row['staff_id']  . '</option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retrieveLeaveStatus($table, $filter1, $filter2, $basevar){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' WHERE ' . $filter2 . ' = ?');
			$res = $query->execute(array($basevar));
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row['id'] .'">' . $row['statusDescription'] . '</option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retrieveLeaveTypes($table, $filter1, $filter2, $basevar){
		global $conn;

		try{
			$query = $conn->prepare('SELECT ' . $filter1 . ' FROM ' . $table . ' WHERE ' . $filter2 . ' = ?');
			$res = $query->execute(array($basevar));
			$out = $query->fetchAll(PDO::FETCH_ASSOC);
			
			while ($row = array_shift($out)) {
				echo('<option value="' . $row['id'] .'">' . $row['Leave_type'] . ' Leave </option>');
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function returnNumberOfEmployees(){
        global $conn;

        try{
        	$query = $conn->prepare('SELECT empNumber FROM employees WHERE companyId = ? AND active =? ORDER BY id ASC');
	        $query->execute(array($_SESSION['companyid'], '1'));
	        $ftres = $query->fetchAll(PDO::FETCH_COLUMN);
	        $count = $query->rowCount();
	        echo $count;
        }
        catch(PDOException $e){
        	echo $e->getMessage();
        }
	}

function retrievePayroll($val1,$val2,$val3,$val4){
		global $conn;

		try{
			$query = $conn->prepare('SELECT tbl_master.period,
															CASE type 
															WHEN 1 THEN sum(tbl_master.allow)
															WHEN 2 THEN sum(tbl_master.deduc)
															END as amount
															FROM
															tbl_master
															INNER JOIN employee ON employee.staff_id = tbl_master.staff_id
															right JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = tbl_master.allow_id
															INNER JOIN tbl_dept ON tbl_dept.dept_id = employee.DEPTCD
															INNER JOIN payperiods ON payperiods.periodId = tbl_master.period
															WHERE tbl_master.period BETWEEN ? and ? and employee.staff_id = ? and allow_id = ?
															GROUP BY employee.staff_id');
            $res = $query->execute(array($val1,$val2, $val3, $val4));
            if ($row = $query->fetch()) {
                return $row['amount'];
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

function retrievegross($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT tbl_master.period,
															CASE type 
															WHEN 1 THEN sum(tbl_master.allow)
															WHEN 2 THEN sum(tbl_master.deduc)
															END as amount
															FROM
															tbl_master
															INNER JOIN employee ON employee.staff_id = tbl_master.staff_id
															right JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = tbl_master.allow_id
															INNER JOIN tbl_dept ON tbl_dept.dept_id = employee.DEPTCD
															INNER JOIN payperiods ON payperiods.periodId = tbl_master.period
															WHERE tbl_master.period = ? and employee.staff_id = ? 
															GROUP BY employee.staff_id');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return $row['amount'];
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function exportTax_new($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
																allow_deduc.staff_id,
																allow_deduc.allow_id,
																allow_deduc.`value` as amount,
																allow_deduc.transcode,
																tbl_earning_deduction.edDesc
																FROM
																allow_deduc
																INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id= allow_deduc.allow_id
																WHERE staff_id = ? and allow_id = ? ');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return $row['amount'];
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	
	
		function calTaxableIncome($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT tbl_master.period,
															CASE type 
															WHEN 1 THEN sum(tbl_master.allow)
															WHEN 2 THEN sum(tbl_master.deduc)
															END as amount,employee.GRADE
															FROM
															tbl_master
															INNER JOIN employee ON employee.staff_id = tbl_master.staff_id
															right JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = tbl_master.allow_id
															INNER JOIN tbl_dept ON tbl_dept.dept_id = employee.DEPTCD
															INNER JOIN payperiods ON payperiods.periodId = tbl_master.period
															WHERE tbl_master.period = ? and employee.staff_id = ?
															GROUP BY employee.staff_id');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return $row['amount'];
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
function retrievePayrollRunStatus($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT master_staff.staff_id, master_staff.period FROM master_staff WHERE staff_id = ? and period = ?');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return 1;
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function lastPayCheck($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
																tbl_lastpay.lastpay_id,
																tbl_lastpay.staff_id,
																tbl_lastpay.period
																FROM
																tbl_lastpay
																WHERE staff_id = ? and period = ?');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return '1';
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function cash_chequeCheck($val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
																*
																FROM
																tbl_cash_cheque
																WHERE staff_id = ?');
            $res = $query->execute(array($val2));
            if ($row = $query->fetch()) {
                return '1';
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function devlevyCheck($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
																tbl_devlevy.dev_id,
																tbl_devlevy.staff_id,
																tbl_devlevy.period_year
																FROM
																tbl_devlevy
																WHERE staff_id = ? and period_year = ?');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return '1';
            } else {
            	return '0';
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	function retrieveLoanStatus($val1,$val2){
		global $conn;

		try{
			$query = $conn->prepare('SELECT sum(tbl_debt.principal)+ sum(tbl_debt.interest) as loan FROM tbl_debt WHERE staff_id = ? and allow_id = ? GROUP BY staff_id, allow_id');
            $res = $query->execute(array($val1,$val2));
            if ($row = $query->fetch()) {
                return $row['loan'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function retrieveLoanBalanceStatus($val1,$val2,$val3){
		global $conn;

		try{
			$query = $conn->prepare('SELECT sum(tbl_repayment.`value`) as repayment FROM tbl_repayment WHERE staff_id = ? and allow_id = ? and period <= ? GROUP BY staff_id,allow_id');
            $res = $query->execute(array($val1,$val2,$val3));
            if ($row = $query->fetch()) {
                return $row['repayment'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retriveTotalPaid($receiptNo){
		global $conn;

		try{
			$query = $conn->prepare('SELECT Sum(ifnull(payment_bill.total_paid,0)) as total_paid FROM payment_bill WHERE session_id = ? GROUP BY ?');
            $res = $query->execute(array($receiptNo,$receiptNo));
            if ($row = $query->fetch()) {
                return $row['total_paid'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retriveCompanyName($companyID){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
			company.company_name
			FROM
			company
			WHERE company_id = ?');
            $res = $query->execute(array($companyID));
            if ($row = $query->fetch()) {
                return $row['company_name'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retriveCompanyAddress($companyID){
		global $conn;

		try{
			$query = $conn->prepare('SELECT company.address FROM company WHERE company_id = ?');
            $res = $query->execute(array($companyID));
            if ($row = $query->fetch()) {
                return $row['address'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retriveCompanyemail($companyID){
		global $conn;

		try{
			$query = $conn->prepare('SELECT company.email FROM company WHERE company_id = ?');
            $res = $query->execute(array($companyID));
            if ($row = $query->fetch()) {
                return $row['email'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retriveCompanyPhone($companyID){
			global $conn;

			try{
				$query = $conn->prepare('SELECT company.tel_no FROM company WHERE company_id = ?');
				$res = $query->execute(array($companyID));
				if ($row = $query->fetch()) {
					return $row['tel_no'];
				} else {
					return 0;
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
	}

	function retriveCompanylogo($companyID){
				global $conn;

				try{
					$query = $conn->prepare('SELECT company.company_logo FROM company WHERE company_id = ?');
					$res = $query->execute(array($companyID));
					if ($row = $query->fetch()) {
						return $row['company_logo'];
					} else {
						return 0;
					}
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
	}

	function retriveAccount($year,$company,$branch,$account){
				global $conn;

				try{
					$query = $conn->prepare('SELECT
					sum(ifnull(`master`.db,0))-sum(ifnull(`master`.cr,0)) as balance,
					account.account,
					payment_header.purchase_date
					FROM
					`master`
					INNER JOIN account ON account.acct_id = `master`.account
					INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
					WHERE DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.company_id = ? and `master`.branch_id = ? and (accountType = ?)
					');
					$res = $query->execute(array($year,$company,$branch,$account ));
					if ($row = $query->fetch()) {
						return $row['balance'];
					} else {
						return 0;
					}
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
	}

	function retriveSundryAccount($year,$company,$branch,$account){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
			sum(ifnull(`master`.cr,0))-sum(ifnull(`master`.db,0)) as balance,
			account.account,
			payment_header.purchase_date
			FROM
			`master`
			INNER JOIN account ON account.acct_id = `master`.account
			INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
			WHERE DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.company_id = ? and `master`.branch_id = ? and (accountType = ?)
			');
			$res = $query->execute(array($year,$company,$branch,$account ));
			if ($row = $query->fetch()) {
				return $row['balance'];
			} else {
				return 0;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
}

	function retriveCurrentRevenue($year,$company,$branch){
		global $conn;

		try{
			$query = $conn->prepare('SELECT
			sum(ifnull(`master`.cr,0)) -
			Sum(ifnull(`master`.db,0)) AS balance,
			account.account
			FROM
			`master`
			LEFT JOIN account ON account.acct_id = `master`.account
			INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
			  WHERE groupHead = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
			  ');
			$res = $query->execute(array(4,$year,$company,$branch ));
			if ($row = $query->fetch()) {
				return $row['balance'];
			} else {
				return 0;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
}

function retriveCurrentExpenses($year,$company,$branch){
	global $conn;

	try{
		$query = $conn->prepare('SELECT
		sum(ifnull(`master`.db,0)) -
		Sum(ifnull(`master`.cr,0)) AS balance,
		account.account
		FROM
		`master`
		LEFT JOIN account ON account.acct_id = `master`.account
		INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
		  WHERE groupHead = ? AND `master`.account <> "180010" AND  DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
		  ');
		$res = $query->execute(array(5,$year,$company,$branch ));
		if ($row = $query->fetch()) {
			return $row['balance'];
		} else {
			return 0;
		}
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
}

function retriveAccumRevenue($year,$company,$branch){
	global $conn;

	try{
		$query = $conn->prepare('SELECT
			sum(ifnull(`master`.cr,0)) -
			Sum(ifnull(`master`.db,0)) AS balance,
			account.account
			FROM
			`master`
			LEFT JOIN account ON account.acct_id = `master`.account
			INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
			  WHERE groupHead = ? and DATE_FORMAT(purchase_date,"%Y") < ? AND `master`.company_id = ? and `master`.branch_id = ?
			  ');
		$res = $query->execute(array(4,$year,$company,$branch ));
		if ($row = $query->fetch()) {
			return $row['balance'];
		} else {
			return 0;
		}
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
}
function retriveAccountDetails($acctID,$company,$branch){
	global $conn;

	try{
		$query = $conn->prepare('SELECT 
		account.account
		FROM
		account
		WHERE acct_id = ? and company_id = ? and branch_id = ?');
		$res = $query->execute(array($acctID,$company,$branch ));
		if ($row = $query->fetch()) {
			return $row['account'];
		} else {
			return '';
		}
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
}
function retrieveAbsolute($number){
	if($number < 0){
		$number = abs($number); 
		return '('.number_format($number).')';
		}else{ 
			 return number_format($number); 
			 }

}
function retriveAccumExpenses($year,$company,$branch){
global $conn;

try{
	$query = $conn->prepare('SELECT
		sum(ifnull(`master`.db,0)) -
		Sum(ifnull(`master`.cr,0)) AS balance,
		account.account
		FROM
		`master`
		LEFT JOIN account ON account.acct_id = `master`.account
		INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
		  WHERE groupHead = ? AND `master`.account <> "180010" AND  DATE_FORMAT(purchase_date,"%Y") < ? AND `master`.company_id = ? and `master`.branch_id = ?
		  ');
	$res = $query->execute(array(5,$year,$company,$branch ));
	if ($row = $query->fetch()) {
		return $row['balance'];
	} else {
		return 0;
	}
}
catch(PDOException $e){
	echo $e->getMessage();
}
}

function retriveCostOfSales($year,$company,$branch){
	global $conn;
	
	try{
		$query = $conn->prepare('SELECT
		sum(ifnull(`master`.db,0)) -
		Sum(ifnull(`master`.cr,0)) AS balance,
		account.account
		FROM
		`master`
		LEFT JOIN account ON account.acct_id = `master`.account
		INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
		 WHERE `master`.account = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
		 
		  ');
		$res = $query->execute(array(180010,$year,$company,$branch ));
		if ($row = $query->fetch()) {
			return $row['balance'];
		} else {
			return 0;
		}
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
	}

	

	function retriveAccumCostOfSales($year,$company,$branch){
		global $conn;
		
		try{
			$query = $conn->prepare('SELECT
			sum(ifnull(`master`.db,0)) -
			Sum(ifnull(`master`.cr,0)) AS balance,
			account.account
			FROM
			`master`
			LEFT JOIN account ON account.acct_id = `master`.account
			INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
			  WHERE`master`.account = ? and DATE_FORMAT(purchase_date,"%Y") < ? AND `master`.company_id = ? and `master`.branch_id = ?
			  ');
			$res = $query->execute(array(180010,$year,$company,$branch ));
			if ($row = $query->fetch()) {
				return $row['balance'];
			} else {
				return 0;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		}
		function retriveAccountType($type,$year,$company,$branch){
				global $conn;
				
				try{
					$query = $conn->prepare('SELECT
					sum(ifnull(`master`.db,0)) -
					Sum(ifnull(`master`.cr,0)) AS balance,
					account.account
					FROM
					`master`
					LEFT JOIN account ON account.acct_id = `master`.account
					INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
					WHERE account.accountType = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
					');
					$res = $query->execute(array($type,$year,$company,$branch ));
					if ($row = $query->fetch()) {
						return $row['balance'];
					} else {
						return 0;
					}
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
				}

				function retriveAccountChart($acct_id,$company,$branch){
					global $conn;
					
					try{
						$query = $conn->prepare('SELECT
						sum(ifnull(`master`.db,0)) -
						Sum(ifnull(`master`.cr,0)) AS balance,
						account.account
						FROM
						`master`
						LEFT JOIN account ON account.acct_id = `master`.account
						INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
						WHERE account.acct_id = ? AND `master`.company_id = ? and `master`.branch_id = ?
						');
						$res = $query->execute(array($acct_id,$company,$branch ));
						if ($row = $query->fetch()) {
							return $row['balance'];
						} else {
							return 0;
						}
					}
					catch(PDOException $e){
						echo $e->getMessage();
					}
					}
						function retriveAccountBalanceRB($acct_id,$year,$company,$branch){
											global $conn;
											
											try{
												$query = $conn->prepare('SELECT
												sum(ifnull(`master`.db,0)) -
												Sum(ifnull(`master`.cr,0)) AS balance,
												account.account
												FROM
												`master`
												LEFT JOIN account ON account.acct_id = `master`.account
												INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
												WHERE account.acct_id = ? and DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.company_id = ? and `master`.branch_id = ?
												');
												$res = $query->execute(array($acct_id,$year,$company,$branch ));
												if ($row = $query->fetch()) {
													return $row['balance'];
												} else {
													return 0;
												}
											}
											catch(PDOException $e){
												echo $e->getMessage();
											}
											}

				
											function retriveAccountBalanceY($acct_id,$year,$company,$branch){
												global $conn;
												
												try{
													$query = $conn->prepare('SELECT
													sum(ifnull(`master`.db,0)) -
													Sum(ifnull(`master`.cr,0)) AS balance,
													account.account
													FROM
													`master`
													LEFT JOIN account ON account.acct_id = `master`.account
													INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
													WHERE account.acct_id = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
													');
													$res = $query->execute(array($acct_id,$year,$company,$branch ));
													if ($row = $query->fetch()) {
														return $row['balance'];
													} else {
														return 0;
													}
												}
												catch(PDOException $e){
													echo $e->getMessage();
												}
												}

				function retriveAccountCashFlow($acct_id,$year,$company,$branch){
					global $conn;
					
					try{
						$query = $conn->prepare('SELECT
						sum(ifnull(`master`.db,0)) -
						Sum(ifnull(`master`.cr,0)) AS balance,
						account.account
						FROM
						`master`
						LEFT JOIN account ON account.acct_id = `master`.account
						INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
						WHERE account.acct_id = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
						');
						$res = $query->execute(array($acct_id,$year,$company,$branch ));
						if ($row = $query->fetch()) {
							return $row['balance'];
						} else {
							return 0;
						}
					}
					catch(PDOException $e){
						echo $e->getMessage();
					}
					}

	function retriveTrialBalance($accountType,$account,$groupHead,$year,$company,$branch){
		global $conn;
		
		try{
			if(($groupHead == 1) && ($accountType !=13)) {
			$query = 'SELECT
			sum(ifnull(`master`.db,0)) -
			Sum(ifnull(`master`.cr,0)) AS balance,
			account.account
			FROM
			`master`
			LEFT JOIN account ON account.acct_id = `master`.account
			INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
			WHERE DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.account = ? AND `master`.company_id = ? and `master`.branch_id = ?';
			} else if($groupHead == 2) {
			$query = 'SELECT
			sum(ifnull(`master`.cr,0)) -
			Sum(ifnull(`master`.db,0)) AS balance,
			account.account
			FROM
			`master`
			LEFT JOIN account ON account.acct_id = `master`.account
			INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
			WHERE DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.account = ? AND `master`.company_id = ? and `master`.branch_id = ?';
			
			}else if(($groupHead == 1) && ($accountType == 13)) {
				$query = 'SELECT
				sum(ifnull(`master`.cr,0)) -
				Sum(ifnull(`master`.db,0)) AS balance,
				account.account
				FROM
				`master`
				LEFT JOIN account ON account.acct_id = `master`.account
				INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
				WHERE DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.account = ? AND `master`.company_id = ? and `master`.branch_id = ?';
			}
			else if($groupHead == 3) {
				$query = 'SELECT
				sum(ifnull(`master`.cr,0)) -
				Sum(ifnull(`master`.db,0)) AS balance,
				account.account
				FROM
				`master`
				LEFT JOIN account ON account.acct_id = `master`.account
				INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
				WHERE DATE_FORMAT(purchase_date,"%Y") <= ? AND `master`.account = ? AND `master`.company_id = ? and `master`.branch_id = ?';
				
			}else if($groupHead == 4) {
				$query = 'SELECT
				sum(ifnull(`master`.cr,0)) -
				Sum(ifnull(`master`.db,0)) AS balance,
				account.account
				FROM
				`master`
				LEFT JOIN account ON account.acct_id = `master`.account
				INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
				WHERE DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.account = ? AND `master`.company_id = ? and `master`.branch_id = ?';
				
				}else if($groupHead == 5) {
					$query = 'SELECT
					sum(ifnull(`master`.db,0)) -
					Sum(ifnull(`master`.cr,0)) AS balance,
					account.account
					FROM
					`master`
					LEFT JOIN account ON account.acct_id = `master`.account
					INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
					WHERE DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.account = ? AND `master`.company_id = ? and `master`.branch_id = ?';
					
				}

			$query = $conn->prepare($query);
			$res = $query->execute(array($year,$account,$company,$branch ));
			if ($row = $query->fetch()) {
				return $row['balance'];
			} else {
				return 0;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
		}


	function retriveVendorDetails($session_id){
					global $conn;

					try{
						$query = $conn->prepare('SELECT ifnull(payment_header.vendor_details,"") as vendor_details, ifnull(payment_header.vendor_name,"") as name FROM payment_header
						WHERE session_id = ?');
						$res = $query->execute(array($session_id));
						if ($row = $query->fetch()) {
							return $row['name'].'<br>'.$row['vendor_details'];
						} else {
							return '';
						}
					}
					catch(PDOException $e){
						echo $e->getMessage();
					}
		}

				function retrivePurchaseDate($session_id){
			global $conn;

			try{
				$query = $conn->prepare('SELECT payment_header.purchase_date FROM payment_header where session_id = ?');
				$res = $query->execute(array($session_id));
				if ($row = $query->fetch()) {
					return $row['purchase_date'];
				} else {
					return '';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function retriveddueDate($session_id){
			global $conn;

			try{
				$query = $conn->prepare('SELECT payment_header.due_date FROM payment_header where session_id = ?');
				$res = $query->execute(array($session_id));
				if ($row = $query->fetch()) {
					return $row['due_date'];
				} else {
					return '';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function retriveRef($session_id){
			global $conn;

			try{
				$query = $conn->prepare('SELECT payment_header.payment_ref FROM payment_header where session_id = ?');
				$res = $query->execute(array($session_id));
				if ($row = $query->fetch()) {
					return $row['payment_ref'];
				} else {
					return '';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function yesterday(){

			return date('Y-m-d', mktime(0,0,0,date("m"),date("d")-1,date("Y")));
		}

		function start_of_this_month(){
			return date('Y-m-d', mktime(0,0,0,date("m"),1,date("Y")));

		}

		function start_of_this_year(){

			return date('Y-m-d', mktime(0,0,0,1,1,date("Y")));
		}

		function end_of_this_year(){

			return date('Y-m-d', mktime(0,0,0,12,31,date("Y")));
		}
		
		function start_of_time(){
			 return date('Y-m-d', 0);
		}

		function end_of_this_month(){
			return date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
		}

		function start_of_last_3_month(){
			return date('Y-m-d', mktime(0,0,0,date("m")-3,1,date("Y")));

		}

		function end_of_last_3_month(){
			return date('Y-m-d',strtotime('-1 second',strtotime('+3 month',strtotime((date('m') - 3).'/01/'.date('Y').' 00:00:00'))));
		}
			 

		

		function retriveBranchName($companyID,$branchID){
			global $conn;

			try{
				$query = $conn->prepare('SELECT branch.branchName FROM 
				company INNER JOIN branch ON branch.company_id = company.company_id 
				WHERE branch.company_id = ? AND branch.branch_id = ? ');
				$res = $query->execute(array($companyID,$branchID));
				if ($row = $query->fetch()) {
					return $row['branchName'];
				} else {
					return '';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}
		function naira(){
			return '&#8358;';
		}

		function retrivePayee($payeeID){
			global $conn;

			try{
				$query = $conn->prepare('SELECT concat(vendor.`name`,' - ', vendor.phone) as details FROM vendor where vendor_id = ? ');
				$res = $query->execute(array($payeeID));
				if ($row = $query->fetch()) {
					return $row['details'] ;
				} else {
					return '';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function retriveOpeningBalance($acctID,$companyID,$branchID,$datefrom){
			global $conn;

			try{
				$query = $conn->prepare('SELECT
				(sum(ifnull(`master`.db,0))-
				sum(ifnull(`master`.cr,0))) as balance,
				account.account,
				`master`.narration,
				payment_header.payment_ref,
				account.acct_id,
				payment_header.purchase_date,
				`master`.account
				FROM
				`master`
				INNER JOIN account ON account.acct_id = `master`.account
				LEFT JOIN payment_header ON payment_header.session_id = `master`.session_id
				WHERE `master`.company_id = ? and `master`.branch_id = ? and acct_id = ? and purchase_date < ?
				GROUP BY acct_id');
				$res = $query->execute(array($companyID,$branchID,$acctID,$datefrom));
				if ($row = $query->fetch()) {
					return $row['balance'];
				} else {
					return '0';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function retriveHomeIncome($goupHead,$year,$companyID,$branchID){
			global $conn;

			try{
				$query = $conn->prepare('SELECT
				sum(ifnull(`master`.cr,0)) -
				Sum(ifnull(`master`.db,0)) AS balance,
				account.account
				FROM
				`master`
				LEFT JOIN account ON account.acct_id = `master`.account
				INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
				  WHERE groupHead = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ?
				  ');

				$res = $query->execute(array($goupHead,$year,$companyID,$branchID));
				if ($row = $query->fetch()) {
					return $row['balance'];
				} else {
					return '0';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function retriveHomeExpenses($year,$companyID,$branchID){
			global $conn;

			try{
				$query = $conn->prepare('SELECT
				sum(ifnull(`master`.db,0)) -
				Sum(ifnull(`master`.cr,0)) AS balance,
				account.account
				FROM
				`master`
				LEFT JOIN account ON account.acct_id = `master`.account
				INNER JOIN payment_header ON payment_header.session_id = `master`.session_id
				  WHERE groupHead = ? and DATE_FORMAT(purchase_date,"%Y") = ? AND `master`.company_id = ? and `master`.branch_id = ? and account.acct_id <> ?
				 ');

				$res = $query->execute(array(5,$year,$companyID,$branchID,180010 ));
				if ($row = $query->fetch()) {
					return $row['balance'];
				} else {
					return '0';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}

		function retrivePayable($companyID,$branchID,$payableId){
			global $conn;

			try{
				$query = $conn->prepare('SELECT
				sum(ifnull(`master`.db,0)) -
				Sum(ifnull(`master`.cr,0)) AS balance,
				account.account
				FROM
				`master`
				LEFT JOIN account ON account.acct_id = `master`.account
				WHERE `master`.company_id = ? and `master`.branch_id = ? AND account.accountType = ? ');

				$res = $query->execute(array($companyID,$branchID,$payableId));
				if ($row = $query->fetch()) {
					return $row['balance'];
				} else {
					return '0';
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}


			function retrivePaymentMethod($session_id){
				global $conn;
	
				try{
					$query = $conn->prepare('SELECT payment_header.payment_method FROM payment_header where session_id = ?');
					$res = $query->execute(array($session_id));
					if ($row = $query->fetch()) {
						return $row['payment_method'];
					} else {
						return '';
					}
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			}

			function retriveBankName($bank_id){
				global $conn;
	
				try{
					$query = $conn->prepare('SELECT account.account FROM account WHERE acct_id = ?');
					$res = $query->execute(array($bank_id));
					if ($row = $query->fetch()) {
						return $row['account'];
					} else {
						return '';
					}
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			}
	
	
	

	function retriveTotalBill($receiptNo){
		global $conn;

		try{
			$query = $conn->prepare('SELECT Sum(ifnull(payment_bill.total_bill,0)) as total_bill FROM payment_bill WHERE session_id = ? GROUP BY ?');
            $res = $query->execute(array($receiptNo,$receiptNo));
            if ($row = $query->fetch()) {
                return $row['total_bill'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	function retriveTotalTax($receiptNo){
		global $conn;

		try{
			$query = $conn->prepare('SELECT Sum(payment_bill.total_bill) as total_bill FROM payment_bill WHERE session_id = ? GROUP BY ?');
            $res = $query->execute(array($receiptNo,$receiptNo));
            if ($row = $query->fetch()) {
                return $row['total_bill'];
            } else {
            	return 0;
            }
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	
	function auditTrailInsert($staff_id, $allow_id, $value, $period){
		global $conn;
		
						
				$query = $conn->prepare('SELECT * FROM tbl_audit WHERE staff_id = ?  AND allow_id = ? AND period = ?');
				$res = $query->execute(array($staff_id, $allow_id,$period));
				$existtrans = $query->fetch();

				if ($existtrans) {
					
					$query = 'UPDATE tbl_audit SET value = ? WHERE staff_id = ?  AND allow_id = ? and period = ?';
					$conn->prepare($query)->execute(array($value,$staff_id ,$allow_id,$period));
				}else{
							$query = 'INSERT INTO tbl_audit (staff_id, allow_id, value, period) VALUES (?,?,?,?)';
						$conn->prepare($query)->execute(array($staff_id, $allow_id, $value, $period));

				}	
		
	}
	
	
	
	function getAmount($curremployee,$newearningcodeAll,$grade_level,$step,$callType){
		global $conn;
	$connect = mysqli_connect("localhost", "emmaggic_root", "Oluwaseyi", "colerine");  
 $output = ''; 
 
 $sql_edType = "SELECT tbl_earning_deduction_type.edType FROM tbl_earning_deduction 
 INNER JOIN tbl_earning_deduction_type ON tbl_earning_deduction_type.edType = tbl_earning_deduction.edType 
 WHERE ed_id = '{$newearningcodeAll}'";  
       
 $edType = mysqli_query($connect,$sql_edType) or die(mysql_error());
 $row_edType = mysqli_fetch_assoc($edType);
 $totalRows_edType = mysqli_num_rows($edType);

	if($totalRows_edType > 0) {
  	if($row_edType['edType']== 1)	{
			 if(isset($grade_level))
			 { 
			  
			  	
			      $sql_source = "SELECT tbl_earning_deduction.source, tbl_earning_deduction.ed_id FROM tbl_earning_deduction WHERE ed_id = '". $newearningcodeAll ."'";  
			  	     
			      
			      $result_source = mysqli_query($connect, $sql_source);
			      $row_source = mysqli_fetch_assoc($result_source);
				  	 
			 
			 	if($row_source['source'] == 1)
			  {  
			   	if ( $newearningcodeAll == 21){
			           $sql = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = '".$grade_level."' AND allowancetable.step = '".$step."' AND allowcode = ". $newearningcodeAll." AND category = '". $callType."'";  
			       }else {
			   				$sql = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = '".$grade_level."' AND allowancetable.step = '".$step."' AND allowcode = ". $newearningcodeAll."";  
			       }
			   		
			      $result = mysqli_query($connect, $sql);
			      $row = mysqli_fetch_assoc($result);
				  	$output = number_format($row['value']);
				  	
				  	if($output == 0){
				  		return "0";
				  		
				  }else{
				  		return $output;
				  	}
				  	 
			    }else {
			     	return "0";
			     	
			    }

			 }	  
 
 
 	     }elseif($row_edType['edType']== 2){
 	     	
 	     	if($newearningcodeAll == 50){
 	     		if(isset($grade_level))
 						{ 
  			$sql_consolidated = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $grade_level ."' and step = '". $step ."'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      
		      $sql_pensionRate = "SELECT (pension.PENSON/100) as rate FROM pension WHERE grade = '". $grade_level ."' and step = '". $step ."'";
      		$result_pensionRate = mysqli_query($connect, $sql_pensionRate);
		      $row_pensionRate = mysqli_fetch_assoc($result_pensionRate);
		      $total_pensionRate = mysqli_num_rows($result_pensionRate);
		      
		      $output = ceil($row_consolidated['value']*$row_pensionRate['rate']);
		      return $output;

			
 	
 						}
 					
 	     	}elseif($newearningcodeAll == 41){
 	     		if(isset($grade_level))
 				{ 
  			  $sql_consolidated = "SELECT allow_deduc.staff_id, allow_deduc.allow_id,sum(allow_deduc.`value`) as tax
															FROM allow_deduc INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = allow_deduc.allow_id
															INNER JOIN employee ON employee.staff_id = allow_deduc.staff_id
															WHERE taxable = 1 AND transcode = 1 and allow_deduc.staff_id = '" .$curremployee."' and DEPTCD = '40'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      
		      
		      
		      $output = number_format($row_consolidated['tax']*0.05,0,'','');
		      if($output > 0){
		      return $output;
				}else{
					return $output = '0';
				}
			
 	
 					}
 					
 	     	}else {
 	     	 	return $output = '0';
 	     	}
 	     	
 	     }elseif($row_edType['edType']== 3){
 	     	if(isset($grade_level))
 { 
  

			$sql_numberOfRows = "SELECT deductiontable.ded_id, deductiontable.allowcode, deductiontable.grade, deductiontable.step, deductiontable.`value`, deductiontable.category, deductiontable.ratetype, deductiontable.percentage FROM deductiontable WHERE allowcode = '". $newearningcodeAll ."'";  
			$result_numberOfRows = mysqli_query($connect, $sql_numberOfRows);
      $row_numberOfRows = mysqli_fetch_assoc($result_numberOfRows);
      $total_rows = mysqli_num_rows($result_numberOfRows);
          
      if($total_rows == 1){
      	if($row_numberOfRows['ratetype'] == 1){
      		$output = $row_numberOfRows['value'];
      		return $output;
      	}else{
      		$sql_consolidated = "SELECT allowancetable.allow_id, allowancetable.allowcode, allowancetable.grade, allowancetable.step, allowancetable.`value`, allowancetable.category, allowancetable.ratetype, allowancetable.percentage FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $grade_level ."' and step = '". $step ."'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      $output = ($row_numberOfRows['percentage']*$row_consolidated['value'])/100;
		      return $output;
		      
      	}
      	
      }else if($total_rows > 1) {
       	$sql_mulitple = "SELECT deductiontable.ded_id, deductiontable.allowcode, deductiontable.grade, deductiontable.step, deductiontable.`value`, deductiontable.category, deductiontable.ratetype, deductiontable.percentage FROM deductiontable WHERE allowcode = '". $newearningcodeAll ."' and grade = '". $grade_level ."'"; 
				$result_mulitple = mysqli_query($connect, $sql_mulitple);
	      		$row_mulitple = mysqli_fetch_assoc($result_mulitple);
	     		$total_mulitple = mysqli_num_rows($result_mulitple);
	      
	      if($row_numberOfRows['ratetype'] == 1){
      		$output = $row_mulitple['value'];
      		
      	}else{
      		$sql_consolidated = "SELECT allowancetable.allow_id, allowancetable.allowcode, allowancetable.grade, allowancetable.step, allowancetable.`value`, allowancetable.category, allowancetable.ratetype, allowancetable.percentage FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $grade_level ."' and step = '". $step ."'";
      		$result_consolidated = mysqli_query($connect, $sql_consolidated);
		      $row_consolidated = mysqli_fetch_assoc($result_consolidated);
		      $total_rowsConsolidated = mysqli_num_rows($result_consolidated);
		      $output = ceil(($row_mulitple['percentage']*$row_consolidated['value'])/100);
		      return $output;
	            	}
 			} else if($total_rows == 0){
 				
 				return '0';
 			}	
 	
 }
 	     	
 	     }
 
  }
}
	
	
	function deletecurrentstaffPayslip($staff_id,$period){
					
				global $conn;
					
				 $payrollquery2 = $conn->prepare('SELECT master_staff.staff_id FROM master_staff WHERE staff_id = ? and period = ?');
         $payrollquery2->execute(array($staff_id,$period));
         //$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
        
     if ($row3 = $payrollquery2->fetch()){
       try{
       	
       	$query = 'DELETE FROM tbl_devlevy where period = ? and staff_id = ?';
				$conn->prepare($query)->execute(array($period,$staff_id));

				$query = 'DELETE FROM tbl_master where period = ? and staff_id = ?';
				$conn->prepare($query)->execute(array($period,$staff_id));
				
				$query = 'DELETE FROM master_staff where period = ? and staff_id = ?';
				$conn->prepare($query)->execute(array($period,$staff_id));
				
				
				$query = 'DELETE FROM tbl_repayment where period = ? and staff_id = ?';
				$conn->prepare($query)->execute(array($period,$staff_id));
				
				 $payrollquery2 = $conn->prepare('SELECT completedloan.id, completedloan.type,completedloan.staff_id, completedloan.allow_id, completedloan.period, completedloan.`value` FROM completedloan WHERE period = ? and staff_id = ?');
         $payrollquery2->execute(array($period,$staff_id));
         $deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         foreach ($deduc as $row2 => $link2) 
         {
          $query = 'INSERT INTO allow_deduc (staff_id,allow_id,`value`,transcode) VALUES (?,?,?,?)';
					$conn->prepare($query)->execute(array($link2['staff_id'],$link2['allow_id'],$link2['value'],$link2['type']));
          
         }
         $query = 'DELETE FROM completedloan where period = ? AND staff_id = ?';
				 $conn->prepare($query)->execute(array($period,$staff_id));
				
			
				
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			}
			}
?>