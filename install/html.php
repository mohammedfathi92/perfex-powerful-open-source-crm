<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <title>Perfex - Installation</title>
  <link href="../assets/css/reset.css" rel="stylesheet">
  <link href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href='../assets/css/bs-overides.css' rel='stylesheet' type='text/css'>
  <link href='../assets/plugins/bootstrap-select/css/bootstrap-select.min.css' rel='stylesheet' type='text/css'>
  <style>
        body {
          font-family:'Open Sans';
          background:#f1f1f1;
        }
        h3 {
         margin-top: 7px;
         font-size: 18px;
        }

        .install-row {
          border:1px solid #e4e5e7;
          border-radius:3px;
          background:#fff;
          padding:15px;
          box-shadow: 0px 2px 4px #d6d6d6;
          display:inline-block;
          width:100%;
        }
        .install-row.install-steps {
          margin-bottom:15px;
          box-shadow: 0px 0px 1px #d6d6d6;
        }
        .logo {
          margin-top:15px;
          margin-bottom:10px;
          padding:15px;
          display:inline-block;
          width:100%;
        }
        .logo img {
          display:block;
          margin:0 auto;
        }
        .control-label {
          font-size:13px;
          font-weight:600;
        }
        .padding-10 {
          padding:10px;
        }
        .mbot15 {
          margin-bottom:15px;
        }
        .bg-default {
          background: #03a9f4;
          border:1px solid #03a9f4;
          color:#fff;
        }
        .bg-success {
          border: 1px solid #dff0d8;
        }
        .bg-not-passed {
          border:1px solid #f1f1f1;
          border-radius:2px;
        }
        .bg-not-passed {
          border-right:0px;
        }
        .bg-not-passed.finish {
          border-right:1px solid #f1f1f1 !important;
        }
        .bg-not-passed h5 {
          font-weight:normal;
          color:#6b6b6b;
        }
        .form-control {
          box-shadow:none;
        }
        .bold {
          font-weight:600;
        }
        .col-xs-5ths,
        .col-sm-5ths,
        .col-md-5ths,
        .col-lg-5ths {
          position: relative;
          min-height: 1px;
          padding-right: 15px;
          padding-left: 15px;
        }
        .col-xs-5ths {
          width: 20%;
          float: left;
        }
        b {
          font-weight:600;
        }
        .bootstrap-select .btn-default {
          background: #fff !important;
          border: 1px solid #d6d6d6 !important;
          box-shadow: none;
          color: #494949 !important;
          padding: 6px 12px;
        }
      </style>
    </head>
    <body>
     <div class="container">
      <div class="row">

       <div class="col-md-8 col-md-offset-2">
        <div class="logo">
          <img src="logo.png">
        </div>
        <div class="install-row install-steps">
            <div class="col-xs-5ths text-center <?php if($passed_steps[1] == true || $step == 1){echo 'bg-default';} ?> padding-10">
            <h5>Requirements</h5>
          </div>
          <div class="col-xs-5ths text-center <?php if($passed_steps[2] || $step == 2){echo 'bg-default';} else {echo 'bg-not-passed';} ?> padding-10">
            <h5>Permissions</h5>
          </div>
          <div class="col-xs-5ths text-center <?php if($passed_steps[3] || $step == 3){echo 'bg-default';} else {echo 'bg-not-passed';} ?> padding-10">
           <h5> Database setup</h5>
         </div>
         <div class="col-xs-5ths text-center <?php if($passed_steps[4] || $step == 4){echo 'bg-default';} else {echo 'bg-not-passed';} ?> padding-10">
           <h5> Install</h5>
         </div>
         <div class="finish col-xs-5ths text-center <?php if($step == 5){echo 'bg-success';}else {echo 'bg-not-passed';} ?> padding-10">
           <h5> Finish</h5>
         </div>
        </div>
        <div class="install-row">

         <?php if($debug != ''){ ?>
         <p class="sql-debug-alert text-success" style="margin-bottom:20px;">
           <b><?php echo $debug; ?></b>
         </p>
         <?php } ?>

         <?php if(isset($error) && $error != ''){ ?>
         <div class="alert alert-danger text-center">
           <?php echo $error; ?>
         </div>
         <?php } ?>
         <?php if($step == 1){
           include_once('requirements.php');
         } else if($step == 2){
          include_once('file_permissions.php');
        } else if($step == 3){ ?>
        <?php echo '<form action="" method="post" accept-charset="utf-8">'; ?>
        <?php echo '<input type="hidden" name="step" value="'.$step.'">'; ?>
        <div class="form-group">
          <label for="hostname" class="control-label">Hostname</label>
          <input type="text" class="form-control" name="hostname" value="localhost">
        </div>
        <div class="form-group">
          <label for="database" class="control-label">Database Name</label>
          <input type="text" class="form-control" name="database">
        </div>
        <div class="form-group">
          <label for="username" class="control-label">Username</label>
          <input type="text" class="form-control" name="username">
        </div>
        <div class="form-group">
          <label for="password" class="control-label"><i class="glyphicon glyphicon-info-sign" title='Avoid use of single(&lsquo;) and double(&ldquo;) quotes in your password'></i> Password</label>
          <input type="text" class="form-control" name="password">
        </div>
        <hr />
        <div class="text-left">
         <button type="submit" class="btn btn-success">Check Database</button>
       </div>
     </form>
     <?php } else if($step == 4){ ?>
     <?php echo '<form action="" method="post" accept-charset="utf-8" id="installForm">'; ?>
     <?php echo '<input type="hidden" name="step" value="'.$step.'">'; ?>
     <?php echo '<input type="hidden" name="hostname" value="'.$_POST['hostname'].'">'; ?>
     <?php echo '<input type="hidden" name="username" value="'.$_POST['username'].'">'; ?>
     <?php echo '<input type="hidden" name="password" value="'.$_POST['password'].'">'; ?>
     <?php echo '<input type="hidden" name="database" value="'.$_POST['database'].'">'; ?>
     <div class="form-group">
      <div class="form-group">
       <label for="base_url" class="control-label">Base URL <a href="https://help.perfexcrm.com/faq/what-is-base-url/" target="_blank">Read more...</a></label>
       <input type="url" class="form-control" value="<?php echo $this->guess_base_url(); ?>" name="base_url" id="base_url" required>
     </div>
   </div>
   <hr />
   <h5>Admin login</h5>
   <hr />
    <div class="form-group">
     <label for="firstname" class="control-label">Firstname</label>
     <input type="text" class="form-control" name="firstname" id="firstname" required>
   </div>
   <div class="form-group">
     <label for="lastname" class="control-label">Lastname</label>
     <input type="text" class="form-control" name="lastname" id="lastname" required>
   </div>
   <div class="form-group">
     <label for="admin_email" class="control-label">Email</label>
     <input type="email" class="form-control" name="admin_email" id="admin_email" required>
   </div>
   <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label for="admin_password" class="control-label">Password</label>
        <input type="password" class="form-control" name="admin_password" id="admin_password" required>
      </div>
    </div>
    <div class="col-md-6">
     <div class="form-group">
      <label for="admin_passwordr" class="control-label">Repeat Password</label>
      <input type="password" class="form-control" name="admin_passwordr" id="admin_passwordr" required>
    </div>
  </div>
</div>
<h5>Other Settings</h5>
<hr />
<div class="form-group">
  <label for="timezone" class="control-label">Timezone</label>
  <select name="timezone" data-live-search="true" id="timezone" class="form-control" required data-none-selected-text="Select system timezone">
    <option value=""></option>
    <?php foreach($this->get_timezones_list() as $key => $timezones){ ?>
    <optgroup label="<?php echo $key; ?>">
      <?php foreach($timezones as $timezone){ ?>
      <option value="<?php echo $timezone; ?>"><?php echo $timezone; ?></option>
      <?php } ?>
    </optgroup>
    <?php } ?>
  </select>
</div>
<hr />
<div class="text-left">
  <button type="submit" class="btn btn-success" id="installBtn">Install</button>
</div>
</form>
<?php } else if($step == 5){ ?>
<h4 class="bold">Installation successful!</h4>
<?php if(isset($rename_failed)){ ?>
<p class="text-danger">
  Failed to rename application/config/app-config-sample.php to app-config.php. Please navigate to application/config/ and rename the file app-config-sample.php to app-config.php
</p>
<?php } ?>
<p>Please <b>delete the install directory</b> and login as administrator at <a href="<?php echo $_POST['base_url']; ?>admin" target="_blank"><?php echo $_POST['base_url']; ?>admin</a></p>
<hr />
<p><b>Remember:</b></p>
<ul class="list-unstyled">
  <li>Administrators login at <a href="<?php echo $_POST['base_url']; ?>admin" target="_blank"><?php echo $_POST['base_url']; ?>admin</a></li>
  <li>Customers login at <a href="<?php echo $_POST['base_url']; ?>clients" target="_blank"><?php echo $_POST['base_url']; ?>clients</a></li>
</ul>
<hr />
<h4>
    <b>404 Not Found After Installation? - <a href="https://help.perfexcrm.com/404-not-found-after-installation/" target="_blank">Read more</a></b>
</h4>
<hr />
<h4>
    <b>Getting Started Guide - <a href="https://help.perfexcrm.com/quick-installation-getting-started-tutorial/" target="_blank">Read more</a></b>
</h4>
<?php } ?>
</div>
</div>
</div>
</div>
<script src='../assets/plugins/jquery/jquery.min.js'></script>
<script src='../assets/plugins/bootstrap/js/bootstrap.min.js'></script>
<script src='../assets/plugins/bootstrap-select/js/bootstrap-select.min.js'></script>
<script>
  $(function(){
    $('select').selectpicker();
    $('#installForm').on('submit',function(e){
        $('#installBtn').prop('disabled',true);
    });
    setTimeout(function(){
      $('.sql-debug-alert').slideUp();
    },4000);
  });
</script>
</body>
</html>
