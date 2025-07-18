<html>
<head>
<title>Send SMS to MHWUN Members</title>
<script src="js/jquery.min.js"></script>
<link rel="stylesheet" href="contact_form.css" />
<script src="contact_form.js"></script>
</head>
<body>
<div id="mainform" align="center">
<h2>SEND BULK SMS TO MEMBERS</h2>
<!-- Required Div Starts Here -->
<form id="form">
<h3>Contact Form</h3>
<p id="returnmessage"></p>
<label>Name: <span>*</span></label>
<input type="text" id="name" placeholder="Name"/>
<label>Email: <span>*</span></label>
<input type="text" id="email" placeholder="Email"/>
<label>Contact No: <span>*</span></label>
<input type="text" id="contact" placeholder="10 digit Mobile no."/>
<label>Message:</label>
<textarea id="message" placeholder="Message......."></textarea>
<input type="button" id="submit" value="Send Message"/>
</form>
</div>
</body>
</html>