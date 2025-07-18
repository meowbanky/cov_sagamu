<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Untitled Document</title>
<link href="../css/bootstrap-4.0.0.css" rel="stylesheet" type="text/css">
</head>

<body>
<div id="accordion1" role="tablist">
  <div class="card">
    <div class="card-header" role="tab" id="headingOne1">
      <h5 class="mb-0"> <a data-toggle="collapse" href="#collapseOne1" role="button" aria-expanded="true" aria-controls="collapseOne1"> Collapsible Group 1 </a> </h5>
    </div>
    <div id="collapseOne1" class="collapse show" role="tabpanel" aria-labelledby="headingOne1" data-parent="#accordion1">
      <div class="card-body">Content for Accordion Panel 1</div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" role="tab" id="headingTwo1">
      <h5 class="mb-0"> <a class="collapsed" data-toggle="collapse" href="#collapseTwo1" role="button" aria-expanded="false" aria-controls="collapseTwo1"> Collapsible Group 2 </a> </h5>
    </div>
    <div id="collapseTwo1" class="collapse" role="tabpanel" aria-labelledby="headingTwo1" data-parent="#accordion1">
      <div class="card-body">Content for Accordion Panel 2</div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" role="tab" id="headingThree1">
      <h5 class="mb-0"> <a class="collapsed" data-toggle="collapse" href="#collapseThree1" role="button" aria-expanded="false" aria-controls="collapseThree1"> Collapsible Group 3 </a> </h5>
    </div>
    <div id="collapseThree1" class="collapse" role="tabpanel" aria-labelledby="headingThree1" data-parent="#accordion1">
      <div class="card-body">Content for Accordion Panel 3</div>
    </div>
  </div>
</div>
<div class="row">
      <div class="col-lg-6" align="right"><img src="../images/mhwun_logo_web.jpg"></div>
      <div class="col-lg-6"></div>
</div>
<div class="row">
      <div class="col-lg-6"></div>
      <div class="col-lg-6" align="left"><img src="../images/mhwun_logo_web.jpg"></div>
</div>
<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/bootstrap-4.0.0.js"></script>
</body>
</html>