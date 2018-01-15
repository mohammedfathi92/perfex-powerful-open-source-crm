<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
  <title><?php if (isset($title)){ echo $title; } ?></title>
  <?php if(get_option('favicon') != ''){ ?>
  <link href="<?php echo base_url('uploads/company/'.get_option('favicon')); ?>" rel="shortcut icon">
  <?php } ?>
  <?php if(!isset($exclude_reset_css)){ ?>
  <?php echo app_stylesheet('assets/css','reset.css'); ?>
  <?php } ?>
  <link href='<?php echo base_url('assets/plugins/roboto/roboto.css'); ?>' rel='stylesheet'>
  <link href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <?php if(is_rtl(true)){ ?>
  <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap-arabic/css/bootstrap-arabic.min.css'); ?>">
  <?php } ?>
  <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
  <link href="<?php echo base_url('assets/plugins/datatables/datatables.min.css'); ?>" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/plugins/font-awesome/css/font-awesome.min.css'); ?>">
  <link href="<?php echo base_url('assets/plugins/datetimepicker/jquery.datetimepicker.min.css'); ?>" rel="stylesheet">
  <link href="<?php echo base_url('assets/plugins/bootstrap-select/css/bootstrap-select.min.css'); ?>" rel="stylesheet">
  <?php if(is_client_logged_in()){ ?>
  <link href="<?php echo base_url('assets/plugins/dropzone/min/basic.min.css'); ?>" rel='stylesheet'>
  <link href="<?php echo base_url('assets/plugins/dropzone/min/dropzone.min.css'); ?>" rel='stylesheet'>
  <link href='<?php echo base_url('assets/plugins/gantt/css/style.css'); ?>' rel='stylesheet' />
  <link href='<?php echo base_url('assets/plugins/jquery-comments/css/jquery-comments.css'); ?>' rel='stylesheet' />
  <link href='<?php echo base_url('assets/plugins/fullcalendar/fullcalendar.min.css'); ?>' rel='stylesheet' />
  <?php } ?>
  <link href="<?php echo base_url('assets/plugins/lightbox/css/lightbox.min.css'); ?>" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css'); ?>">
  <?php echo app_stylesheet('assets/css','bs-overides.css'); ?>
  <?php echo app_stylesheet(template_assets_path().'/css','style.css'); ?>
  <?php if(file_exists(FCPATH.'assets/css/custom.css')){ ?>
  <link href="<?php echo base_url('assets/css/custom.css'); ?>" rel="stylesheet" type='text/css'>
  <?php } ?>
  <?php render_custom_styles(array('general','tabs','buttons','customers','modals')); ?>
  <?php $isRTL = (is_rtl(true) ? 'true' : 'false'); ?>
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
      <!-- DO NOT REMOVE -->
    <?php do_action('app_customers_head',array('language'=>$language)); ?>
    </head>
    <body class="customers <?php if(isset($bodyclass)){echo $bodyclass; } ?>" <?php if(is_rtl(true)){ echo 'dir="rtl"';} ?>>
