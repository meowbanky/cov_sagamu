<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="refresh" content="3600" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />
    <title>50Kobo RapidSMS :: Send Bulk SMS - oouthreminder oouthreminder</title>
    <link href="/favicon (1).ico" type="image/x-icon" rel="icon" />
    <link href="/favicon (1).ico" type="image/x-icon" rel="shortcut icon" />
    <link rel="stylesheet" type="text/css" href="/theme/thaz/css/thaz.min.css" />

    <!--[if lt IE 9]>
        
	<script type="text/javascript" src="/theme/thaz/js/html5shiv.min.js"></script>
	<script type="text/javascript" src="/theme/thaz/js/respond.min.js"></script>
        <![endif]-->
        <link rel="stylesheet" href="contact_form.css" />
        <script src="js/jquery.min.js"></script>
</head>

<body>
<div id="mainform" align="center">
<div class="wrapper">
    <div class="left-nav">
            <div id="side-nav">
                <ul id="nav">
                    
                </ul>
            </div>
  </div>
    <div class="page-content">
      <div class="content container">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div id="content_main">
                <form action="/messages?rand=d0ebfd1966eaf48626d2df7786ea6369" class="form-horizontal" name="message" accept-charset="utf-8" id="MessageIndexForm" method="post">
                  <div class="row">
                    <div class="col-md-8">
                        <div class="widget">
                                                <div class="widget-content">
                                                    <div class="body">
                                                        <div class="form-group">
                                                            <div class="row col-md-3">
                                                                <label for="SentMessageDisplayname" class="help-block">From (sender name) </label> </div>
                                                            
                                                                
                                                                   <input name="data[SentMessage][displayname]" type="text" maxlength="11" value="" id="SentMessageDisplayname" />
                                                                
                                                           
                                                          <div class="col-md-3">
                                                                <div class="form-group"></div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group"></div>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <label for="SentMessageRecipient" class="help-block col-md-4">Recipients <small>e.g. 08051234567</small> </label>
                                                                <div class="input text"><input name="num_recipients" type="text" id="num_recipients" style="margin-left: 5%" disabled="disabled" /></div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                            <textarea name="data[SentMessage][recipient]" class="form-control" onblur="getContacts();" rows="5" id="SentMessageRecipient"></textarea> </div>
                                                        <div class="clearfix">&nbsp;</div>
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <label for="SentMessageMessage" class="help-block col-md-3">Enter your SMS message</label>
                                                                <div class="input text"><input name="chars" type="text" id="chars" class="btn col-md-1" style="margin-left: 5%" disabled="disabled" /></div>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                            <textarea name="data[SentMessage][message]" class="form-control" onkeypress="countPagesNCharacters();" onkeyup="countPagesNCharacters();" onblur="countPagesNCharacters();" rows="5" id="SentMessageMessage"></textarea>
                                                            <div class="clearfix"></div>
                                                            <div class="row">
                                                                <label for="SentMessageMessage" class="help-block col-md-2">Pages: &nbsp;</label>
                                                                <div class="input text"><input name="pages" type="text" disabled="disabled" class="btn col-md-1" id="pages" style="margin-left: 5%" size="5" /></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                  <div class="clearfix">&nbsp;</div>
                                                    <div class="form-actions lightbluebg bordered-top">
                                                        <div class="row">
                                                          <div class="col-md-3"><button type="submit" name="action" value="sendsms" class="btn btn-danger btn-block" onclick="this.style.disabled=" disabled "">Send SMS Now</button><input type="button" id="submit" value="Send Message"/></div>
                                                          <div class="col-md-3"><button type="reset" value="reset" class="btn btn-default btn-block">Clear Text</button></div>
                                                            <div class="col-md-3"><button type="button" class="btn btn-success" onclick="setShortText('SentMessageMessage');">Shorten Text</button></div>
                                                      </div>
                                                  </div>
                                                </div>
                      </div>
                    </div>
                  </div>

                </form>
                <script type="text/javascript">
                                    window.setTimeout(function() {}, '180000');
                                </script>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
    <!-- Footer --><script type="text/javascript" src="/theme/thaz/js/jquery.js"></script>
<script type="text/javascript" src="/theme/thaz/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/theme/thaz/js/smooth-sliding-menu.js"></script>
<script type="text/javascript" src="50kobo.js"></script>
<script type="text/javascript">
        //<![CDATA[
        loadVars();
        countPagesNCharacters();
        getContacts();
        countRecipients();
        toggleSchedule(document.getElementById('enable_schedule'));

        //]]>
    </script>
<script>
        (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-8639750-1', 'auto');
        ga('require', 'displayfeatures');
        ga('send', 'pageview');
    </script>
    </div>
</body>

</html>