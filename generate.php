<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script language="javascript">
function generate(str, str2){
//var str = '0000000';
//var str2 = "123";
var final = "";
for (var i = 0, len = str.length - str2.length; i < len; i++) {
final = final + str[i];//alert(final);
}  
document.getElementById('textfield').value = final + str2;

} 
</script>
</head>

<body>
<p>
  <input type="button" name="button" id="button" value="Button" / onclick="generate('000000','1234')">
  
</p>
<p>
  <label for="textfield"></label>
  <input type="text" name="textfield" id="textfield" />
</p>
</body>
</html>