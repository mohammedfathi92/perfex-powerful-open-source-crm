<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
  <?php echo form_open_multipart($this->uri->uri_string().'?group='.$view_name,array('id'=>'settings-form')); ?>
  <div class="row">
   <?php if($this->session->flashdata('debug')){ ?>
   <div class="col-lg-12">
    <div class="alert alert-warning">
     <?php echo $this->session->flashdata('debug'); ?>
   </div>
 </div>
 <?php } ?>
 <div class="col-md-3">
  <div class="panel_s">
   <div class="panel-body">
    <ul class="nav navbar-pills nav-tabs nav-stacked">
      <?php $settings_groups = array(
        array(
          'name'=>'general',
          'lang'=>_l('settings_group_general'),
          'order'=>1,
          ),
        array(
          'name'=>'company',
          'lang'=>_l('company_information'),
          'order'=>2,
          ),
        array(
          'name'=>'localization',
          'lang'=>_l('settings_group_localization'),
          'order'=>3,
          ),
        array(
          'name'=>'email',
          'lang'=>_l('settings_group_email'),
          'order'=>4,
          ),
        array(
          'name'=>'sales',
          'lang'=>_l('settings_group_sales'),
          'order'=>5,
          ),
        array(
          'name'=>'online_payment_modes',
          'lang'=>_l('settings_group_online_payment_modes'),
          'order'=>6,
          ),
        array(
          'name'=>'clients',
          'lang'=>_l('settings_group_clients'),
          'order'=>7,
          ),
        array(
          'name'=>'tasks',
          'lang'=>_l('tasks'),
          'order'=>8,
          ),
        array(
          'name'=>'tickets',
          'lang'=>_l('support'),
          'order'=>9,
          ),
        array(
          'name'=>'leads',
          'lang'=>_l('leads'),
          'order'=>10,
          ),
        array(
          'name'=>'calendar',
          'lang'=>_l('settings_calendar'),
          'order'=>11,
          ),
        array(
          'name'=>'pdf',
          'lang'=>_l('settings_pdf'),
          'order'=>12,
          ),
        array(
          'name'=>'cronjob',
          'lang'=>_l('settings_group_cronjob'),
          'order'=>13,
          ),
        array(
          'name'=>'tags',
          'lang'=>_l('tags'),
          'order'=>14,
          ),
        array(
          'name'=>'pusher',
          'lang'=>'Pusher.com',
          'order'=>15,
          ),
        array(
          'name'=>'misc',
          'lang'=>_l('settings_group_misc'),
          'order'=>16,
          ),
        array(
          'name'=>'update',
          'lang'=>_l('settings_update'),
          'order'=>17,
          ),
        );

      $settings_groups = do_action('settings_groups',$settings_groups);
      usort($settings_groups, function($a, $b) {
          return $a['order'] - $b['order'];
      });
      ?>
      <?php
      $i = 0;
      foreach($settings_groups as $group){
        if($group['name'] == 'update' && !is_admin()){continue;}
        ?>
        <li<?php if($i == 0){echo " class='active'"; } ?>>
        <a href="<?php echo (!isset($group['url']) ? admin_url('settings?group='.$group['name']) : $group['url']) ?>" data-group="<?php echo $group['name']; ?>">
          <?php echo $group['lang']; ?></a>
        </li>
        <?php $i++; } ?>
      </ul>
      <div class="btn-bottom-toolbar text-right">
       <button type="submit" class="btn btn-info"><?php echo _l('settings_save'); ?></button>
     </div>
   </div>
 </div>
</div>
<div class="col-md-9">
  <div class="panel_s">
   <div class="panel-body">
    <?php do_action('before_settings_group_view',$group_view); ?>
    <?php echo $group_view; ?>
    <?php do_action('after_settings_group_view',$group_view); ?>
  </div>
</div>
</div>
<div class="clearfix"></div>
</div>
<?php echo form_close(); ?>
<div class="btn-bottom-pusher"></div>
</div>
</div>
<div id="new_version"></div>
<?php init_tail(); ?>
<script>
 $(function(){
  $('input[name="settings[email_protocol]"]').on('change',function(){
      if($(this).val() == 'mail'){
        $('.smtp-fields').addClass('hide');
      } else {
         $('.smtp-fields').removeClass('hide');
      }
  });
  $('.sms_gateway_active input').on('change',function(){
      if($(this).val() == '1') {
          $('body .sms_gateway_active').not($(this).parents('.sms_gateway_active')[0]).find('input[value="0"]').prop('checked',true);
      }
  });
   <?php if($view_name == 'pusher'){ ?>
    <?php if(get_option('desktop_notifications') == '1'){ ?>
      // Let's check if the browser supports notifications
      if (!("Notification" in window)) {
        $('#pusherHelper').html('<div class="alert alert-danger">Your browser does not support desktop notifications, please disable this option or use more modern browser.</div>');
      } else {
        if(Notification.permission == "denied"){
          $('#pusherHelper').html('<div class="alert alert-danger">Desktop notifications not allowed in browser settings, search on Google "How to allow desktop notifications for <?php echo $this->agent->browser(); ?>"</div>');
        }
      }
      <?php } ?>
      <?php if(get_option('pusher_realtime_notifications') == '0'){ ?>
        $('input[name="settings[desktop_notifications]"]').prop('disabled',true);
        <?php } ?>
        <?php } ?>
        $('input[name="settings[pusher_realtime_notifications]"]').on('change',function(){
          if($(this).val() == '1'){
            $('input[name="settings[desktop_notifications]"]').prop('disabled',false);
          } else {
            $('input[name="settings[desktop_notifications]"]').prop('disabled',true);
            $('input[name="settings[desktop_notifications]"][value="0"]').prop('checked',true);
          }
        });
        $('.test_email').on('click', function() {
          var email = $('input[name="test_email"]').val();
          if (email != '') {
           $(this).attr('disabled', true);
           $.post(admin_url + 'emails/sent_smtp_test_email', {
            test_email: email
          }).done(function(data) {
            window.location.reload();
          });
        }
      });
        $('#update_app').on('click',function(e){
         e.preventDefault();
         $('input[name="settings[purchase_key]"]').parents('.form-group').removeClass('has-error');
         var purchase_key = $('input[name="settings[purchase_key]"]').val();
         var latest_version = $('input[name="latest_version"]').val();
         var update_errors;
         if(purchase_key != ''){
           var ubtn = $(this);
           ubtn.html('<?php echo _l('wait_text'); ?>');
           ubtn.addClass('disabled');
           $.post(admin_url+'auto_update',{purchase_key:purchase_key,latest_version:latest_version,auto_update:true}).done(function(){
             window.location.reload();
           }).fail(function(response){
             update_errors = JSON.parse(response.responseText);
             $('#update_messages').html('<div class="alert alert-danger"></div>');
             for (var i in update_errors){
               $('#update_messages .alert').append('<p>'+update_errors[i]+'</p>');
             }
             ubtn.removeClass('disabled');
             ubtn.html($('.update_app_wrapper').data('original-text'));
           });
         } else {
          $('input[name="settings[purchase_key]"]').parents('.form-group').addClass('has-error');
        }
      });
      });
    </script>
  </body>
  </html>
