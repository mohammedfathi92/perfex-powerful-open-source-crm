<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5" id="survey-add-edit-wrapper">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel_s">

                            <?php echo form_open($this->uri->uri_string(),array('id'=>'survey_form')); ?>
                            <div class="panel-body">
                            <h4 class="no-margin">
                            <?php echo $title; ?>
                            </h4>
                           <hr class="hr-panel-heading" />
                                <?php $value = (isset($survey) ? $survey->subject : ''); ?>
                                <?php $attrs = (isset($survey) ? array() : array('autofocus'=>true)); ?>
                                <?php echo render_input('subject','survey_add_edit_subject',$value,'text',$attrs); ?>


                                <p class="bold"><?php echo _l('survey_add_edit_short_description_view'); ?></p>
                                <?php $value = (isset($survey) ? $survey->viewdescription : ''); ?>
                                <?php echo render_textarea('viewdescription','',$value,array(),array(),'','tinymce-view-description'); ?>

                                <p class="bold"><?php echo _l('survey_add_edit_email_description'); ?></p>
                                <?php $contents = ''; if(isset($survey)){$contents = $survey->description;} ?>
                                <?php echo render_textarea('description','',$contents,array(),array(),'','tinymce-email-description'); ?>

                                <p class="bold">
                                    <?php echo _l('survey_include_survey_link'); ?> : <span class="text-info">{survey_link}</span>
                                </p>
                                <?php if($found_custom_fields){ ?>
                                <hr />
                                <p class="bold tooltip-pointer" data-toggle="tooltip" title="<?php echo _l('survey_mail_lists_custom_fields_tooltip'); ?>"><?php echo _l('survey_available_mail_lists_custom_fields'); ?></p>
                                <?php } ?>
                                <?php
                                foreach($mail_lists as $list){
                                   if(count($list['customfields']) == 0){
                                      continue;
                                  }
                                  ?>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?php echo $list['name']; ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach($list['customfields'] as $custom_field){ ?>
                                        <li><a href="#" class="add_email_list_custom_field_to_survey" data-toggle="tooltip" title="{<?php echo $custom_field['fieldslug']; ?>}" data-slug="{<?php echo $custom_field['fieldslug']; ?>}"><?php echo $custom_field['fieldname']; ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php } ?>
                                <hr />
                                <div class="clearfix"></div>
                                <?php $value = (isset($survey) ? $survey->fromname : ''); ?>
                                <?php echo render_input('fromname','survey_add_edit_from',$value); ?>
                                <?php $value = (isset($survey) ? $survey->redirect_url : ''); ?>
                                <?php echo render_input('redirect_url','survey_add_edit_redirect_url',$value,'text',array('data-toggle'=>'tooltip','title'=>'survey_add_edit_red_url_note')); ?>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="disabled" id="disabled" <?php if(isset($survey) && $survey->active == 0){echo 'checked';} ?>>
                                    <label for="disabled"><?php echo _l('survey_add_edit_disabled'); ?></label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="onlyforloggedin" id="onlyforloggedin" <?php if(isset($survey) && $survey->onlyforloggedin == 1){echo 'checked';} ?>>
                                    <label for="onlyforloggedin"><?php echo _l('survey_add_edit_only_for_logged_in'); ?></label>
                                </div>
                                <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                    <?php if(isset($survey)){ ?>
                    <div class="col-md-12">
                        <div class="panel_s">
                            <div class="panel-body">
                             <h4 class="no-margin">
                             <?php echo _l('send_survey_string'); ?>
                             </h4>
                             <hr class="hr-panel-heading" />
                                <?php echo form_open('admin/surveys/send/'.$survey->surveyid); ?>
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="no-margin text-warning"><?php echo _l('survey_send_mail_lists_note_logged_in'); ?></p>
                                        <div class="form-group">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="send_survey_to[clients]" id="ml_clients">
                                                <label for="ml_clients"><?php echo _l('survey_send_mail_list_clients'); ?></label>
                                            </div>
                                            <div class="customer-groups" style="display:none;">
                                                <div class="clearfix"></div>
                                                <div class="checkbox checkbox-primary mleft10">
                                                    <input type="checkbox" checked name="ml_customers_all" id="ml_customers_all">
                                                    <label for="ml_customers_all"><?php echo _l('survey_customers_all'); ?></label>
                                                </div>
                                                <?php foreach($customers_groups as $group){ ?>
                                                <div class="checkbox checkbox-primary mleft10 survey-customer-groups">
                                                    <input type="checkbox" name="customer_group[<?php echo $group['id']; ?>]" id="ml_customer_group_<?php echo $group['id']; ?>">
                                                    <label for="ml_customer_group_<?php echo $group['id']; ?>"><?php echo $group['name']; ?></label>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="send_survey_to[leads]" id="ml_leads">
                                                <label for="ml_leads"><?php echo _l('leads'); ?></label>
                                            </div>

                                            <div class="leads-statuses" style="display:none;">
                                               <div class="clearfix"></div>
                                               <?php foreach($leads_statuses as $status){ ?>
                                               <div class="checkbox checkbox-primary mleft10">
                                                <input type="checkbox" checked name="leads_status[<?php echo $status['id']; ?>]" id="ml_leads_status_<?php echo $status['id']; ?>">
                                                <label for="ml_leads_status_<?php echo $status['id']; ?>"><?php echo $status['name']; ?></label>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" name="send_survey_to[staff]" id="ml_staff">
                                            <label for="ml_staff"><?php echo _l('survey_send_mail_list_staff'); ?></label>
                                        </div>
                                        <?php if(count($mail_lists) > 0){ ?>
                                        <p class="bold"><?php echo _l('survey_send_mail_lists_string'); ?></p>
                                        <?php foreach($mail_lists as $list){ ?>
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" id="ml_custom_<?php echo $list['listid']; ?>" name="send_survey_to[<?php echo $list['listid']; ?>]">
                                            <label for="ml_custom_<?php echo $list['listid']; ?>"><?php echo $list['name']; ?></label>
                                        </div>
                                        <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button type="submit" class="btn btn-info"><?php echo _l('survey_send_string'); ?></button>
                                </div>
                                <div class="col-md-12">
                                    <?php if(total_rows('tblsurveysendlog',array('iscronfinished'=>0,'surveyid'=>$survey->surveyid)) > 0){ ?>
                                    <p class="text-warning"><?php echo _l('survey_send_notice'); ?></p>
                                    <?php } ?>
                                    <?php foreach($send_log as $log){ ?>
                                    <p class="text-success">
                                        <?php if(has_permission('surveys','','delete')){ ?>
                                        <a href="<?php echo admin_url('surveys/remove_survey_send/'.$log['id']); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
                                        <?php } ?>
                                        <?php echo _l('survey_added_to_queue',_dt($log['date'])); ?>
                                        ( <?php echo ($log['iscronfinished'] == 0 ? _l('survey_send_till_now'). ' ' : '') ?>
                                        <?php echo _l('survey_send_to_total',$log['total']); ?> )
                                        <br />
                                        <b class="bold">
                                            <?php echo _l('survey_send_finished',($log['iscronfinished'] == 1 ? _l('settings_yes') : _l('settings_no'))); ?>
                                        </b>
                                    </p>
                                    <?php if(!empty($log['send_to_mail_lists'])){ ?>
                                    <p>
                                        <b><?php echo _l('survey_send_to_lists'); ?>:</b> <?php
                                        $send_lists = unserialize($log['send_to_mail_lists']);
                                        foreach($send_lists as $send_list){
                                           $last = end($send_lists);
                                           echo _l($send_list,'',false) . ($last == $send_list ? '':',');
                                       }
                                       ?>
                                   </p>
                                   <?php } ?>
                                   <hr />
                                   <?php } ?>
                               </div>
                           </div>
                           <?php echo form_close(); ?>
                       </div>
                   </div>
               </div>
               <?php } ?>
           </div>
       </div>
       <div class="col-md-7" id="survey_questions_wrapper">
        <div class="panel_s">
            <div class="panel-body">
            <h4 class="no-margin">
             <?php echo _l('survey_questions_string'); ?>
            </h4>
            <hr class="hr-panel-heading" />
                <?php if(isset($survey)){ ?>
                <a href="#" onclick="survey_toggle_full_view(); return false;" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="toggle_view pull-left mtop10">
                    <i class="fa fa-expand"></i></a>
                    <div class="_buttons">
                        <?php
                        if (total_rows('tblsurveyresultsets', 'surveyid=' . $survey->surveyid) > 0) { ?>
                              <a href="<?php echo admin_url('surveys/results/' . $survey->surveyid); ?>" target="_blank" class="btn btn-success pull-right mleft10 btn-with-tooltip" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('survey_list_view_results_tooltip'); ?>"><i class="fa fa-area-chart"></i></a>
                        <?php } ?>
                        <!-- Single button -->
                        <a href="<?php echo site_url('survey/'.$survey->surveyid . '/' . $survey->hash); ?>" target="_blank" class="btn btn-success pull-right mleft10 btn-with-tooltip" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('survey_list_view_tooltip'); ?>"><i class="fa fa-eye"></i></a>
                        <?php if(has_permission('surveys','','edit')){ ?>
                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo _l('survey_insert_field'); ?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#" onclick="add_survey_question('checkbox',<?php echo $survey->surveyid; ?>);return false;">
                                        <?php echo _l('survey_field_checkbox'); ?></a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="add_survey_question('radio',<?php echo $survey->surveyid; ?>);return false;">
                                            <?php echo _l('survey_field_radio'); ?></a>
                                        </li>
                                        <li>
                                            <a href="#" onclick="add_survey_question('input',<?php echo $survey->surveyid; ?>);return false;">
                                                <?php echo _l('survey_field_input'); ?></a>
                                            </li>
                                            <li>
                                                <a href="#" onclick="add_survey_question('textarea',<?php echo $survey->surveyid; ?>);return false;">
                                                    <?php echo _l('survey_field_textarea'); ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="clearfix"></div>
                                    <hr />
                                    <?php
                                    $question_area = '<ul class="list-unstyled survey_question_callback" id="survey_questions">';
                                    if(count($survey->questions) > 0){
                                       foreach($survey->questions as $question){
                                          $question_area .= '<li>';
                                          $question_area .= '<div class="form-group question">';
                                          $question_area .= '<div class="checkbox checkbox-primary required">';
                                          if($question['required'] == 1){
                                             $_required = ' checked';
                                         } else {
                                             $_required = '';
                                         }
                                         $question_area .= '<input type="checkbox" id="req_'.$question['questionid'].'" onchange="update_question(this,\''.$question['boxtype'].'\','.$question['questionid'].');" data-question_required="'.$question['questionid'].'" name="required[]" '.$_required.'>';
                                         $question_area .= '<label for="req_'.$question['questionid'].'">'._l('survey_question_required').'</label>';
                                         $question_area .= '</div>';
                                         $question_area .= '<input type="hidden" value="" name="order[]">';
                                         // used only to identify input key no saved in database
                                         $question_area .='<label for="'.$question['questionid'].'" class="control-label display-block">'._l('question_string').'
                                         <a href="#" onclick="update_question(this,\''.$question['boxtype'].'\','.$question['questionid'].'); return false;" class="pull-right update-question-button"><i class="fa fa-refresh text-success question_update"></i></a>
                                         <a href="#" onclick="remove_question_from_database(this,'.$question['questionid'].'); return false;" class="pull-right"><i class="fa fa-remove text-danger"></i></a>
                                     </label>';
                                     $question_area .= '<input type="text" onblur="update_question(this,\''.$question['boxtype'].'\','.$question['questionid'].');" data-questionid="'.$question['questionid'].'" class="form-control questionid" value="'.$question['question'].'">';
                                     if($question['boxtype'] == 'textarea'){
                                      $question_area .= '<textarea class="form-control mtop20" disabled="disabled" rows="6">'._l('survey_question_only_for_preview').'</textarea>';
                                  } else if($question['boxtype'] == 'checkbox' || $question['boxtype'] == 'radio'){
                                      $question_area .= '<div class="row">';
                                      $x = 0;
                                      foreach($question['box_descriptions'] as $box_description){
                                         $box_description_icon_class = 'fa-minus text-danger';
                                         $box_description_function = 'remove_box_description_from_database(this,'.$box_description['questionboxdescriptionid'].'); return false;';
                                         if($x == 0){
                                            $box_description_icon_class = 'fa-plus';
                                            $box_description_function = 'add_box_description_to_database(this,'.$question['questionid'].','.$question['boxid'].'); return false;';
                                        }
                                        $question_area .= '<div class="box_area">';

                                        $question_area .= '<div class="col-md-12">';
                                        $question_area .= '<a href="#" class="add_remove_action survey_add_more_box" onclick="'.$box_description_function.'"><i class="fa '.$box_description_icon_class.'"></i></a>';
                                        $question_area .= '<div class="'.$question['boxtype'].' '.$question['boxtype'].'-primary">';
                                        $question_area .= '<input type="'.$question['boxtype'].'" disabled="disabled"/>';
                                        $question_area .= '
                                        <label>
                                            <input type="text" onblur="update_question(this,\''.$question['boxtype'].'\','.$question['questionid'].');" data-box-descriptionid="'.$box_description['questionboxdescriptionid'].'" value="'.$box_description['description'].'" class="survey_input_box_description">
                                        </label>';
                                        $question_area .= '</div>';
                                        $question_area .= '</div>';
                                        $question_area .= '</div>';
                                        $x++;
                                    }
                            // end box row
                                    $question_area .= '</div>';
                                } else {
                                  $question_area .= '<input type="text" class="form-control mtop20" disabled="disabled" value="'._l('survey_question_only_for_preview').'">';
                              }
                              $question_area .= '</div>';
                              $question_area .= '</li>';
                          }
                      }
                      $question_area .= '</ul>';
                      echo $question_area;
                      ?>
                      <?php } else { ?>
                      <p class="no-margin"><?php echo _l('survey_create_first'); ?></p>
                      <?php } ?>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
<?php init_tail(); ?>
<?php echo app_script('assets/js','surveys.js'); ?>
<script>
    $(function(){
        init_editor('.tinymce-email-description');
        init_editor('.tinymce-view-description');
    });
</script>
</body>
</html>
