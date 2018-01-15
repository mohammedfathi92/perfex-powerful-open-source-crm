<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
  <title><?php echo do_action('ticket_form_title',_l('new_ticket')); ?></title>
  <?php app_external_form_header($form); ?>
  <style>
    .g-recaptcha > div{
      margin:0 auto;
    }
  </style>
  <?php do_action('app_ticket_form_head'); ?>
</head>
<body class="ticket_form"<?php if(is_rtl(true)){ echo ' dir="rtl"';} ?>>
  <div class="container-fluid">
    <div class="row">
      <div class="<?php if($this->input->get('col')){echo $this->input->get('col');} else {echo 'col-md-12';} ?>">
        <div id="response"></div>
        <?php echo form_open($this->uri->uri_string(),array('id'=>'ticketForm')); ?>
        <?php do_action('ticket_form_start'); ?>

        <?php echo render_input('subject','ticket_form_subject','','text',array('required'=>'true')); ?>
        <?php do_action('ticket_form_after_subject'); ?>

        <div class="row">
          <div class="col-md-6">
            <?php echo render_input('name','ticket_form_name','','text',array('required'=>'true')); ?>
            <?php do_action('ticket_form_after_name'); ?>
          </div>
          <div class="col-md-6">
           <?php echo render_input('email','ticket_form_email','','email',array('required'=>'true')); ?>
           <?php do_action('ticket_form_after_email'); ?>
         </div>
       </div>

       <?php echo render_select('department',$departments,array('departmentid','name'),'ticket_form_department',(count($departments) == 1 ? $departments[0]['departmentid'] : ''),array('required'=>'true')); ?>
       <?php do_action('ticket_form_after_department'); ?>

       <?php echo render_select('priority',$priorities,array('priorityid','name'),'ticket_form_priority',do_action('new_ticket_priority_selected',2),array('required'=>'true')); ?>
       <?php do_action('ticket_form_after_priority'); ?>

       <?php if(get_option('services') == 1 && count($services) > 0){ ?>
       <?php echo render_select('service',$services,array('serviceid','name'),'ticket_form_service',(count($services) == 1 ? $services[0]['serviceid'] : '')); ?>
       <?php do_action('ticket_form_after_service'); ?>
       <?php } ?>

       <?php echo render_custom_fields('tickets',false,array('show_on_ticket_form'=>1)); ?>
       <?php do_action('ticket_form_after_custom_fields'); ?>

       <?php echo render_textarea('message','ticket_form_message','',array('required'=>'true','rows'=>8)); ?>
       <?php do_action('ticket_form_after_message'); ?>

       <div class="attachments">
         <div class="row attachment form-group">
          <div class="col-md-6 col-md-offset-3">
           <label for="attachment" class="control-label"><?php echo _l('ticket_form_attachments'); ?></label>
           <div class="input-group">
            <input type="file" extension="<?php echo str_replace('.','',get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>">
            <span class="input-group-btn">
              <button class="btn btn-info add_more_attachments" type="button"><i class="fa fa-plus"></i></button>
            </span>
          </div>
        </div>
      </div>
    </div>
    <?php do_action('ticket_form_after_attachments'); ?>

    <?php if(get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1){ ?>
    <div class="row">
      <div class="col-md-12">
       <div class="form-group"><div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div>
       <div id="recaptcha_response_field" class="text-danger"></div></div>
     </div>
   </div>
   <?php } ?>

   <div class="clearfix"></div>
   <div class="text-center">
    <button class="btn btn-success" id="form_submit" type="submit"><?php echo _l('ticket_form_submit'); ?></button>
  </div>

  <?php do_action('ticket_form_after_submit_button'); ?>

  <?php do_action('ticket_form_end'); ?>
  <?php echo form_close(); ?>
</div>
</div>
</div>
<?php app_external_form_footer($form); ?>
<script>
 var form_id = '#ticketForm';
 $(function() {
  var ticketAttachmentKey = 1;
  $('.add_more_attachments').on('click', function() {
    var allowedAttachments = "<?php echo get_option("maximum_allowed_ticket_attachments"); ?>";
    allowedAttachments = parseInt(allowedAttachments);
    var total_attachments = $('.attachments input[name*="attachments"]').length;
    if (total_attachments >= allowedAttachments) {
      return false;
    }
    var newattachment = $('.attachments').find('.attachment').eq(0).clone().appendTo('.attachments');
    newattachment.find('input').val('').attr('name','attachments['+ticketAttachmentKey+']');
    newattachment.find('input').removeAttr('aria-describedby');
    newattachment.find('input').removeAttr('aria-invalid');
    newattachment.find('p[id*="error"]').remove();
    $(newattachment).find('i').removeClass('fa-plus').addClass('fa-minus');
    $(newattachment).find('button').removeClass('add_more_attachments').addClass('remove_attachment').removeClass('btn-success').addClass('btn-danger');
    ticketAttachmentKey++;
  });

  $('body').on('click', '.remove_attachment', function() {
    $(this).parents('.attachment').remove();
  });

  $(form_id).validate({

    submitHandler:function(form) {
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
                     }, 'slow');
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
               }});

  $.each($(form_id).find('[data-custom-field-required]'), function() {
    $(this).rules("add", {
      required: true
    });
  });
});
</script>
<?php do_action('app_ticket_form_footer'); ?>
</body>
</html>
