<?php
require_once("./include/membersite_config.php");
require_once('header.php');

if(isset($_POST['submitted']))
{
   if($fgmembersite->RegisterUser())
   {
        $fgmembersite->RedirectToURL("thank-you.html");
   }
}
?>

<div class="flex items-center justify-center min-h-[70vh]">
  <div class="w-full max-w-lg mx-auto bg-white rounded-xl shadow-lg p-8 mt-8 mb-12">
    <div class="flex flex-col items-center mb-7">
      <div class="rounded-full bg-blue-100 w-16 h-16 flex items-center justify-center mb-2">
        <i class="fa fa-user-plus text-3xl text-blue-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-blue-900 mb-1">Register New User</h2>
      <p class="text-sm text-gray-500">Fill in the form to create an account</p>
    </div>
    <form id="register" action="<?php echo $fgmembersite->GetSelfScript(); ?>" method="post" accept-charset="UTF-8" class="space-y-4">
      <input type="hidden" name="submitted" id="submitted" value="1"/>
      <input type="text" class="hidden" name="<?php echo $fgmembersite->GetSpamTrapInputName(); ?>" />

      <div class="text-xs text-gray-500 mb-1 text-right">* required fields</div>
      <?php if ($fgmembersite->GetErrorMessage()) : ?>
        <div class="bg-red-100 text-red-700 rounded px-3 py-2 text-sm mb-2">
          <i class="fa fa-triangle-exclamation mr-2"></i><?php echo $fgmembersite->GetErrorMessage(); ?>
        </div>
      <?php endif; ?>

      <div>
        <label for="name" class="block font-semibold mb-1">Your Full Name <span class="text-red-600">*</span></label>
        <input type="text" name="name" id="name"
          value="<?php echo $fgmembersite->SafeDisplay('name') ?>"
          maxlength="50"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          required />
        <span id="register_name_errorloc" class="text-red-500 text-xs"></span>
      </div>
      <div>
        <label for="email" class="block font-semibold mb-1">Email Address <span class="text-red-600">*</span></label>
        <input type="email" name="email" id="email"
          value="<?php echo $fgmembersite->SafeDisplay('email') ?>"
          maxlength="50"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          required />
        <span id="register_email_errorloc" class="text-red-500 text-xs"></span>
      </div>
      <div>
        <label for="username" class="block font-semibold mb-1">Username <span class="text-red-600">*</span></label>
        <input type="text" name="username" id="username"
          value="<?php echo $fgmembersite->SafeDisplay('username') ?>"
          maxlength="50"
          class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          required />
        <span id="register_username_errorloc" class="text-red-500 text-xs"></span>
      </div>
      <div>
        <label for="password" class="block font-semibold mb-1">Password <span class="text-red-600">*</span></label>
        <div class="pwdwidgetdiv" id="thepwddiv"></div>
        <noscript>
          <input type="password" name="password" id="password" maxlength="50"
            class="w-full border border-gray-300 rounded px-3 py-2" />
        </noscript>
        <span id="register_password_errorloc" class="text-red-500 text-xs"></span>
      </div>
      <div>
        <button type="submit" name="Submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition flex items-center justify-center">
          <i class="fa fa-paper-plane mr-2"></i> Register
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JS Validation -->
<script type='text/javascript' src='scripts/gen_validatorv31.js'></script>
<script src="scripts/pwdwidget.js" type="text/javascript"></script>
<script>
    var pwdwidget = new PasswordWidget('thepwddiv','password');
    pwdwidget.MakePWDWidget();
    var frmvalidator  = new Validator("register");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("name","req","Please provide your name");
    frmvalidator.addValidation("email","req","Please provide your email address");
    frmvalidator.addValidation("email","email","Please provide a valid email address");
    frmvalidator.addValidation("username","req","Please provide a username");
    frmvalidator.addValidation("password","req","Please provide a password");
</script>

<?php require_once('footer.php'); ?>
