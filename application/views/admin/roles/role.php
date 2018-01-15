<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                         <?php echo $title; ?>
                     </h4>
                         <hr class="hr-panel-heading" />
                     <?php if(isset($role)){ ?>
                     <a href="<?php echo admin_url('roles/role'); ?>" class="btn btn-success pull-right mbot20 display-block"><?php echo _l('new_role'); ?></a>
                     <div class="clearfix"></div>
                     <?php } ?>
                     <?php echo form_open($this->uri->uri_string()); ?>
                     <?php if(isset($role)){ ?>
                     <?php if(total_rows('tblstaff',array('role'=>$role->roleid)) > 0){ ?>
                     <div class="alert alert-warning bold">
                        <?php echo _l('change_role_permission_warning'); ?>
                        <div class="checkbox">
                            <input type="checkbox" name="update_staff_permissions" id="update_staff_permissions">
                            <label for="update_staff_permissions"><?php echo _l('role_update_staff_permissions'); ?></label>
                        </div>
                    </div>
                    <?php } ?>
                    <?php } ?>
                    <?php $attrs = (isset($role) ? array() : array('autofocus'=>true)); ?>
                    <?php $value = (isset($role) ? $role->name : ''); ?>
                    <?php echo render_input('name','role_add_edit_name',$value,'text',$attrs); ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="bold"><?php echo _l('permission'); ?></th>
                                    <th class="text-center bold"><?php echo _l('permission_view'); ?></th>
                                    <th class="text-center bold"><?php echo _l('permission_view_own'); ?></th>
                                    <th class="text-center bold"><?php echo _l('permission_create'); ?></th>
                                    <th class="text-center bold"><?php echo _l('permission_edit'); ?></th>
                                    <th class="text-center text-danger bold"><?php echo _l('permission_delete'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $conditions = get_permission_conditions();
                                foreach($permissions as $permission){
                                    $permission_condition = $conditions[$permission['shortname']];
                                    ?>
                                    <tr>
                                        <td>
                                         <?php echo $permission['name']; ?></td>
                                         <td class="text-center">
                                            <?php if($permission_condition['view'] == true){
                                                $statement = '';
                                                if(isset($role)){
                                                    if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_view'=>1)) > 0){
                                                        $statement = 'checked';
                                                    }

                                                if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_view_own'=>1)) > 0){
                                                    $statement = 'disabled';
                                                }
                                                }
                                                ?>
                                                <?php if(isset($permission_condition['help'])){
                                                 echo '<i class="fa fa-question-circle text-danger" data-toggle="tooltip" data-title="'.$permission_condition['help'].'"></i>';
                                             }
                                             ?>
                                             <div class="checkbox">
                                                <input type="checkbox" data-can-view <?php echo $statement; ?> name="view[]" value="<?php echo $permission['permissionid']; ?>">
                                                <label></label>
                                            </div>
                                            <?php } ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($permission_condition['view_own'] == true){
                                               $statement = '';
                                               if(isset($role)){
                                                if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_view_own'=>1)) > 0){
                                                    $statement = 'checked';
                                                }

                                                 if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_view'=>1)) > 0){
                                                    $statement = 'disabled';
                                                }


                                            }
                                            ?>
                                            <div class="checkbox">
                                               <input type="checkbox" data-shortname="<?php echo $permission['shortname']; ?>" <?php echo $statement; ?> name="view_own[]" value="<?php echo $permission['permissionid']; ?>" data-can-view-own>
                                               <label></label>
                                           </div>
                                           <?php } else if($permission['shortname'] == 'customers'){
                                              echo '<i class="fa fa-question-circle mtop15" data-toggle="tooltip" data-title="'._l('permission_customers_based_on_admins').'"></i>';
                                          } else if($permission['shortname'] == 'projects'){
                                              echo '<i class="fa fa-question-circle mtop25" data-toggle="tooltip" data-title="'._l('permission_projects_based_on_assignee').'"></i>';
                                          } else if($permission['shortname'] == 'tasks'){
                                              echo '<i class="fa fa-question-circle mtop25" data-toggle="tooltip" data-title="'._l('permission_tasks_based_on_assignee').'"></i>';
                                          } else if($permission['shortname'] == 'payments'){
                                          echo '<i class="fa fa-question-circle mtop15" data-toggle="tooltip" data-title="'._l('permission_payments_based_on_invoices').'"></i>';
                                          } ?>
                                      </td>

                                        <td class="text-center">
                                            <?php if($permission_condition['create'] == true){
                                                $statement = '';
                                                if(isset($role)){
                                                    if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_create'=>1)) > 0){
                                                        $statement = 'checked';
                                                    }
                                                }
                                                ?>
                                                <div class="checkbox">
                                                    <input type="checkbox" data-shortname="<?php echo $permission['shortname']; ?>" data-can-create <?php echo $statement; ?> name="create[]" value="<?php echo $permission['permissionid']; ?>">
                                                    <label></label>
                                                </div>
                                                <?php } ?>
                                            </td>
                                              <td class="text-center">
                                        <?php if($permission_condition['edit'] == true){
                                            $statement = '';
                                            if(isset($role)){
                                                if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_edit'=>1)) > 0){
                                                    $statement = 'checked';
                                                }
                                            }
                                            ?>
                                            <div class="checkbox">
                                                <input type="checkbox" data-shortname="<?php echo $permission['shortname']; ?>" data-can-edit <?php echo $statement; ?> name="edit[]" value="<?php echo $permission['permissionid']; ?>">
                                                <label></label>
                                            </div>
                                            <?php } ?>
                                        </td>
                                            <td class="text-center">
                                                <?php if($permission_condition['delete'] == true){
                                                    $statement = '';
                                                    if(isset($role)){
                                                        if(total_rows('tblrolepermissions',array('roleid'=>$role->roleid,'permissionid'=>$permission['permissionid'],'can_delete'=>1)) > 0){
                                                            $statement = 'checked';
                                                        }
                                                    }
                                                    ?>
                                                    <div class="checkbox checkbox-danger">
                                                        <input type="checkbox" data-shortname="<?php echo $permission['shortname']; ?>" data-can-delete <?php echo $statement; ?> name="delete[]" value="<?php echo $permission['permissionid']; ?>">
                                                        <label></label>
                                                    </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php init_tail(); ?>
            <script>
                $(function(){
                  _validate_form($('form'),{name:'required'});
                });
            </script>
        </body>
        </html>
