<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
  <?php if(get_option('favicon') != ''){ ?>
  <link href="<?php echo base_url('uploads/company/'.get_option('favicon')); ?>" rel="shortcut icon">
  <?php } ?>
  <title>
    <?php echo get_option('companyname'); ?> - Authentication
  </title>
  <?php echo app_stylesheet('assets/css','reset.css'); ?>
  <!-- Bootstrap -->
  <link href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <?php if(is_rtl()){ ?>
  <link href="<?php echo base_url('assets/plugins/bootstrap-arabic/css/bootstrap-arabic.min.css'); ?>" rel="stylesheet">
  <?php } ?>
  <link href='<?php echo base_url('assets/plugins/roboto/roboto.css'); ?>' rel='stylesheet'>
  <link href='<?php echo base_url('assets/css/bs-overides.min.css'); ?>' rel='stylesheet'>
  <?php echo app_stylesheet('assets/css','authentication.css'); ?>
  <?php if(get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != ''){ ?>
  <script src='https://www.google.com/recaptcha/api.js'></script>
  <?php } ?>
  <?php if(file_exists(FCPATH.'assets/css/custom.css')){ ?>
  <link href="<?php echo base_url('assets/css/custom.css'); ?>" rel="stylesheet">
  <?php } ?>
  <?php render_custom_styles(array('general','buttons')); ?>
  <?php do_action('app_admin_login_head'); ?>
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
    </head>
