<?php echo form_open_multipart(admin_url('tasks/task/'.$id),array('id'=>'task-form')); ?>
<div class="modal fade<?php if(isset($task)){echo ' edit';} ?>" id="_task_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"<?php if($this->input->get('opened_from_lead_id')){echo 'data-lead-id='.$this->input->get('opened_from_lead_id'); } ?>>
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">
          <?php echo $title; ?>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <?php
              $rel_type = '';
              $rel_id = '';
              if(isset($task) || ($this->input->get('rel_id') && $this->input->get('rel_type'))){
                  $rel_id = isset($task) ? $task->rel_id : $this->input->get('rel_id');
                  $rel_type = isset($task) ? $task->rel_type : $this->input->get('rel_type');
               }
               if(isset($task) && $task->billed == 1){
                 echo '<div class="alert alert-success text-center no-margin">'._l('task_is_billed','<a href="'.admin_url('invoices/list_invoices/'.$task->invoice_id).'" target="_blank">'.format_invoice_number($task->invoice_id)). '</a></div><br />';
               }
             ?>
             <?php if(isset($task)){ ?>
             <div class="pull-right mbot10 task-single-menu task-menu-options">
              <div class="content-menu hide">
               <ul>
                <?php if(has_permission('tasks','','create')){ ?>
                <?php
                $copy_template = "";
                if(total_rows('tblstafftaskassignees',array('taskid'=>$task->id)) > 0){
                  $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_assignees' id='copy_task_assignees' checked><label for='copy_task_assignees'>"._l('task_single_assignees')."</label></div>";
                }
                if(total_rows('tblstafftasksfollowers',array('taskid'=>$task->id)) > 0){
                  $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_followers' id='copy_task_followers' checked><label for='copy_task_followers'>"._l('task_single_followers')."</label></div>";
                }
                if(total_rows('tbltaskchecklists',array('taskid'=>$task->id)) > 0){
                 $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_checklist_items' id='copy_task_checklist_items' checked><label for='copy_task_checklist_items'>"._l('task_checklist_items')."</label></div>";
               }
               if(total_rows('tblfiles',array('rel_id'=>$task->id,'rel_type'=>'task')) > 0){
                 $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_task_attachments' id='copy_task_attachments'><label for='copy_task_attachments'>"._l('task_view_attachments')."</label></div>";
               }

               $copy_template .= "<p>"._l('task_status')."</p>";
               $task_copy_statuses = do_action('task_copy_statuses',$task_statuses);
               foreach($task_copy_statuses as $copy_status){
                $copy_template .= "<div class='radio radio-primary'><input type='radio' value='".$copy_status['id']."' name='copy_task_status' id='copy_task_status_".$copy_status['id']."'".($copy_status['id'] == do_action('copy_task_default_status',1) ? ' checked' : '')."><label for='copy_task_status_".$copy_status['id']."'>".$copy_status['name']."</label></div>";
              }

              $copy_template .= "<div class='text-center'>";
              $copy_template .= "<button type='button' data-task-copy-from='".$task->id."' class='btn btn-success copy_task_action'>"._l('copy_task_confirm')."</button>";
              $copy_template .= "</div>";
              ?>
              <li> <a href="#" onclick="return false;" data-placement="bottom" data-toggle="popover" data-content="<?php echo htmlspecialchars($copy_template); ?>" data-html="true"><?php echo _l('task_copy'); ?></span></a>
              </li>
              <?php } ?>
              <?php if(has_permission('tasks','','delete')){ ?>
              <li>
               <a href="<?php echo admin_url('tasks/delete_task/'.$task->id); ?>" class="_delete task-delete">
                 <?php echo _l('task_single_delete'); ?>
               </a>
             </li>
             <?php } ?>
           </ul>
         </div>
         <?php if(has_permission('tasks','','delete') || has_permission('tasks','','create')){ ?>
         <a href="#" onclick="return false;" class="trigger manual-popover mright5">
          <i class="fa fa-circle-thin" aria-hidden="true"></i>
          <i class="fa fa-circle-thin" aria-hidden="true"></i>
          <i class="fa fa-circle-thin" aria-hidden="true"></i>
        </a>
        <?php } ?>
      </div>
      <?php } ?>
      <div class="checkbox checkbox-primary no-mtop checkbox-inline">
        <input type="checkbox" id="task_is_public" name="is_public" <?php if(isset($task)){if($task->is_public == 1){echo 'checked';}}; ?>>
        <label for="task_is_public" data-toggle="tooltip" data-placement="bottom" title="<?php echo _l('task_public_help'); ?>"><?php echo _l('task_public'); ?></label>
      </div>

      <div class="checkbox checkbox-primary checkbox-inline">
        <input type="checkbox" id="task_is_billable" name="billable"
        <?php if((isset($task) && $task->billable == 1) || (!isset($task) && get_option('task_biillable_checked_on_creation') == 1)) {echo ' checked'; }?>>
        <label for="task_is_billable"><?php echo _l('task_billable'); ?></label>
      </div>
      <div class="task-visible-to-customer checkbox checkbox-inline checkbox-primary<?php if((isset($task) && $task->rel_type != 'project') || !isset($task) || (isset($task) && $task->rel_type == 'project' && total_rows('tblprojectsettings',array('project_id'=>$task->rel_id,'name'=>'view_tasks','value'=>0)) > 0)){echo ' hide';} ?>">
        <input type="checkbox" id="task_visible_to_client" name="visible_to_client" <?php if(isset($task)){if($task->visible_to_client == 1){echo 'checked';}} ?>>
        <label for="task_visible_to_client"><?php echo _l('task_visible_to_client'); ?></label>
      </div>
      <?php if(!isset($task)){ ?>
      <a href="#" class="pull-right" onclick="slideToggle('#new-task-attachments'); return false;">
       <?php echo _l('attach_files'); ?>
     </a>
     <div id="new-task-attachments" class="hide">
      <hr />
      <div class="row attachments">
        <div class="attachment">
          <div class="col-md-12">
            <div class="form-group">
              <label for="attachment" class="control-label"><?php echo _l('add_task_attachments'); ?></label>
              <div class="input-group">
                <input type="file" extension="<?php echo str_replace('.','',get_option('allowed_files')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[0]">
                <span class="input-group-btn">
                  <button class="btn btn-success add_more_attachments p8-half" type="button"><i class="fa fa-plus"></i></button>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
    <hr />
    <?php $value = (isset($task) ? $task->name : ''); ?>
    <?php echo render_input('name','task_add_edit_subject',$value); ?>
    <div class="task-hours<?php if(isset($task) && $task->rel_type == 'project' && total_rows('tblprojects',array('id'=>$task->rel_id,'billing_type'=>3)) == 0){echo ' hide';} ?>">
      <?php $value = (isset($task) ? $task->hourly_rate : 0); ?>
      <?php echo render_input('hourly_rate','task_hourly_rate',$value); ?>
    </div>
    <div class="project-details<?php if($rel_type != 'project'){echo ' hide';} ?>">
      <div class="form-group">
        <label for="milestone"><?php echo _l('task_milestone'); ?></label>
        <select name="milestone" id="milestone" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
          <option value=""></option>
          <?php foreach($milestones as $milestone){ ?>
          <option value="<?php echo $milestone['id']; ?>" <?php if(isset($task) && $task->milestone == $milestone['id']){echo 'selected'; } ?>><?php echo $milestone['name']; ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <?php if(isset($task)){
          $value = _d($task->startdate);
        } else if(isset($start_date)){
          $value = $start_date;
        } else {
          $value = _d(date('Y-m-d'));
        }
        $date_attrs = array();
        if(isset($task) && $task->recurring > 0 && $task->last_recurring_date != null) {
          $date_attrs['disabled'] = true;
        }
        ?>
        <?php echo render_date_input('startdate','task_add_edit_start_date',$value, $date_attrs); ?>
      </div>
      <div class="col-md-6">
        <?php $value = (isset($task) ? _d($task->duedate) : ''); ?>
        <?php echo render_date_input('duedate','task_add_edit_due_date',$value,$project_end_date_attrs); ?>
      </div>
      <div class="col-md-6">
       <div class="form-group">
        <label for="priority" class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
        <select name="priority" class="selectpicker" id="priority" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
          <?php foreach(get_tasks_priorities() as $priority) { ?>
          <option value="<?php echo $priority['id']; ?>"<?php if(isset($task) && $task->priority == $priority['id'] || !isset($task) && get_option('default_task_priority') == $priority['id']){echo ' selected';} ?>><?php echo $priority['name']; ?></option>
          <?php } ?>
          <?php do_action('task_priorities_select',(isset($task)?$task:0)); ?>
        </select>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
       <label for="repeat_every" class="control-label"><?php echo _l('task_repeat_every'); ?></label>
       <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
        <option value=""></option>
        <option value="1-week" <?php if(isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('week'); ?></option>
        <option value="2-week" <?php if(isset($task) && $task->repeat_every == 2 && $task->recurring_type == 'week'){echo 'selected';} ?>>2 <?php echo _l('weeks'); ?></option>
        <option value="1-month" <?php if(isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'month'){echo 'selected';} ?>>1 <?php echo _l('month'); ?></option>
        <option value="2-month" <?php if(isset($task) && $task->repeat_every == 2 && $task->recurring_type == 'month'){echo 'selected';} ?>>2 <?php echo _l('months'); ?></option>
        <option value="3-month" <?php if(isset($task) && $task->repeat_every == 3 && $task->recurring_type == 'month'){echo 'selected';} ?>>3 <?php echo _l('months'); ?></option>
        <option value="6-month" <?php if(isset($task) && $task->repeat_every == 6 && $task->recurring_type == 'month'){echo 'selected';} ?>>6 <?php echo _l('months'); ?></option>
        <option value="1-year" <?php if(isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'year'){echo 'selected';} ?>>1 <?php echo _l('year'); ?></option>
        <option value="custom" <?php if(isset($task) && $task->custom_recurring == 1){echo 'selected';} ?>><?php echo _l('recurring_custom'); ?></option>
      </select>
    </div>
  </div>
</div>
<div class="recurring_custom <?php if((isset($task) && $task->custom_recurring != 1) || (!isset($task))){echo 'hide';} ?>">
 <div class="row">
  <div class="col-md-6">
   <?php $value = (isset($task) && $task->custom_recurring == 1 ? $task->repeat_every : 1); ?>
   <?php echo render_input('repeat_every_custom','',$value,'number',array('min'=>1)); ?>
 </div>
 <div class="col-md-6">
   <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
    <option value="day" <?php if(isset($task) && $task->custom_recurring == 1 && $task->recurring_type == 'day'){echo 'selected';} ?>><?php echo _l('task_recurring_days'); ?></option>
    <option value="week" <?php if(isset($task) && $task->custom_recurring == 1 && $task->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('task_recurring_weeks'); ?></option>
    <option value="month" <?php if(isset($task) && $task->custom_recurring == 1 && $task->recurring_type == 'month'){echo 'selected';} ?>><?php echo _l('task_recurring_months'); ?></option>
    <option value="year" <?php if(isset($task) && $task->custom_recurring == 1 && $task->recurring_type == 'year'){echo 'selected';} ?>><?php echo _l('task_recurring_years'); ?></option>
  </select>
</div>
</div>
</div>
<div id="recurring_ends_on" class="<?php if(!isset($task) || (isset($task) && $task->recurring == 0)){echo 'hide';}?>">
  <?php $value = (isset($task) ? _d($task->recurring_ends_on) : ''); ?>
  <?php echo render_date_input('recurring_ends_on','recurring_ends_on',$value); ?>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="rel_type" class="control-label"><?php echo _l('task_related_to'); ?></label>
      <select name="rel_type" class="selectpicker" id="rel_type" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
        <option value=""></option>
        <option value="project"
        <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'project'){echo 'selected';}} ?>><?php echo _l('project'); ?></option>
        <option value="invoice" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'invoice'){echo 'selected';}} ?>>
          <?php echo _l('invoice'); ?>
        </option>
        <option value="customer"
        <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'customer'){echo 'selected';}} ?>>
        <?php echo _l('client'); ?>
      </option>
      <option value="estimate" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'estimate'){echo 'selected';}} ?>>
        <?php echo _l('estimate'); ?>
      </option>
      <option value="contract" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'contract'){echo 'selected';}} ?>>
       <?php echo _l('contract'); ?>
     </option>
     <option value="ticket" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'ticket'){echo 'selected';}} ?>>
      <?php echo _l('ticket'); ?>
    </option>
    <option value="expense" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'expense'){echo 'selected';}} ?>>
     <?php echo _l('expense'); ?>
   </option>
   <option value="lead" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'lead'){echo 'selected';}} ?>>
    <?php echo _l('lead'); ?>
  </option>
  <option value="proposal" <?php if(isset($task) || $this->input->get('rel_type')){if($rel_type == 'proposal'){echo 'selected';}} ?>>
    <?php echo _l('proposal'); ?>
  </option>
</select>
</div>
</div>
<div class="col-md-6">
  <div class="form-group<?php if($rel_id == ''){echo ' hide';} ?>" id="rel_id_wrapper">
    <label for="rel_id" class="control-label"><span class="rel_id_label"></span></label>
    <div id="rel_id_select">
      <select name="rel_id" id="rel_id" class="ajax-sesarch" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
        <?php if($rel_id != '' && $rel_type != ''){
          $rel_data = get_relation_data($rel_type,$rel_id);
          $rel_val = get_relation_values($rel_data,$rel_type);
          echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
        } ?>
      </select>
    </div>
  </div>
</div>
</div>
<?php if(isset($task) && $task->status == 5 && (has_permission('create') || has_permission('edit'))){
  echo render_datetime_input('datefinished','task_finished',_dt($task->datefinished));
}
?>
<div class="form-group checklist-templates-wrapper<?php if(count($checklistTemplates) == 0 || isset($task)){echo ' hide';}  ?>">
 <label for="checklist_items"><?php echo _l('insert_checklist_templates'); ?></label>
 <select id="checklist_items" name="checklist_items[]" class="selectpicker checklist-items-template-select" multiple="1" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>" data-width="100%" data-live-search="true">
   <option value=""></option>
   <?php foreach($checklistTemplates as $chkTemplate){ ?>
   <option value="<?php echo $chkTemplate['id']; ?>">
    <?php echo $chkTemplate['description']; ?>
  </option>
  <?php } ?>
</select>
</div>
<div class="form-group">
  <div id="inputTagsWrapper">
    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
    <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($task) ? prep_tags_input(get_tags_in($task->id,'task')) : ''); ?>" data-role="tagsinput">
  </div>
</div>
<?php $rel_id_custom_field = (isset($task) ? $task->id : false); ?>
<?php echo render_custom_fields('tasks',$rel_id_custom_field); ?>
<hr />
<p class="bold"><?php echo _l('task_add_edit_description'); ?></p>
<?php echo render_textarea('description','',(isset($task) ? $task->description : ''),array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true,'onclick'=>(!isset($task) || isset($task) && $task->description == '' ? 'init_editor(\'.tinymce-task\',{height:200,auto_focus: true});' : '')),array(),'no-mbot','tinymce-task'); ?>
</div>
</div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
</div>
</div>
</div>
<?php echo form_close(); ?>
<script>
 var _rel_id = $('#rel_id'),
 _rel_type = $('#rel_type'),
 _rel_id_wrapper = $('#rel_id_wrapper'),
 data = {};

 var _milestone_selected_data;
 _milestone_selected_data = undefined;

 $(function(){

  $( "body" ).off( "change", "#rel_id" );

  var inner_popover_template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>';

  $('#_task_modal .task-menu-options .trigger').popover({
    html: true,
    placement: "bottom",
    trigger: 'click',
    title:"<?php echo _l('actions'); ?>",
    content: function() {
     return $('body').find('#_task_modal .task-menu-options .content-menu').html();
   },
   template: inner_popover_template
 });

  custom_fields_hyperlink();


  _validate_form($('#task-form'), {
    name: 'required',
    startdate: 'required'
  },task_form_handler);

  $('.rel_id_label').html(_rel_type.find('option:selected').text());
  _rel_type.on('change', function() {
   var clonedSelect = _rel_id.html('').clone();
   _rel_id.selectpicker('destroy').remove();
   _rel_id = clonedSelect;
   $('#rel_id_select').append(clonedSelect);
   $('.rel_id_label').html(_rel_type.find('option:selected').text());

   task_rel_select();
   if($(this).val() != ''){
    _rel_id_wrapper.removeClass('hide');
  } else {
    _rel_id_wrapper.addClass('hide');
  }
  init_project_details(_rel_type.val());
});

  init_datepicker();
  init_color_pickers();
  init_selectpicker();
  task_rel_select();
  $('body').on('change','#rel_id',function(){
   if($(this).val() != ''){
     if(_rel_type.val() == 'project'){
       $.get(admin_url + 'projects/get_rel_project_data/'+$(this).val()+'/'+taskid,function(project){
         $("select[name='milestone']").html(project.milestones);
         if(typeof(_milestone_selected_data) != 'undefined'){
          $("select[name='milestone']").val(_milestone_selected_data.id);
          $('input[name="duedate"]').val(_milestone_selected_data.due_date)
        }
        $("select[name='milestone']").selectpicker('refresh');
        if(project.billing_type == 3){
         $('.task-hours').addClass('project-task-hours');
       } else {
         $('.task-hours').removeClass('project-task-hours');
       }
       init_project_details(_rel_type.val(),project.allow_to_view_tasks);
     },'json');
     }
   }
 });

  <?php if(!isset($task) && $rel_id != ''){ ?>
    _rel_id.change();
    <?php } ?>

  });

 <?php if(isset($_milestone_selected_data)){ ?>
  _milestone_selected_data = '<?php echo json_encode($_milestone_selected_data); ?>';
  _milestone_selected_data = JSON.parse(_milestone_selected_data);
  <?php } ?>

  function task_rel_select(){
    var serverData = {};
    serverData.rel_id = _rel_id.val();
    data.type = _rel_type.val();
    <?php if(isset($task)){ ?>
     data.connection_type = 'task';
     data.connection_id = '<?php echo $task->id; ?>';
     <?php } ?>
     init_ajax_search(_rel_type.val(),_rel_id,serverData);
   }

   function init_project_details(type,tasks_visible_to_customer){
    var wrap = $('.non-project-details');
    var wrap_task_hours = $('.task-hours');
    if(type == 'project'){
      if(wrap_task_hours.hasClass('project-task-hours') == true){
        wrap_task_hours.removeClass('hide');
      } else {
        wrap_task_hours.addClass('hide');
      }
      wrap.addClass('hide');
      $('.project-details').removeClass('hide');
    } else {
      wrap_task_hours.removeClass('hide');
      wrap.removeClass('hide');
      $('.project-details').addClass('hide');
      $('.task-visible-to-customer').addClass('hide').prop('checked',false);
    }
    if(typeof(tasks_visible_to_customer) != 'undefined'){
      if(tasks_visible_to_customer == 1){
        $('.task-visible-to-customer').removeClass('hide');
        $('.task-visible-to-customer input').prop('checked',true);
      } else {
        $('.task-visible-to-customer').addClass('hide')
        $('.task-visible-to-customer input').prop('checked',false);
      }
    }
  }
</script>
