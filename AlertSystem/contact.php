<html>
<head>
<title>Send SMS to MHWUN Members</title>
<script src="js/jquery.min.js"></script>
<link rel="stylesheet" href="contact_form.css" />
<script src="contact_form.js"></script>
<script src="50kobo.js" language="javascript" type="text/javascript"></script>
</head>
<body>
<div id="mainform" align="center">
<h2>
  <input type="text" id="name" placeholder="Name"/>
  <textarea name="message2" id="message" placeholder="Message......."></textarea>
  SEND BULK SMS TO MEMBERS</h2>
<!-- Required Div Starts Here -->
<form id="form" name="message" accept-charset="utf-8">
<h3>Contact Form</h3>
<p id="returnmessage"></p>
<label>Name: <span>*</span></label>
<label>Email: <span>*</span></label>
<input type="text" id="email" placeholder="Email"/>
<label>Contact No: <span>*</span></label>
<input type="text" id="contact" placeholder="10 digit Mobile no."/>
<label>Message:</label>
<input type="button" id="submit" value="Send Message"/>
</form>

<form action="/messages?rand=d0ebfd1966eaf48626d2df7786ea6369" class="form-horizontal" name="message" accept-charset="utf-8" id="MessageIndexForm" method="post">
                                    <div style="display:none;"><input type="hidden" name="_method" value="POST" /></div>

<div class="input text"><input name="num_recipients" type="text" id="num_recipients" class="btn col-md-1" style="margin-left: 5%" disabled="disabled"/></div>


<textarea name="data[SentMessage][recipient]" class="form-control" onBlur="getContacts();" rows="5" id="SentMessageRecipient"></textarea>


<div class="form-group">
<div class="row">
<label for="SentMessageMessage" class="help-block col-md-3">Enter your SMS message</label> <div class="input text"><input name="chars" type="text" id="chars" class="btn col-md-1" style="margin-left: 5%" disabled="disabled"/></div> </div>
<div class="clearfix"></div>
<textarea name="data[SentMessage][message]" class="form-control" onKeyPress="countPagesNCharacters();" onKeyUp="countPagesNCharacters();" onBlur="countPagesNCharacters();" rows="5" id="SentMessageMessage"></textarea> <div class="clearfix"></div>
<div class="row">
<label for="SentMessageMessage" class="help-block col-md-2">Pages: &nbsp;</label> <div class="input text"><input name="pages" type="text" id="pages" class="btn col-md-1" style="margin-left: 5%" disabled="disabled"/></div> </div>
</div>
</form>
</div>


<script type="text/javascript">
        //<![CDATA[
        loadVars();
        countPagesNCharacters();
        getContacts();
        countRecipients();
        toggleSchedule(document.getElementById('enable_schedule'));

        //]]>
    </script>
</body>
</html>