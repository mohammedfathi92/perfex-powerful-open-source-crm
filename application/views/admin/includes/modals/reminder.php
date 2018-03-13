  <div class="modal fade modal-reminder reminder-modal-<?php echo $name; ?>-<?php echo $id; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <?php echo form_open('admin/misc/add_reminder/'.$id . '/'.$name,array('id'=>'form-reminder-'.$name)); ?>
        <div class="modal-header">
          <button type="button" class="close close-reminder-modal" data-rel-id="<?php echo $id; ?>" data-rel-type="<?php echo $name; ?>" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel"><i class="fa fa-question-circle" data-toggle="tooltip" title="<?php echo _l('set_reminder_tooltip'); ?>" data-placement="bottom"></i> <?php echo $reminder_title; ?></h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <?php echo form_hidden('rel_id',$id); ?>
              <?php echo form_hidden('rel_type',$name); ?>
              <?php echo render_datetime_input('date','set_reminder_date','',array('data-date-min-date'=>_d(date('Y-m-d')))); ?>
              <?php echo render_select('staff',$members,array('staffid',array('firstname','lastname')),'reminder_set_to',get_staff_user_id()); ?>
              <?php echo render_textarea('description','reminder_description'); ?>
              <?php if(total_rows('tblemailtemplates',array('slug'=>'reminder-email-staff','active'=>0)) == 0) { ?>
              <div class="form-group">
                <div class="checkbox checkbox-primary">
                  <input type="checkbox" name="notify_by_email" id="notify_by_email">
                  <label for="notify_by_email"><?php echo _l('reminder_notify_me_by_email'); ?></label>
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default close-reminder-modal" data-rel-id="<?php echo $id; ?>" data-rel-type="<?php echo $name; ?>"><?php echo _l('close'); ?></button>
          <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
        </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
