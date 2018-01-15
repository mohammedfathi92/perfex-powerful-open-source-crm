<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
  <title><?php echo $form->name; ?></title>
  <?php app_external_form_header($form); ?>
  <?php do_action('app_web_to_lead_form_head'); ?>
</head>
<body class="web-to-lead"<?php if(is_rtl(true)){ echo ' dir="rtl"';} ?>>
  <div class="container-fluid">
    <div class="row">
      <div class="<?php if($this->input->get('col')){echo $this->input->get('col');} else {echo 'col-md-12';} ?>">
        <div id="response"></div>
        <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>$form->form_key)); ?>
        <?php do_action('web_to_lead_form_start'); ?>
        <?php echo form_hidden('key',$form->form_key); ?>
        <div class="row">
          <?php foreach($form_fields as $field){
           render_form_builder_field($field);
         } ?>
         <?php if(get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1){ ?>
         <div class="col-md-12">
           <div class="form-group"><div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div>
           <div id="recaptcha_response_field" class="text-danger"></div></div>
         </div>
         <?php } ?>
         <div class="clearfix"></div>
         <div class="text-left col-md-12">
          <button class="btn btn-success" id="form_submit" type="submit"><?php echo $form->submit_btn_name; ?></button>
        </div>
      </div>
      <?php do_action('web_to_lead_form_end'); ?>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
<?php app_external_form_footer($form); ?>
<script>
 var form_id = '#<?php echo $form->form_key; ?>';
 $(function() {
   $(form_id).validate({

    submitHandler: function(form) {

     var formURL = $(form).attr("action");
     var formData = new FormData($(form)[0]);

     $('#form_submit').prop('disabled', true);

     $.ajax({
       type: $(form).attr('method'),
       data: formData,
       mimeType: $(form).attr('enctype'),
       contentType: false,
       cache: false,
       processData: false,
       url: formURL
     }).done(function(response){
      response = JSON.parse(response);
                 // In case action hook is used to redirect
                 if (response.redirect_url) {
                   window.top.location.href = response.redirect_url;
                   return;
                 }
                 if (response.success == false) {
                     $('#recaptcha_response_field').html(response.message); // error message
                   } else if (response.success == true) {
                     $(form_id).remove();
                     $('#response').html('<div class="alert alert-success">'+response.message+'</div>');
                     $('html,body').animate({
                       scrollTop: $("#online_payment_form").offset().top
                     },'slow');
                   } else {
                     $('#response').html('Something went wrong...');
                   }
                   if (typeof(grecaptcha) != 'undefined') {
                     grecaptcha.reset();
                   }
                 }).fail(function(data){
                  if (typeof(grecaptcha) != 'undefined') {
                   grecaptcha.reset();
                 }
                 $('#response').html(data.responseText);
               });
                 return false;
               }
             });
 });
</script>
<?php do_action('app_web_to_lead_form_footer'); ?>
</body>
</html>
