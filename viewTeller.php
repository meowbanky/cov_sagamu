<?php require_once('Connections/hms.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$fro_tellerpix = "-1";
if (isset($_GET['from'])) {
  $fro_tellerpix = $_GET['from'];
}
$id_tellerpix = "-1";
if (isset($_GET['id'])) {
  $id_tellerpix = $_GET['id'];
}
$to_tellerpix = "-1";
if (isset($_GET['to'])) {
  $to_tellerpix = $_GET['to'];
}
mysql_select_db($database_hms, $hms);
$query_tellerpix = sprintf("SELECT tbl_teller.memberid, tbl_teller.periodid, tbl_teller.teller_upload, tbl_teller.repayment_bank FROM tbl_teller WHERE tbl_teller.memberid = %s AND tbl_teller.periodid BETWEEN  %s AND %s", GetSQLValueString($id_tellerpix, "int"),GetSQLValueString($fro_tellerpix, "int"),GetSQLValueString($to_tellerpix, "int"));
$tellerpix = mysql_query($query_tellerpix, $hms) or die(mysql_error());
$row_tellerpix = mysql_fetch_assoc($tellerpix);
$totalRows_tellerpix = mysql_num_rows($tellerpix);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OOUTH MHWUN - Teller View</title>
<link href="registration_files/oouth.css" rel="stylesheet" type="text/css" />
<script language="javascript">
function print__(){
var x = document.getElementById("disappear");
x.style.visibility = 'hidden';
window.print() ;
}

function DaysArray(n) {
                                            for (var i = 1; i <= n; i++) {
                                            this[i] = 31
                                            if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
                                            if (i==2) {this[i] = 29}
                                            }
                                            return this
                                            }

														function DaysInMonth(Y, M) {
                                                        with (new Date(Y, M, 1, 12)) {
                                                        setDate(0);
                                                        return getDate();
                                                        }
                                                        }

														
														function datediff(date1, date2) {

                                                        
														var y1 = date1.getFullYear(), m1 = date1.getMonth(), d1 = date1.getDate(),
                                                        y2 = date2.getFullYear(), m2 = date2.getMonth(), d2 = date2.getDate();
                                                        if (d1 < d2) {
                                                        m1--;
                                                        d1 += DaysInMonth(y2, m2);
                                                        }
                                                        if (m1 < m2) {
                                                        y1--;
                                                        m1 += 12;
                                                        }

                                                        return [y1 - y2, m1 - m2, d1 - d2];

                                                        }

													    function AgeofDateCalculation(DobValue)
														 {
															 
															var daysInMonth = DaysArray(12);
 
														var logindateforage="dd-mm-yyyy";

                                                        var date = new Date();
                                                        var d  = date.getDate();
                                                        var day = (d < 10) ? '0' + d : d;
                                                        var m = date.getMonth() + 1;
                                                        var month = (m < 10) ? '0' + m : m;
                                                        var yy = date.getYear();
                                                        var year = (yy < 1000) ? yy + 1900 : yy;


                                                        var a1=year;
                                                        //alert("year"+a1);
                                                        var b1=month;
                                                        //alert("month"+b1);
                                                        var c1=day;
                                                        // alert("day"+c1);



                                                        var m=DobValue

                                                        var n=m.split("-");
                                                        if(logindateforage=="mm-dd-yyyy")
                                                        {
                                                        var c=n[0];
                                                        //alert("month"+c);
                                                        var b=n[1];
                                                        //alert("day"+b);
                                                        var a=n[2];
                                                        //alert("year"+a);
                                                        }
                                                        else if(logindateforage=="dd-mm-yyyy")
                                                        {
                                                        var c=n[1];
                                                        var b=n[0];
                                                        var a=n[2];
                                                        }
                                                        else if(logindateforage=="yyyy-mm-dd"){
                                                        var c=n[1];
                                                        //  alert("month"+c);
                                                        var b=n[2];
                                                        // alert("day"+b);
                                                        var a=n[0];
                                                        // alert("year"+a);
                                                        }

                                                        var curd = new Date(a1,b1-1,c1);
                                                        var cald = new Date(a,c-1,b);

                                                        var diff =  Date.UTC(a1,b1,c1,0,0,0) - Date.UTC(a,c,b,0,0,0);

                                                        var dife = datediff(curd,cald);
                                                        return dife[0]+" years, "+dife[1]+" months, and "+dife[2]+" days";

                                                        }

</script>

</head>

<body>
<p>&nbsp;</p>
<p><img src="images/mhwun_logo_web.jpg" width="499" height="95" /></p>
<p>
  
    
   
</p>
<table width="100%" border="1">
  <tr class="tableHeaderContent">
    <th scope="col"><strong>Teller</strong></th>
    <th scope="col"><strong>Teller Amount</strong></th>
  </tr>
  <?php do { ?><tr>
    <td><img src="<?php echo $row_tellerpix['teller_upload'] ?>" alt="teller" name="teller" id="teller" /></td>
    <td><?php echo $row_tellerpix['repayment_bank']; ?></td>
  </tr> <?php } while ($row_tellerpix = mysql_fetch_assoc($tellerpix)); ?>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
mysql_free_result($tellerpix);
?>
