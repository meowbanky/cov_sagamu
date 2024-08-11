<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<link href="../jquery-mobile/jquery.mobile.theme-1.3.0.min.css" rel="stylesheet" type="text/css">
<link href="../jquery-mobile/jquery.mobile.structure-1.3.0.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.core.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.theme.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.datepicker.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.autocomplete.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.menu.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.accordion.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.dialog.min.css" rel="stylesheet" type="text/css">
<link href="../SpryAssets/jquery.ui.resizable.min.css" rel="stylesheet" type="text/css">
<script src="../jquery-mobile/jquery-1.11.1.min.js"></script>
<script src="../jquery-mobile/jquery.mobile-1.3.0.min.js"></script>
<script src="../SpryAssets/jquery.ui-1.10.4.datepicker.min.js"></script>
<script src="../SpryAssets/jquery.ui-1.10.4.autocomplete.min.js"></script>
<script src="../SpryAssets/jquery.ui-1.10.4.accordion.min.js"></script>
<script src="../SpryAssets/jquery.ui-1.10.4.dialog.min.js"></script>
</head>

<body>
<div data-role="page" id="page">
  <div data-role="header">
    <h1>Header</h1>
  </div>
  <div data-role="content">Content</div>
  <ul data-role="listview" data-inset="true">
    <li><a href="#">
      <h3>Page</h3>
      <p>Lorem ipsum</p>
      <span class="ui-li-count">1</span>
      <p class="ui-li-aside">Aside</p>
    </a><a href="#">Default</a></li>
    <li><a href="#">
      <h3>Page</h3>
      <p>Lorem ipsum</p>
      <span class="ui-li-count">1</span>
      <p class="ui-li-aside">Aside</p>
    </a><a href="#">Default</a></li>
    <li><a href="#">
      <h3>Page</h3>
      <p>Lorem ipsum</p>
      <span class="ui-li-count">1</span>
      <p class="ui-li-aside">Aside</p>
    </a><a href="#">Default</a></li>
  </ul>
  <div class="ui-grid-a">
    <div class="ui-block-a">Block 1,1
      <div data-role="fieldcontain">
        <label for="textinput">Text Input:</label>
        <input type="text" name="textinput" id="textinput" value=""  />
      </div>
    </div>
    <div class="ui-block-b">Block 1,2
      <div data-role="fieldcontain">
        <label for="number">Number:</label>
        <input type="number" name="number" id="number" value=""  />
      </div>
    </div>
      <div class="ui-block-b">blok
        <div data-role="fieldcontain">
          <label for="date">Date:</label>
          <input type="date" name="date" id="date" value=""  />
        </div>
        <div data-role="fieldcontain">
          <label for="flipswitch">Option:</label>
          <select name="flipswitch" id="flipswitch" data-role="slider">
            <option value="off">Off</option>
            <option value="on">On</option>
          </select>
        </div>
        <button data-icon="arrow-d">Button</button>
          
      </div>
      <div data-role="fieldcontain">
        <label for="selectmenu" class="select">Options:</label>
        <select name="selectmenu" id="selectmenu">
          <option value="option1">Option 1</option>
          <option value="option2">Option 2</option>
          <option value="option3">Option 3</option>
        </select>
        <div data-role="fieldcontain">
          <label for="textarea">Textarea:</label>
          <textarea cols="40" rows="8" name="textarea" id="textarea"></textarea>
        </div>
          
      </div>
    <div data-role="fieldcontain">
        <label for="slider">Value:</label>
        <input type="range" name="slider" id="slider" value="0" min="0" max="100" />
      </div>
    <input type="text" id="Datepicker1">
    <input type="text" id="Autocomplete1">
    <div data-role="fieldcontain">
      <label for="month">Month:</label>
      <input type="month" name="month" id="month" value=""  />
        
    </div>
    <div id="Accordion1">
        <h3><a href="#">Section 1</a></h3>
        <div>
          <p>Content 1</p>
        </div>
        <h3><a href="#">Section 2</a></h3>
        <div>
          <p>Content 2</p>
        </div>
        <h3><a href="#">Section 3</a></h3>
        <div>
          <p>Content 3</p>
        </div>
    </div>
    <div id="Dialog1">Content for New Dialog Goes Here</div>
      <input type="tel">09989898
      <article>Content for New article Tag Goes Here</article>
<footer>Content for New footer Tag Goes Here</footer>
  </div>
    
    
  <div data-role="footer">
    <h4>Footer</h4>
  </div>
</div>
<script type="text/javascript">
$(function() {
	$( "#Datepicker1" ).datepicker(); 
});
$(function() {
	$( "#Autocomplete1" ).autocomplete(); 
});
$(function() {
	$( "#Accordion1" ).accordion(); 
});
$(function() {
	$( "#Dialog1" ).dialog(); 
});
</script>
</body>
</html>