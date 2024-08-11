<?php 
session_start();
//ini_set('display_errors', 1);
//error_reporting(E_ALL);




if (!isset($_SESSION['UserID'])){
 
  header("Location:index.php");
    
} else{

}

require_once('Connections/cov.php'); 

?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
    {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

      $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn_vote, $theValue) : mysqli_escape_string($conn_vote, $theValue);

      switch ($theType) {
        case "text":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;    
        case "long":
        case "int":
          $theValue = ($theValue != "") ? intval($theValue) : "NULL";
          break;
        case "double":
          $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

mysqli_select_db($cov,$database_cov);
$query_logo = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 2";
$logo = mysqli_query($cov,$query_logo) or die(mysql_error());
$row_logo = mysqli_fetch_assoc($logo);
$totalRows_logo = mysqli_num_rows($logo);

mysqli_select_db($cov,$database_cov);
$query_title = "SELECT tbl_globa_settings.`value` FROM tbl_globa_settings WHERE tbl_globa_settings.setting_id = 1";
$title = mysqli_query($cov,$query_title) or die(mysql_error());
$row_title = mysqli_fetch_assoc($title);
$totalRows_title = mysqli_num_rows($title);
 
 ?>
<?php
//date_default_timezone_set('Asia/Calcutta');

function findage($dob)
{
    $localtime = getdate();
    $today = $localtime['mday']."-".$localtime['mon']."-".$localtime['year'];
    $dob_a = explode("-", $dob);
    $today_a = explode("-", $today);
    $dob_d = $dob_a[0];$dob_m = $dob_a[1];$dob_y = $dob_a[2];
    $today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
    $years = $today_y - $dob_y;
    $months = $today_m - $dob_m;
    if ($today_m.$today_d < $dob_m.$dob_d) 
    {
        $years--;
        $months = 12 + $today_m - $dob_m;
    }

    if ($today_d < $dob_d) 
    {
        $months--;
    }

    $firstMonths=array(1,3,5,7,8,10,12);
    $secondMonths=array(4,6,9,11);
    $thirdMonths=array(2);

    if($today_m - $dob_m == 1) 
    {
        if(in_array($dob_m, $firstMonths)) 
        {
            array_push($firstMonths, 0);
        }
        elseif(in_array($dob_m, $secondMonths)) 
        {
            array_push($secondMonths, 0);
        }elseif(in_array($dob_m, $thirdMonths)) 
        {
            array_push($thirdMonths, 0);
        }
    }
    echo "$years years $months months.";
}
?>
<?php


?>
<html><head>


<title><?php echo $row_title['value']; ?>-Registration</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="shortcut icon" href="favicon (1).ico" type="image/x-icon">

<!--Fireworks MX 2004 Dreamweaver MX 2004 target.  Created Sat Dec 04 17:23:24 GMT+0100 2004-->
<link href="resource/oouth.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="resource/general.js" type="text/javascript"></script>
<script type="text/javascript" src="resource/popcalendar.js"></script>
<script language="javascript">

            

            function Toggle(item) {

obj=document.getElementById(item);

//alert(item);

visible=(obj.style.display!="none");

key=document.getElementById("x" + item);

//alert(obj);

if (visible) {

obj.style.display="none";

key.innerHTML="<img src='images/sidearrow1.gif'  hspace='0' vspace='0' border='0'>";

} 



else {

// alert("fd");

obj.style.display="block";

key.innerHTML="<img src='images/downarrow1.gif'  hspace='0' vspace='0' border='0'>";

}

}
</script>

<script type="text/javascript" language="javascript">
function getXMLHTTP() { //fuction to return the xml http object
        var xmlhttp=false;  
        try{
            xmlhttp=new XMLHttpRequest();
        }
        catch(e)    {       
            try{            
                xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch(e){
                try{
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                }
                catch(e1){
                    xmlhttp=false;
                }
            }
        }
            
        return xmlhttp;
    }

            function makeRequest(url,divID) {

                //alert("ajax code");

                // alert(divID);

                //alert(url);

                var http_request = false;

                if (window.XMLHttpRequest) { // Mozilla, Safari, ...

                    http_request = new XMLHttpRequest();

                    if (http_request.overrideMimeType) {

                        http_request.overrideMimeType('text/xml');

                        // See note below about this line

                    }

                }

                else

                    if (window.ActiveXObject) { // IE

                        //alert("fdsa");

                        try {

                            http_request = new ActiveXObject("Msxml2.XMLHTTP");

                        } catch (e) {

                            lgErr.error("this is exception1 in his_secpatientreg.jsp"+e);

                            try {

                                http_request = new ActiveXObject("Microsoft.XMLHTTP");

                            } catch (e) {

                                lgErr.error("this is exception2 in his_secpatientreg.jsp"+e);

                            }

                    }

                }

                if (!http_request) {

                    alert('Giving up :( Cannot create an XMLHTTP instance');

                    return false;

                }

                http_request.onreadystatechange = function() {  alertContents(http_request,divID); };

                http_request.open('GET', url, true);

                http_request.send(null);

            }
            function alertContents(http_request,divid) {

                if (http_request.readyState == 4) {

                    //alert(http_request.status);

                    //alert(divid);

                    if (http_request.status == 200) {

                        document.getElementById(divid).innerHTML=http_request.responseText;

                    } else {

                        //document.getElementById(divid).innerHTML=http_request.responseText;

                        alert("There was a problem with the request");

                    }

                }

            }
                        
                        
                        function onSelected(oForm) {

                //options[document.form.profile.selectedIndex].value

                //var s=oForm.selectedIndex;

                var s1=oForm.value;

                //  alert("ddddddddddddd"+s+s1);

                var url="doctor_dept.php?departmentid="+s1;

                // alert(url);

                makeRequest(url,"doctor");

            }
                        


function Ipopcases1(oForm) {

                //options[document.form.profile.selectedIndex].value

                var s=oForm.selectedIndex;

                var s1=oForm.options[oForm.selectedIndex].value;

                // alert("ddddddddddddd"+s+s1);

                var url="config/patientype.php?apptype="+s1;

                // alert(url);

                makeRequest(url,"patcategory");



            }


function backup(){
    
        
        
        var strURL="backup.php";
        var req = getXMLHTTP();
        
        if (req) {
            
            req.onreadystatechange = function() {
                if ((req.readyState == 4)) {
                    // only if "OK"
                    ///if (req.status == 200) { 
                    document.getElementById('wait').style.visibility = "hidden";
                    //document.getElementById('wait').style.visibility = "visible";         
                        alert("Backup Complete");                   
                    } else {document.getElementById('wait').style.visibility = "visible";
                        //alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                    //}
                }               
            }           
            req.open("GET", strURL, true);
            req.send(null);
        }
            }
    
function Expand90(itemm){

                //alert(itemm.value);

            

                    if(itemm.value=="NC"){

                       // alert("in new");

                        //document.getElementById('patocpdetailsiframe').style.display="none";

                        //document.getElementById("modeofpay").style.display="none";

                        document.getElementById("patnc").style.display="block";

                        //document.getElementById("patnp").style.display="block";

                        document.getElementById("patoc").style.display="none";

                        //document.getElementById("patop").style.display="none";

                        //document.getElementById("patdisplay").style.display="none";

                        //document.getElementById("patocpdetails").style.display="none";



                        //document.getElementById("patientolddetails").style.display="none";

                        //document.getElementById("patocpdetails").width="0";

                        //document.getElementById("patocpdetails").height="0";



                        //document.getElementById("Temporrarayappointment").style.display="none";

                        //document.getElementById("paylater").style.display="none";

                        //document.getElementById('fname').value="";

                        //document.getElementById('lname').value="";











                    }else{

                        //alert("in old");

                        //document.getElementById("modeofpay").style.display="block";

                        document.getElementById("patnc").style.display="none";

                        document.getElementById("patoc").style.display="block";

                        //document.getElementById("patnp").style.display="none";

                        //document.getElementById("patop").style.display="block";

                        //document.getElementById("patocpdetails").style.display="none";

                        //document.getElementById("patientolddetails").style.display="none";

                        //document.getElementById("patocpdetails").width="0";

                        //document.getElementById("patocpdetails").height="0";

                        //document.getElementById("Temporrarayappointment").style.display="none";

                        //document.getElementById("companynamesss").style.display="none";

                        //document.getElementById("paylater").style.display="none";

                        //document.getElementById('patfirstname').value="";

                        //document.getElementById('patlastname').value="";

                    }
}



function ischecked(oFormEle,msg)

                                {

                                var s=oFormEle.value

                                if (s=="na"){

                                alert(msg);

                                oFormEle.focus()

                                return false;

                                }

                                return true;

                                }
function UserFeedback(oFormEle)

        {

        oFormEle.focus();

        }
function isSpace(s,message)
                {
                    

                ss=s.value;

                var length=ss.length;

                var c = ss.charAt(0);

                var d=ss.charAt(length-1);

                //    var regexpr =/[A-Za-z0-9]/;

                //     result= regexpr.test(c)

                //  if (!result)
                
                
                if(c == " " || d == " ")

                {

                UserFeedback(s);

                s.value = ss.trim();

                alert(message);

               return false;

                }

                return true;

                }

//function cansubmit(){ can (cansubmit=isSpace(document.eduEntry.Fname,"Space not allowed"));}
//cansubmit=isSpace(document.eduEntry.Fname.value,"Space not allowed");
function validate(){
//var cansubmit=false
     //alert(document.eduEntry.userid.value);
      if (document.eduEntry.Dept.value == "na"){
     alert( "Pls Select Department to Schedule Appointment!" );
     document.eduEntry.Dept.focus() ;
     return false;}
     
     if (document.eduEntry.userid.value == "na"){
     alert( "Pls Select Doctor to Schedule Appointment!" );
     document.eduEntry.userid.focus() ;
     return false;}
     
     if (document.eduEntry.apptFrom.value == "na"){
     alert( "Pls Selct Appointment From!" );
     document.eduEntry.apptFrom.focus() ;
     return false;}
     
     if (document.eduEntry.apptTo.value == "na"){
     alert( "Pls Selct Appointment To!" );
     document.eduEntry.apptTo.focus() ;
     return false;}
     
         
    if (document.eduEntry.apptFrom.value > document.eduEntry.apptTo.value){
       alert( "From Appointment can not be less that To Appointment!" );
      document.eduEntry.apptFrom.focus() ;
     return false;}
     
     return( true );
}

function getAge(dateString) {
  var now = new Date();
  var today = new Date(now.getYear(),now.getMonth(),now.getDate());
 
  var yearNow = now.getYear();
  var monthNow = now.getMonth();
  var dateNow = now.getDate();
 
  var dob = new Date(dateString.substring(6,10),
                     dateString.substring(0,2)-1,                   
                     dateString.substring(3,5)                  
                     );
 
  var yearDob = dob.getYear();
  var monthDob = dob.getMonth();
  var dateDob = dob.getDate();
  var age = {};
  var ageString = "";
  var yearString = "";
  var monthString = "";
  var dayString = "";
 
 
  yearAge = yearNow - yearDob;
 
  if (monthNow >= monthDob)
    var monthAge = monthNow - monthDob;
  else {
    yearAge--;
    var monthAge = 12 + monthNow -monthDob;
  }
 
  if (dateNow >= dateDob)
    var dateAge = dateNow - dateDob;
  else {
    monthAge--;
    var dateAge = 31 + dateNow - dateDob;
 
    if (monthAge < 0) {
      monthAge = 11;
      yearAge--;
    }
  }
 
  age = {
      years: yearAge,
      months: monthAge,
      days: dateAge
      };
 
  if ( age.years > 1 ) yearString = " years";
  else yearString = " year";
  if ( age.months> 1 ) monthString = " months";
  else monthString = " month";
  if ( age.days > 1 ) dayString = " days";
  else dayString = " day";
 
 
  if ( (age.years > 0) && (age.months > 0) && (age.days > 0) )
    ageString = age.years + yearString + ", " + age.months + monthString + ", and " + age.days + dayString + " old.";
  else if ( (age.years == 0) && (age.months == 0) && (age.days > 0) )
    ageString = "Only " + age.days + dayString + " old!";
  else if ( (age.years > 0) && (age.months == 0) && (age.days == 0) )
    ageString = age.years + yearString + " old. Happy Birthday!!";
  else if ( (age.years > 0) && (age.months > 0) && (age.days == 0) )
    ageString = age.years + yearString + " and " + age.months + monthString + " old.";
  else if ( (age.years == 0) && (age.months > 0) && (age.days > 0) )
    ageString = age.months + monthString + " and " + age.days + dayString + " old.";
  else if ( (age.years > 0) && (age.months == 0) && (age.days > 0) )
    ageString = age.years + yearString + " and " + age.days + dayString + " old.";
  else if ( (age.years == 0) && (age.months > 0) && (age.days == 0) )
    ageString = age.months + monthString + " old.";
  else ageString = "Oops! Could not calculate age!";
 
  return ageString;
}


</script>
<script language="javascript">



            function assignfunction15()

            {

                // alert("HI");



                var cansubmit=false,eeemmm=false;



             

            // alert(document.getElementById("checkbox").value);



                var total = 0 ;



                var max=eval(document.nursing.checkbox.length);



                //alert(max);

                if(max){

                    for (var idx = 0; idx < max; idx++) {

                        if (eval("document.nursing.checkbox[idx].checked") == true) {

                            //alert("it is coming to loop");

                          //var textvalue=document.getElementsByTagName("p").value;

                        //alert(textvalue);

                         //if((textvalue=="na") || (textvalue=="")){


                              //alert("Please enter value for the TextField");

                             // return false;

                        //  }



                           

                            total=1;



                            cansubmit = true;





                        }

                    }

                }



                if(total == 0) {



                    alert("Please Select At least One Record");

                    cansubmit = false;

                    

                }





return cansubmit;





            }



        </script>

<script language="JavaScript">

      

            function checknum(oFormEle){





                var sValue=oFormEle.value;

     

                if(isNaN(sValue) == true){



                    alert("Please Enter Numeric Value only");

                    oFormEle.value="";

                    oFormEle.focus()

                    return false;

                }

                return true;

            }





            function formValidate(oForm)

            {

                alert("enterd into formValidate");

                /*var dob=document.nursing.dob.value;

                if(dob !="" && dob !=null){

                    //alert(dob);

                    var sss=isDate(dob);

                    // alert(sss);

                    if(sss == true){

                        //alert(sss);

                        //  break;

                        return true;

                        // return true;

                    } else{

                        return false;

                    }

                }*/





                var cansubmit=false,eeemmm=false;

                alert("cansubmittttttttttt"+cansubmit);

                

                /*var s=document.getElementById("17000001");

                var s1=document.getElementById("17000002");

                var s2=document.getElementById("17000007");

                if(s.value!="")

                {

                    cansubmit=isSpace(s,"Space not allowed");

                    if(cansubmit)

                        cansubmit=isNumVital(s,"Please enter Only Numeric");

                    if(cansubmit)

                        cansubmit=isweight(s,"Please Enter Less Than 400");

                }

                if(s1.value!="")

                {  if(cansubmit)

                        cansubmit=isSpace(s1,"Space not allowed");

                    if(cansubmit)

                        cansubmit=isNumVital(s1,"Please enter Only Numeric");

                    if(cansubmit)

                        cansubmit=isheight(s1,"Please Enter Less Than 300");

                }

                if(s2.value!="")

                {

                    if(cansubmit)

                        cansubmit=isSpace(s2,"Space not allowed");

                    if(cansubmit)

                        cansubmit=isNumVital(s2,"Please enter Only Numeric");

                    if(cansubmit)

                        cansubmit=ispulse(s2,"Please Enter Less Than 120");

                }*/



                return cansubmit;





            }

        </script>

<script>

                    var isNS4=(navigator.appName=="Netscape")?1:0;

                    function auto_logout(iSessionTimeout,iSessTimeOut,sessiontimeout)

                    {

                             window.setTimeout('', iSessionTimeout);

                              window.setTimeout('winClose()', iSessTimeOut);

                    }

                    function winClose() {

                        //alert("Your Application session is expired.");

                   if(!isNS4)

               {

                  window.navigate("index.php");

               }

                  else

              {

                window.location="index.php";

               }

             }

            auto_logout(1440000,1500000,1500)

</script>
<script language="JavaScript">
function saveVitalsign() {      
        
    //assignfunction15()    
    var save = ""   
    var ln = 0;
    var checkbox = document.getElementsByName('vitalsign');
    var i;
    for (i=0;i<checkbox.length;i++){
            
         if (checkbox[i].checked){
             
             //alert(checkbox[i].value);
            //alert("Test1");
                        ln++;
                    //if (ln == 1){
            //alert(checkbox[i].value);
            var username = document.forms[0].username.value;
            var pid = (document.forms[0].pid.value);
            var vid = (document.forms[0].vid.value);
            
            if (checkbox[i].value == "Weight"){
                var valueS = document.forms[0].weight_value.value;
                var uom = document.forms[0].WeightUOM.value;
                //alert (document.getElementsByName('WeightUOM').value);
                //alert (valueS);
                } 
                else if (checkbox[i].value == "Pulse") {
                var valueS = document.forms[0].pulse_value.value;
                var uom = document.forms[0].PulseUOM.value;
                }
                else if (checkbox[i].value == "Height") {
                var valueS = document.forms[0].height_value.value;
                var uom = document.forms[0].HeightUOM.value;
                }
                else if (checkbox[i].value == "Respiratory Rate") {
                var valueS = document.forms[0].respiratoryRate_value.value;
                var uom = document.forms[0].RespiratoryRateUOM.value;
                }
                else if (checkbox[i].value == "Temperature") {
                var valueS = document.forms[0].temp_value.value;
                var uom = document.forms[0].TemperatureUOM.value;
                }
                else if (checkbox[i].value == "BP") {
                var valueS = document.forms[0].bp1.value+'/'+ document.forms[0].bp2.value;
                var uom = document.forms[0].bpUOM.value;
                }
            
            //alert(document.forms[0].username.value);
            var usernameS = document.forms[0].username.value; 
            var vitalsigsS = checkbox[i].value;
            //alert(uom+valueS+" " + vitalsigsS+" " +pid+" " +vid+" " +usernameS);
            //confirm("Do you want to save the Vital Sign");
            
        var strURL="vitalsignsave.php?uom="+uom+"&value="+valueS+"&vitalSign="+vitalsigsS+"&pid="+pid+"&vid="+vid+"&MM_insert=form1&username="+usernameS;


        //var strURL="vitalsignsave.php?uom=weigjt&value=90&vitalSign=Weight&pid=5&vid=6&MM_insert=form1&username=1";
        
        var req = getXMLHTTP();
        if (req) {
            
            req.onreadystatechange = function() {
                if ((req.readyState == 4) && (req.status == 200 || req.status == 0)){
                    // only if "OK"
                        //alert("ok");              
                        document.getElementById('txtCoopName').innerHTML=req.responseText;                      
                        
            }   
            }
            
            req.open("GET", strURL, true);
            req.send(null);
        }       
    
    }

    }if (valueS && uom){alert("Vital Sign Saved Successfully")};
    }
    function getBankName(coopid) {      
        //alert("hi");
        var strURL="bankName.php?id="+coopid;
        var req = getXMLHTTP();
        
        if (req) {
            
            req.onreadystatechange = function() {
                if (req.readyState == 4) {
                    // only if "OK"
                    if (req.status == 200) {                        
                        document.getElementById('BankName').innerHTML=req.responseText;                     
                    } else {
                        alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                    }
                }               
            }           
            req.open("GET", strURL, true);
            req.send(null);
        }       
    }



</script>


</head>
<body>
<div onClick="bShow=true" id="calendar" style="z-index: 999; position: absolute; visibility: hidden;">
<table style="border: 1px solid rgb(160, 160, 160); font-size: 11px; font-family: arial;" width="220" bgcolor="#ffffff">
<tbody><tr bgcolor="#0000aa"><td><table width="218">
<tbody><tr><td style="padding: 2px; font-family: arial; font-size: 11px;"><font color="#ffffff"><b><span id="caption"><span id="spanLeft" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeLeft","left2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to previous month. Hold mouse button to scroll automatically."' onClick="javascript:decMonth()" onmouseout='clearInterval(intervalID1);swapImage("changeLeft","left1.gif");this.style.borderColor="#3366FF";window.status=""' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartDecMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeLeft" src="resource/left1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanRight" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeRight","right2.gif");this.style.borderColor="#88AAFF";window.status="Click to scroll to next month. Hold mouse button to scroll automatically."' onmouseout='clearInterval(intervalID1);swapImage("changeRight","right1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="incMonth()" onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout("StartIncMonth()",500)' onMouseUp="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp;<img id="changeRight" src="resource/right1.gif" width="10" border="0" height="11">&nbsp;</span>&nbsp;<span id="spanMonth" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeMonth","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a month."' onmouseout='swapImage("changeMonth","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpMonth()"></span>&nbsp;<span id="spanYear" style="border: 1px solid rgb(51, 102, 255); cursor: pointer;" onmouseover='swapImage("changeYear","drop2.gif");this.style.borderColor="#88AAFF";window.status="Click to select a year."' onmouseout='swapImage("changeYear","drop1.gif");this.style.borderColor="#3366FF";window.status=""' onClick="popUpYear()"></span>&nbsp;</span></b></font></td><td align="right"><a href="javascript:hideCalendar()"><img src="resource/close.gif" alt="Close the Calendar" width="15" border="0" height="13"></a></td></tr></tbody></table></td></tr><tr><td style="padding: 5px;" bgcolor="#ffffff"><span id="content"></span></td></tr><tr bgcolor="#f0f0f0"><td style="padding: 5px;" align="center"><span id="lblToday">Today is <a onmousemove='window.status="Go To Current Month"' onmouseout='window.status=""' title="Go To Current Month" style="text-decoration: none; color: black;" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">Wed, 8 Jun    2011</a></span></td></tr></tbody></table></div><div id="selectMonth" style="z-index: 999; position: absolute; visibility: hidden;"></div><div id="selectYear" style="z-index: 999; position: absolute; visibility: hidden;"></div>



<table width="100%" border="0" cellpadding="0" cellspacing="0" height="100%">
<!-- fwtable fwsrc="MTN4U.png" fwbase="index.jpg" fwstyle="Dreamweaver" fwdocid = "1226677029" fwnested="0" -->
  <tbody><tr>
   <td><img src="resource/spacer.gif" alt="" width="750" border="0" height="1"></td>
  </tr>

  <tr>
   <td class="centerAligned" valign="top" height="100"><div align="center"></div>
<table width="750" border="0" cellpadding="0" cellspacing="0">
<!-- fwtable fwsrc="Untitled" fwbase="top.gif" fwstyle="Dreamweaver" fwdocid = "2000728079" fwnested="0" -->
  <tbody><tr>
   <td><img src="resource/spacer.gif" alt="" width="7" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="78" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="491" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="153" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="21" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
  </tr>

  <tr>
   <td colspan="5"><img name="top_r1_c1" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
  </tr>
  <tr>
   <td rowspan="4"><img name="top_r2_c1" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
    <td colspan="3" rowspan="4"><img src="<?php echo $row_logo['value']; ?>"><img name="top_r4_c4" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
    <td>&nbsp;</td>
   <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="17"></td>
  </tr>
  <tr>
   <td rowspan="3"><img name="top_r3_c5" src="resource/spacer.gif" alt="" width="1" border="0" height="1"></td>
   <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="37"></td>
  </tr>
  <tr>
   <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="25"></td>
  </tr>
  <tr>
   <td><img src="resource/spacer.gif" alt="" width="1" border="0" height="11"></td>
  </tr>
</tbody></table>

</td>
  </tr>
  <tr>
   <td class="mainNav" valign="top" height="21"><table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
     <tbody><tr>
       <td class="mainNavTxt" valign="bottom">&nbsp;</td>
       <td class="leftAligned" width="12">&nbsp;</td>
     </tr>
   </tbody></table>
</td>
  </tr>
  <tr>
   <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r3_c1" src="resource/index_r3_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="globalNav" valign="top" height="25"><table width="750" border="0" cellpadding="0" cellspacing="0" height="21">
     <tbody><tr>
       <td class="rightAligned" width="10"><img src="resource/spacer.gif" width="1" height="1"></td>
       <td><img src="resource/spacer.gif" width="6"></td>
       <td class="leftAligned" width="12"><img src="resource/spacer.gif" width="1" height="1"></td>
     </tr>
   </tbody></table>

</td>
  </tr>
  <tr>
   <td class="dividerCenterAligned" valign="top" height="1"><img name="index_r5_c1" src="resource/index_r5_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top"><table width="750" border="0" cellpadding="0" cellspacing="0">
     <tbody><tr>
       <td rowspan="2" width="8"><img src="resource/spacer.gif" width="1" height="1"></td>
       <td colspan="2" class="breadcrumbs" valign="bottom" height="20"><a href="http://careers.mtnonline.com/index.asp"> </a></td>
       <td rowspan="2" width="12"><img src="resource/spacer.gif" width="1" height="1"></td>
     </tr>
     <tr>
       <td class="Content" valign="top" width="180">

<p>&nbsp;</p><br>

<table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
  <tbody><tr> 
    <td class="sidenavtxt" align=""> <em><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Welcome,</font></em> 
      <font size="1" face="Verdana, Arial, Helvetica, sans-serif"><span><?php echo ($_SESSION['FirstName']); ?><br>
      <img src="resource/spacer.gif" width="1" border="0" height="8"><img src="resource/arrow_bullets2.gif" border="0">       
<a href="index.php">Logout</a>
      </span></font> </td>
  </tr>
</tbody></table>
<br>
<br>
<table class="innerWhiteBox" width="96%" border="0" cellpadding="4" cellspacing="0">
  <tbody><tr>
    <td colspan="2" class="sidenavtxt" width="100%" align=""><p><br>
      </p></td>
  </tr>
  
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td width="100%" align="" class="sidenavtxt"><a href="registration.php">Registration</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="dashboard.php">DashBoard</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="process2.php">Process Transaction</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="editContributions.php">Edit Contribution</a></td>
    </tr>
    <tr>
      <td align=""><img src="skills_files/spacer.gif" alt="" width="1" height="8" border="0"><img src="skills_files/arrow_bullets2.gif" alt="" border="0"></td>
      <td class="sidenavtxt" align=""><a href="addloan.php">Add Loan</a></td>
    </tr>
  
</tbody></table>
         <br>
         <?php include("marquee.php"); ?> 
<br>
<br>
<script language="JavaScript1.2" src="resource/misc.htm"></script>
</td>
       <td rowspan="2" class="Content" valign="top"><hr size="1" width="500" align="left" color="#cccccc">
         <table width="500" border="0" align="right" cellpadding="0" cellspacing="0">
           <tbody><tr>
             <td class="toplinks2" valign="top"><div align="justify">
                 <table class="Content" width="100%" border="0" cellpadding="4" cellspacing="0">
                   <tbody><tr>
                     <td align="left" valign="middle"><span class="homeContentSmaller">
                       
                       </span>
                        <form method="POST" name="nursing" onSubmit="return(validate()); ">
                         <fieldset>
                         <legend class="contentHeader1"> DashBoard<a name="top"></a></legend><div id="wait" class="overlay" style="background-color:white;visibility:hidden;border: 1px solid black;padding:5px;">
<img src="images/pageloading.gif" class="area"> Backup in Progress Please wait...
 </div>
                         <table width="96%" align="center" cellpadding="4" cellspacing="0">
                           <tbody><tr valign="top" align="left">
                           <td width="100%" height="1"><img src="resource/spacer.gif" width="1" height="1"></td>
                         </tr>
                         <tr valign="top" align="center">
                           <td valign="middle" height="10"></td>
                         </tr>
                         <tr valign="top" align="left">
                           <td height="3"><img src="resource/spacer.gif" width="1" height="5">
                             <table width="100%" border="0" class="table_border_new">
                               <tr>
                                 <th width="49%" align="center" scope="col"><p><img src="images/new_user.jpg" width="104" height="104" title="Click to Create New Member" onClick='location.href="registration.php"'>
                                   </p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral2" value="New Member"  title="Click to Create New Member" onClick='location.href="registration.php"'>
                                   </p></th>
                                 <th width="51%" align="center" scope="col"><p><img src="images/edit_records.jpg" width="104" height="104" title="Click to Edit Member's Record" onClick='location.href="registration_search.php"'> </p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral4" value="Edit Member's Record"  title="Click to Edit Member's Record" onClick='location.href="registration_search.php"'>
                                   </p></th>
                                 <th width="51%" align="center" scope="col"><p><img src="image_upload/abc.png" alt="" width="100" height="100" title="Click to Upload Member's Passport" onClick='location.href="registration_search.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral15" value="Upload Passport"  title="Click to Upload Member's Passport" onClick='location.href="registration_search.php"'>
                                   </p></th>
                               </tr>
                               <tr>
                                 <td align="center"><p><img src="images/edit_records.jpg" width="104" height="104" title="Click to Print Member's Record" onClick='location.href="memberlist.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral14" value="Print Member's List"  title="Click to Print Member's Record" onClick='location.href="memberlist.php"'>
                                   </p>
                                   </td>
                                 <td align="center"><p><img src="images/create_period.jpg" width="99" height="104" title="Click to Create Transaction Period" onClick='location.href="transact_period.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral3" value="Create Period"  title="Click to Create Transaction Period" onClick='location.href="transact_period.php"'>
                                   </p>
                                   </td>
                                 <td align="center"><p><img src="images/add_loan.jpg" width="102" height="102" title="Click to Add Loan" onClick='location.href="addloan.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral8" value="Add Loan"  title="Click to Add Loan" onClick='location.href="addloan.php"'>
                                   </p>
                                   </td>
                               </tr>
                       
                         <tr>
                                 <td align="center"><p><img src="images/edit_contribution.jpg" width="104" height="104"title="Click to Edit Contributions" onClick='location.href="editContributions.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral9" value="Edit Contributions"  title="Click to Edit Contributions" onClick='location.href="editContributions.php"'>
                                   </p></td>
                                 <td align="center"><p><img src="images/process.jpg" width="104" height="104" title="Click to View Process Deduction" onClick='location.href="process2.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral10" value="Process Deductions"  title="Click to View Process Deduction" onClick='location.href="process2.php"'>
                                   </p></td>
                                 <td align="center"><p><img src="images/edit_contribution.png" width="102" height="102" title="Click to View Members Deduction" onClick='location.href="mastertransaction.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral11" value="Master Transaction"  title="Click to View Members Deduction" onClick='location.href="mastertransaction.php"'>
                                   </p></td>
                               </tr>
                         <tr>
                           <td align="center"><p><img src="images/checkstatus.jpg" width="107" height="104" title="Click to Members Balance" onClick='location.href="status.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral7" value="Check Status"  title="Click to Members Balance" onClick='location.href="status.php"'>
                             </p></td>
                           <td align="center"><p><img src="images/download.png" width="102" height="102" title="Click to View Members Deduction" onClick='location.href="status_reducing.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral18" value="Reducing Loan Status"  title="Click to View Members Deduction" onClick='location.href="status_reducing.php"'>
                             </p></td>
                             
                           <td align="center"><p><img src="images/smsalert.jpg" alt="" width="102" height="102" title="Click to Send Alert" onClick='location.href="AlertSystem/index.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral6" value="Send Alert"  title="Click to Send Alert" onClick='location.href="AlertSystem/index.php"'>
                             </p></td>
                           </tr>
                         <tr>
                           <td align="center"><p><img src="images/deletemember.jpg" alt="" width="102" height="102" title="Click to Delete Member" onClick='location.href="deletemember.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral" value="Withdrawal"  title="Click to Delete Member" onClick='location.href="deletemember.php"'>
                             </p></td>
                           <td align="center"><p><img src="images/withdraw.jpg" alt="" width="102" height="102" title="Click to Delete Member" onClick='location.href="withdrawal_savings.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral16" value="Withdrawl from Savings"  title="Click to Delete Member" onClick='location.href="withdrawal_savings.php"'>
                             </p></td>
                           <td align="center"><p><img src="images/backup.jpg" alt="" width="102" height="102" title="Click to Backup Database" onClick='location.href="backup2.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral12" value="Backup"  title="Click to Backup Database"onClick='location.href="backup2.php"'>
                             </p></td>
                           </tr>
                         <tr>
                           <td align="center"><p><img src="images/smsalert.jpg" alt="" width="102" height="102" title="Click to Send SMS" onClick='location.href="AlertSystem/50web.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral13" value="Send SMS"  title="Click to Send Alert" onClick='location.href="AlertSystem/50web.php"'>
                             </p></td>
                           <td align="center"><p><img src="images/register_user.jpg" width="104" height="104" title="Click to Register New User" onClick='location.href="registeruser.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral5" value="Register User"  title="Click to Register New User" onClick='location.href="registeruser.php"'>
                             </p></td>
                           <td align="center"><p><img src="images/dividend.jpg" alt="" width="102" height="102" title="Click to Calculate Dividend" onClick='location.href="dividend.php"'>
                             <input type="button" class="BlueButton" style="width:130px" name="referral17" value="Dividend"  title="Click to Calculate Dividend" onClick='location.href="dividend.php"'>
                           </p></td>
                           </tr>
                           <tr>
                                 <td align="center"><p><img src="images/edit_contribution.jpg" width="104" height="104"title="Click to Edit Special Contributions" onClick='location.href="editSpecialContributions.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral9" value="Edit Special Contri"  title="Click to Edit Special Contributions" onClick='location.href="editSpecialContributions.php"'>
                                   </p></td>
                                 
                                 <td align="center"><p><img src="images/add_loan.jpg" width="102" height="102" title="Click to Add Special Loan" onClick='location.href="addSpecialLoan.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral8" value="Add Special Loan"  title="Click to Add Special Loan" onClick='location.href="addSpecialLoan.php"'>
                                   </p>
                                   </td>
                                   <td align="center"><p><img src="images/process.jpg" width="104" height="104" title="Click to View Process Deduction" onClick='location.href="processSpecial.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral10" value="Process Deductions"  title="Click to View Process Deduction" onClick='location.href="processSpecial.php"'>
                                   </p></td>
                               </tr>
                            <tr>
                                 <td align="center"><p><img src="images/edit_contribution.png" width="102" height="102" title="Click to View Members Deduction" onClick='location.href="mastertransactionSpecial.php"'></p>
                                   <p>
                                     <input type="button" class="BlueButton" style="width:130px" name="referral11" value="Master Transaction"  title="Click to View Members Deduction" onClick='location.href="mastertransactionSpecial.php"'>
                                   </p></td>
                                 <td align="center"><p><img src="images/checkstatus.jpg" width="107" height="104" title="Click to Check Special Members Balance" onClick='location.href="statusSpecial.php"'></p>
                             <p>
                               <input type="button" class="BlueButton" style="width:130px" name="referral7" value="Check Status"  title="Click to check Special Members Balance" onClick='location.href="statusSpecial.php"'>
                             </p></td>
                                 
                               </tr>   
                         <tr>
                           <td align="center"><p>&nbsp;</p></td>
                           <td align="center"><p>&nbsp;</p></td>
                           <td align="center"><p>&nbsp;</p></td>
                           </tr>
                             </table></td>
                         </tr>
                           </tbody>
                         </table>
                         </fieldset>
                        </form>
                        <p>
                        <p><br>
                     </p></td></tr>
                     <tr>
                       <td valign="top">&nbsp;</td>
                     </tr>
                   </tbody>
                 </table>
             </div></td>
           </tr>
         </tbody></table>
         <br>         <br>            <br>          </td>
       </tr>
     <tr>
       <td class="Content" valign="top">&nbsp;</td>
     </tr>
   </tbody></table></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top" height="1"><img name="index_r7_c1" src="resource/index_r7_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top" height="21"><table class="contentHeader1" width="750" border="0" cellpadding="0" cellspacing="0" height="21">
  <tbody><tr>
    <td class="rightAligned" width="10">&nbsp;</td>
    <td class="baseNavTxt">&nbsp;</td>
    <td class="leftAligned" width="12">&nbsp;</td>
  </tr>
</tbody></table>
</td>
  </tr>
  <tr>
   <td class="innerPg" valign="top" height="1"><img name="index_r9_c1" src="resource/index_r9_c1.jpg" alt="" width="750" border="0" height="1"></td>
  </tr>
  <tr>
   <td class="innerPg" valign="top">&nbsp;</td>
  </tr>
</tbody></table>
</body></html>
<?php
mysqli_free_result($logo);

mysqli_free_result($title);
?>
