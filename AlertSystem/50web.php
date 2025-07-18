<?php require_once('Connections/cov.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
  function GetSQLValueString($conn_vote, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
  {


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

mysqli_select_db($cov, $database_cov);
$query_masterTransaction = "SELECT tbl_personalinfo.MobilePhone,tbl_personalinfo.patientid FROM tbl_personalinfo WHERE `Status` = 'Active'";
$masterTransaction = mysqli_query($cov, $query_masterTransaction) or die(mysqli_error($cov));
$row_masterTransaction = mysqli_fetch_assoc($masterTransaction);
$totalRows_masterTransaction = mysqli_num_rows($masterTransaction);
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name=viewport content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="refresh" content="3600" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Cache-Control" content="no-cache" />
  <meta http-equiv="Expires" content="-1" />
  <title>COVCMSSMS PLATFORM</title>
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
                  <form action="test.php" class="form-horizontal" name="message" accept-charset="utf-8" id="MessageIndexForm" method="post">
                    <div class="row">
                      <div class="col-md-8">
                        <div class="widget">
                          <div class="widget-content">
                            <div class="body">
                              <div class="form-group">
                                <div class="row col-md-3">
                                  <p id="returnmessage"></p>

                                  <label for="SentMessageDisplayname" class="help-block">From (sender name) </label>
                                </div>


                                <input name="data[SentMessage][displayname]" type="text" maxlength="11" value="COVCMS" id="SentMessageDisplayname" />


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

                                <textarea name="data[SentMessage][recipient]" class="form-control" onblur="getContacts();" rows="5" id="SentMessageRecipient"> <?php do { ?> <?php echo $row_masterTransaction['MobilePhone']; ?> <?php } while ($row_masterTransaction = mysqli_fetch_assoc($masterTransaction)); ?></textarea>

                              </div>
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
                                <div class="col-md-3"></div>
                                <input type="button" id="submit" value="Send Message" />
                                <input type="button" value="Clear Text" id="clear">
                                <input type="button" value="Shorten Text" onclick="setShortText('SentMessageMessage');"></button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </form>
                  <iframe src="" scrolling="yes" frameborder="0" name="contacts" style="overflow-x:hidden;" height="375" width="100%"></iframe>
                </div>
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
  <!-- Footer <script type="text/javascript" src="/theme/thaz/js/jquery.js"></script>
<script type="text/javascript" src="/theme/thaz/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/theme/thaz/js/smooth-sliding-menu.js"></script>-->
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
    $(document).ready(function() {
      $("#submit").click(function() {
        $("#submit").attr('disabled', true);
        $("#submit").val('Sending...');
        var SentMessageMessage = $("#SentMessageMessage").val();
        var SentMessageDisplayname = $("#SentMessageDisplayname").val();
        var pages = $("#pages").val();
        var SentMessageRecipient = $("#SentMessageRecipient").val();
        //var message = $("#message").val();
        //var contact = $("#contact").val();

        $("#returnmessage").empty(); // To empty previous error/success message.
        // Checking for blank fields.
        if (SentMessageDisplayname == '') { //||  || ) {
          alert("Please Fill Display Field");
          $("#SentMessageDisplayname").focus();
          $("#submit").attr('disabled', false);
          $("#submit").val('Send Message');
        } else if (SentMessageMessage == '') {
          alert("Please Fill Message Name Field");
          $("#SentMessageMessage").focus();
          $("#submit").attr('disabled', false);
          $("#submit").val('Send Message');
        } else {
          // Returns successful data submission message when the entered information is stored in database.
          $.post("../send_bulk.php", {
            SentMessageMessage: SentMessageMessage,
            SentMessageDisplayname: SentMessageDisplayname,
            pages: pages,
            SentMessageRecipient: SentMessageRecipient

            //message1: message,
            //contact1: contact
          }, function(data) {
            $("#submit").attr('disabled', false);
            $("#submit").val('Send Message');
            $("#returnmessage").append(data); // Append returned message to message paragraph.

            if (data != "") {
              $("#MessageIndexForm")[0].reset(); // To reset form fields on success.
            }
          });
        }
      });
    });

    $("#clear").click(function() {
      $("#SentMessageMessage").val('')
    })


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
<?php
mysqli_free_result($masterTransaction);
?>