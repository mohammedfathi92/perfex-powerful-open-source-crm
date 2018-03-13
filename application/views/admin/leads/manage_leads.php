<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
          <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                  <a href="#" onclick="init_lead(); return false;" class="btn mright5 btn-info pull-left display-block">
                      <?php echo _l('new_lead'); ?>
                  </a>
                  <?php if(is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1'){ ?>
                  <a href="<?php echo admin_url('leads/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                     <?php echo _l('import_leads'); ?>
                  </a>
                  <?php } ?>
                  <div class="row">
                     <div class="col-md-5">
                        <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview'); return false;"><i class="fa fa-bar-chart"></i></a>
                        <a href="<?php echo admin_url('leads/switch_kanban/'.$switch_kanban); ?>" class="btn btn-default mleft10 hidden-xs">
                        <?php if($switch_kanban == 1){ echo _l('leads_switch_to_kanban');}else{echo _l('switch_to_list_view');}; ?>
                        </a>
                     </div>
                     <div class="col-md-4 col-xs-12 pull-right leads-search">
                        <?php if($this->session->userdata('leads_kanban_view') == 'true') { ?>
                        <div data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                        <?php echo render_input('search','','','search',array('data-name'=>'search','onkeyup'=>'leads_kanban();','placeholder'=>_l('leads_search')),array(),'no-margin') ?>
                        </div>
                        <?php } ?>
                        <?php echo form_hidden('sort_type'); ?>
                        <?php echo form_hidden('sort',(get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                     </div>
                  </div>
                  <div class="clearfix"></div>

                  <div class="row hide leads-overview">
                        <hr class="hr-panel-heading" />
                     <div class="col-md-12">
                        <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                     </div>
                     <?php
                        $whereNoViewPermission = '(addedfrom = '.get_staff_user_id().' OR assigned='.get_staff_user_id().' OR is_public = 1)';
                        $numStatuses = count($statuses);
                        $has_permission_view = has_permission('leads','','view');
                        foreach($statuses as $status){ ?>
                     <div class="col-md-2 col-xs-6 border-right">
                        <?php
                           $this->db->where('status',$status['id']);
                          if(!$has_permission_view){
                              $this->db->where($whereNoViewPermission);
                           }
                           $total = $this->db->count_all_results('tblleads');
                           ?>
                        <h3 class="bold"><?php echo $total; ?></h3>
                        <span style="color:<?php echo $status['color']; ?>"><?php echo $status['name']; ?></span>
                     </div>
                     <?php } ?>
                     <?php
                        if(!$has_permission_view){
                          $this->db->where($whereNoViewPermission);
                        }
                        $total_leads = $this->db->count_all_results('tblleads');
                        ?>
                     <div class="col-md-2 col-xs-6">
                        <?php
                           $this->db->where('lost',1);
                           if(!$has_permission_view){
                            $this->db->where($whereNoViewPermission);
                           }
                           $total_lost = $this->db->count_all_results('tblleads');
                           $percent_lost = ($total_leads > 0 ? number_format(($total_lost * 100) / $total_leads,2) : 0);
                           ?>
                        <h3 class="bold"><span data-toggle="tooltip" data-title="<?php echo $total_lost; ?>"><?php echo $percent_lost; ?>%</span></h3>
                        <span class="text-danger"><?php echo _l('lost_leads'); ?></span>
                     </div>
                     <div class="col-md-2 col-xs-6">
                        <?php
                           $this->db->where('junk',1);
                           if(!$has_permission_view){
                            $this->db->where($whereNoViewPermission);
                           }
                           $total_junk = $this->db->count_all_results('tblleads');
                           $percent_junk = ($total_leads > 0 ? number_format(($total_junk * 100) / $total_leads,2) : 0);
                           ?>
                        <h3 class="bold"><span data-toggle="tooltip" data-title="<?php echo $total_junk; ?>"><?php echo $percent_junk; ?>%</span></h3>
                        <span class="text-danger"><?php echo _l('junk_leads'); ?></span>
                     </div>
                  </div>
               </div>
                  <hr class="hr-panel-heading" />
                  <div class="tab-content">
                     <?php
                        if($this->session->has_userdata('leads_kanban_view') && $this->session->userdata('leads_kanban_view') == 'true') { ?>
                     <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                        <div class="kanban-leads-sort">
                           <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                           <a href="#" onclick="leads_kanban_sort('dateadded'); return false" class="dateadded">
                            <?php if(get_option('default_leads_kanban_sort') == 'dateadded'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_leads_kanban_sort_type')).'"></i> ';} ?><?php echo _l('leads_sort_by_datecreated'); ?>
                            </a>
                           |
                           <a href="#" onclick="leads_kanban_sort('leadorder');return false;" class="leadorder">
                            <?php if(get_option('default_leads_kanban_sort') == 'leadorder'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_leads_kanban_sort_type')).'"></i> ';} ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                            </a>
                           |
                           <a href="#" onclick="leads_kanban_sort('lastcontact');return false;" class="lastcontact">
                            <?php if(get_option('default_leads_kanban_sort') == 'lastcontact'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_leads_kanban_sort_type')).'"></i> ';} ?><?php echo _l('leads_sort_by_lastcontact'); ?>
                            </a>
                        </div>
                        <div class="row">
                           <div class="container-fluid leads-kan-ban">
                              <div id="kan-ban"></div>
                           </div>
                        </div>
                     </div>
                     <?php } else { ?>
                     <div class="row" id="leads-table">
                        <div class="col-md-12">
                           <div class="row">
                              <div class="col-md-12">
                                 <p class="bold"><?php echo _l('filter_by'); ?></p>
                              </div>
                              <?php if(has_permission('leads','','view')){ ?>
                              <div class="col-md-3 leads-filter-column">
                                 <?php echo render_select('view_assigned',$staff,array('staffid',array('firstname','lastname')),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_dt_assigned')),array(),'no-mbot'); ?>
                              </div>
                              <?php } ?>
                              <div class="col-md-3 leads-filter-column">
                                 <?php
                                 $selected = array();
                                 if($this->input->get('status')) {
                                  $selected[] = $this->input->get('status');
                                 } else {
                                  foreach($statuses as $key => $status) {
                                    if($status['isdefault'] == 0) {
                                      $selected[] = $status['id'];
                                    } else {
                                      $statuses[$key]['option_attributes'] = array('data-subtext'=>_l('leads_converted_to_client'));
                                    }
                                  }
                                 }
                                 echo '<div id="leads-filter-status">';
                                    echo render_select('view_status[]',$statuses,array('id','name'),'',$selected,array('data-width'=>'100%','data-none-selected-text'=>_l('leads_all'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false);
                                    echo '</div>';
                                    ?>
                              </div>
                              <div class="col-md-3 leads-filter-column">
                                 <?php
                                    echo render_select('view_source',$sources,array('id','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('leads_source')),array(),'no-mbot');
                                    ?>
                              </div>
                              <div class="col-md-3 leads-filter-column">
                                <div class="select-placeholder">
                                 <select name="custom_view" title="<?php echo _l('additional_filters'); ?>" id="custom_view" class="selectpicker" data-width="100%">
                                    <option value=""></option>
                                    <option value="lost"><?php echo _l('lead_lost'); ?></option>
                                    <option value="junk"><?php echo _l('lead_junk'); ?></option>
                                    <option value="public"><?php echo _l('lead_public'); ?></option>
                                    <option value="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></option>
                                    <option value="created_today"><?php echo _l('created_today'); ?></option>
                                    <?php if(has_permission('leads','','edit')){ ?>
                                    <option value="not_assigned"><?php echo _l('leads_not_assigned'); ?></option>
                                    <?php } ?>
                                 </select>
                               </div>
                              </div>
                           </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <a href="#" data-toggle="modal" data-table=".table-leads" data-target="#leads_bulk_actions" class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                           <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1" role="dialog">
                              <div class="modal-dialog" role="document">
                                 <div class="modal-content">
                                    <div class="modal-header">
                                       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                       <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                       <?php if(has_permission('leads','','delete')){ ?>
                                       <div class="checkbox checkbox-danger">
                                          <input type="checkbox" name="mass_delete" id="mass_delete">
                                          <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                       </div>
                                       <hr class="mass_delete_separator" />
                                       <?php } ?>
                                       <div id="bulk_change">
                                          <?php echo render_select('move_to_status_leads_bulk',$statuses,array('id','name'),'ticket_single_change_status'); ?>
                                          <?php
                                              echo render_select('move_to_source_leads_bulk',$sources,array('id','name'),'lead_source');
                                              echo render_datetime_input('leads_bulk_last_contact','leads_dt_last_contact');
                                              if(has_permission('leads','','edit')){
                                                echo render_select('assign_to_leads_bulk',$staff,array('staffid',array('firstname','lastname')),'leads_dt_assigned');
                                              }
                                             ?>
                                          <div class="form-group">
                                          <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                          <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value="" data-role="tagsinput">
                                       </div>
                                       <hr />
                                       <div class="form-group no-mbot">
                                          <div class="radio radio-primary radio-inline">
                                              <input type="radio" name="leads_bulk_visibility" id="leads_bulk_public" value="public">
                                                 <label for="leads_bulk_public">
                                                   <?php echo _l('lead_public'); ?>
                                                </label>
                                          </div>
                                          <div class="radio radio-primary radio-inline">
                                              <input type="radio" name="leads_bulk_visibility" id="leads_bulk_private" value="private">
                                                 <label for="leads_bulk_private">
                                                   <?php echo _l('private'); ?>
                                                </label>
                                          </div>
                                       </div>
                                       </div>
                                    </div>
                                    <div class="modal-footer">
                                       <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                       <a href="#" class="btn btn-info" onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                    </div>
                                 </div>
                                 <!-- /.modal-content -->
                              </div>
                              <!-- /.modal-dialog -->
                           </div>
                           <!-- /.modal -->
                           <?php
                              $table_data = array();
                              $_table_data = array(
                                '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label></div>',
                                '#',
                                _l('leads_dt_name'),
                                _l('lead_company'),
                                _l('leads_dt_email'),
                                _l('leads_dt_phonenumber'),
                                _l('tags'),
                                _l('leads_dt_assigned'),
                                _l('leads_dt_status'),
                                _l('leads_source'),
                                _l('leads_dt_last_contact'),
                                array(
                                 'name'=>_l('leads_dt_datecreated'),
                                 'th_attrs'=>array('class'=>'date-created')
                                 ));
                              foreach($_table_data as $_t){
                               array_push($table_data,$_t);
                              }
                              $custom_fields = get_custom_fields('leads',array('show_on_table'=>1));
                              foreach($custom_fields as $field){
                               array_push($table_data,$field['name']);
                              }
                              $table_data = do_action('leads_table_columns',$table_data);
                              $_op = _l('options');
                              array_push($table_data,$_op);
                              render_datatable($table_data,'leads'); ?>
                        </div>
                     </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include_once(APPPATH.'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>
<script>
   var c_leadid = '<?php echo $leadid; ?>';
</script>
<script>
   $(function(){
      leads_kanban();
   });
</script>
</body>
</html>
