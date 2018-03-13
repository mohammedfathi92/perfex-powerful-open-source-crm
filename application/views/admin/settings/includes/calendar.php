<ul class="nav nav-tabs" role="tablist">
  <li role="presentation" class="active">
    <a href="#general" aria-controls="general" role="tab" data-toggle="tab"><?php echo _l('settings_group_general'); ?></a>
  </li>
  <li role="presentation">
    <a href="#colors" aria-controls="colors" role="tab" data-toggle="tab"><?php echo _l('settings_calendar_colors_heading'); ?></a>
  </li>
</ul>

<div class="tab-content mtop30">
  <div role="tabpanel" class="tab-pane active" id="general">

    <a href="<?php echo admin_url('departments'); ?>" class="mbot30 display-block"><?php echo _l('setup_calendar_by_departments'); ?></a>
    <?php echo render_input('settings[google_calendar_main_calendar]','settings_gcal_main_calendar_id',get_option('google_calendar_main_calendar'),'text',array('data-toggle'=>'tooltip','title'=>'settings_gcal_main_calendar_id_help')); ?>
    <hr />
    <?php echo render_input('settings[calendar_events_limit]','calendar_events_limit',get_option('calendar_events_limit'),'number'); ?>
    <hr />
    <div class="form-group">
      <label for="settings[default_view_calendar]"><?php echo _l('default_view'); ?></label>
      <br />
      <select class="selectpicker" data-width="100%" name="settings[default_view_calendar]" id="default_view_calendar">
        <option value="month"<?php if(get_option('default_view_calendar') == 'month'){echo ' selected';} ?>><?php echo _l('month'); ?></option>
        <option value="basicWeek""<?php if(get_option('default_view_calendar') == 'basicWeek'){echo ' selected';} ?>><?php echo _l('week'); ?></option>
        <option value="basicDay""<?php if(get_option('default_view_calendar') == 'basicDay'){echo ' selected';} ?>><?php echo _l('day'); ?></option>
        <option value="agendaWeek""<?php if(get_option('default_view_calendar') == 'agendaWeek'){echo ' selected';} ?>><?php echo _l('agenda'); ?> <?php echo _l('week'); ?></option>
        <option value="agendaDay""<?php if(get_option('default_view_calendar') == 'agendaDay'){echo ' selected';} ?>><?php echo _l('agenda'); ?> <?php echo _l('day'); ?></option>
      </select>
    </div>
    <hr />
    <label><?php echo _l('calendar_first_day'); ?></label>
    <select name="settings[calendar_first_day]" class="selectpicker" data-width="100%">
      <?php
      $weekdays = get_weekdays();
      end($weekdays);
      $last = key($weekdays);
      foreach($weekdays as $key=>$val){
        if($key == $last){
          $key = 0;
        } else {
          $key = $key + 1;
        }
        ?>
        <option value="<?php echo $key; ?>" <?php if(get_option('calendar_first_day') == $key){echo 'selected';} ?>><?php echo $val; ?></option>
        <?php } ?>
      </select>
      <hr />
      <h4><?php echo _l('show_on_calendar'); ?></h4>
      <hr />
      <div class="row">
         <div class="col-md-6">
         <?php echo render_yes_no_option('hide_notified_reminders_from_calendar','hide_notified_reminders_from_calendar'); ?>
         <hr />
         <?php render_yes_no_option('show_lead_reminders_on_calendar','show_lead_reminders_on_calendar'); ?>
         <hr />
         <?php render_yes_no_option('show_customer_reminders_on_calendar','show_customer_reminders_on_calendar'); ?>
         <hr />
         <?php render_yes_no_option('show_estimate_reminders_on_calendar','show_estimate_reminders_on_calendar'); ?>
         <hr />
         <?php render_yes_no_option('show_invoice_reminders_on_calendar','show_invoice_reminders_on_calendar'); ?>
         <hr />
         <?php render_yes_no_option('show_proposal_reminders_on_calendar','show_proposal_reminders_on_calendar'); ?>
         <hr />
         <?php render_yes_no_option('show_expense_reminders_on_calendar','calendar_expense_reminder'); ?>
         <hr />
         <?php render_yes_no_option('show_credit_note_reminders_on_calendar','show_credit_note_reminders_on_calendar'); ?>
       </div>
        <div class="col-md-6">
          <?php render_yes_no_option('show_invoices_on_calendar','show_invoices_on_calendar'); ?>
          <hr />
          <?php render_yes_no_option('show_estimates_on_calendar','show_estimates_on_calendar'); ?>
          <hr />
          <?php render_yes_no_option('show_proposals_on_calendar','show_proposals_on_calendar'); ?>
          <hr />
          <?php render_yes_no_option('show_contracts_on_calendar','show_contracts_on_calendar'); ?>
          <hr />
          <?php render_yes_no_option('show_tasks_on_calendar','show_tasks_on_calendar'); ?>
          <hr />
          <?php render_yes_no_option('show_projects_on_calendar','show_projects_on_calendar'); ?>
          <hr />
        </div>

     </div>
   </div>
   <div role="tabpanel" class="tab-pane" id="colors">

    <?php echo render_color_picker('settings[calendar_invoice_color]',_l('settings_calendar_color',_l('invoice')),get_option('calendar_invoice_color')); ?>
    <?php echo render_color_picker('settings[calendar_estimate_color]',_l('settings_calendar_color',_l('estimate')),get_option('calendar_estimate_color')); ?>
    <?php echo render_color_picker('settings[calendar_proposal_color]',_l('settings_calendar_color',_l('proposal')),get_option('calendar_proposal_color')); ?>
    <?php echo render_color_picker('settings[calendar_reminder_color]',_l('settings_calendar_color',_l('reminder')),get_option('calendar_reminder_color')); ?>
    <?php echo render_color_picker('settings[calendar_contract_color]',_l('settings_calendar_color',_l('contract')),get_option('calendar_contract_color')); ?>
    <?php echo render_color_picker('settings[calendar_project_color]',_l('settings_calendar_color',_l('project')),get_option('calendar_project_color')); ?>

  </div>
