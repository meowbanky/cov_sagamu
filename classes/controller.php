<?php
	session_start();
	ini_set('max_execution_time','0');
	//$connect = mysqli_connect("localhost", "emmaggic_root", "Oluwaseyi", "emmaggic_colerine"); 
	include_once('../Connections/colerine.php');
	include_once('functions.php');
	include_once('model.php');
	//include_once('passwordHash.php');
	//$act = strip_tags(addslashes($_GET['act'));
	$act = filter_var($_GET['act'], FILTER_SANITIZE_STRING);
	$source = $_SERVER['HTTP_REFERER'];
	//$hostname = gethostbyname($_SERVER['REMOTE_ADDR');
	//session variables
	$comp = '1';



	switch ($act) {
		case 'login':
			$uname = filter_var((filter_var($_POST['uname'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			$pass = filter_var($_POST['upassword'], FILTER_SANITIZE_STRING);
			//$upass = password_hash(filter_var($_POST['upassword'], FILTER_SANITIZE_STRING), PASSWORD_BCRYPT);

			try{
				$query = $conn->prepare('SELECT * FROM users WHERE emailAddress = ? AND active = ?');
				$fin = $query->execute(array($uname, '1'));

				//unset($_SESSION('email'));
        		//unset($_SESSION('first_name'));
        		//unset($_SESSION('last_name'));

        		if (isset($_SESSION['periodstatuschange'])) {
	    			unset($_SESSION['periodstatuschange']);
	    		}

				if (($row = $query->fetch()) || (password_verify($pass, $row['password']))) {
					
					$_SESSION['logged_in'] = '1';
					$_SESSION['user'] = $row['userId'];
					$_SESSION['email'] = $row['emailAddress'];
            		$_SESSION['first_name'] = $row['firstName'];
            		$_SESSION['last_name'] = $row['lastName'];
            		$_SESSION['companyid'] = $row['companyId'];
            		$_SESSION['emptrack'] = 0;            		
					$_SESSION['empDataTrack'] = 'next';

            			//Get current active period for the organization
	            		$payp = $conn->prepare('SELECT periodId, description, periodYear FROM payperiods WHERE companyId = ? AND active = ? ORDER BY periodId ASC LIMIT 1');
	            		$myperiod = $payp->execute(array($_SESSION['companyid'], 1));
	            		$final = $payp->fetch();
	            		$_SESSION['currentactiveperiod'] = $final['periodId'];
	            		$_SESSION['activeperiodDescription'] = $final['description'] . " " . $final['periodYear'];
	            		//exit($_SESSION['currentactiveperiod');

	            		//If temp period change, reset session
            			if (isset($_SESSION['periodstatuschange'])) {
			    			unset($_SESSION['periodstatuschange']);
			    		}

					$page = "../../dashboard.php";
					$_SESSION['msg'] = $msg = "Welcome " . $_SESSION['first_name'] . " " . $_SESSION['last_name'];
					$_SESSION['alertcolor'] = $type = "success";
					header('Location: ../../dashboard.php');
					//redirect($msg, $type, $page);
				}
				else {
					
					$_SESSION['msg'] = $msg = "Invalid login. Please try again.";
					$_SESSION['alertcolor'] = 'danger';
					header('Location: ' . $source);
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			
		break;

		
		case 'adduser':
			//
			//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
			$user_name = filter_var($_POST['user_name'], FILTER_SANITIZE_STRING);
			$first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
			$middle_name = filter_var($_POST['middle_name'], FILTER_SANITIZE_STRING);
			$surname_name = filter_var($_POST['surname_name'], FILTER_SANITIZE_STRING);
			$User_Address = filter_var($_POST['User_Address'], FILTER_SANITIZE_STRING);
			$branch_id = filter_var($_POST['branch_id'], FILTER_SANITIZE_STRING);
			$user_email = filter_var((filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
			$user_password = filter_var($_POST['user_password'], FILTER_SANITIZE_STRING);
			$recordtime = date('Y-m-d H:i:s');

			
				try{


					$query = $conn->prepare('SELECT * FROM username WHERE username = ? ');
					$res = $query->execute(array($user_name));
					$existtrans = $query->fetch();

					if ($existtrans) {
						//user exists
						//$_SESSION['msg'] = "A user account associated with the supplied Staff ID exists.";
						//$_SESSION['alertcolor'] = "danger";
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "0";

						}else {

					
						//$upass = password($user_password);

						$query = 'INSERT INTO username (username, firstname, middlename, lastname, addres, branch_id,email,password,plainPassword,date_insert,company_id) VALUES (?,?,?,?,?,?,?,password('.$user_password.'),?,?,?)';
						$conn->prepare($query)->execute(array($user_name, $first_name,$middle_name ,$surname_name, $User_Address, $branch_id,$user_email,$user_password,$recordtime,$_SESSION['COMPANY']));

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "1";
					
						}
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;



		case 'newVendor':
			//
			//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
			$customer_name = filter_var($_POST['customer_name'], FILTER_SANITIZE_STRING);
			$customer_town = filter_var($_POST['customer_town'], FILTER_SANITIZE_STRING);
			$customer_tin = filter_var($_POST['customer_tin'], FILTER_SANITIZE_STRING);
			$customer_address = filter_var($_POST['customer_address'], FILTER_SANITIZE_STRING);
			$customer_phone = filter_var($_POST['customer_phone'], FILTER_SANITIZE_STRING);
			$customer_state = filter_var($_POST['customer_state'], FILTER_SANITIZE_STRING);
			$customer_email = filter_var((filter_var($_POST['customer_email'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
			$recordtime = date('Y-m-d H:i:s');

			
				try{


					$query = $conn->prepare('SELECT * FROM vendor WHERE name = ? ');
					$res = $query->execute(array($customer_name));
					$existtrans = $query->fetch();

					if ($existtrans) {
						//user exists
						//$_SESSION['msg'] = "A user account associated with the supplied Staff ID exists.";
						//$_SESSION['alertcolor'] = "danger";
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "0";

						}else {

					
						//$upass = password($user_password);

						$query = 'INSERT INTO vendor (vendor.`name`,
						vendor.email,
						vendor.address,
						vendor.phone,
						vendor.vendor_tin,
						vendor.company_id,
						vendor.state,
						vendor.town,
						vendor.date_insert,
						vendor.inserted_by,vendor.branch_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($customer_name,$customer_email, $customer_address,$customer_phone,
						$customer_tin, $_SESSION['COMPANY'], $customer_state,$customer_town,
						$recordtime,$_SESSION['SESS_MEMBER_ID'],$_SESSION['BRANCH']));

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "1";
					
						}
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'edit_payment':
			if(isset($_POST['session_id'])){
			$session_id =  $_POST['session_id'];
			$recordtime = date('Y-m-d H:i:s');
			
			 $i = 0;

			 $query = 'DELETE FROM master where session_id = ?';
			 $conn->prepare($query)->execute(array($session_id));

			$query = 'DELETE FROM payment_header where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));

			$query = 'DELETE FROM payment_details where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));
			
			$query = 'DELETE FROM payment_bill where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));
			
			$query = 'DELETE FROM inventory where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));


			$query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
			$conn->prepare($query)->execute(array('CDJ',$session_id,$_POST['cash_acct'],$_POST['total_bill'],
			$_POST['vendor_name'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));



			
			foreach ($_POST['price'] as $price){
				
				   
				
				
				if($_POST['price'][$i] > 0 ) {
			
			
			   
									// Account to credit
									$query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array('CDJ',$session_id,$_POST['gl_salesacct'][$i],$_POST['subtotal'][$i],
									$_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));


								
									  if(isset($_POST['product'][$i])){

										$query = 'INSERT INTO inventory (session_id,item_descr,item_id,qty,cost_price,selling_price) VALUES (?,?,?,?,?,?)';
										  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
									  '-'.$_POST['qty'][$i],$_POST['subtotal'][$i], $_POST['price'][$i]));

									  $query = 'UPDATE item SET cost_price = ? where item_id = ?';
									  $conn->prepare($query)->execute(array($_POST['price'][$i],$_POST['product'][$i]));
									}
			
									// Sales Account to debit
									  $query = 'INSERT INTO payment_details (session_id,item_descr,item_id,qty,rate,subtotal,gl_inventory_acct,gl_sales_acct,gl_cost_sales,cost_price) VALUES (?,?,?,?,?,?,?,?,?,?)';
									  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
									  $_POST['qty'][$i],$_POST['price'][$i], $_POST['subtotal'][$i],$_POST['gl_salesacct'][$i],$_POST['income'][$i],$_POST['expenses'][$i],$_POST['price'][$i]));
			
									  								  
			
								   
									$i++;
				}
			}
			
						
						if((!ISSET($_POST['date']))||($_POST['date'] == '')){
							$_POST['date'] = date("Y-m-d");
							$date = $_POST['date'];
						 }

						
			
						$payee_id = filter_var($_POST['payee_id'], FILTER_SANITIZE_STRING);   
						$billing_address = filter_var($_POST['billing_address'], FILTER_SANITIZE_STRING);
						$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
						$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
						$ref_no = filter_var($_POST['ref_no'], FILTER_SANITIZE_STRING);
					   	$invoicemessage = filter_var($_POST['invoicemessage'], FILTER_SANITIZE_STRING);
						$memo = filter_var($_POST['memo'], FILTER_SANITIZE_STRING);
						$total_bill= filter_var($_POST['total_bill'], FILTER_SANITIZE_STRING);
						$received= filter_var($_POST['total_paid'], FILTER_SANITIZE_STRING);
						$cash_acct = filter_var($_POST['cash_acct'], FILTER_SANITIZE_STRING);
						$sub_total = filter_var($_POST['sub_total'], FILTER_SANITIZE_STRING);
						$vendor_name = filter_var($_POST['vendor_name'], FILTER_SANITIZE_STRING);
			
						
							try{
			
			
			
									$query = 'INSERT INTO payment_header (vendor_name,transType_id,session_id,vendor_id,vendor_details,purchase_date,payment_method,
									payment_ref,invoicemessage,memo,branch_id,company_id,recordtime,cash_acct) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array($vendor_name,1,$session_id,$payee_id, $billing_address,$date,
									$payment_method, $ref_no,$invoicemessage,$memo,$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$cash_acct));
			
									$query = 'INSERT INTO payment_bill (session_id,total_bill,total_paid,sub_total) VALUES (?,?,?,?)';
									$conn->prepare($query)->execute(array($session_id,$total_bill,$received,$sub_total));
			
								
								//	echo "1";
								
									
						}
							catch(PDOException $e){
								echo $e->getMessage();
							}

		 
				
						$source = '../payments.php';
						header('Location: ' . $source);
				
			}else{
					$source = '../payments.php';
					header('Location: ' . $source);
				
			}
				break;

			case 'delete_trans':
						
						$session_id = $_GET['session_id'];

						$query = 'DELETE FROM master where session_id = ?';
						$conn->prepare($query)->execute(array($session_id));

						$query = 'DELETE FROM payment_header where session_id = ?';
						$conn->prepare($query)->execute(array($session_id));

						$query = 'DELETE FROM payment_details where session_id = ?';
						$conn->prepare($query)->execute(array($session_id));
						
						$query = 'DELETE FROM payment_bill where session_id = ?';
						$conn->prepare($query)->execute(array($session_id));
						
						$query = 'DELETE FROM inventory where session_id = ?';
						$conn->prepare($query)->execute(array($session_id));

			break;

				

		case 'add_payment':
			//print_r($_POST);
					$session_id =  rand(1000,99999);
					$recordtime = date('Y-m-d H:i:s');
			
			 $i = 0;

			

			$query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
			$conn->prepare($query)->execute(array('CDJ',$session_id,$_POST['cash_acct'],$_POST['total_bill'],
			$_POST['vendor_name'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));



			
			foreach ($_POST['price'] as $price){
				
				   
				
				
				if($_POST['price'][$i] > 0 ) {
			
			
			   
									// Account to credit
									$query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array('CDJ',$session_id,$_POST['gl_salesacct'][$i],$_POST['subtotal'][$i],
									$_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));


								
									  if(isset($_POST['product'][$i])){

										$query = 'INSERT INTO inventory (session_id,item_descr,item_id,qty,cost_price,selling_price) VALUES (?,?,?,?,?,?)';
										  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
									  '-'.$_POST['qty'][$i],$_POST['subtotal'][$i], $_POST['price'][$i]));

									  $query = 'UPDATE item SET cost_price = ? where item_id = ?';
									  $conn->prepare($query)->execute(array($_POST['price'][$i],$_POST['product'][$i]));




									}
			
									// Sales Account to debit
									  $query = 'INSERT INTO payment_details (session_id,item_descr,item_id,qty,rate,subtotal,gl_inventory_acct,gl_sales_acct,gl_cost_sales,cost_price) VALUES (?,?,?,?,?,?,?,?,?,?)';
									  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
									  $_POST['qty'][$i],$_POST['price'][$i], $_POST['subtotal'][$i],$_POST['gl_salesacct'][$i],$_POST['income'][$i],$_POST['expenses'][$i],$_POST['price'][$i]));
			
									  
			
								   
									$i++;
				}
			}
			
						
						if((!ISSET($_POST['date']))||($_POST['date'] == '')){
							$_POST['date'] = date("Y-m-d");
							$date = $_POST['date'];
						 }

						
			
						$payee_id = filter_var($_POST['payee_id'], FILTER_SANITIZE_STRING);   
						$billing_address = filter_var($_POST['billing_address'], FILTER_SANITIZE_STRING);
						$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
						$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
						$ref_no = filter_var($_POST['ref_no'], FILTER_SANITIZE_STRING);
					   	$invoicemessage = filter_var($_POST['invoicemessage'], FILTER_SANITIZE_STRING);
						$memo = filter_var($_POST['memo'], FILTER_SANITIZE_STRING);
						$total_bill= filter_var($_POST['total_bill'], FILTER_SANITIZE_STRING);
						$received= filter_var($_POST['total_paid'], FILTER_SANITIZE_STRING);
						$cash_acct = filter_var($_POST['cash_acct'], FILTER_SANITIZE_STRING);
						$sub_total = filter_var($_POST['sub_total'], FILTER_SANITIZE_STRING);
						$vendor_name = filter_var($_POST['vendor_name'], FILTER_SANITIZE_STRING);
			
						
							try{
			
			
			
									   $query = 'INSERT INTO payment_header (vendor_name,transType_id,session_id,vendor_id,vendor_details,purchase_date,payment_method,
									payment_ref,invoicemessage,memo,branch_id,company_id,recordtime,cash_acct) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array($vendor_name,1,$session_id,$payee_id, $billing_address,$date,
									$payment_method, $ref_no,$invoicemessage,$memo,$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$cash_acct));
			
									$query = 'INSERT INTO payment_bill (session_id,total_bill,total_paid,sub_total) VALUES (?,?,?,?)';
									$conn->prepare($query)->execute(array($session_id,$total_bill,$received,$sub_total));
			
								
								//	echo "1";
								
									
						}
							catch(PDOException $e){
								echo $e->getMessage();
							}

		 
				
						$source = '../payments.php';
						header('Location: ' . $source);
				break;

			case 'add_journalVoucher':

				//print_r($_POST);
					$session_id =  rand(1000,99999);
					$recordtime = date('Y-m-d H:i:s');
			
			 $i = 0;

			 
			
			foreach ($_POST['debitamount'] as $debitamount){
				if(!isset($_POST['debitamount'][$i])){
					$_POST['debitamount'][$i] = 0;

					
				}

				if(!isset($_POST['creditamount'][$i])){

					$_POST['creditamount'][$i] = 0;
					
				}

				
							//Account to credit
				$query = 'INSERT INTO master (session_id,account,db,cr, narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($session_id,$_POST['account_head'][$i],$_POST['debitamount'][$i],$_POST['creditamount'][$i],
				$_POST['narration'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

				$i++;

		 
						
					}

					if((!ISSET($_POST['date']))||($_POST['date'] == ''))
				{
					$_POST['date'] = date("Y-m-d");
					$date = $_POST['date'];
				}
			
						$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
						$ref_no = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
					   	
			
						
							try{
			
			
			
									$query = 'INSERT INTO payment_header (transType_id,session_id,purchase_date,
									payment_ref,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array(4,$session_id,$date,$ref_no,
									$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
			
											
								
								//	echo "1";
								
									
						}
							catch(PDOException $e){
								echo $e->getMessage();
							}
					
						$source = '../journal_list.php';
						header('Location: ' . $source);
				break;

				case 'edit_journalVoucher':
					$session_id =  $_POST['session_id'];
					$recordtime = date('Y-m-d H:i:s');
			
			 $i = 0;
			 if(isset($_POST['session_id'])){

			 $query = 'DELETE FROM master where session_id = ?';
			 $conn->prepare($query)->execute(array($session_id));

			$query = 'DELETE FROM payment_header where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));

			$query = 'DELETE FROM payment_details where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));
			
			$query = 'DELETE FROM payment_bill where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));
			
			$query = 'DELETE FROM inventory where session_id = ?';
			$conn->prepare($query)->execute(array($session_id));

			
			foreach ($_POST['debitamount'] as $debitamount){
				
			
				//Account to credit
				$query = 'INSERT INTO master (session_id,account,db,cr, narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($session_id,$_POST['account_head'][$i],$_POST['debitamount'][$i],$_POST['creditamount'][$i],
				$_POST['narration'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));


				


							$i++;

		 
						
					}
					if((!ISSET($_POST['date']))||($_POST['date'] == ''))
				{
					$_POST['date'] = date("Y-m-d");
					$date = $_POST['date'];
				}
			
						$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
						$ref_no = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
					   	
			
						
							try{
			
			
			
									$query = 'INSERT INTO payment_header (transType_id,session_id,purchase_date,
									payment_ref,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array(4,$session_id,$date,$ref_no,
									$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
			
											
								
							//		echo "1";
								
									
						}
							catch(PDOException $e){
								echo $e->getMessage();
							}
				}
					
						$source = '../journal_list.php';
						header('Location: ' . $source);
				break;

				
				
				
				case 'add_Invoicepayment':
					$session_id =  rand(1000,99999);
					$recordtime = date('Y-m-d H:i:s');
			
			 $i = 0;

			 


				



			
			foreach ($_POST['payments'] as $payments){
				
				  
				
				
				if($_POST['payments'][$i] > 0 ) {

					$query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array('CDJ',$session_id,$_POST['cash_acct'],$_POST['payments'][$i],
					$_POST['vendor_name'].'-'.$_POST['ref_no'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

					// Account to credit
					$query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array('CDJ',$session_id,$_POST['payable'][$i],$_POST['payments'][$i],
					$_POST['narration'][$i].'-'.$_POST['ref_no'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

					$query = 'INSERT INTO payment_bill (session_id,total_paid) VALUES (?,?)';
					$conn->prepare($query)->execute(array($_POST['session_id'][$i],$_POST['payments'][$i]));

					$query = 'INSERT INTO payment_header (transType_id,session_id,vendor_id,vendor_details,purchase_date,payment_method,
					payment_ref,branch_id,company_id,recordtime,cash_acct) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array(5,$session_id,$_POST['payee_id'], $_POST['billing_address'],$_POST['date'],
					$_POST['payment_method'], $_POST['ref_no'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$_POST['cash_acct']));


					$query = 'INSERT INTO payment_details (session_id,invoice_id,subtotal) VALUES (?,?,?)';
					$conn->prepare($query)->execute(array($session_id,$_POST['invoice_no'][$i],$_POST['payments'][$i]));
				


									
			
									
									}
			
									
									  
			
								   
									$i++;
				}
			
			
						
						

		 
				
						$source = '../invoice.php';
						header('Location: ' . $source);
				break;

			

				case 'add_invoice':

					$session_id =  rand(1000,99999);
					$recordtime = date('Y-m-d H:i:s');
			
			 $i = 0;

			

				$query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
				 $conn->prepare($query)->execute(array('PJ',$session_id,$_POST['contractorAcct'],$_POST['total_bill'],
				 $_POST['vendor_name'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));



			
			foreach ($_POST['price'] as $price){
				
				   
				
				
				if($_POST['price'][$i] > 0 ) {
			
			
			   
									// Account to credit
									$query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array('PJ',$session_id,$_POST['gl_salesacct'][$i],$_POST['subtotal'][$i],
									$_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));


									
			
									
									  if(isset($_POST['product'][$i])){

										$query = 'INSERT INTO inventory (session_id,item_descr,item_id,qty,cost_price,selling_price) VALUES (?,?,?,?,?,?)';
									}
			
									// Sales Account to debit
									  $query = 'INSERT INTO payment_details (session_id,item_descr,item_id,qty,rate,subtotal,gl_inventory_acct) VALUES (?,?,?,?,?,?,?)';
									  $conn->prepare($query)->execute(array($session_id,$_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_POST['product'][$i],
									  $_POST['qty'][$i],$_POST['price'][$i], $_POST['subtotal'][$i],$_POST['gl_salesacct'][$i]));
			
									  
			
								   
									$i++;
				}
			}
			
						
						if((!ISSET($_POST['date']))||($_POST['date'] == '')){
							$_POST['date'] = date("Y-m-d");
							$date = $_POST['date'];
						 }
			
						$payee_id = filter_var($_POST['payee_id'], FILTER_SANITIZE_STRING);   
						 $billing_address = filter_var($_POST['billing_address'], FILTER_SANITIZE_STRING);
						$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
						//$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
						$ref_no = filter_var($_POST['ref_no'], FILTER_SANITIZE_STRING);
					   $invoicemessage = filter_var($_POST['invoicemessage'], FILTER_SANITIZE_STRING);
						$memo = filter_var($_POST['memo'], FILTER_SANITIZE_STRING);
						$total_bill= filter_var($_POST['total_bill'], FILTER_SANITIZE_STRING);
						$received= filter_var($_POST['received'], FILTER_SANITIZE_STRING);
						$contractorAcct = filter_var($_POST['contractorAcct'], FILTER_SANITIZE_STRING);
						$sub_total = filter_var($_POST['sub_total'], FILTER_SANITIZE_STRING);
						$due_date = filter_var($_POST['due_date'], FILTER_SANITIZE_STRING);
						$vendor_name = filter_var($_POST['vendor_name'], FILTER_SANITIZE_STRING);

						if($total_bill == $received){
							$received = 0;
						}
			
						
							try{
			
			
			
									   $query = 'INSERT INTO payment_header (vendor_name,transType_id,session_id,vendor_id,vendor_details,purchase_date,
									payment_ref,invoicemessage,memo,branch_id,company_id,recordtime,cash_acct,due_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
									$conn->prepare($query)->execute(array($vendor_name,3,$session_id,$payee_id, $billing_address,$date,
									 $ref_no,$invoicemessage,$memo,$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$contractorAcct,$due_date));
			
									$query = 'INSERT INTO payment_bill (session_id,total_bill,total_paid,sub_total) VALUES (?,?,?,?)';
									$conn->prepare($query)->execute(array($session_id,$total_bill,$received,$sub_total));
			
								
							//		echo "1";
								
									
						}
							catch(PDOException $e){
								echo $e->getMessage();
							}

		 
				
						$source = '../invoice.php';
						 header('Location: ' . $source);
					
					break;


					case 'edit_invoice':

						if(isset($_POST['session_id'])){
							$session_id =  $_POST['session_id'];
							$recordtime = date('Y-m-d H:i:s');


							$query = 'DELETE FROM master where session_id = ?';
							$conn->prepare($query)->execute(array($session_id));

							$query = 'DELETE FROM payment_header where session_id = ?';
							$conn->prepare($query)->execute(array($session_id));

							$query = 'DELETE FROM payment_details where session_id = ?';
							$conn->prepare($query)->execute(array($session_id));
							
							$query = 'DELETE FROM payment_bill where session_id = ?';
							$conn->prepare($query)->execute(array($session_id));
							
							$query = 'DELETE FROM inventory where session_id = ?';
							$conn->prepare($query)->execute(array($session_id));
							
							$i = 0;

			

							$query = 'INSERT INTO master (session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?)';
							 $conn->prepare($query)->execute(array($session_id,$_POST['contractorAcct'],$_POST['total_bill'],
							 $_POST['vendor_name'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
			
			
			
						
						foreach ($_POST['price'] as $price){
							
							   
							
							
							if($_POST['price'][$i] > 0 ) {
						
						
						   
												// Account to credit
												$query = 'INSERT INTO master (session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?)';
												$conn->prepare($query)->execute(array($session_id,$_POST['gl_salesacct'][$i],$_POST['subtotal'][$i],
												$_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
			
			
												
						
												 // Account to credit
											//	 $query = 'INSERT INTO master (session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?)';
											//	 $conn->prepare($query)->execute(array($session_id,$_POST['income'][$i],$_POST['subtotal'][$i],
											//	 $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
												
												  // Sales Account to debit
											//	  $query = 'INSERT INTO master (session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?)';
											//	  $conn->prepare($query)->execute(array($session_id,$_POST['expenses'][$i],$_POST['cost_price'][$i],
											//	  $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
			
												  if(isset($_POST['product'][$i])){
			
													$query = 'INSERT INTO inventory (session_id,item_descr,item_id,qty,cost_price,selling_price) VALUES (?,?,?,?,?,?)';
											//	  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
											//	  '-'.$_POST['qty'][$i],$_POST['cost_price'][$i], $_POST['price'][$i]));
												}
						
												// Sales Account to debit
												  $query = 'INSERT INTO payment_details (session_id,item_descr,item_id,qty,rate,subtotal,gl_inventory_acct) VALUES (?,?,?,?,?,?,?)';
												  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
												  $_POST['qty'][$i],$_POST['price'][$i], $_POST['subtotal'][$i],$_POST['gl_salesacct'][$i]));
						
												  
						
											   
												$i++;
							}
						}
						
									
									if((!ISSET($_POST['date']))||($_POST['date'] == '')){
										$_POST['date'] = date("Y-m-d");
										$date = $_POST['date'];
									 }
						
									$payee_id = filter_var($_POST['payee_id'], FILTER_SANITIZE_STRING);   
									 $billing_address = filter_var($_POST['billing_address'], FILTER_SANITIZE_STRING);
									$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
									//$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
									$ref_no = filter_var($_POST['ref_no'], FILTER_SANITIZE_STRING);
								   $invoicemessage = filter_var($_POST['invoicemessage'], FILTER_SANITIZE_STRING);
									$memo = filter_var($_POST['memo'], FILTER_SANITIZE_STRING);
									$total_bill= filter_var($_POST['total_bill'], FILTER_SANITIZE_STRING);
									$received= filter_var($_POST['received'], FILTER_SANITIZE_STRING);
									$contractorAcct = filter_var($_POST['contractorAcct'], FILTER_SANITIZE_STRING);
									$sub_total = filter_var($_POST['sub_total'], FILTER_SANITIZE_STRING);
									$due_date = filter_var($_POST['due_date'], FILTER_SANITIZE_STRING);
									$vendor_name = filter_var($_POST['vendor_name'], FILTER_SANITIZE_STRING);

									if($total_bill == $received){
										$received = 0;
									}
						
									
										try{
						
						
						
												   $query = 'INSERT INTO payment_header (vendor_name,transType_id,session_id,vendor_id,vendor_details,purchase_date,
												payment_ref,invoicemessage,memo,branch_id,company_id,recordtime,cash_acct,due_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
												$conn->prepare($query)->execute(array($vendor_name,3,$session_id,$payee_id, $billing_address,$date,
												 $ref_no,$invoicemessage,$memo,$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$contractorAcct,$due_date));
						
												$query = 'INSERT INTO payment_bill (session_id,total_bill,total_paid,sub_total) VALUES (?,?,?,?)';
												$conn->prepare($query)->execute(array($session_id,$total_bill,$received,$sub_total));
						
											
									//			echo "1";
											
												
									}
										catch(PDOException $e){
											echo $e->getMessage();
										}
			
					 
							
								 	$source = '../invoice.php';
								    header('Location: ' . $source);
						}
						
						break;
						
						case 'add_sales':
							$session_id =  rand(1000,99999);
							$recordtime = date('Y-m-d H:i:s');
							//print_r($_POST);
							
							 $i = 0;
							
							 $query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
							 $conn->prepare($query)->execute(array('CRJ',$session_id,$_POST['cash_acct'],$_POST['total_bill'],
							 $_POST['vendor_name'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
							
							foreach ($_POST['price'] as $price){
							
							   
							
							
								if($_POST['price'][$i] > 0 ) {
							
							
							   
													// Account to credit
													
			
			
													if((isset($_POST['product'][$i])) && ($_POST['product'][$i] > 0) ){

														if(!isset($_POST['tax'][$i])){

															$_POST['tax'][$i] = 0;
														}
							
													 // Account to credit
													 $query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
													 $conn->prepare($query)->execute(array('CRJ',$session_id,$_POST['income'][$i],$_POST['subtotal'][$i],
													 $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

													 if(($_POST['tax'][$i])>0){
													 $query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
													 $conn->prepare($query)->execute(array('CRJ',$session_id,'170050',$_POST['tax'][$i],
													 $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
													
													}
													

													  // Sales Account to debit
													  $query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
													  $conn->prepare($query)->execute(array('COGS',$session_id,$_POST['expenses'][$i],$_POST['cost_price'][$i]*$_POST['qty'][$i],
													  $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
																	
													  $query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
													  $conn->prepare($query)->execute(array('COGS',$session_id,$_POST['gl_salesacct'][$i],$_POST['cost_price'][$i]*$_POST['qty'][$i],
													  $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

													  
													  $query = 'INSERT INTO inventory (session_id,item_descr,item_id,qty,cost_price,selling_price) VALUES (?,?,?,?,?,?)';
														  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
													  '-'.$_POST['qty'][$i],$_POST['cost_price'][$i]*$_POST['qty'][$i], $_POST['price'][$i]));
													
			
													}else{

													if(!isset($_POST['item'][$i])){

														$_POST['item'][$i] = '';
													}
													if(!isset($_POST['tax'][$i])){

														$_POST['tax'][$i] = 0;
													}
													
			
														$query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
														$conn->prepare($query)->execute(array('CRJ',$session_id,$_POST['gl_salesacct'][$i],($_POST['subtotal'][$i]*$_POST['qty'][$i])+$_POST['tax'][$i],
														$_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

													}
			
			
													
							
													// Sales Account to debit
													if(!isset($_POST['cost_price'][$i])){
														$_POST['cost_price'][$i] = 0;
													}
													  $query = 'INSERT INTO payment_details (session_id,item_descr,item_id,qty,rate,tax,subtotal,gl_inventory_acct,gl_sales_acct,gl_cost_sales,cost_price) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
													  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
													  $_POST['qty'][$i],$_POST['price'][$i], $_POST['tax'][$i], $_POST['subtotal'][$i],$_POST['gl_salesacct'][$i],$_POST['income'][$i],$_POST['expenses'][$i],$_POST['cost_price'][$i]));
							
													  
							
												   
													$i++;
								}
							}
							
										
										if((!ISSET($_POST['date']))||($_POST['date'] == '')){
											$_POST['date'] = date("Y-m-d");
											$date = $_POST['date'];
										 }
							
										$payee_id = filter_var($_POST['payee_id'], FILTER_SANITIZE_STRING);   
										 $billing_address = filter_var($_POST['billing_address'], FILTER_SANITIZE_STRING);
										$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
										$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
										$ref_no = filter_var($_POST['ref_no'], FILTER_SANITIZE_STRING);
									   $invoicemessage = filter_var($_POST['invoicemessage'], FILTER_SANITIZE_STRING);
										$memo = filter_var($_POST['memo'], FILTER_SANITIZE_STRING);
										$total_bill= filter_var($_POST['total_bill'], FILTER_SANITIZE_STRING);
										$received= filter_var($_POST['received'], FILTER_SANITIZE_STRING);
										$cash_acct = filter_var($_POST['cash_acct'], FILTER_SANITIZE_STRING);
										$sub_total = filter_var($_POST['sub_total'], FILTER_SANITIZE_STRING);
										$total_tax = filter_var($_POST['total_tax'], FILTER_SANITIZE_STRING);
										$vendor_name = filter_var($_POST['vendor_name'], FILTER_SANITIZE_STRING);
							
										
											try{
							
							
							
													$query = 'INSERT INTO payment_header (vendor_name,transType_id,session_id,vendor_id,vendor_details,purchase_date,payment_method,
													payment_ref,invoicemessage,memo,branch_id,company_id,recordtime,cash_acct) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
													$conn->prepare($query)->execute(array($vendor_name,2,$session_id,$payee_id, $billing_address,$date,
													$payment_method, $ref_no,$invoicemessage,$memo,$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$cash_acct));
							
													$query = 'INSERT INTO payment_bill (session_id,total_bill,total_paid,sub_total,total_tax) VALUES (?,?,?,?,?)';
													$conn->prepare($query)->execute(array($session_id,$total_bill,$received,$sub_total,$total_tax));
							
												
											//		echo "1";
												
													
										}
											catch(PDOException $e){
												echo $e->getMessage();
											}
			
						 
								
										$source = '../sales.php';
									header('Location: ' . $source);
							break;

				case 'edit_sales':
					
					//print_r($_POST);
					
					if(isset($_POST['session_id'])){
						$session_id =  $_POST['session_id'];
						$recordtime = date('Y-m-d H:i:s');

					$query = 'DELETE FROM master where session_id = ?';
					$conn->prepare($query)->execute(array($session_id));
	   
				   $query = 'DELETE FROM payment_header where session_id = ?';
				   $conn->prepare($query)->execute(array($session_id));
	   
				   $query = 'DELETE FROM payment_details where session_id = ?';
				   $conn->prepare($query)->execute(array($session_id));
				   
				   $query = 'DELETE FROM payment_bill where session_id = ?';
				   $conn->prepare($query)->execute(array($session_id));
				   
				   $query = 'DELETE FROM inventory where session_id = ?';
				   $conn->prepare($query)->execute(array($session_id));
					
					 $i = 0;
					
					 $query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
					 $conn->prepare($query)->execute(array('CRJ',$session_id,$_POST['cash_acct'],$_POST['total_bill'],
					 $_POST['vendor_name'],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
					
					foreach ($_POST['price'] as $price){
					
					   
					
					
						if($_POST['price'][$i] > 0 ) {
							
							
							   
							// Account to credit
							


							if((isset($_POST['product'][$i])) && ($_POST['product'][$i] > 0) ){

								if(!isset($_POST['tax'][$i])){

									$_POST['tax'][$i] = 0;
								}
	
							 // Account to credit
							 $query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
							 $conn->prepare($query)->execute(array('CRJ',$session_id,$_POST['income'][$i],$_POST['subtotal'][$i],
							 $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

							 if(($_POST['tax'][$i])>0){
							 $query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
							 $conn->prepare($query)->execute(array('CRJ',$session_id,'170050',$_POST['tax'][$i],
							 $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
							
							}
							

							  // Sales Account to debit
							  $query = 'INSERT INTO master (jrnl,session_id,account,db,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
							  $conn->prepare($query)->execute(array('COGS',$session_id,$_POST['expenses'][$i],$_POST['cost_price'][$i]*$_POST['qty'][$i],
							  $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));
											
							  $query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
							  $conn->prepare($query)->execute(array('COGS',$session_id,$_POST['gl_salesacct'][$i],$_POST['cost_price'][$i]*$_POST['qty'][$i],
							  $_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

							  
							  $query = 'INSERT INTO inventory (session_id,item_descr,item_id,qty,cost_price,selling_price) VALUES (?,?,?,?,?,?)';
								  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
							  '-'.$_POST['qty'][$i],$_POST['cost_price'][$i]*$_POST['qty'][$i], $_POST['price'][$i]));
							

							}else{

							if(!isset($_POST['item'][$i])){

								$_POST['item'][$i] = '';
							}

							if(!isset($_POST['tax'][$i])){

								$_POST['tax'][$i] = 0;
							}
							

								$query = 'INSERT INTO master (jrnl,session_id,account,cr,narration,branch_id,company_id,recordtime) VALUES (?,?,?,?,?,?,?,?)';
								$conn->prepare($query)->execute(array('CRJ',$session_id,$_POST['gl_salesacct'][$i],($_POST['subtotal'][$i]*$_POST['qty'][$i])+$_POST['tax'][$i] ,
								$_POST['vendor_name']. ' - '.$_POST['item'][$i].' '. $_POST['descriptionarr'][$i],$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime));

							}


							
	
							// Sales Account to debit
							if(!isset($_POST['cost_price'][$i])){
								$_POST['cost_price'][$i] = 0;
							}
							  $query = 'INSERT INTO payment_details (session_id,item_descr,item_id,qty,rate,tax,subtotal,gl_inventory_acct,gl_sales_acct,gl_cost_sales,cost_price) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
							  $conn->prepare($query)->execute(array($session_id,$_POST['descriptionarr'][$i],$_POST['product'][$i],
							  $_POST['qty'][$i],$_POST['price'][$i], $_POST['tax'][$i], $_POST['subtotal'][$i],$_POST['gl_salesacct'][$i],$_POST['income'][$i],$_POST['expenses'][$i],$_POST['cost_price'][$i]));
	
							  
	
						   
							$i++;
		}
	}
					
								
								if((!ISSET($_POST['date']))||($_POST['date'] == '')){
									$_POST['date'] = date("Y-m-d");
									$date = $_POST['date'];
								 }
					
								$payee_id = filter_var($_POST['payee_id'], FILTER_SANITIZE_STRING);   
								 $billing_address = filter_var($_POST['billing_address'], FILTER_SANITIZE_STRING);
								$date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
								$payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
								$ref_no = filter_var($_POST['ref_no'], FILTER_SANITIZE_STRING);
							   $invoicemessage = filter_var($_POST['invoicemessage'], FILTER_SANITIZE_STRING);
								$memo = filter_var($_POST['memo'], FILTER_SANITIZE_STRING);
								$total_bill= filter_var($_POST['total_bill'], FILTER_SANITIZE_STRING);
								$received= filter_var($_POST['received'], FILTER_SANITIZE_STRING);
								$cash_acct = filter_var($_POST['cash_acct'], FILTER_SANITIZE_STRING);
								$sub_total = filter_var($_POST['sub_total'], FILTER_SANITIZE_STRING);
								$total_tax = filter_var($_POST['total_tax'], FILTER_SANITIZE_STRING);
								$vendor_name = filter_var($_POST['vendor_name'], FILTER_SANITIZE_STRING);
					
								
									try{
					
					
					
											$query = 'INSERT INTO payment_header (vendor_name,transType_id,session_id,vendor_id,vendor_details,purchase_date,payment_method,
											payment_ref,invoicemessage,memo,branch_id,company_id,recordtime,cash_acct) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
											$conn->prepare($query)->execute(array($vendor_name,2,$session_id,$payee_id, $billing_address,$date,
											$payment_method, $ref_no,$invoicemessage,$memo,$_SESSION['BRANCH'], $_SESSION['COMPANY'],$recordtime,$cash_acct));
					
											$query = 'INSERT INTO payment_bill (session_id,total_bill,total_paid,sub_total,total_tax) VALUES (?,?,?,?,?)';
											$conn->prepare($query)->execute(array($session_id,$total_bill,$received,$sub_total,$total_tax));
					
										
									//		echo "1";
										
											
								}
									catch(PDOException $e){
										echo $e->getMessage();
									}

			 
					
							$source = '../sales.php';
							header('Location: ' . $source);
					}
					break;
				
				
			case 'newCustomer':
					//
					//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
					$customer_name = filter_var($_POST['customer_name'], FILTER_SANITIZE_STRING);
					$customer_town = filter_var($_POST['customer_town'], FILTER_SANITIZE_STRING);
					$customer_tin = filter_var($_POST['customer_tin'], FILTER_SANITIZE_STRING);
					$customer_address = filter_var($_POST['customer_address'], FILTER_SANITIZE_STRING);
					$customer_phone = filter_var($_POST['customer_phone'], FILTER_SANITIZE_STRING);
					$customer_state = filter_var($_POST['customer_state'], FILTER_SANITIZE_STRING);
					$customer_email = filter_var((filter_var($_POST['customer_email'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
					$recordtime = date('Y-m-d H:i:s');

					
						try{


							$query = $conn->prepare('SELECT * FROM customer WHERE name = ? and company_id = ? and branch_id = ?');
							$res = $query->execute(array($customer_name,$_SESSION['COMPANY'],$_SESSION['BRANCH']));
							$existtrans = $query->fetch();

							if ($existtrans) {
								//user exists
								//$_SESSION['msg'] = "A user account associated with the supplied Staff ID exists.";
								//$_SESSION['alertcolor'] = "danger";
								//$source = $_SERVER['HTTP_REFERER'];
								//header('Location: ' . $source);
								echo "0";

								}else {

							
								//$upass = password($user_password);

								$query = 'INSERT INTO customer (customer.`name`,
								customer.email,
								customer.address,
								customer.phone,
								customer.customer_tin,
								customer.company_id,
								customer.state,
								customer.town,
								customer.date_insert,
								customer.inserted_by,customer.branch_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
								$conn->prepare($query)->execute(array($customer_name,$customer_email, $customer_address,$customer_phone,
								$customer_tin, $_SESSION['COMPANY'], $customer_state,$customer_town,
								$recordtime,$_SESSION['SESS_MEMBER_ID'],$_SESSION['BRANCH']));

								//$_SESSION['msg'] = $msg = 'User Successfully Created';
								//$_SESSION['alertcolor'] = $type = 'success';
								//$source = $_SERVER['HTTP_REFERER'];
								//header('Location: ' . $source);
								echo "1";
							
								}
					}
						catch(PDOException $e){
							echo $e->getMessage();
						}
					
					
					
				break;
		

		case 'editVendor':
			//
			//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
			$customer_name = filter_var($_POST['customer_name'], FILTER_SANITIZE_STRING);
			$customer_town = filter_var($_POST['customer_town'], FILTER_SANITIZE_STRING);
			$customer_tin = filter_var($_POST['customer_tin'], FILTER_SANITIZE_STRING);
			$customer_address = filter_var($_POST['customer_address'], FILTER_SANITIZE_STRING);
			$customer_phone = filter_var($_POST['customer_phone'], FILTER_SANITIZE_STRING);
			$customer_state = filter_var($_POST['customer_state'], FILTER_SANITIZE_STRING);
			$vendor_id = filter_var($_POST['vendor_id'], FILTER_SANITIZE_STRING);
			$customer_email = filter_var((filter_var($_POST['customer_email'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
			$recordtime = date('Y-m-d H:i:s');

			
				try{

						//$upass = password($user_password);

						$query = 'UPDATE vendor SET vendor.`name` = ?,
						vendor.email = ?,
						vendor.address = ?,
						vendor.phone = ? ,
						vendor.vendor_tin = ?,
						vendor.company_id = ?,
						vendor.state = ? ,
						vendor.town = ?,
						vendor.date_insert = ?,
						vendor.inserted_by = ? 
						WHERE vendor_id = ? ';
						$conn->prepare($query)->execute(array($customer_name,$customer_email, $customer_address,$customer_phone,
						$customer_tin, $_SESSION['COMPANY'], $customer_state,$customer_town,
						$recordtime,$_SESSION['SESS_MEMBER_ID'],$vendor_id));

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'editCustomer':
			//
			//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
			$customer_name = filter_var($_POST['customer_name'], FILTER_SANITIZE_STRING);
			$customer_town = filter_var($_POST['customer_town'], FILTER_SANITIZE_STRING);
			$customer_tin = filter_var($_POST['customer_tin'], FILTER_SANITIZE_STRING);
			$customer_address = filter_var($_POST['customer_address'], FILTER_SANITIZE_STRING);
			$customer_phone = filter_var($_POST['customer_phone'], FILTER_SANITIZE_STRING);
			$customer_state = filter_var($_POST['customer_state'], FILTER_SANITIZE_STRING);
			$vendor_id = filter_var($_POST['vendor_id'], FILTER_SANITIZE_STRING);
			$customer_email = filter_var((filter_var($_POST['customer_email'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
			$recordtime = date('Y-m-d H:i:s');

			
				try{

						//$upass = password($user_password);

						$query = 'UPDATE vendor SET vendor.`name` = ?,
						vendor.email = ?,
						vendor.address = ?,
						vendor.phone = ? ,
						vendor.vendor_tin = ?,
						vendor.company_id = ?,
						vendor.state = ? ,
						vendor.town = ?,
						vendor.date_insert = ?,
						vendor.inserted_by = ? 
						WHERE vendor_id = ? ';
						$conn->prepare($query)->execute(array($customer_name,$customer_email, $customer_address,$customer_phone,
						$customer_tin, $_SESSION['COMPANY'], $customer_state,$customer_town,
						$recordtime,$_SESSION['SESS_MEMBER_ID'],$vendor_id));

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;


		case 'addItem':
			//
			//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
			$product_name = filter_var($_POST['product_name'], FILTER_SANITIZE_STRING);
			$description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
			$product_classs = filter_var($_POST['product_classs'], FILTER_SANITIZE_STRING);
			$GL_Sales_Acct = filter_var($_POST['GL_Sales_Acct'], FILTER_SANITIZE_STRING);
			$GL_Inventory_Acct = filter_var($_POST['GL_Inventory_Acct'], FILTER_SANITIZE_STRING);
			$GL_costOfSales_Acct = filter_var($_POST['GL_costOfSales_Acct'], FILTER_SANITIZE_STRING);
			$cost_price = filter_var($_POST['cost_price'], FILTER_SANITIZE_STRING);
			$price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
			$sales_tax = filter_var($_POST['sales_tax'], FILTER_SANITIZE_STRING);
			$min_stock = filter_var($_POST['min_stock'], FILTER_SANITIZE_STRING);
			$re_order = filter_var($_POST['re_order'], FILTER_SANITIZE_STRING);
			$beginning_balance = filter_var($_POST['beginning_balance'], FILTER_SANITIZE_STRING);
			
			$recordtime = date('Y-m-d H:i:s');

			
				try{

						//$upass = password($user_password);

						$query = 'INSERT INTO item (item.item,item.item_type,item.item_description,item.gl_sales_acct,item.gl_inventory_acct,item.gl_cost_sales,item.tax,
						item.cost_price,item.selling_price,item.company_id,item.recordtime,mini_stock,`re-order`,begin_balance,branch_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($product_name,$product_classs, $description,$GL_Sales_Acct,
						$GL_Inventory_Acct, $GL_costOfSales_Acct, $sales_tax,$cost_price,$price,$_SESSION['COMPANY'],$recordtime,$min_stock,$re_order,$beginning_balance,$_SESSION['BRANCH']));

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'addIncomeSettings': 
			
			$GL_Sales_Acct = filter_var($_POST['GL_Sales_Acct'], FILTER_SANITIZE_STRING);
			
			
			$recordtime = date('Y-m-d H:i:s');

			
				try{

						//$upass = password($user_password);

						$query = 'INSERT INTO table_settings (company_id,branch_id,acct_id) VALUES (?,?,?)';
						$conn->prepare($query)->execute(array($_SESSION['COMPANY'],$_SESSION['BRANCH'],$GL_Sales_Acct));

						
						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'editItem':
			//
			$id = filter_var($_POST['id'], FILTER_SANITIZE_STRING);
			$product_name = filter_var($_POST['product_name'], FILTER_SANITIZE_STRING);
			$description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
			$product_classs = filter_var($_POST['product_classs'], FILTER_SANITIZE_STRING);
			$GL_Sales_Acct = filter_var($_POST['GL_Sales_Acct'], FILTER_SANITIZE_STRING);
			$GL_Inventory_Acct = filter_var($_POST['GL_Inventory_Acct'], FILTER_SANITIZE_STRING);
			$GL_costOfSales_Acct = filter_var($_POST['GL_costOfSales_Acct'], FILTER_SANITIZE_STRING);
			$cost_price = filter_var($_POST['cost_price'], FILTER_SANITIZE_STRING);
			$price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
			$sales_tax = filter_var($_POST['sales_tax'], FILTER_SANITIZE_STRING);
			$min_stock = filter_var($_POST['min_stock'], FILTER_SANITIZE_STRING);
			$re_order = filter_var($_POST['re_order'], FILTER_SANITIZE_STRING);
			$beginning_balance = filter_var($_POST['beginning_balance'], FILTER_SANITIZE_STRING);
			
			$recordtime = date('Y-m-d H:i:s');

			
				try{

						//$upass = password($user_password);

						$query = 'UPDATE item SET item.item = ?,item.item_type = ? ,item.item_description = ?,item.gl_sales_acct = ?,item.gl_inventory_acct=?,item.gl_cost_sales=?,item.tax=?,
						item.cost_price=?,item.selling_price=?,item.company_id=?,item.recordtime=?,item.mini_stock=?,item.`re-order`=?,begin_balance=? WHERE item_id = ?';
						$conn->prepare($query)->execute(array($product_name,$product_classs, $description,$GL_Sales_Acct,
						$GL_Inventory_Acct, $GL_costOfSales_Acct, $sales_tax,$cost_price,$price,$_SESSION['COMPANY'],$recordtime,$min_stock,$re_order,$beginning_balance,$id));

						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'addRecons':
			//
			$master_id = filter_var($_POST['checkbox'], FILTER_SANITIZE_STRING);
			$date_clear = filter_var($_POST['date_clear'], FILTER_SANITIZE_STRING);

			
				try{
					$query = 	'UPDATE `master` SET  bankRecons = ?,date_clear = ?  WHERE master_id = ?';
								$conn->prepare($query)->execute(array(1,$date_clear,$master_id));
				}
					catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'removeRecons':
			//
			$master_id = filter_var($_POST['checkbox'], FILTER_SANITIZE_STRING);

			
				try{
					$query = 	'UPDATE `master` SET  bankRecons = ?,date_clear=? WHERE master_id = ?';
								$conn->prepare($query)->execute(array(0,NULL,$master_id));
				}
					catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'statementRecons':
			//
			$statement = filter_var($_POST['statement'], FILTER_SANITIZE_STRING);
			$statementDate = filter_var($_POST['statementDate'], FILTER_SANITIZE_STRING);
			$statementDateSplit = explode('-',$statementDate);
			$statementDate = $statementDateSplit[0].$statementDateSplit[1];

			

					$query = $conn->prepare('SELECT * FROM recons WHERE month_year = ? ');
					$res = $query->execute(array($statementDate));
					$existtrans = $query->fetch();
					if($existtrans){
				try{
					$query1 = 	'UPDATE recons SET  `statement` = ? WHERE month_year = ?';
								$conn->prepare($query1)->execute(array($statement,$statementDate));
				}
					catch(PDOException $e){
					echo $e->getMessage();
				}
					}else{
						try{
							$query1 = 	'INSERT INTO recons (`statement`,month_year) VALUES(?,?)';
										$conn->prepare($query1)->execute(array($statement,$statementDate));
						}
							catch(PDOException $e){
							echo $e->getMessage();
						}


					}
			
			
		break;

		case 'addaccount':
			//
			//$staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_STRING);
			$account_name = strtoupper(filter_var($_POST['account_name'], FILTER_SANITIZE_STRING));
			$accountType_id = filter_var($_POST['accountType_id'], FILTER_SANITIZE_STRING);
			$accountType_group = filter_var($_POST['accountType_group'], FILTER_SANITIZE_STRING);

			$lastInsert = 0;
			$newInsertId = 0;

			
				try{


					$query = $conn->prepare('SELECT * FROM account WHERE account = ? and company_id = ? and branch_id = ? ');
					$res = $query->execute(array($account_name,$_SESSION['COMPANY'],$_SESSION['BRANCH']));
					$existtrans = $query->fetch();

					if ($existtrans) {
						
						echo "0";

						}else {
							$query = $conn->prepare('SELECT accountheadlastinsert.accountType,accountheadlastinsert.lastInsert FROM accountheadlastinsert WHERE accountType = ?');
							$res = $query->execute(array($accountType_id));
							$out = $query->fetchAll(PDO::FETCH_ASSOC);
							while ($row = array_shift($out)) { 
								$lastInsert = $row['lastInsert'];
								$lastInsert = $lastInsert + 10;
								$newInsertId = $lastInsert;
								$lastInsert = str_pad($lastInsert,4,'0',STR_PAD_LEFT);
								$lastInsert = $accountType_id.$lastInsert;
								}

					
						

						$query = 'INSERT INTO account (groupHead,acct_id,accountType, account,branch_id,company_id) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($accountType_group,$lastInsert,$accountType_id, $account_name.'_'.$_SESSION['BRANCH'],$_SESSION['BRANCH'] ,$_SESSION['COMPANY']));

						$query = 'UPDATE accountheadlastinsert SET lastInsert = ? WHERE accountType = ?';
						$conn->prepare($query)->execute(array($newInsertId,$accountType_id));


						echo "1";
					
						}
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'editaccount':
			//
			$account_id = filter_var($_POST['account_id'], FILTER_SANITIZE_STRING);
			$account_name = strtoupper(filter_var($_POST['account_name'], FILTER_SANITIZE_STRING));
			$accountType_id = filter_var($_POST['accountType_id'], FILTER_SANITIZE_STRING);
			$accountType_group = filter_var($_POST['accountType_group'], FILTER_SANITIZE_STRING);

			$lastInsert = 0;
			$newInsertId = 0;

			$query = $conn->prepare('DELETE FROM account WHERE acct_id = ? ');
			$res = $query->execute(array($account_id));
			//$existtrans = $query->fetch();

			
				try{


					$query = $conn->prepare('SELECT * FROM account WHERE account = ? and company_id = ? and branch_id = ?');
					$res = $query->execute(array($account_name,$_SESSION['COMPANY'] ,$_SESSION['BRANCH']));
					$existtrans = $query->fetch();

					
							$query = $conn->prepare('SELECT accountheadlastinsert.accountType,accountheadlastinsert.lastInsert FROM accountheadlastinsert WHERE accountType = ?');
							$res = $query->execute(array($accountType_id));
							$out = $query->fetchAll(PDO::FETCH_ASSOC);
							while ($row = array_shift($out)) { 
								$lastInsert = $row['lastInsert'];
								$lastInsert = $lastInsert + 10;
								$newInsertId = $lastInsert;
								$lastInsert = str_pad($lastInsert,4,'0',STR_PAD_LEFT);
								$lastInsert = $accountType_id.$lastInsert;
								}

					
						

						$query = 'INSERT INTO account (groupHead,acct_id,accountType, account,company_id,branch_id) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($accountType_group,$lastInsert,$accountType_id, $account_name,$_SESSION['COMPANY'] ,$_SESSION['BRANCH']));

						$query = 'UPDATE accountheadlastinsert SET lastInsert = ? WHERE accountType = ?';
						$conn->prepare($query)->execute(array($newInsertId,$accountType_id));


						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;
		case 'AddBranch':
			//
			//$branch_id = filter_var($_POST['branch_id'], FILTER_SANITIZE_STRING);
			if(trim(($_POST['branch_name']) != '')){
			$branch_name = strtoupper(filter_var($_POST['branch_name'], FILTER_SANITIZE_STRING));
			$branch_address = filter_var($_POST['branch_address'], FILTER_SANITIZE_STRING);

			$query = 'INSERT INTO branch (branchName, branchAddress, company_id) VALUES (?,?,?)';
					$conn->prepare($query)->execute(array($branch_name, $branch_address, $_SESSION['COMPANY']));
					$latestBranchinsert = $conn->lastInsertId();

					

			
			//$existtrans = $query->fetch();

			
				try{

					$query2 = $conn->prepare('SELECT
					account_template.acct_id,account_template.sufffix,account_template.accountType,
					account_template.groupHead,account_template.account,account_template.`status` FROM account_template');
					$res2 = $query2->execute();
					$out2 = $query2->fetchAll(PDO::FETCH_ASSOC);
					while ($row2 = array_shift($out2)) { 


						$query = 'INSERT INTO account (groupHead,acct_id,accountType, account,company_id,branch_id) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($row2['groupHead'],$row2['acct_id'].'_'.$latestBranchinsert,$row2['accountType'], $row2['account'],$_SESSION['COMPANY'], 
						$latestBranchinsert));

						$query3 = $conn->prepare('SELECT accountheadlastinsert.accountType,accountheadlastinsert.lastInsert FROM accountheadlastinsert WHERE accountType = ?');
							$res3 = $query3->execute(array($row2['accountType']));
							$out3 = $query3->fetchAll(PDO::FETCH_ASSOC);
							while ($row3 = array_shift($out3)) { 
								$lastInsert = $row3['lastInsert'];
								$lastInsert = $lastInsert + 10;
								$newInsertId = $lastInsert;
								$lastInsert = str_pad($lastInsert,4,'0',STR_PAD_LEFT);
								$lastInsert = $row3['accountType'].$lastInsert;
								}

								$query = 'UPDATE accountheadlastinsert SET lastInsert = ? WHERE accountType = ?';
						$conn->prepare($query)->execute(array($newInsertId,$row2['accountType']));


					}





					


						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			}else {

				echo "0";
			}
		break;

		case 'editBranch':
			//
			$branch_id = filter_var($_POST['branch_id'], FILTER_SANITIZE_STRING);
			$branch_name = filter_var($_POST['branch_name'], FILTER_SANITIZE_STRING);
			$branch_address = filter_var($_POST['branch_address'], FILTER_SANITIZE_STRING);
			

			
				try{


						$query = 'UPDATE branch SET branchName = ? , branchAddress = ? WHERE branch_id = ?';
						$conn->prepare($query)->execute(array($branch_name,$branch_address,$branch_id));


						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'deleteuser':
			//
			$username_id = filter_var($_GET['id'], FILTER_SANITIZE_STRING);
			$action=filter_var($_GET['action'], FILTER_SANITIZE_STRING);

			
				try{



						$query = 'UPDATE username SET status=? WHERE username_id = ?';
						if($action == 'deactivate'){
						$conn->prepare($query)->execute(array('In-Active',$username_id));
						}else {
							$conn->prepare($query)->execute(array('Active',$username_id));
						}

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
						//echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'deleteIncomeAccount':
			//
			$account_id = filter_var($_GET['id'], FILTER_SANITIZE_STRING);
			$action=filter_var($_GET['action'], FILTER_SANITIZE_STRING);

			
				try{



						$query = 'DELETE FROM table_settings WHERE id = ?';
						if($action == 'deactivate'){
						$conn->prepare($query)->execute(array($account_id));
						}

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
						//echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'deleteaccount':
			//
			$acct_id = filter_var($_GET['id'], FILTER_SANITIZE_STRING);
			$action=filter_var($_GET['action'], FILTER_SANITIZE_STRING);

			
				try{



						$query = 'UPDATE account SET status=? WHERE acct_id = ?';
						if($action == 'deactivate'){
						$conn->prepare($query)->execute(array('0',$acct_id));
						}else {
							$conn->prepare($query)->execute(array('1',$acct_id));
						}

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
						//echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		

		case 'edituser':
			//
			$username_id = filter_var($_POST['username_id'], FILTER_SANITIZE_STRING);
			$user_name = filter_var($_POST['user_name'], FILTER_SANITIZE_STRING);
			$first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
			$middle_name = filter_var($_POST['middle_name'], FILTER_SANITIZE_STRING);
			$surname_name = filter_var($_POST['surname_name'], FILTER_SANITIZE_STRING);
			$User_Address = filter_var($_POST['User_Address'], FILTER_SANITIZE_STRING);
			$branch_id = filter_var($_POST['branch_id'], FILTER_SANITIZE_STRING);
			$user_email = filter_var((filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL)),FILTER_VALIDATE_EMAIL);
			$user_password = filter_var($_POST['user_password'], FILTER_SANITIZE_STRING);
			$recordtime = date('Y-m-d H:i:s');

			
				try{


					
						

					
						//$upass = password($user_password);

						$query = 'UPDATE username SET firstname=?, middlename=?, lastname=?, addres=?, 
						branch_id=?,email=?,password=password(".$user_password."),plainPassword=? WHERE username_id = ?';
						$conn->prepare($query)->execute(array($first_name,$middle_name ,$surname_name, $User_Address, 
						$branch_id,$user_email,$user_password,$username_id));

						//$_SESSION['msg'] = $msg = 'User Successfully Created';
						//$_SESSION['alertcolor'] = $type = 'success';
						//$source = $_SERVER['HTTP_REFERER'];
						//header('Location: ' . $source);
						echo "1";
					
						
			}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			
			
			
		break;

		case 'createcompanyaccount':
			//create new company
			$title = "New Payroll Account";
			$companyname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
			$contactemail = filter_var((filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			$contactphone = filter_var($_POST['phone'], FILTER_VALIDATE_INT);
			$companyaddress = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
			$compcity = filter_var($_POST['city'], FILTER_SANITIZE_STRING);

			$useremail = filter_var((filter_var($_POST['username'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);
			$userfname = filter_var($_POST['ufname'], FILTER_SANITIZE_STRING);
			$userlname = filter_var($_POST['ulname'], FILTER_SANITIZE_STRING);
			$userpass1 = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
			$userpass = password_hash($userpass1, PASSWORD_DEFAULT);

			try{
				$query = $conn->prepare('SELECT * FROM users WHERE emailAddress = ? AND active = ? ');
				$res = $query->execute(array($useremail, '1'));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$_SESSION['msg'] = "A user account associated with the supplied email exists.";
					$_SESSION['alertcolor'] = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					header('Location: ' . $source);
				}
				else {
					
					$query = 'INSERT INTO company (companyName, city, companyAddress, companyEmail, contactTelephone) VALUES (?,?,?,?,?)';
					$conn->prepare($query)->execute(array($companyname, $compcity, $companyaddress, $contactemail, $contactphone));
					$last_id = $conn->lastInsertId();
					
					$query = 'INSERT INTO users (emailAddress, password, userTypeId, firstName, lastName, companyId, active) VALUES (?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($useremail, $userpass, '1', $userfname, $userlname, $last_id, '0'));
					$latestuserinsert = $conn->lastInsertId();
				
					//user account becomes active after validating emailed link
					//Send email validation
					//Generate update token
					$reset_token = bin2hex(openssl_random_pseudo_bytes(32));
					
					//write token to token table and assign validity state, creation timestamp
					$tokenrecordtime = date('Y-m-d H:i:s');


					//check for any previous tokens and invalidate
						$tokquery = $conn->prepare('SELECT * FROM reset_token WHERE userEmail = ? AND valid = ? AND type = ?');
						$fin = $tokquery->execute(array($useremail, '1', '2'));
						
						if($row = $tokquery->fetch()){
							$upquery = 'UPDATE reset_token SET valid = ? WHERE userEmail = ? AND valid = ?';
							$conn->prepare($upquery)->execute('0', $useremail, '1');
						}

					$tokenquery = 'INSERT INTO reset_token (userEmail, token, creationTime, valid, type) VALUES (?,?,?,?,?)';
					$conn->prepare($tokenquery)->execute(array($useremail, $reset_token, $tokenrecordtime, '1', '2'));
						
					//exit($resetemail . " " . $reset_token);
					
					$sendmessage = "You've recently created a new Red Payroll account linked to the email address: " . $useremail . "<br /><br />To activate your account, click the link below:<br /><br /> " . $sysurl . 'validate.php?act=auth&jam=' . $latestuserinsert .'&queue=' . $last_id . '&token=' . $reset_token;
					//generate reset cdde and append to email submitted

					require 'phpmailer/PHPMailerAutoload.php';

					$mail = new PHPMailer;

					$mail->SMTPDebug = 3;                               // Enable verbose debug output

					$mail->isSMTP();                                      // Set mailer to use SMTP
					$mail->Host = 'smtp.zoho.com';  // Specify main and backup SMTP servers
					$mail->SMTPAuth = true;                               // Enable SMTP authentication
					$mail->Username = 'noreply@redsphere.co.ke';                 // SMTP username
					$mail->Password = 'redsphere_2017***';                           // SMTP password
					$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
					$mail->Port = 587;                                    // TCP port to connect to

					$mail->setFrom('noreply@redsphere.co.ke', 'Red Payroll');
					$mail->addAddress($useremail, 'Redsphere Payroll');     // Add a recipient
					//$mail->addAddress('ellen@example.com');               // Name is optional
					$mail->addReplyTo('noreply@redsphere.co.ke', 'Red Payroll');
					//$mail->addCC('fgesora@gmail.com');
					$mail->addBCC('fgesora@gmail.com');

					//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
					//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
					$mail->isHTML(true);                                  // Set email format to HTML

					$mail->Subject = $title;
					$mail->Body    = $sendmessage;
					$mail->AltBody = $sendmessage;

					if(!$mail->send()) {
						//exit($mail->ErrorInfo);
					    echo 'Mailer Error: ' . $mail->ErrorInfo;
					  //  $_SESSION('msg') = "Failed. Error sending email.";
					    $_SESSION['alertcolor'] = "danger";
					    header("Location: " . $source);
					} else {
					    $status = "Success";
					    $_SESSION['msg'] = "An activation link has been sent to the provided email address. Please activate your account in order to log in.";
					    $_SESSION['alertcolor'] = "success";
					    header("Location: " . $source);
					}
				}

					/*
					********
					********
					Check if user account exists
					********
					********
					*/

				


			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

			//exit($companyname . ', ' . $contactemail . ', ' . $last_id);
		break;


		case 'addcostcenter':
			$ccname = filter_var($_POST['cctrname'], FILTER_SANITIZE_STRING);

			try{
				$query = 'INSERT INTO company_costcenters (companyId, costCenterName, active) VALUES (?,?,?)';
				$conn->prepare($query)->execute(array($_SESSION['companyid'], $ccname, '1'));
				$_SESSION['msg'] = $msg = 'Cost Center successfully Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;
		
		case 'earningchange':
			$ccname = filter_var($_POST['cctrname'], FILTER_SANITIZE_STRING);

			try{
				$query = 'INSERT INTO company_costcenters (companyId, costCenterName, active) VALUES (?,?,?)';
				$conn->prepare($query)->execute(array($_SESSION['companyid'], $ccname, '1'));
				$_SESSION['msg'] = $msg = 'Cost Center successfully Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'adddepartment':
			$dept = filter_var($_POST['deptname'], FILTER_SANITIZE_STRING);
			
			try{
				
				$query = $conn->prepare('SELECT * FROM tbl_dept WHERE dept = ? ');
				$res = $query->execute(array($dept));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$_SESSION['msg'] = "Department already existing";
					$_SESSION['alertcolor'] = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					header('Location: ' . $source);
				}else{
				
				$query = 'INSERT INTO tbl_dept (dept) VALUES (?)';
				$conn->prepare($query)->execute(array($dept));
				$_SESSION['msg'] = $msg = 'Department successfully Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;

		function retrieveAbsolute($number){
			if($number < 0){
				$number = abs($number); 
				return '('.number_format($number).')';
				}else{ 
					 return number_format($number); 
					 }
		
		}

		case 'getBankBalance':
			$acct_id = filter_var($_GET['bank_id'], FILTER_SANITIZE_STRING);
			$company =  $_SESSION['COMPANY'];
			$branch = $_SESSION['BRANCH'];

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
					echo $row['account'].'- <span>&#8358;</span>'. retrieveAbsolute($row['balance']);
				} else {
					echo $row['account'].'-'.retrieveAbsolute(0);
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;

			case 'amount' :
				$amount = filter_var($_POST['amount'], FILTER_SANITIZE_STRING);
				$temp_id = filter_var($_POST['temp_id'], FILTER_VALIDATE_INT);
			if (isSet($amount)){
			   	if($amount !=""){
			   mysql_select_db($database_salary, $salary);
			   $updateSQL = sprintf("update  tbl_workingFile SET `value` = %s where temp_id = %s",
			   					   filter_var($_POST['amount'], FILTER_SANITIZE_FLOAT),
			   					   filter_var($_POST['temp_id'], FILTER_SANITIZE_INT));
			   					   
			   		try { 	 // code to try 		   
			     $Result1 = mysql_query($updateSQL, $salary)  ;
			     $source = $_SERVER['HTTP_REFERER'];
					 header('Location: ' . $source);
			   	} 	catch(PDOException $e){	  // error handling }	
			     // error handling }	
			  														}
			   }
			 }
			   break;
			   
			   
		case 'addearning':
			$newearning = filter_var($_POST['eddescription'], FILTER_SANITIZE_STRING);
			$recordtime = date('Y-m-d H:i:s');
		//	$recurrent = filter_var($_POST['recurrent'], FILTER_VALIDATE_INT);

			try{
				$getlast = $conn->prepare('SELECT edDesc FROM tbl_earning_deduction WHERE edDesc = ?');
				$res = $getlast->execute(array($newearning));

				if ($row = $getlast->fetch()) {
			    $_SESSION['alertcolor'] = $type = "danger";
					$msg = "Duplicate Earning not allowed";
					$source = $_SERVER['HTTP_REFERER'];
					redirect($msg, $type, $source);
			        }else{

			
				$query = 'INSERT INTO tbl_earning_deduction (ed,edDesc, edType, status, operator, edCreatedBy,edCreatedDate) VALUES (?,?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($newearning,$newearning,'1', 'Active', '+', $_SESSION['SESS_MEMBER_ID'],$recordtime));

				$_SESSION['msg'] = $msg = 'New earning Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'adddeduction':
			$newearning = filter_var($_POST['eddescription'], FILTER_SANITIZE_STRING);
			//$recurrent = filter_var($_POST['recurrent'], FILTER_VALIDATE_INT);

			try{
				$getlast = $conn->prepare('SELECT edDesc FROM tbl_earning_deduction WHERE edDesc = ?');
				$res = $getlast->execute(array($newearning));

				if ($row = $getlast->fetch()) {
			    $_SESSION['alertcolor'] = $type = "danger";
					$msg = "Duplicate Earning not allowed";
					$source = $_SERVER['HTTP_REFERER'];
					redirect($msg, $type, $source);
			        }else{

			
				$query = 'INSERT INTO tbl_earning_deduction (ed,edDesc, edType, status, operator, edCreatedBy,edCreatedDate) VALUES (?,?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($newearning,$newearning,'2', 'Active', '-', $_SESSION['SESS_MEMBER_ID'],$recordtime));

				$_SESSION['msg'] = $msg = 'New earning Created';
				$_SESSION['alertcolor'] = $type = 'success';
				$source = $_SERVER['HTTP_REFERER'];
				header('Location: ' . $source);
			}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;

		case 'addloanparameter':
					$newearning = filter_var($_POST['newloandesc'], FILTER_SANITIZE_STRING);
					//$recurrent = filter_var($_POST['recurrent'], FILTER_VALIDATE_INT);

					try{
						$getlast = $conn->prepare('SELECT edDesc FROM tbl_earning_deduction WHERE edDesc = ?');
						$res = $getlast->execute(array($newearning));

						if ($row = $getlast->fetch()) {
					    $_SESSION['alertcolor'] = $type = "danger";
							$msg = "Duplicate Earning not allowed";
							$source = $_SERVER['HTTP_REFERER'];
							redirect($msg, $type, $source);
					        }else{

					
						$query = 'INSERT INTO tbl_earning_deduction (ed,edDesc, edType, status, operator, edCreatedBy,edCreatedDate) VALUES (?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($newearning,$newearning,'4', 'Active', '-', $_SESSION['SESS_MEMBER_ID'],$recordtime));

						$_SESSION['msg'] = $msg = 'New earning Created';
						$_SESSION['alertcolor'] = $type = 'success';
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
					}
					}
					catch(PDOException $e){
						echo $e->getMessage();
					}

				break;
				
				case 'addunion':
					$newearning = filter_var($_POST['newunion'], FILTER_SANITIZE_STRING);
					//$recurrent = filter_var($_POST['recurrent'], FILTER_VALIDATE_INT);

					try{
						$getlast = $conn->prepare('SELECT edDesc FROM tbl_earning_deduction WHERE edDesc = ?');
						$res = $getlast->execute(array($newearning));

						if ($row = $getlast->fetch()) {
					    $_SESSION['alertcolor'] = $type = "danger";
							$msg = "Duplicate Earning not allowed";
							$source = $_SERVER['HTTP_REFERER'];
							redirect($msg, $type, $source);
					        }else{

					
						$query = 'INSERT INTO tbl_earning_deduction (ed,edDesc, edType, status, operator, edCreatedBy,edCreatedDate) VALUES (?,?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($newearning,$newearning,'3', 'Active', '-', $_SESSION['SESS_MEMBER_ID'],$recordtime));

						$_SESSION['msg'] = $msg = 'New earning Created';
						$_SESSION['alertcolor'] = $type = 'success';
						$source = $_SERVER['HTTP_REFERER'];
						header('Location: ' . $source);
					}
					}
					catch(PDOException $e){
						echo $e->getMessage();
					}

				break;


		case 'addloan':
			$newloandesc = filter_var($_POST['newloandesc'], FILTER_SANITIZE_STRING);
			//define 900** as the loan ED Code
			try{
				$getlast = $conn->prepare('SELECT edCode FROM earnings_deductions WHERE edType = ? AND companyId = ? AND active = ? ORDER BY id DESC');
				$res = $getlast->execute(array('Loan', $_SESSION['companyid'], '1'));

				if ($row = $getlast->fetch()) {
					$latestcode = intval($row['edCode']);
					$principleinsertcode = $latestcode + 1;
					$repaymentinsertcode = $latestcode + 2;
				}
				$principleinsertdesc = $newloandesc . 'Loan Principle';
				$repaymentinsertdesc = $newloandesc . 'Loan Repayment';
				exit($principleinsertcode . ',' . $repaymentinsertcode);

				$query = 'INSERT INTO earnings_deductions (edCode, edDesc, edType, companyId, active, recurrentEd) VALUES (?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($principleinsertcode, $principleinsertdesc, 'Loan', $_SESSION['companyid'], '1', '0'));

				$query = 'INSERT INTO earnings_deductions (edCode, edDesc, edType, companyId, active, recurrentEd) VALUES (?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($repaymentinsertcode, $repaymentinsertdesc, 'Deduction', $_SESSION['companyid'], '1', '1'));



			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

			exit($newloandesc);
		break;
		
		

	case 'loan_corporate':
			$currentempl = $_POST['curremployee'];
			$edcode = $_POST['newdeductioncodeloan'];
			$earningamount = trim($_POST['monthlyRepayment']);
			$principal = trim($_POST['Principal']);
			$interest = trim($_POST['interest']);
			$recordtime = date('Y-m-d H:i:s');

			try{
				$query = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ?  AND allow_id = ? ');
				$res = $query->execute(array($currentempl, $edcode));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$query2 = 'INSERT INTO tbl_debt (staff_id, allow_id,date_insert, inserted_by,principal,interest) VALUES (?,?,?,?,?,?)';
					$conn->prepare($query2)->execute(array($currentempl, $edcode,$recordtime, $_SESSION['SESS_MEMBER_ID'],$principal,$interest));
					
					
					$query = 'update allow_deduc SET value = ? date_insert = ? , inserted_by = ? where staff_id = ? AND allow_id = ?';
					$conn->prepare($query)->execute(array($earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$currentempl,$edcode));
					
					$_SESSION['alertcolor'] = $type = "danger";
					$msg = "Duplicate Earning not allowed";
					$source = $_SERVER['HTTP_REFERER'];
					redirect($msg, $type, $source);
				} else {
					if ($earningamount > 0 ){
					
					$query2 = 'INSERT INTO tbl_debt (staff_id, allow_id,date_insert, inserted_by,principal,interest) VALUES (?,?,?,?,?,?)';
					$conn->prepare($query2)->execute(array($currentempl, $edcode,$recordtime, $_SESSION['SESS_MEMBER_ID'],$principal,$interest));
					
					
					$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, transcode, date_insert, inserted_by) VALUES (?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($currentempl, $edcode, $earningamount,'2', $recordtime, $_SESSION['SESS_MEMBER_ID']));
					
										
					$_SESSION['msg'] = $msg = "Earning successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					}else {
						
						
						
						$_SESSION['msg'] = $msg = "Employee not Entitiled to the Allowance";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
						
					}
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break; 
		
		case 'cash_corporate':
			$currentempl = $_POST['curremployee'];
			$edcode = $_POST['newCashcodeloan'];
			$earningamount = trim($_POST['cashAmount']);
			$recordtime = date('Y-m-d H:i:s');

			try{
				
					if ($earningamount > 0 ){
					
					$query2 = 'INSERT INTO tbl_repayment (tbl_repayment.staff_id,tbl_repayment.allow_id,tbl_repayment.period,tbl_repayment.cashPay,tbl_repayment.userID,tbl_repayment.editTime) VALUES (?,?,?,?,?,?)';
					$conn->prepare($query2)->execute(array($currentempl, $edcode,$_SESSION['currentactiveperiod'],$earningamount, $_SESSION['SESS_MEMBER_ID'],$recordtime));
									

					$loan = 0;
					$repayment = 0;					
					$query_loan = $conn->prepare('SELECT tbl_debt.loan_id, tbl_debt.staff_id,tbl_debt.allow_id, SUM(ifnull(tbl_debt.principal,0))+SUM(ifnull(tbl_debt.interest,0)) as loan FROM tbl_debt WHERE staff_id = ? AND allow_id = ? ');
	        $res = $query_loan->execute(array($currentempl,$edcode));
	        if ($row = $query_loan->fetch()) {
	            $loan  = $row['loan'] ;
	          }
	          
	          $query_repayment = $conn->prepare('SELECT tbl_repayment.staff_id, tbl_repayment.allow_id, (SUM(ifnull(tbl_repayment.value,0)) + SUM(ifnull(tbl_repayment.cashPay,0))) as repayment FROM tbl_repayment WHERE staff_id = ? AND allow_id = ? ');
	        $res = $query_repayment->execute(array($currentempl,$edcode));
	        if ($row = $query_repayment->fetch()) {
	            $repayment  = $row['repayment'] ;
	          }
					$balance = $loan - $repayment;
					if ($balance == 0){
					$query2 = 'delete from allow_deduc WHERE staff_id = ?  AND allow_id = ? ';
					$conn->prepare($query2)->execute(array($currentempl, $edcode));
					}
					
					$_SESSION['msg'] = $msg = "Cash Payment successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					}else {
					
					$_SESSION['msg'] = $msg = "Error Saving Cash Information";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
						
					}
				
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break; 

		case 'addemployeeearning':
			$currentempl = $_POST['curremployee'];
			if(isset($_POST['newearningcodeAll'])){
				$edcode = $_POST['newearningcodeAll'];
			}elseif (isset($_POST['SelNewContinueLoan'])) {
					$edcode = $_POST['SelNewContinueLoan'];
			}
			
			if(isset($_POST['no_times'])){
				$counter = $_POST['no_times'];//filter_var($_POST['no_times'], FILTER_SANITIZE_INT);
			}else {
				$counter = 0;
			}
			
			
			if(isset($_POST['earningamount'])){
					$earningamount = str_replace(',','',trim($_POST['earningamount']));
			}elseif (isset($_POST['continueDeductionAmount'])) {
						$earningamount = str_replace(',','',trim($_POST['continueDeductionAmount']));
			}
			
			$recordtime = date('Y-m-d H:i:s');
		
			try{
				$query = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ?  AND allow_id = ? ');
				$res = $query->execute(array($currentempl, $edcode));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$query = 'UPDATE allow_deduc SET value = ?, counter = ? WHERE staff_id = ?  AND allow_id = ? ';
					$conn->prepare($query)->execute(array($earningamount,$counter ,$currentempl, $edcode));
					
					auditTrailInsert($currentempl, $edcode, $earningamount, $_SESSION['currentactiveperiod']);
					
					$_SESSION['alertcolor'] = $type = "danger";
					$msg = "Duplicate Earning not allowed";
					$source = $_SERVER['HTTP_REFERER'];
					redirect($msg, $type, $source);
				} else {
					if ($earningamount > -1 ){
						
					$query1 = $conn->prepare('SELECT code, tbl_earning_deduction.ed_id, tbl_earning_deduction.ed FROM tbl_earning_deduction WHERE tbl_earning_deduction.ed_id = ?');
					$res1 = $query1->execute(array($edcode));
					$existtrans1 = $query1->fetch();
					if($existtrans1){
						$transcode =  $existtrans1['code'];
					}	
							
					$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode,counter) VALUES (?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($currentempl, $edcode, $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$transcode,$counter));
					
					auditTrailInsert($currentempl, $edcode, $earningamount, $_SESSION['currentactiveperiod']);
					
					$_SESSION['msg'] = $msg = "Earning successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					}else {
						$_SESSION['msg'] = $msg = "Employee not Entitiled to the Allowance";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
						
					}
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break; 


		case 'addemployeededuction':
			$currentempl = $_POST['curremployee'];
			$edcode = $_POST['newdeductioncode'];
			$deductionamount = trim($_POST['deductionamount']);
			$recordtime = date('Y-m-d H:i:s');
			$counter = $_POST['no_times'];//filter_var($_POST['no_times'], FILTER_SANITIZE_INT);

			try{
				$query = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ?  AND allow_id = ? ');
				$res = $query->execute(array($currentempl, $edcode));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$query = 'UPDATE allow_deduc SET value = ?, date_insert = ?, inserted_by = ?, counter = ? WHERE staff_id = ?  AND allow_id = ? ';
					$conn->prepare($query)->execute(array($deductionamount,$recordtime,$_SESSION['SESS_MEMBER_ID'],$counter,$currentempl, $edcode ));
					$_SESSION['msg'] = $msg = "Deduction UPdated successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				
				} else {
					if ($deductionamount > 0 ){
					$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode,counter) VALUES (?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($currentempl, $edcode, $deductionamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],'02',$counter));
					$_SESSION['msg'] = $msg = "Deduction successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					}else {
					$_SESSION['msg'] = $msg = "Employee not Entitiled to the Deduction";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
						
					}
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break;
		
		
		case 'addemployeedeductionunion':

			$currentempl = $_POST['curremployee'];
			$edcode = $_POST['newdeductioncodeunion'];
			$deductionamount = trim($_POST['deductionamountunion']);
			$recordtime = date('Y-m-d H:i:s');
			$counter = $_POST['no_times'];//filter_var($_POST['no_times'], FILTER_SANITIZE_INT);

			try{
				$query = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ?  AND allow_id = ? ');
				$res = $query->execute(array($currentempl, $edcode));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$query = 'UPDATE allow_deduc SET counter = ?,value = ?, date_insert = ?, inserted_by = ? WHERE staff_id = ?  AND allow_id = ? ';
					$conn->prepare($query)->execute(array($counter,$deductionamount,$recordtime,$_SESSION['SESS_MEMBER_ID'],$currentempl, $edcode ));
					$_SESSION['msg'] = $msg = "Deduction UPdated successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				} else {
					if ($deductionamount > 0 ){
					$query = 'INSERT INTO allow_deduc (counter,staff_id, allow_id, value, date_insert, inserted_by,transcode) VALUES (?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($counter,$currentempl, $edcode, $deductionamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],'02'));
					$_SESSION['msg'] = $msg = "Deduction successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					}else {
					$_SESSION['msg'] = $msg = "Employee not Entitiled to the Deduction";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
						
					}
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break;
		
		case 'newtempemployeededuction':
			$currentempl = $_POST['curremployee'];
			$edtype = $_POST['payType_id'];
			$edcode = $_POST['newdeductioncode'];
			$earningamount = trim($_POST['deductionamount']);
			$counter = $_POST['no_times'];
			$recordtime = date('Y-m-d H:i:s');
			
			$query = $conn->prepare('SELECT * FROM tbl_earning_deduction WHERE ed_id = ?');
	        $res = $query->execute(array($edcode));
	        if ($row = $query->fetch()) {
	            echo($row['operator']);
	          }

			try{
				$query = $conn->prepare('SELECT * FROM tbl_temp WHERE staff_id = ?  AND allow_id = ? ');
				$res = $query->execute(array($currentempl, $edcode));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$query = 'UPDATE tbl_temp SET value = ?, date_insert = ?, inserted_by = ?,counter = ? WHERE staff_id = ?  AND allow_id = ? ';
					$conn->prepare($query)->execute(array($earningamount,$recordtime,$_SESSION['SESS_MEMBER_ID'],$counter,$currentempl, $edcode ));
					$_SESSION['msg'] = $msg = "Temp Deduction / Allowance successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				} else {
					if ($earningamount > 0 ){
					$query = 'INSERT INTO tbl_temp (staff_id, allow_id, value, date_insert, inserted_by,type,counter) VALUES (?,?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($currentempl, $edcode, $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'], $row['operator'], $counter));
					$_SESSION['msg'] = $msg = "Temp Deduction/Earning successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					}else {
					$_SESSION['msg'] = $msg = "Employee not Entitiled to the Deduction";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
						
					}
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break;
		
		
		case 'lastpay':
			
			$currentempl = $_POST['lastPaystaffid'];
			$period = $_POST['currentPeriod'];
			$check = $_POST['check'];
			
			if($check == 1){
				
				$query = $conn->prepare('SELECT * FROM tbl_lastpay WHERE staff_id = ? AND  period = ? ');
				$res = $query->execute(array($currentempl, $period));
				$existtrans = $query->fetch();
				if ($existtrans) {
					
					}else{
				
				
				try{
					
				

					
				$query = 'INSERT INTO tbl_lastpay (staff_id, period) VALUES (?,?)';
				$conn->prepare($query)->execute(array($currentempl, $period));
				$msg = "Last Pay Settings Saved Successfully";
				$type = "success";
				$source = $_SERVER['HTTP_REFERER'];
				redirect($msg, $type, $source);
			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
		}
			}else{
				try{
				$query = 'DELETE FROM tbl_lastpay WHERE staff_id = ? AND  period = ?';
				$conn->prepare($query)->execute(array($currentempl, $period));
				$msg = "Last Pay Settings Saved Successfully";
				$type = "success";
				$source = $_SERVER['HTTP_REFERER'];
				redirect($msg, $type, $source);
			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
				
				
			}
	
			

		break;
		
		case 'cash_cheque':
			
			$currentempl = $_POST['cashstaffid'];
			//$period = $_POST['currentPeriod'];
			$check = $_POST['check'];
			
			if($check == 1){
				
				$query = $conn->prepare('SELECT * FROM tbl_cash_cheque WHERE staff_id = ?');
				$res = $query->execute(array($currentempl));
				$existtrans = $query->fetch();
				if ($existtrans) {
					
					}else{
				
				
				try{
				$query = 'INSERT INTO tbl_cash_cheque (staff_id,bcode,acctno) SELECT staff_id,BCODE,ACCTNO FROM employee WHERE staff_id = ?';
				$conn->prepare($query)->execute(array($currentempl));
				
				$query = 'UPDATE employee SET BCODE = ? WHERE staff_id = ?';
				$conn->prepare($query)->execute(array('00',$currentempl));
				
				$msg = "Employee Pay Method Settings Saved Successfully";
				$type = "success";
				$source = $_SERVER['HTTP_REFERER'];
				redirect($msg, $type, $source);
			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
		}
			}else{
				try{
					
				$query = 'UPDATE employee SET BCODE = (SELECT BCODE FROM tbl_cash_cheque WHERE staff_id = ?) WHERE staff_id = ?';
				$conn->prepare($query)->execute(array($currentempl,$currentempl));	
				
				$query = 'DELETE FROM tbl_cash_cheque WHERE staff_id = ?';
				$conn->prepare($query)->execute(array($currentempl));
				$msg = "Employee Pay Method Settings Saved Successfully";
				$type = "success";
				$source = $_SERVER['HTTP_REFERER'];
				redirect($msg, $type, $source);
			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
				
				
			}
	
			

		break;
		
		case 'addallowance_deduction':
			$deductionname = filter_var($_POST['deductionname'], FILTER_SANITIZE_STRING);
			$newdeductionCode = trim($_POST['newdeductionCode']);
			$recordtime = date('Y-m-d H:i:s');
			if($newdeductionCode < 2){
				$operator = '+';
				}else{
					
				$operator = '-';
				}

			try{
				$query = $conn->prepare('SELECT * FROM tbl_earning_deduction WHERE ed = ? or edDesc = ?');
				$res = $query->execute(array($deductionname,$deductionname));
				$existtrans = $query->fetch();

				if ($existtrans) {
					//same transaction for current employee, current period posted
					$_SESSION['msg'] = $msg = "Deduction Already Existing";
					$_SESSION['alertcolor'] = $type = "danger";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
				} else {
					
					$query = 'INSERT INTO tbl_earning_deduction (ed, edType, edDesc, edCreatedDate,edCreatedBy,operator ) VALUES (?,?,?,?,?,?)';
					$conn->prepare($query)->execute(array($deductionname, $newdeductionCode, $deductionname, $recordtime, $_SESSION['SESS_MEMBER_ID'],$operator));
					$_SESSION['msg'] = $msg = "Earning successfully saved";
					$_SESSION['alertcolor'] = $type = "success";
					$source = $_SERVER['HTTP_REFERER'];
					//redirect($msg, $type, $source);					
					header('Location: ' . $source);
					
											
					}
				
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		break;

		case 'editorganization':
			$compname = filter_var($_POST['compname'], FILTER_SANITIZE_STRING);
			$city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
			$county = filter_var($_POST['county'], FILTER_SANITIZE_STRING);
			$compemail = filter_var((filter_var($_POST['compemail'], FILTER_SANITIZE_STRING)), FILTER_VALIDATE_EMAIL);
			$compphone = filter_var($_POST['compphone'], FILTER_SANITIZE_STRING);
			$companypin = filter_var($_POST['companypin'], FILTER_SANITIZE_STRING);
			$nssfnumber = filter_var($_POST['nssfnumber'], FILTER_SANITIZE_STRING);
			$nhifnumber = filter_var($_POST['nhifnumber'], FILTER_SANITIZE_STRING);

			$startyear = date('Y-m-d', strtotime(filter_var($_POST['startyear'], FILTER_SANITIZE_STRING)));
			$endyear = date('Y-m-d', strtotime(filter_var($_POST['endyear'], FILTER_SANITIZE_STRING)));

			try{
				$query = 'INSERT INTO company (companyName, city, county, companyEmail, contactTelephone, companyPin, companyNssf, companyNhif, companyId, yearStart, yearEnd) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
				$conn->prepare($query)->execute(array($compname, $city, $county, $compemail, $compphone, $companypin, $nssfnumber, $nhifnumber, $_SESSION['companyid'], $startyear, $endyear));
				$msg = "Company Details successfully saved";
				$type = "success";
				$source = $_SERVER['HTTP_REFERER'];
				redirect($msg, $type, $source);
			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}

		break;


		case 'addperiod':
			$periodname = filter_var($_POST['perioddesc'], FILTER_SANITIZE_STRING);
			$periodyear = filter_var($_POST['periodyear'], FILTER_SANITIZE_STRING);
			$periodDescription = $periodname . " " . $periodyear;
			//exit(var_dump(is_int($_SESSION['currentactiveperiod')));
			try{
				//check for replication and create period
				$query = $conn->prepare('SELECT * FROM payperiods WHERE description = ? AND periodYear = ? ');
				$fin = $query->execute(array($periodname, $periodyear));

				if ($row = $query->fetch()){
					$_SESSION['msg'] = "Selected period values already exist.";
					$_SESSION['alertcolor'] = "danger";
					header('Location: ' . $source);
				} else {
					//Get last id in table
					$payp = $conn->prepare('SELECT periodId, description FROM payperiods  ORDER BY id DESC LIMIT 1');
            		$myperiod = $payp->execute();
            		$final = $payp->fetch();

					$workperiod = intval($final['periodId']);
					$insertPayId = $workperiod + 1;

					$query = 'INSERT INTO payperiods (periodId, description, periodYear, active, payrollRun) VALUES (?,?,?,?,?)';
					$conn->prepare($query)->execute(array($insertPayId, $periodname, $periodyear , '1', '0'));
					
					$query2 = 'UPDATE payperiods SET active = 0 WHERE periodId = ?';
					$conn->prepare($query2)->execute(array($workperiod));
					
					
					$_SESSION['msg'] = "New Period Succesfully Created";
					$_SESSION['alertcolor'] = "success";
					header('Location: ' . $source);
				}

			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
		break;


		case 'closeActivePeriod':
			try{

				//reset period id
				//reset assigned active period id

				exit('closeActivePeriod');
			}
			catch(PDOEXception $e){
				echo $e->getMessage();
			}
		break;

		case 'deletecurrentperiod':
					
					$period = filter_var($_GET['activeperiodID'], FILTER_SANITIZE_STRING);
					
				 $payrollquery2 = $conn->prepare('SELECT * FROM payperiods WHERE periodId = ? and payrollRun = ?');
         $payrollquery2->execute(array($period,1));
         //$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
        
     if ($row = $payrollquery2->fetch()){
       try{
       	
       	$query = 'DELETE FROM tbl_devlevy where period = ?';
				$conn->prepare($query)->execute(array($period));

				$query = 'DELETE FROM tbl_master where period = ?';
				$conn->prepare($query)->execute(array($period));
				
				$query = 'DELETE FROM master_staff where period = ?';
				$conn->prepare($query)->execute(array($period));
				
				
				$query = 'DELETE FROM tbl_repayment where period = ?';
				$conn->prepare($query)->execute(array($period));
				
				 $payrollquery2 = $conn->prepare('SELECT completedloan.id, completedloan.type,completedloan.staff_id, completedloan.allow_id, completedloan.period, completedloan.`value` FROM completedloan WHERE period = ?');
         $payrollquery2->execute(array($period));
         $deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         foreach ($deduc as $row => $link2) 
         {
          $query = 'INSERT INTO allow_deduc (staff_id,allow_id,`value`,transcode) VALUES (?,?,?,?)';
					$conn->prepare($query)->execute(array($link2['staff_id'],$link2['allow_id'],$link2['value'],$link2['type']));
          
         }
         $query = 'DELETE FROM completedloan where period = ?';
				 $conn->prepare($query)->execute(array($period));
				 
				 //Update employee status to Active
				 
				 $payrollquery2 = $conn->prepare('SELECT * FROM tbl_lastpay WHERE period = ?');
         $payrollquery2->execute(array($period));
         $deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         foreach ($deduc as $row => $link2) 
         {
          $query = 'UPDATE employee SET STATUSCD = ?  WHERE staff_id = ?';
					$conn->prepare($query)->execute(array('A',$link2['staff_id']));
         
         }
         
				 $query = 'UPDATE payperiods SET payrollRun = ? where periodId = ?';
				 $conn->prepare($query)->execute(array(0,$period));
				
				
				
				$_SESSION['msg'] = $msg = $_SESSION['activeperiodDescription']." Succesfully Deleted.";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);
				
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			}else{
				echo '0';
				
			}
				break;
				
			case 'deletecurrentstaffPayslip':
					
					$period = filter_var($_SESSION['currentactiveperiod'], FILTER_SANITIZE_STRING);
					$staff_id = filter_var($_POST['thisemployee'], FILTER_SANITIZE_STRING);
					
				 $payrollquery2 = $conn->prepare('SELECT * FROM payperiods WHERE periodId = ? and payrollRun = ?');
         $payrollquery2->execute(array($period,1));
         //$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
        
     if ($row = $payrollquery2->fetch()){
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
         foreach ($deduc as $row => $link2) 
         {
         	
         	$query = $conn->prepare('SELECT allow_id FROM allow_deduc WHERE staff_id = ? AND allow_id = ? ');
					$allowCheck = $query->execute(array($link2['staff_id'], $link2['allow_id']));

					if (!$row = $query->fetch()) {
         	
          $query = 'INSERT INTO allow_deduc (staff_id,allow_id,`value`,transcode) VALUES (?,?,?,?)';
					$conn->prepare($query)->execute(array($link2['staff_id'],$link2['allow_id'],$link2['value'],$link2['type']));
        		}
         }
         $query = 'DELETE FROM completedloan where period = ? AND staff_id = ?';
				 $conn->prepare($query)->execute(array($period,$staff_id));
				 
				 //$query = 'UPDATE payperiods SET payrollRun = ? where periodId = ?';
				 //$conn->prepare($query)->execute(array(0,$period));
				
				
				
				$_SESSION['msg'] = $msg = $staff_id." Succesfully Deleted.";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);
				
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}
			}else{
				$_SESSION['msg'] = $msg = "Payroll for {$staff_id} has not been Run.";
				$_SESSION['alertcolor'] = 'warning';
				header('Location: ' . $source);
				
			}
				break;	
				
		case 'activateclosedperiod':
			try{
				$reactivateperiodid = filter_var($_POST['reactivateperiodid'], FILTER_SANITIZE_STRING);
				//exit('activateclosedperiod ' . $reactivateperiodid);

				//Change period session variables
        		$_SESSION['currentactiveperiod'] = $reactivateperiodid;

        			$periodquery = $conn->prepare('SELECT description, periodYear FROM payperiods WHERE periodId = ?');
        			$perres = $periodquery->execute(array($_SESSION['currentactiveperiod']));
        			if ($rowp = $periodquery->fetch()) {
        				$reactivatedperioddesc = $rowp['description'];
        				$reactivatedperiodyear = $rowp['periodYear'];
        			}

        		$_SESSION['activeperiodDescription'] = $reactivatedperioddesc . ' ' . $reactivatedperiodyear;

        			//Ensure all openview status are reset before activating particular one
					$statuschange = $conn->prepare('UPDATE payperiods SET openview = ? ');
					$perres = $statuschange->execute(array('0'));

        			//set openview status
        			$statuschange = $conn->prepare('UPDATE payperiods SET openview = ? WHERE periodId = ?');
        			$perres = $statuschange->execute(array('1', $_SESSION['currentactiveperiod']));
        			$_SESSION['periodstatuschange'] = '1';

        		$_SESSION['msg'] = "You are now viewing data from a closed period. Transactions are not allowed.";
				$_SESSION['alertcolor'] = "success";
				header('Location: ' . $source);

			}
			catch(PDOExcepton $e){
				echo $e->getMessage();
			}
		break;

		
		case 'addNewEmp':
			//check for existing same employee number


					$emp_no = filter_var($_POST['emp_no'], FILTER_SANITIZE_STRING);
					$namee = ucwords(strtolower(strip_tags(addslashes($_POST['namee']))));
					$dept = filter_var($_POST['dept'], FILTER_SANITIZE_STRING);
					$designation = ucwords(strtolower(strip_tags(addslashes($_POST['designation']))));
					$callType = filter_var($_POST['callType'], FILTER_SANITIZE_STRING);
					$grade = filter_var($_POST['grade'], FILTER_SANITIZE_STRING);
					$gradestep = filter_var($_POST['gradestep'], FILTER_SANITIZE_STRING);
					//$doe = date('Y-m-d', strtotime(filter_var($_POST['doe'], FILTER_SANITIZE_STRING)));
					//$dob = date('Y-m-d', strtotime(filter_var($_POST['dob'], FILTER_SANITIZE_STRING)));
					$bank = filter_var($_POST['bank'], FILTER_SANITIZE_STRING);
					$acct_no = filter_var($_POST['acct_no'], FILTER_SANITIZE_STRING);
					$pfa = filter_var($_POST['pfa'], FILTER_SANITIZE_STRING);
					$rsa_pin = filter_var($_POST['rsa_pin'], FILTER_SANITIZE_STRING);
					$recordtime	= $recordtime = date('Y-m-d H:i:s');
					$ppno = filter_var($_POST['ppno'], FILTER_SANITIZE_STRING);
					$doe = date('Y-m-d', strtotime(filter_var($_POST['doe'], FILTER_SANITIZE_STRING)));
					$dob = date('Y-m-d', strtotime(filter_var($_POST['dob'], FILTER_SANITIZE_STRING)));
					$dopa = date('Y-m-d', strtotime(filter_var($_POST['dopa'], FILTER_SANITIZE_STRING)));
					
			//validate for empty mandatory fields

			try{
				//check for replication and create period
				
				
					if($_POST['doe'] == ''){
							$doe = NULL;
						}
						if($_POST['dob'] == ''){
							$dob = NULL;
						}
						if($_POST['dopa'] == ''){
							$dopa = NULL;
						}



					$query = 'INSERT INTO employee (employee.PPNO,employee.staff_id,employee.`NAME`, employee.EMPDATE, employee.DEPTCD, employee.POST, employee.GRADE, employee.STEP, employee.ACCTNO, employee.BCODE, employee.CALLTYPE, employee.STATUSCD, employee.PFACODE, employee.PFAACCTNO,userID,editTime,employee.DOPA,employee.DOB) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
				
					$conn->prepare($query)->execute(array($ppno,$emp_no, $namee, $doe, $dept, $designation, $grade, $gradestep, $acct_no, $bank, $callType, 'A', $pfa, $rsa_pin, $_SESSION['SESS_MEMBER_ID'], $recordtime, $dopa,$dob));
					
					
					// Auto add Allowances
					$payrollquery2 = $conn->prepare('SELECT * FROM tbl_autoinsert WHERE dept_id = ? AND type = 1');
         	$payrollquery2->execute(array($dept));
         	$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         	foreach ($deduc as $row => $link2) 
         	{
         		$earningamount = getAmount($emp_no,$link2['allow_id'],$grade,$gradestep,$callType);
          	$earningamount = str_replace(',','',trim($earningamount));
          	$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($emp_no, $link2['allow_id'], $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$link2['type']));
						
						auditTrailInsert($emp_no, $link2['allow_id'], $earningamount, $_SESSION['currentactiveperiod']);
         	}
         	
         	if(($grade > 6) and (($dept != 5)||($dept != 14||($dept != 24)||($dept != 32))||($dept != 40))){
         		
         		$earningamount = getAmount($emp_no,23,$grade,$gradestep,$callType);
          	$earningamount = str_replace(',','',trim($earningamount));
          	
          	$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($emp_no, 23, $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$link2['type']));
						
         	}
         	//Auto add Deductions
         	$payrollquery2 = $conn->prepare('SELECT * FROM tbl_autoinsert WHERE dept_id = ? AND type = 2');
         	$payrollquery2->execute(array($dept));
         	$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         	foreach ($deduc as $row => $link2) 
         	{
          	$earningamount = getAmount($emp_no,$link2['allow_id'],$grade,$gradestep,$callType);
          	$earningamount = str_replace(',','',trim($earningamount));
          	
          	$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($emp_no, $link2['allow_id'], $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$link2['type']));
						
						auditTrailInsert($emp_no, $link2['allow_id'], $earningamount, $_SESSION['currentactiveperiod']);
         	}
         	
//         	if($callType > 0){
//         		
//         		$earningamount = getAmount(21,$grade,$gradestep,$callType);
//          	$earningamount = str_replace(',','',trim($earningamount));
//          	
//          	$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode) VALUES (?,?,?,?,?,?)';
//						$conn->prepare($query)->execute(array($emp_no, 21, $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],$link2['type']));
//						
//         	}
         	
         	
         	

					$_SESSION['msg'] = $msg = "Employee Successfully added.";
					$_SESSION['alertcolor'] = 'success';
					$_SESSION['emptNumTack'] = $emp_no;
					header('Location: ' . $source);
					//redirect($msg,$type,$source);

				
			
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;
		
		case 'upgradeGrade_Step':
			//check for existing same employee number


					$emp_no = filter_var($_POST['curremployee'], FILTER_SANITIZE_STRING);
					$callType = filter_var($_POST['callType'], FILTER_SANITIZE_STRING);
					$grade = filter_var($_POST['new_grade'], FILTER_SANITIZE_STRING);
					$gradestep = filter_var($_POST['new_step'], FILTER_SANITIZE_STRING);
					$oldstep = filter_var($_POST['step'], FILTER_SANITIZE_STRING);
					$oldgrade = filter_var($_POST['grade'], FILTER_SANITIZE_STRING);
					$recordtime	= $recordtime = date('Y-m-d H:i:s');
					
					
			

			try{
				
					$payrollquery2 = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ? and transcode = ?');
         	$payrollquery2->execute(array($emp_no,1));
         	$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         	foreach ($deduc as $row => $link2) 
         	{
         		$earningamount = getAmount($emp_no,$link2['allow_id'],$grade,$gradestep,$callType);
          	$earningamount = str_replace(',','',trim($earningamount));
          	if($earningamount > 0){
          	$query = 'UPDATE allow_deduc SET `value` = ? WHERE staff_id = ? and allow_id = ?';
						$conn->prepare($query)->execute(array($earningamount,$emp_no, $link2['allow_id']));
						
						auditTrailInsert($emp_no, $link2['allow_id'], $earningamount, $_SESSION['currentactiveperiod']);
         	}
         	}
         	//update deductions
         	$payrollquery2 = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ? and transcode = ?');
         	$payrollquery2->execute(array($emp_no,2));
         	$deduc = $payrollquery2->fetchAll(PDO::FETCH_ASSOC);
         	foreach ($deduc as $row => $link2) 
         	{
         		$earningamount = getAmount($emp_no,$link2['allow_id'],$grade,$gradestep,$callType);
          	$earningamount = str_replace(',','',trim($earningamount));
          	if($earningamount > 0){
          	$query = 'UPDATE allow_deduc SET `value` = ? WHERE staff_id = ? and allow_id = ?';
						$conn->prepare($query)->execute(array($earningamount,$emp_no, $link2['allow_id']));
						
						auditTrailInsert($emp_no, $link2['allow_id'], $earningamount, $_SESSION['currentactiveperiod']);
         	}
         	}
         	
         	 if ($oldgrade < 5 & $grade > 6){
         		$earningamount = getAmount($emp_no,23,$grade,$gradestep,$callType);
          	$earningamount = str_replace(',','',trim($earningamount));
          	
          	$query = $conn->prepare('SELECT * FROM allow_deduc WHERE staff_id = ? and allow_id = ?');
														$res = $query->execute(array($emp_no,23));
														$existtrans = $query->fetch();
														if ($existtrans) {
															$query = 'UPDATE allow_deduc SET `value` = ? WHERE staff_id = ? and allow_id = ?';
															$conn->prepare($query)->execute(array($earningamount,$emp_no, 23));
																
															}else{
          	$query = 'INSERT INTO allow_deduc (staff_id, allow_id, value, date_insert, inserted_by,transcode) VALUES (?,?,?,?,?,?)';
						$conn->prepare($query)->execute(array($emp_no, 23, $earningamount, $recordtime, $_SESSION['SESS_MEMBER_ID'],1));
						
						auditTrailInsert($emp_no, 23, $earningamount, $_SESSION['currentactiveperiod']);
         	
        }
         	}
         			$query = $conn->prepare('UPDATE employee SET STEP = ?, GRADE = ? WHERE staff_id = ?');
					  	$res = $query->execute(array($gradestep, $grade, $emp_no));
         	$_SESSION['msg'] = $msg = "Employee Successfully added.";
					$_SESSION['alertcolor'] = 'success';
					header('Location: ' . $source);
					//redirect($msg,$type,$source);

				
			
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;
		
		
		case 'updateEmp':
					//check for existing same employee number
					echo $_POST['dob'];

					$emp_no = filter_var($_POST['emp_no'], FILTER_SANITIZE_STRING);
					$namee = ucwords(strtolower(strip_tags(addslashes($_POST['namee']))));
					$dept = filter_var($_POST['dept'], FILTER_SANITIZE_STRING);
					$post = ucwords(strtolower(strip_tags(addslashes($_POST['post']))));
					$callType = filter_var($_POST['callType'], FILTER_SANITIZE_STRING);
					$grade = filter_var($_POST['grade'], FILTER_SANITIZE_STRING);
					$gradestep = filter_var($_POST['gradestep'], FILTER_SANITIZE_STRING);
					//$doe = $_POST['doe']; //date('Y-m-d', strtotime(filter_var($_POST['doe'], FILTER_SANITIZE_STRING)));
					//$dob = $_POST['dob']; //date('Y-m-d', strtotime(filter_var($_POST['dob'], FILTER_SANITIZE_STRING)));
					$ppno = filter_var($_POST['ppno'], FILTER_SANITIZE_STRING);
					
//					if($_POST['doe']!= ''){
//					$Old_doe = explode('-',$_POST['doe']);
//					$_POST['doe'] = $Old_doe[2].'-'.$Old_doe[1].'-'.$Old_doe[0];
//					}
//					if($_POST['dob']!= ''){
//					$Old_dob = explode('-',$_POST['dob']);
//					$_POST['doe'] = $Old_dob[2].'-'.$Old_dob[1].'-'.$Old_dob[0];
//					}
//					if($_POST['dopa']!= ''){
//					$Old_dopa = explode('-',$_POST['dopa']);
//					$_POST['dopa'] = $Old_dopa[2].'-'.$Old_dopa[1].'-'.$Old_dopa[0];
//					}
					
					$doe = date('Y-m-d', strtotime(filter_var($_POST['doe'], FILTER_SANITIZE_STRING)));
					$dob = date('Y-m-d', strtotime(filter_var($_POST['dob'], FILTER_SANITIZE_STRING)));
					$dopa = date('Y-m-d', strtotime(filter_var($_POST['dopa'], FILTER_SANITIZE_STRING)));
					
					$bank = filter_var($_POST['bank'], FILTER_SANITIZE_STRING);
					$acct_no = filter_var($_POST['acct_no'], FILTER_SANITIZE_STRING);
					$pfa = filter_var($_POST['pfa'], FILTER_SANITIZE_STRING);
					$rsa_pin = filter_var($_POST['rsa_pin'], FILTER_SANITIZE_STRING);
					
					
			

					try{
						//check for replication and create period
						if($_POST['doe'] == ''){
							$doe = NULL;
						}
						if($_POST['dob'] == ''){
							$dob = NULL;
						}
						if($_POST['dopa'] == ''){
							$dopa = NULL;
						}
						

						
							$query = 'UPDATE employee SET employee.PPNO = ? , employee.`NAME` = ? ,employee.POST = ?, employee.DEPTCD = ?, employee.CALLTYPE = ?, employee.GRADE = ?, employee.STEP = ?, employee.EMPDATE = ?, employee.BCODE = ?, employee.ACCTNO = ?, employee.PFACODE = ?, employee.PFAACCTNO = ?,  employee.dopa = ?, employee.DOB = ? WHERE staff_id = ?';
							//$query = 'INSERT INTO employees (empNumber, fName, lName, gender, idNumber, companyId, companyDept, companyBranch, empType, dob, citizenship, empTaxPin, empNssf, empNhif, empEmplDate, empPosition, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
						
							$conn->prepare($query)->execute(array($ppno,$namee,$post, $dept,  $callType, $grade, $gradestep,$doe,$bank,$acct_no,$pfa,$rsa_pin,$dopa,$dob,$emp_no));
							
							$query = $conn->prepare('SELECT * FROM payperiods WHERE payrollRun = ? and periodId = ?');
							$fin = $query->execute(array(1, $_SESSION['currentactiveperiod']));
							$existtrans = $query->fetch();

							if ($existtrans) { 
							$query = $conn->prepare('UPDATE master_staff SET PFACODE = ?,	PFAACCTNO = ? WHERE staff_id = ? and period = ?');
							$fin = $query->execute(array($pfa,$rsa_pin,$emp_no, $_SESSION['currentactiveperiod']));
			
			}
							

							$_SESSION['msg'] = $msg = "Employee Successfully updated.";
							$_SESSION['alertcolor'] = 'success';
						//	header('Location: ' . $source);
							//redirect($msg,$type,$source);

						
					
					}
					catch(PDOException $e){
						echo $e->getMessage();
					}

				break;

		case 'getPreviousEmployee':
			
			$_SESSION['emptrack'] =$_SESSION['emptrack'] - 1;
			header('Location: ' . $source);
		break;


		case 'getNextEmployee':
			
			$_SESSION['emptrack'] =$_SESSION['emptrack'] + 1;
			$_SESSION['empDataTrack'] = 'next';
			header('Location: ' . $source);

		break;


		case 'retrieveLeaveData':

			$leavestate = filter_var($_GET['state'], FILTER_SANITIZE_STRING);
			$_SESSION['leavestate'] = $leavestate;

			header('Location: ' . $source);


		break;

		case 'retrieveSingleEmployeeData':
							
								if(isset($_POST['item'])){
									
									$_POST['item'] = $_POST['item'];
								}else{
									
									$_POST['item'] = -1;
								}
                $empnumber = filter_var($_POST['item'], FILTER_SANITIZE_STRING);
                $_SESSION['empDataTrack'] = 'option';
                $_SESSION['emptNumTack'] = $empnumber; 

                header('Location: ' . $source);

        break;

			case 'retrieveSingleEmployee1':

			                $empnumber = filter_var($_POST['item'], FILTER_SANITIZE_STRING);
			                $_SESSION['empDataTrack'] = 'option';
			                $_SESSION['emptNumTack'] = $empnumber;
											//$source = 'employee.php?staff_id=$empnumber';
											//echo $source;
			                header('Location: ' . $source);

			        break;

		case 'vtrans':
			$empRecordId = filter_var($_GET['td'], FILTER_SANITIZE_STRING);
			//exit($empRecordId);
			$_SESSION['empDataTrack'] = 'option';
			$_SESSION['emptNumTack'] = $empRecordId;

			header('Location: ../empearnings.php');
		break;

		
		case 'runCurrentEmployeePayroll':
			
			define('TAX_RELIEF', '1280' );

			$thisemployee = filter_var($_POST['thisemployee'], FILTER_SANITIZE_STRING);

			//check if employee has basic salary, if not return error & exit
			$query = $conn->prepare('SELECT earningDeductionCode FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ? ');
			$rerun = $query->execute(array($thisemployee, $_SESSION['companyid'], '200', $_SESSION['currentactiveperiod'], '1'));

			if (!$row = $query->fetch()) {
				$_SESSION['msg'] = $msg = "This employee has no basic salary. Please assign basic salary in order to process employee's earnings.";
				$_SESSION['alertcolor'] = 'danger';
				header('Location: ' . $source);
			} else {

				//check if employee rerun
				try{
					$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ? ');
					$rerun = $query->execute(array($thisemployee, $_SESSION['companyid'], '601', $_SESSION['currentactiveperiod'], '1'));

					if ($row = $query->fetch()) {

						$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
	                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Earning', $_SESSION['currentactiveperiod'], '1'));
	                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
	                    $thisemployeeearnings = 0;

	                    foreach ($res as $row => $link) {
	                    	$thisemployeeearnings = $thisemployeeearnings + $link['amount'];
	                    }

	                    $recordtime = date('Y-m-d H:i:s');
	                    	//Run with an update query
	                    $grossquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
						$conn->prepare($grossquery)->execute(array($thisemployeeearnings, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '601',  $_SESSION['currentactiveperiod'], '1'));

						//NHIF Bands
								if ($thisemployeeearnings > 0 && $thisemployeeearnings < 5999) {
									$thisEmpNhif = 150;
								} elseif ($thisemployeeearnings > 5999 && $thisemployeeearnings <= 7999) {
									$thisEmpNhif = 300;
								} elseif ($thisemployeeearnings > 7999 && $thisemployeeearnings <= 11999) {
									$thisEmpNhif = 400;
								} elseif ($thisemployeeearnings > 11999 && $thisemployeeearnings <= 14999) {
									$thisEmpNhif = 500;
								} elseif ($thisemployeeearnings > 14999 && $thisemployeeearnings <= 19999) {
									$thisEmpNhif = 600;
								} elseif ($thisemployeeearnings > 19999 && $thisemployeeearnings <= 24999) {
									$thisEmpNhif = 750;
								} elseif ($thisemployeeearnings > 24999 && $thisemployeeearnings <= 29999) {
									$thisEmpNhif = 850;
								} elseif ($thisemployeeearnings > 29999 && $thisemployeeearnings <= 34999) {
									$thisEmpNhif = 900;
								} elseif ($thisemployeeearnings > 34999 && $thisemployeeearnings <= 39999) {
									$thisEmpNhif = 950;
								} elseif ($thisemployeeearnings > 39999 && $thisemployeeearnings <= 44999) {
									$thisEmpNhif = 1000;
								} elseif ($thisemployeeearnings > 44999 && $thisemployeeearnings <= 49999) {
									$thisEmpNhif = 1100;
								} elseif ($thisemployeeearnings > 49999 && $thisemployeeearnings <= 59999) {
									$thisEmpNhif = 1200;
								} elseif ($thisemployeeearnings > 59999 && $thisemployeeearnings <= 69999) {
									$thisEmpNhif = 1300;
								} elseif ($thisemployeeearnings > 69999 && $thisemployeeearnings <= 79999) {
									$thisEmpNhif = 1400;
								} elseif ($thisemployeeearnings > 79999 && $thisemployeeearnings <= 89999) {
									$thisEmpNhif = 1500;
								} elseif ($thisemployeeearnings > 89999 && $thisemployeeearnings <= 99999) {
									$thisEmpNhif = 1600;
								} elseif ($thisemployeeearnings > 99999) {
									$thisEmpNhif = 1700;
								}

							$nhifquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($nhifquery)->execute(array($thisEmpNhif, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '481',  $_SESSION['currentactiveperiod'], '1'));

						//NSSF is standard. No recalculation
							$thisemployeeNssfBand1 = 200;
						//Compute Taxable Income
							$thisEmpTaxablePay = $thisemployeeearnings - $thisemployeeNssfBand1;
							$taxpayquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($taxpayquery)->execute(array($thisEmpTaxablePay, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '400',  $_SESSION['currentactiveperiod'], '1'));

						//Compute PAYE
							$employeepayee = 0;
							$taxpay = $thisEmpTaxablePay;
							if ($taxpay > 0 && $taxpay <= 11180) {
								$employeepayee = $taxpay * 0.1;
							} elseif ($taxpay > 11180 && $taxpay <= 21714) {
								$employeepayee = (11180 * 0.1) + (($taxpay - 11180)*0.15);
							} elseif ($taxpay > 21714 && $taxpay <= 32248) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (($taxpay - 11181 - 10533)*0.2);
							} elseif ($taxpay > 32248 && $taxpay <= 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (($taxpay - 11181 - 10533 - 10534)*0.25);
							} elseif ($taxpay > 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (10534 * 0.25) + (($taxpay - 11181 - 10533 - 10534 - 10534)*0.3);
							}

							$taxcharged = $employeepayee;
							$taxchargequery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($taxchargequery)->execute(array($taxcharged, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '399',  $_SESSION['currentactiveperiod'], '1'));


							$finalEmployeePayee = $employeepayee - TAX_RELIEF;

							if ($finalEmployeePayee  <= 0) {
								$finalEmployeePayee = 0;
							}

							$taxpayequery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($taxpayequery)->execute(array($finalEmployeePayee, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '550',  $_SESSION['currentactiveperiod'], '1'));


						//Fetch and populate all deductions and write total
							$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
		                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', $_SESSION['currentactiveperiod'], '1'));
		                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
		                    $thisemployeeearnings = 0;

		                    foreach ($res as $row => $link) {
		                    	$thisemployeedeductions = $thisemployeedeductions + $link['amount'];
		                    }

		                    $recordtime = date('Y-m-d H:i:s');
		                    $deductionsquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($deductionsquery)->execute(array($thisemployeedeductions, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '603',  $_SESSION['currentactiveperiod'], '1'));

						//Calculate Net Salary
							$thisemployeeNet = $thisEmpTaxablePay - $thisemployeedeductions;

							$netquery = 'UPDATE employee_earnings_deductions SET amount = ?, editTime = ?, userId = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
							$conn->prepare($netquery)->execute(array($thisemployeeNet, $recordtime, $_SESSION['user'], $thisemployee, $_SESSION['companyid'], '600',  $_SESSION['currentactiveperiod'], '1'));


						$_SESSION['msg'] = 'Employee payroll re-run successful';
						$_SESSION['alertcolor'] = 'success';
						//echo $thisemployeeearnings;
						//exit("Re run");
						header('Location: ' . $source);
					}
					else {
						//new; insert records
						//Fetch and populate all taxable earnings and write total
							$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
		                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Earning', $_SESSION['currentactiveperiod'], '1'));
		                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
		                    $thisemployeeearnings = 0;

		                    foreach ($res as $row => $link) {
		                    	$thisemployeeearnings = $thisemployeeearnings + $link['amount'];
		                    }

		                    $recordtime = date('Y-m-d H:i:s');
		                    $grossquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($grossquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '601', $thisemployeeearnings, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//Get initial statutories - NHIF, NSSF, Tax relief
							//NHIF Bands
								if ($thisemployeeearnings > 0 && $thisemployeeearnings < 5999) {
									$thisEmpNhif = 150;
								} elseif ($thisemployeeearnings > 5999 && $thisemployeeearnings <= 7999) {
									$thisEmpNhif = 300;
								} elseif ($thisemployeeearnings > 7999 && $thisemployeeearnings <= 11999) {
									$thisEmpNhif = 400;
								} elseif ($thisemployeeearnings > 11999 && $thisemployeeearnings <= 14999) {
									$thisEmpNhif = 500;
								} elseif ($thisemployeeearnings > 14999 && $thisemployeeearnings <= 19999) {
									$thisEmpNhif = 600;
								} elseif ($thisemployeeearnings > 19999 && $thisemployeeearnings <= 24999) {
									$thisEmpNhif = 750;
								} elseif ($thisemployeeearnings > 24999 && $thisemployeeearnings <= 29999) {
									$thisEmpNhif = 850;
								} elseif ($thisemployeeearnings > 29999 && $thisemployeeearnings <= 34999) {
									$thisEmpNhif = 900;
								} elseif ($thisemployeeearnings > 34999 && $thisemployeeearnings <= 39999) {
									$thisEmpNhif = 950;
								} elseif ($thisemployeeearnings > 39999 && $thisemployeeearnings <= 44999) {
									$thisEmpNhif = 1000;
								} elseif ($thisemployeeearnings > 44999 && $thisemployeeearnings <= 49999) {
									$thisEmpNhif = 1100;
								} elseif ($thisemployeeearnings > 49999 && $thisemployeeearnings <= 59999) {
									$thisEmpNhif = 1200;
								} elseif ($thisemployeeearnings > 59999 && $thisemployeeearnings <= 69999) {
									$thisEmpNhif = 1300;
								} elseif ($thisemployeeearnings > 69999 && $thisemployeeearnings <= 79999) {
									$thisEmpNhif = 1400;
								} elseif ($thisemployeeearnings > 79999 && $thisemployeeearnings <= 89999) {
									$thisEmpNhif = 1500;
								} elseif ($thisemployeeearnings > 89999 && $thisemployeeearnings <= 99999) {
									$thisEmpNhif = 1600;
								} elseif ($thisemployeeearnings > 99999) {
									$thisEmpNhif = 1700;
								}

			                    $nhifquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
								$conn->prepare($nhifquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', '481', $thisEmpNhif, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//NSSF Band Calculation
							$thisemployeeNssfBand1 = 200;

							/*$thisemployeeNssfBand1 = $thisemployeeearnings * 0.06;
							if ($thisemployeeNssfBand1 > 360) {
								$thisemployeeNssfBand1 = 360;
							}*/
							$nssfquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($nssfquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', '482', $thisemployeeNssfBand1, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//Compute Taxable Income
							$thisEmpTaxablePay = $thisemployeeearnings - $thisemployeeNssfBand1;
							$taxpayquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($taxpayquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '400', $thisEmpTaxablePay, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						
						//Compute PAYE
							$employeepayee = 0;
							$taxpay = $thisEmpTaxablePay;
							if ($taxpay > 0 && $taxpay <= 11180) {
								$employeepayee = $taxpay * 0.1;
							} elseif ($taxpay > 11180 && $taxpay <= 21714) {
								$employeepayee = (11180 * 0.1) + (($taxpay - 11180)*0.15);
							} elseif ($taxpay > 21714 && $taxpay <= 32248) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (($taxpay - 11181 - 10533)*0.2);
							} elseif ($taxpay > 32248 && $taxpay <= 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (($taxpay - 11181 - 10533 - 10534)*0.25);
							} elseif ($taxpay > 42782) {
								$employeepayee = (11180 * 0.1) + (10534 * 0.15) + (10534 * 0.2) + (10534 * 0.25) + (($taxpay - 11181 - 10533 - 10534 - 10534)*0.3);
							}

							$taxcharged = $employeepayee;
							$taxchargequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($taxchargequery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '399', $taxcharged, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

							$finalEmployeePayee = $employeepayee - TAX_RELIEF;

							if ($finalEmployeePayee  <= 0) {
								$finalEmployeePayee = 0;
							}

							$taxpayequery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($taxpayequery)->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', '550', $finalEmployeePayee, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));


						//Fetch and populate all deductions and write total
							$query = $conn->prepare('SELECT * FROM employee_earnings_deductions WHERE employeeId = ? AND companyId = ? AND transactionType = ? AND payPeriod = ? AND active = ? ');
		                    $fin = $query->execute(array($thisemployee, $_SESSION['companyid'], 'Deduction', $_SESSION['currentactiveperiod'], '1'));
		                    $res = $query->fetchAll(PDO::FETCH_ASSOC);
		                    $thisemployeedeductions = 0;

		                    foreach ($res as $row => $link) {
		                    	$thisemployeedeductions = $thisemployeedeductions + $link['amount'];
		                    }

		                    $recordtime = date('Y-m-d H:i:s');
		                    $deductionsquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($deductionsquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '603', $thisemployeedeductions, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						//Calculate Net Salary
							$thisemployeeNet = $thisEmpTaxablePay - $thisemployeedeductions;

							$netquery = 'INSERT INTO employee_earnings_deductions (employeeId, companyId, transactionType, earningDeductionCode, amount, payPeriod, standardRecurrent, active, editTime, userId) VALUES (?,?,?,?,?,?,?,?,?,?)';
							$conn->prepare($netquery)->execute(array($thisemployee, $_SESSION['companyid'], 'Calc', '600', $thisemployeeNet, $_SESSION['currentactiveperiod'], '0', '1', $recordtime, $_SESSION['user']));

						$_SESSION['msg'] = 'Employee payroll run successful';
						$_SESSION['alertcolor'] = 'success';
						header('Location: ' . $source);

					}	
				}

				catch(PDOException $e){
					echo $e->getMessage();
				}
			}
			
			

		break;


		case 'runGlobalPayroll':
			ini_set('max_execution_time','0');
 			$connect = mysqli_connect("localhost", "emmaggic_root", "Oluwaseyi", "colerine"); 
 			//include_once('functions.php'); 
 			//session_start();
   		
   		
				$period = $_SESSION['currentactiveperiod'];
				 
 			 	 
		           
		   $query = $conn->prepare('SELECT * FROM payperiods WHERE payrollRun = ? and periodId = ?');
		    $fin = $query->execute(array(0, $_SESSION['currentactiveperiod']));
				$existtrans = $query->fetch();

		if ($existtrans) {       
 	 
        try{ //echo $period ;
              global $conn;
              $query = $conn->prepare('SELECT * FROM employee WHERE STATUSCD = ?');
              $res = $query->execute(array('A'));
              $out = $query->fetchAll(PDO::FETCH_ASSOC);
               //get employee info                                          
             while ($row = array_shift($out)) 
             {
             		$queryMaster = $conn->prepare('INSERT INTO master_staff (staff_id,NAME,DEPTCD,BCODE,ACCTNO,GRADE,STEP,period,PFACODE,PFAACCTNO) VALUES (?,?,?,?,?,?,?,?,?,?)');
              	$master = $queryMaster->execute(array($row['staff_id'],$row['NAME'],$row['DEPTCD'],$row['BCODE'],$row['ACCTNO'],$row['GRADE'],$row['STEP'],$period,$row['PFACODE'],$row['PFAACCTNO']));
             	
              	echo 'staff id'.' '.$row['staff_id'].'<br>';
              	$query_allow = $conn->prepare('SELECT allow_deduc.temp_id, allow_deduc.staff_id, allow_deduc.allow_id, allow_deduc.`value`, allow_deduc.transcode, allow_deduc.counter,  allow_deduc.running_counter, allow_deduc.inserted_by, allow_deduc.date_insert,tbl_earning_deduction.edDesc FROM allow_deduc
 																				INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = allow_deduc.allow_id WHERE staff_id = ? and transcode = ? order by allow_deduc.allow_id asc');
              		$res_allow = $query_allow->execute(array($row['staff_id'],'1'));
              		$out_allow = $query_allow->fetchAll(PDO::FETCH_ASSOC);
              	while ($row_allow = array_shift($out_allow)) {
              		
              		
					  			if($row_allow['allow_id'] == '21' )
					  			{
              			
              			$query_value = $conn->prepare('SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = ? AND allowancetable.step = ? AND allowcode = ? AND category = ?');
										$rerun_value = $query_value->execute(array($row['GRADE'],$row['STEP'],$row_allow['allow_id'],$row['CALLTYPE']));
									}else
									{
										
              		$query_value = $conn->prepare('SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.grade = ? AND allowancetable.step = ? AND allowcode = ?');
									$rerun_value = $query_value->execute(array($row['GRADE'],$row['STEP'],$row_allow['allow_id']));
									}
									

									if ($row_value = $query_value->fetch()) 
									{
										$output = $row_value['value'];
					  
									}else
									{

										$output = $row_allow['value'];
									}
              			
						  
									  echo $row_allow['allow_id'].' '.$row_allow['edDesc'].' '.number_format($output).'<br>';
									  try{
										$recordtime = date('Y-m-d H:i:s');
										$query = 'INSERT INTO tbl_master (staff_id, allow_id, allow, type, period,editTime,userID) VALUES (?,?,?,?,?,?,?)';
										$conn->prepare($query)->execute(array($row['staff_id'], $row_allow['allow_id'], $output, '1',  $period,$recordtime,$_SESSION['SESS_MEMBER_ID']));
				
										}
										catch(PDOException $e){
											echo $e->getMessage();
										}
              			if(intval($row_allow['counter']) > 0){
              				echo 'allowance deduction counter check';
              				$running_counter = intval($row_allow['running_counter']);
              				$running_counter = $running_counter + 1;
              				if(($running_counter) == intval($row_allow['counter'])){
              					
              					 $query = 'INSERT INTO completedLoan (staff_id,allow_id,period,value,type)VALUES (?,?,?,?,?)';
												 $conn->prepare($query)->execute(array($row['staff_id'],$row_allow['allow_id'],$period,$output,'1'));
              					 
              					 //delete allow once cycle is complete
              					 $sqlDelete = "DELETE FROM allow_deduc WHERE temp_id = '".$row_allow['temp_id']."'";
              					 $conn->exec($sqlDelete);
              					 
              				}else{
              					$sqlUpdate = "update allow_deduc set running_counter = '".$running_counter."' WHERE temp_id = '".$row_allow['temp_id']."'";
              					$conn->exec($sqlUpdate);
              					
              				}
              			
											
					 					}
								}
					
					
			 					 // deduction process
			  
			  				
						  $total_rows = '';
						  
						  $query_deduct = $conn->prepare('SELECT allow_deduc.temp_id, allow_deduc.staff_id, allow_deduc.allow_id, allow_deduc.`value`, allow_deduc.transcode, allow_deduc.counter,  allow_deduc.running_counter, allow_deduc.inserted_by, allow_deduc.date_insert,tbl_earning_deduction.edDesc,tbl_earning_deduction.edType FROM allow_deduc
																			 INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.ed_id = allow_deduc.allow_id WHERE staff_id = ? and transcode = ? order by allow_deduc.allow_id asc');
						  $res_deduct = $query_deduct->execute(array($row['staff_id'],'2'));
						  $out_deduct = $query_deduct->fetchAll(PDO::FETCH_ASSOC);
			  			while ($row_deduct = array_shift($out_deduct)) 
			  			{
			  			$output = 0;
			  			//Process Normal deduction
			  				if(intval($row_deduct['edType']) == '2'){
			  					
			  					if(intval($row_deduct['allow_id'])== 50)
									{//process pension
									$sql_consolidated = "SELECT allowancetable.`value` FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $row['GRADE'] ."' and step = '". $row['STEP'] ."'";
									$result_consolidated = mysqli_query($connect, $sql_consolidated);
									$row_consolidated = mysqli_fetch_assoc($result_consolidated);
									$total_rowsConsolidated = mysqli_num_rows($result_consolidated);
									
									$sql_pensionRate = "SELECT (pension.PENSON/100) as rate FROM pension WHERE grade = '". $row['GRADE'] ."' and step = '". $row['STEP'] ."'";
									$result_pensionRate = mysqli_query($connect, $sql_pensionRate);
									$row_pensionRate = mysqli_fetch_assoc($result_pensionRate);
									$total_pensionRate = mysqli_num_rows($result_pensionRate);
									
									$output = ceil($row_consolidated['value']*$row_pensionRate['rate']);
									//echo $output;	

									}else
									{
										$output = $row_deduct['value'];
									}
									//Save into db
									//echo $row_allow['allow_id'].' '.$row_allow['edDesc'].' '.number_format($output).'<br>';
									try{
										$recordtime = date('Y-m-d H:i:s');
										$query = 'INSERT INTO tbl_master (staff_id, allow_id, deduc, type, period,editTime,userID) VALUES (?,?,?,?,?,?,?)';
										$conn->prepare($query)->execute(array($row['staff_id'], $row_deduct['allow_id'], $output, '2',  $period,$recordtime,$_SESSION['SESS_MEMBER_ID']));
										//delete temp deduction
										if(intval($row_deduct['counter']) > 0){
											echo 'Normal deduction counter check';
              				$running_counter = intval($row_deduct['running_counter']);
              				$running_counter = intval($row_deduct['running_counter']) + 1;
              				if(($running_counter) == intval($row_deduct['counter'])){
              					echo 'normal deduction counter check';
              					$query = 'INSERT INTO completedLoan (staff_id,allow_id,period,value,type)VALUES (?,?,?,?,?)';
														$conn->prepare($query)->execute(array($row['staff_id'],$row_deduct['allow_id'],$period,$output,'2'));
              					 //delete allow once cycle is complete
              					 $sqlDelete = "DELETE FROM allow_deduc WHERE temp_id = '".$row_deduct['temp_id']."'";
              					 $conn->exec($sqlDelete);
              					 
              				}else{
              					$sqlUpdate = "update allow_deduc set running_counter = '".$running_counter."' WHERE temp_id = '".$row_deduct['temp_id']."'";
              					$conn->exec($sqlUpdate);
              					
              				}
              			
											
					 					}
				
									}
									catch(PDOException $e){
											echo $e->getMessage();
									}
									
			  				}else if(intval($row_deduct['edType']) == '3')
			  				{
			  					//Process Union deduction
						  		$sql_numberOfRows = "SELECT deductiontable.ded_id, deductiontable.allowcode, deductiontable.grade, deductiontable.step, deductiontable.`value`, deductiontable.category, deductiontable.ratetype, deductiontable.percentage FROM deductiontable WHERE allowcode = '". $row_deduct['allow_id'] ."'";  
									$result_numberOfRows = mysqli_query($connect, $sql_numberOfRows);
									$row_numberOfRows = mysqli_fetch_assoc($result_numberOfRows);
									$total_rows = mysqli_num_rows($result_numberOfRows);
						  		if($total_rows == 1)
						  		{
										if($row_numberOfRows['ratetype'] == 1)
										{
											$output = $row_numberOfRows['value'];
										
										}else
										{
											$sql_consolidated = "SELECT allowancetable.allow_id, allowancetable.allowcode, allowancetable.grade, allowancetable.step, allowancetable.`value`, allowancetable.category, allowancetable.ratetype, allowancetable.percentage FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $row['GRADE'] ."' and step = '". $row['STEP'] ."'";
											$result_consolidated = mysqli_query($connect, $sql_consolidated);
											$row_consolidated = mysqli_fetch_assoc($result_consolidated);
											$total_rowsConsolidated = mysqli_num_rows($result_consolidated);
											$output = ($row_numberOfRows['percentage']*$row_consolidated['value'])/100;
										
										}
										// if deduction is found in the table
									}else if($total_rows > 1) 
									{
									    $sql_mulitple = "SELECT deductiontable.ded_id, deductiontable.allowcode, deductiontable.grade, deductiontable.step, deductiontable.`value`, deductiontable.category, deductiontable.ratetype, deductiontable.percentage FROM deductiontable WHERE allowcode = '". $row_deduct['allow_id'] ."' and grade = '". $row['GRADE'] ."'"; 
										  $result_mulitple = mysqli_query($connect, $sql_mulitple);
											$row_mulitple = mysqli_fetch_assoc($result_mulitple);
										  $total_mulitple = mysqli_num_rows($result_mulitple);
											if($total_mulitple > 0)
											{
													if($row_mulitple['ratetype'] == 1)
													{
													$output = $row_mulitple['value'];
												//echo $sql_mulitple ; 
													}else
													{
													$sql_consolidated = "SELECT allowancetable.allow_id, allowancetable.allowcode, allowancetable.grade, allowancetable.step, allowancetable.`value`, allowancetable.category, allowancetable.ratetype, allowancetable.percentage FROM allowancetable WHERE allowancetable.allowcode = 1 and grade = '". $row['GRADE'] ."' and step = '". $row['STEP'] ."'";
													$result_consolidated = mysqli_query($connect, $sql_consolidated);
													$row_consolidated = mysqli_fetch_assoc($result_consolidated);
													$total_rowsConsolidated = mysqli_num_rows($result_consolidated);
													$output = ceil(($row_mulitple['percentage']*$row_consolidated['value'])/100);
												 
													}
											}else
											{
												$output = $row_deduct['value'];
											}
										
							   
									}else
									{
										$output = $row_deduct['value'];
									}	
									echo $row_allow['allow_id'].' '.$row_allow['edDesc'].' '.number_format($output).'<br>';		
			  					try{
										$recordtime = date('Y-m-d H:i:s');
										$query = 'INSERT INTO tbl_master (staff_id, allow_id, deduc, type, period,editTime,userID) VALUES (?,?,?,?,?,?,?)';
										$conn->prepare($query)->execute(array($row['staff_id'], $row_deduct['allow_id'], $output, '2',  $period,$recordtime,$_SESSION['SESS_MEMBER_ID']));
										//process temp allow id
										
										if(intval($row_deduct['counter']) > 0){
											echo 'union deduction counter check';
              				$running_counter = intval($row_deduct['running_counter']);
              				$running_counter = intval($row_deduct['running_counter']) + 1;
              				if(($running_counter) == intval($row_deduct['counter'])){
              					 //delete allow once cycle is complete
              					 $query = 'INSERT INTO completedLoan (staff_id,allow_id,period,value,type)VALUES (?,?,?,?,?)';
														$conn->prepare($query)->execute(array($row['staff_id'],$row_deduct['allow_id'],$period,$output,'2'));
														
              					 $sqlDelete = "DELETE FROM allow_deduc WHERE temp_id = '".$row_deduct['temp_id']."'";
              					 $conn->exec($sqlDelete);
              					 
              					 
              				}else{
              					$sqlUpdate = "update allow_deduc set running_counter = '".$running_counter."' WHERE temp_id = '".$row_deduct['temp_id']."'";
              					$conn->exec($sqlUpdate);
              					
              				}
              			
											
					 					}
				
									}
									catch(PDOException $e)
									{
											echo $e->getMessage();
									}
			  					//process loan deduction
			  				}else if(intval($row_deduct['edType']) == '4')
			  				{
			  					$sql_loancheck = "SELECT tbl_earning_deduction_type.edType FROM tbl_earning_deduction_type INNER JOIN tbl_earning_deduction ON tbl_earning_deduction.edType = tbl_earning_deduction_type.edType WHERE tbl_earning_deduction.ed_id = '".$row_deduct['allow_id'] ."' and tbl_earning_deduction_type.edType = 4" ;
									$result_loancheck = mysqli_query($connect,$sql_loancheck);
									$row_loan = mysqli_fetch_assoc($result_loancheck);
									$total_loancheck = mysqli_num_rows($result_loancheck);
									//echo 'sql check ='. $sql_loancheck. '<br>';
									//echo 'loan check ='. $total_loancheck. '<br>';
									if($total_loancheck > 0)
									{
											
										$sql_loan = "SELECT tbl_debt.loan_id, tbl_debt.staff_id,tbl_debt.allow_id, SUM(ifnull(tbl_debt.principal,0))+SUM(ifnull(tbl_debt.interest,0)) as loan FROM tbl_debt WHERE staff_id = '".$row['staff_id']."' AND allow_id = '".$row_deduct['allow_id']."'";
										$result_loan = mysqli_query($connect,$sql_loan);
										$row_loan = mysqli_fetch_assoc($result_loan);
										$total_loan = mysqli_num_rows($result_loan);
											
										$sql_repayment = "SELECT tbl_repayment.staff_id, tbl_repayment.allow_id, SUM(ifnull(tbl_repayment.value,0)) as repayment FROM tbl_repayment WHERE staff_id = '".$row['staff_id']."' and allow_id = '".$row_deduct['allow_id']."'";
										$result_repayment = mysqli_query($connect,$sql_repayment);
										$row_repayment = mysqli_fetch_assoc($result_repayment);
										$total_repayment = mysqli_num_rows($result_repayment);
											
										$balance = $row_loan['loan'] - $row_repayment['repayment'];
											//print number_format($balance);
											//echo $sql_repayment ;
											if(floatval($balance) > floatval($row_deduct['value']))
											{
												$output = floatval($row_deduct['value']);
												//add payment
												try{
													$recordtime = date('Y-m-d H:i:s');
													$query_repayment = 'INSERT INTO tbl_repayment (staff_id, allow_id, value,  period,userID,editTime) VALUES (?,?,?,?,?,?)';
													$conn->prepare($query_repayment)->execute(array($row['staff_id'], $row_deduct['allow_id'], $output, $period,$period,$recordtime));
							
												}
												catch(PDOException $e){
													echo $e->getMessage();
												}

											}else if(floatval($balance) <= floatval($row_deduct['value']))
											{
												$output = floatval($balance);
												try{
													echo 'loan deduction counter check';
														$recordtime = date('Y-m-d H:i:s');
														
														$query = 'INSERT INTO completedLoan (staff_id,allow_id,period,value,type)VALUES (?,?,?,?,?)';
														$conn->prepare($query)->execute(array($row['staff_id'],$row_deduct['allow_id'],$period,$output,'2'));
										
														$query_repayment = 'INSERT INTO tbl_repayment (staff_id, allow_id, value,  period,userID,editTime) VALUES (?,?,?,?,?,?)';
														$conn->prepare($query_repayment)->execute(array($row['staff_id'], $row_deduct['allow_id'], $output, $period,$period,$recordtime));
														//delete loan id
														$query = 'DELETE FROM allow_deduc where allow_id = ? and staff_id = ?';
																				$conn->prepare($query)->execute(array($row_deduct['allow_id'],$row['staff_id']));
												
												}
												catch(PDOException $e){
														echo $e->getMessage();
												}
													

											}

									}
									echo $row_deduct['allow_id'].' '.$row_deduct['edDesc'].' '.number_format($output).'<br>';
			  					try{
			  						
			  						
			  						
					 					
			  						$recordtime = date('Y-m-d H:i:s');
										$query = 'INSERT INTO tbl_master (staff_id, allow_id, deduc, type, period,editTime,userID) VALUES (?,?,?,?,?,?,?)';
										$conn->prepare($query)->execute(array($row['staff_id'], $row_deduct['allow_id'], $output, '2',  $period,$recordtime,$_SESSION['SESS_MEMBER_ID']));
				
									}
									catch(PDOException $e){
											echo $e->getMessage();
									}
			
			  				}
			  			
			  			}
			  		}
				}
				
        			
        catch(PDOException $e)
        {
        echo $e->getMessage();
         }
             
 							//set openview status
        			$statuschange = $conn->prepare('UPDATE payperiods SET payrollRun = ? WHERE periodId = ?');
        			$perres = $statuschange->execute(array('1', $period));

			//exit ($_SESSION['companyid'] . 'Entire Employee Run');
		echo 0;
		}
		break;


		case 'addNewLeave':
			//check for existing same employee number

			$empnumber = filter_var($_POST['empnumber'], FILTER_SANITIZE_STRING);
			$leavetype = filter_var($_POST['leavetype'], FILTER_SANITIZE_STRING);
			$startleave = date('Y-m-d', strtotime(filter_var($_POST['startleave'], FILTER_SANITIZE_STRING)));
				$day1 = strtotime(filter_var($_POST['startleave'], FILTER_SANITIZE_STRING));
			$endleave = date('Y-m-d', strtotime(filter_var($_POST['endleave'], FILTER_SANITIZE_STRING)));
				$day2 = strtotime(filter_var($_POST['endleave'], FILTER_SANITIZE_STRING));

			$days_diff = $day2 - $day1;
    		$numofdays = date('d',$days_diff);

			$currdate = date('Y-m-d');
			//validate for empty mandatory fields

			try{
				//check for same leave request for same staffer
				$leavequery = $conn->prepare('SELECT * FROM hr_leave_requests WHERE employeeNumber = ? AND leaveType = ? AND status = ? OR status = ? AND active = ?');
				$res = $leavequery->execute (array($empnumber, $leavetype, '1', '2', '1'));

				if ($row = $leavequery->fetch()) {
					$_SESSION['msg'] = $msg = "Active / Pending similar leave type for this employee. Please review all approved or pending leave requests.";
					$_SESSION['alertcolor'] = 'danger';
					header('Location: ' . $source);
				} else {
					$query = 'INSERT INTO hr_leave_requests (employeeNumber, leaveType, fromDate, toDate, applicationDate, numberOfDays, status) VALUES (?,?,?,?,?,?,?)';

					$conn->prepare($query)->execute (array($empnumber, $leavetype, $startleave, $endleave, $currdate, $numofdays, '2'));
					
					$_SESSION['msg'] = $msg = "New Leave Successfully added.";
					$_SESSION['alertcolor'] = 'success';
					header('Location: ' . $source);
				}

			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'manageLeave':

			$empalternumber = filter_var($_POST['empalternumber'], FILTER_SANITIZE_STRING);
			$empalterid = filter_var($_POST['empalterid'], FILTER_SANITIZE_STRING);
			$leaveaction = filter_var($_POST['leaveaction'], FILTER_SANITIZE_STRING);
				//exit($empalternumber . ",". $empalterid. "," .$leaveaction);

			try{

				$query = ('UPDATE hr_leave_requests SET status = ? WHERE id = ? AND employeeNumber = ?');
				$conn->prepare($query)->execute (array($leaveaction, $empalterid, $empalternumber));

				$_SESSION['msg'] = $msg = "Leave status successfully amended";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);

			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'deactivateEmployee':
			$empalterid = filter_var($_POST['empalterid'], FILTER_VALIDATE_INT);
			$empalternumber = filter_var($_POST['empalternumber'], FILTER_SANITIZE_STRING);
			//$exitdate = date('Y-m-d', strtotime(filter_var($_POST['exitdate'], FILTER_SANITIZE_STRING)));
			$deactivate = filter_var($_POST['deactivate'], FILTER_SANITIZE_STRING);
			$editDate = date('Y-m-d H:i:s');

			//exit($empalternumber . ", " . $empalterid . ", " . $exitdate . ", " . $exitreason);
			$query = 'UPDATE employee SET STATUSCD = ? WHERE staff_id = ?';
			$conn->prepare($query)->execute (array($deactivate,$empalterid ));

			//	$deactivatequery = 'INSERT INTO hr_exited_employees (employeeId, exitDate, exitReason, editTime, userEditorId) VALUES (?,?,?,?,?)';
			//	$conn->prepare($deactivatequery)->execute (array($empalternumber, $exitdate, $exitreason, $editDate, $_SESSION['user']));
			
			$_SESSION['msg'] = $msg = "Employee successfully deactivated.";
			$_SESSION['alertcolor'] = 'success';
			header('Location: ' . $source);
		break;


		case 'suspendEmployee':
			$empalterid = filter_var($_POST['empalterid'], FILTER_VALIDATE_INT);
			$empalternumber = filter_var($_POST['empalternumber'], FILTER_SANITIZE_STRING);
			$startsuspension = date('Y-m-d', strtotime(filter_var($_POST['startsuspension'], FILTER_SANITIZE_STRING)));
			$endsuspension = date('Y-m-d', strtotime(filter_var($_POST['endsuspension'], FILTER_SANITIZE_STRING)));
			$suspendreason = filter_var($_POST['suspendreason'], FILTER_SANITIZE_STRING);
			$editDate = date('Y-m-d H:i:s');

			try{
				
				$susquery = $conn->prepare('SELECT * FROM employees WHERE empNumber = ? AND companyId = ? AND active = ? AND suspended = ?');
				$fin = $susquery->execute (array($empalternumber, $_SESSION['companyid'], '1', '1'));

				if ($row = $susquery->fetch()){
					$_SESSION['msg'] = "Selected employee currently on suspension.";
					$_SESSION['alertcolor'] = "danger";
					header('Location: ' . $source);
				} else {
					//exit($empalternumber . ", " . $empalterid . ", " . $exitdate . ", " . $exitreason);
					$query = 'UPDATE employees SET suspended = ? WHERE empNumber = ? AND companyId = ? AND active = ? AND suspended = ?';
					$conn->prepare($query)->execute (array('1', $empalternumber, $_SESSION['companyid'], '1', '0'));

						$deactivatequery = 'INSERT INTO employee_suspensions (employeeId, suspendStartDate, suspendEndDate, suspenReason, editTime, userEditorId) VALUES (?,?,?,?,?,?)';
						$conn->prepare($deactivatequery)->execute (array($empalternumber, $startsuspension, $endsuspension, $suspendreason, $editDate, $_SESSION['user']));
					
					$_SESSION['msg'] = $msg = "Employee successfully suspended.";
					$_SESSION['alertcolor'] = 'success';
					header('Location: ' . $source);
				}

			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'editemployeeearning':
			//exit('Edit Employee Earning');
			$empedit = filter_var($_POST['empeditnum'], FILTER_SANITIZE_STRING);
			$edited = filter_var($_POST['edited'], FILTER_VALIDATE_INT);
			$editname = filter_var($_POST['editname'], FILTER_VALIDATE_INT);
			$editvalue = filter_var($_POST['editvalue'], FILTER_VALIDATE_INT);
			$grossquery = 'UPDATE employee_earnings_deductions SET amount = ? WHERE employeeId = ? AND companyId = ? AND earningDeductionCode = ? AND payPeriod = ? AND active = ?';
			$conn->prepare($grossquery)->execute (array($editvalue, $empedit, $_SESSION['companyid'], $edited,  $_SESSION['currentactiveperiod'], '1'));

			$_SESSION['msg'] = 'Successfully Edited Earning / Deduction';
			$_SESSION['alertcolor'] = 'success';
			header('Location: ' . $source);
		break;


		case 'deactivateEd':
			$empeditnum = filter_var($_GET['empeditnum'], FILTER_SANITIZE_STRING);
			//$edited = filter_var($_POST['edited'], FILTER_VALIDATE_INT);
			//exit($empeditnum . " " . $edited . " " . $_SESSION['currentactiveperiod');
			try{
				
					$query = $conn->prepare('SELECT * FROM allow_deduc WHERE temp_id = ?');
	        $res = $query->execute(array($empeditnum));
	      	$existtrans = $query->fetch();

				if ($existtrans) {
	        
				$query = 'DELETE FROM allow_deduc where temp_id = ?';
			}
				$conn->prepare($query)->execute (array($empeditnum));

				$_SESSION['msg'] = $msg = "E/D successfully deactivated.";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;


		case 'batchprocess':
			exit('Batch Process');
		break;


		case 'resetpass':
			//exit('reset');

			$title = "Password Reset";
			$resetemail = filter_var((filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)), FILTER_VALIDATE_EMAIL);

			//check if account exists with emailaddress
			$query = $conn->prepare('SELECT emailAddress FROM users WHERE emailAddress = ? AND active = ?');
			$fin = $query->execute (array($resetemail, '1'));

			if ($row = $query->fetch()) {

				//Generate update token
				$reset_token = bin2hex(openssl_random_pseudo_bytes(32));
				
				//write token to token table and assign validity state, creation timestamp
				$tokenrecordtime = date('Y-m-d H:i:s');

				//check for any previous tokens and invalidate
					$tokquery = $conn->prepare('SELECT * FROM reset_token WHERE userEmail = ? AND valid = ? AND type = ?');
					$fin = $tokquery->execute (array($resetemail, '1', '1'));
					
					if($row = $tokquery->fetch()){
						$upquery = 'UPDATE reset_token SET valid = ? WHERE userEmail = ? AND valid = ?';
						$conn->prepare($upquery)->execute (array('0', $resetemail, '1'));
					}

				$tokenquery = 'INSERT INTO reset_token (userEmail, token, creationTime, valid, type) VALUES (?,?,?,?,?)';
				$conn->prepare($tokenquery)->execute (array($resetemail, $reset_token, $tokenrecordtime, '1', '1'));
					
				//exit($resetemail . " " . $reset_token);
				
				$sendmessage = "You've recently asked to reset the password for this Redsphere Payroll account: " . $resetemail . "<br /><br />To update your password, click the link below:<br /><br /> " . $sysurl . 'password_reset.php?token=' . $reset_token;
				//generate reset cdde and append to email submitted

				require 'phpmailer/PHPMailerAutoload.php';

				$mail = new PHPMailer;

				$mail->SMTPDebug = 3;                               // Enable verbose debug output

				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = 'smtp.zoho.com';  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = 'noreply@redsphere.co.ke';                 // SMTP username
				$mail->Password = 'redsphere_2017***';                           // SMTP password
				$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 587;                                    // TCP port to connect to

				$mail->setFrom('noreply@redsphere.co.ke', 'Redsphere Payroll');
				$mail->addAddress($resetemail, 'Redsphere Payroll');     // Add a recipient
				//$mail->addAddress('ellen@example.com');               // Name is optional
				$mail->addReplyTo('info@example.com', 'Information');
				$mail->addCC('fgesora@gmail.com');
				//$mail->addBCC('bcc@example.com');

				//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
				$mail->isHTML(true);                                  // Set email format to HTML

				$mail->Subject = $title;
				$mail->Body    = $sendmessage;
				$mail->AltBody = $sendmessage;

				if(!$mail->send()) {
					//exit($mail->ErrorInfo);
				    echo 'Mailer Error: ' . $mail->ErrorInfo;
				    $_SESSION['msg'] = "Failed. Error sending email.";
				    $_SESSION['alertcolor'] = "danger";
				    header("Location: " . $source);
				} else {
				    $status = "Success";
				    $_SESSION['msg'] = "If there is an account associated with this email address, an email has been sent to reset your password.";
				    $_SESSION['alertcolor'] = "success";
				    header("Location: " . $source);
				}

			} else {

				$_SESSION['msg'] = "If there is an account associated with this email address, an email has been sent to reset your password.";
			    $_SESSION['alertcolor'] = "success";
			    header("Location: " . $source);
			}
			
		break;



		case 'deactivateuser':
			$thisuser = filter_var($_POST['thisuser'], FILTER_SANITIZE_STRING);
			$useremail = filter_var($_POST['useremail'], FILTER_SANITIZE_STRING);

			try{
				$query = 'UPDATE username SET deleted = ? WHERE staff_id = ?';
				$conn->prepare($query)->execute (array('1', $thisuser));

				$_SESSION['msg'] = $msg = "User successfully deactivated.";
				$_SESSION['alertcolor'] = 'success';
				header('Location: ' . $source);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}

		break;



		case 'logout':
			$_SESSION['logged_in'] = '0';
			unset($_SESSION['user']);
			unset($_SESSION['email']);
    		unset($_SESSION['first_name']);
    		unset($_SESSION['last_name']);
    		unset($_SESSION['companyid']);
    		unset($_SESSION['emptrack']);
    		unset($_SESSION['currentactiveperiod']);
    		unset($_SESSION['activeperiodDescription']);
    		unset($_SESSION['msg']);
    		unset($_SESSION['alertcolor']);
    		unset($_SESSION['empDataTrack']);
    		unset($_SESSION['emptNumTack']);
    		
    		if (isset($_SESSION['leavestate'])) {
    			unset($_SESSION['leavestate']);
    		}

    		if (isset($_SESSION['periodstatuschange'])) {
    			unset($_SESSION['periodstatuschange']);
    		}

    		//reset global openview status
			$statuschange = $conn->prepare('UPDATE payperiods SET openview = ? ');
			$perres = $statuschange->execute (array('0'));

    		$_SESSION['msg'] = $msg = "Successfully logged out";
    		$_SESSION['alertcolor'] = $type = "success";
    		$page = "../../index.php";
			header('Location: ' . $page);
		break;

		
		default:
			exit('Unexpected route. Please contact administrator.');
		break;
	}