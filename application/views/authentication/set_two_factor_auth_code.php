<?php $this->load->view('authentication/includes/head.php'); ?>
<body class="authentication">
 <div class="container">
  <div class="row">
   <div class="col-md-4 col-md-offset-4 authentication-form-wrapper">
   <div class="company-logo">
     <?php echo get_company_logo(); ?>
   </div>
   <div class="mtop40 authentication-form">
    <h1><?php echo _l('admin_two_factor_auth_heading'); ?>
      <br /><small><?php echo _l('two_factor_authentication'); ?></small>
    </h1>
    <?php echo form_open($this->uri->uri_string()); ?>
    <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>
    <?php $this->load->view('authentication/includes/alerts'); ?>
    <?php echo render_input('code','two_factor_authentication_code'); ?>
    <div class="form-group">
      <a href="<?php echo site_url('authentication'); ?>"><?php echo _l('back_to_login'); ?></a>
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-info btn-block"><?php echo _l('confirm'); ?></button>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>
</div>
</div>
</body>
</html>
