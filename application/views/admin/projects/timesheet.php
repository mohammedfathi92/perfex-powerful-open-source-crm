<!-- Timesheet Modal -->
<div class="modal fade" id="timesheet" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('projects/timesheet'),array('id'=>'timesheet_form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo _l('record_timesheet'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo form_hidden('project_id',$project->id); ?>
                        <?php echo form_hidden('timer_id'); ?>
                        <div id="additional"></div>
                        <div class="row">
                        <div class="col-md-12">
                               <div class="form-group">
                            <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                            <input type="text" class="tagsinput" id="tags" name="tags" value="" data-role="tagsinput">
                            <hr class="no-mtop" />
                        </div>
                        </div>
                        <div class="timesheet-start-end-time">
                            <div class="col-md-12">
                                <div class="form-group no-mbot">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="control-label" for="start_time"><?php echo _l('project_timesheet_start_time'); ?></label>
                                    </div>
                                    <div class="col-md-9">
                                        <?php echo render_datetime_input('start_time'); ?>
                                    </div>
                                </div>
                                </div>
                            </div>

                           <div class="col-md-12">
                                <div class="form-group no-mbot">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="control-label" for="end_time"><?php echo _l('project_timesheet_end_time'); ?></label>
                                    </div>
                                    <div class="col-md-9">
                                        <?php echo render_datetime_input('end_time'); ?>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="timesheet-duration hide">
                         <div class="col-md-12">
                            <div class="form-group no-mbot">
                                <div class="row">
                                    <div class="col-md-3 popover-250">
                                        <label class="control-label" for="timesheet_duration">
                                              <?php echo _l('project_timesheet_time_spend'); ?>
                                        </label>
                                         <i class="fa fa-question-circle pointer" data-toggle="popover" data-html="true" data-content="
                                         :15 - 15 <?php echo _l('minutes'); ?><br />
                                         2 - 2 <?php echo _l('hours'); ?><br />
                                         5:5 - 5 <?php echo _l('hours'); ?> & 5 <?php echo _l('minutes'); ?><br />
                                         2:50 - 2 <?php echo _l('hours'); ?> & 50 <?php echo _l('minutes'); ?><br />
                                         "></i>
                                    </div>
                                    <div class="col-md-9">
                                        <?php echo render_input('timesheet_duration','','','text',array('placeholder'=>'HH:MM')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 col-md-offset-3 mbot15 mntop15">
                           <a href="#" class="timesheet-toggle-enter-type">
                             <span class="timesheet-duration-toggler-text switch-to">
                               <?php echo _l('timesheet_duration_instead'); ?>
                           </span>
                           <span class="timesheet-date-toggler-text hide ">
                               <?php echo _l('timesheet_date_instead'); ?>
                           </span>
                       </a>
                   </div>
                     </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="timesheet_task_id"><?php echo _l('project_timesheet_task'); ?></label>
                                    </div>
                                    <div class="col-md-9">
                                            <select name="timesheet_task_id" id="timesheet_task_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="-">
                                            <option value=""></option>
                                            <?php $has_permission_create = has_permission('projects','','create');
                                            foreach($tasks as $task){
                                                if((!$has_permission_create && !$this->tasks_model->is_task_assignee(get_staff_user_id(),$task['id']))){
                                                    continue;
                                                }
                                                    echo '<option value="'.$task['id'].'">'.$task['name'].'</option>';
                                                }
                                                ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mtop15">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="timesheet_staff_id"><?php echo _l('project_timesheet_user'); ?></label>
                                    </div>
                                    <div class="col-md-9">
                                        <select name="timesheet_staff_id" id="timesheet_staff_id" class="selectpicker" data-live-search="true" data-width="100%" data-none-selected-text="-">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                          <div class="row mtop15">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="note"><?php echo _l('note'); ?></label>
                                    </div>
                                    <div class="col-md-9">
                                      <?php echo render_textarea('note'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Timesheet Modal End -->
