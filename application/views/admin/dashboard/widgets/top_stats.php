<div class="widget relative" id="widget-<?php echo basename(__FILE__,".php"); ?>" data-name="<?php echo _l('quick_stats'); ?>">
      <div class="widget-dragger"></div>
      <div class="row">
      <?php
         $initial_column = 'col-lg-3';
         if(!is_staff_member() && (!has_permission('invoices','','view') && !has_permission('invoices','','view_own'))) {
            $initial_column = 'col-lg-6';
         } else if(!is_staff_member() || (!has_permission('invoices','','view') && !has_permission('invoices','','view_own'))) {
            $initial_column = 'col-lg-4';
         }
      ?>
         <?php if(has_permission('invoices','','view') || has_permission('invoices','','view_own')){ ?>
         <div class="col-xs-12 col-md-6 col-sm-6 <?php echo $initial_column; ?>">
            <div class="top_stats_wrapper">
               <?php
                  $total_invoices = total_rows('tblinvoices','status NOT IN (5,6)'.(!has_permission('invoices','','view') ? ' AND addedfrom='.get_staff_user_id() : ''));
                  $total_invoices_awaiting_payment = total_rows('tblinvoices','status NOT IN (2,5,6)'.(!has_permission('invoices','','view') ? ' AND addedfrom='.get_staff_user_id() : ''));
                  $percent_total_invoices_awaiting_payment = ($total_invoices > 0 ? number_format(($total_invoices_awaiting_payment * 100) / $total_invoices,2) : 0);
                  ?>
               <p class="text-uppercase mtop5"><i class="hidden-sm fa fa-balance-scale"></i> <?php echo _l('invoices_awaiting_payment'); ?>
                  <span class="pull-right"><?php echo $total_invoices_awaiting_payment; ?> / <?php echo $total_invoices; ?></span>
               </p>
               <div class="clearfix"></div>
               <div class="progress no-margin progress-bar-mini">
                  <div class="progress-bar progress-bar-danger no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_total_invoices_awaiting_payment; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_total_invoices_awaiting_payment; ?>">
                  </div>
               </div>
            </div>
         </div>
         <?php } ?>
         <?php if(is_staff_member()){ ?>
         <div class="col-xs-12 col-md-6 col-sm-6 <?php echo $initial_column; ?>">
            <div class="top_stats_wrapper">
               <?php
                  $where = '';
                  if(!is_admin()){
                    $where .= '(addedfrom = '.get_staff_user_id().' OR assigned = '.get_staff_user_id().')';
                  }
                          // Junk leads are excluded from total
                  $total_leads = total_rows('tblleads',($where == '' ? 'junk=0' : $where .= ' AND junk =0'));
                  if($where == ''){
                   $where .= 'status=1';
                  } else {
                   $where .= ' AND status =1';
                  }
                  $total_leads_converted = total_rows('tblleads',$where);
                  $percent_total_leads_converted = ($total_leads > 0 ? number_format(($total_leads_converted * 100) / $total_leads,2) : 0);
                  ?>
               <p class="text-uppercase mtop5"><i class="hidden-sm fa fa-tty"></i> <?php echo _l('leads_converted_to_client'); ?>
                  <span class="pull-right"><?php echo $total_leads_converted; ?> / <?php echo $total_leads; ?></span>
               </p>
               <div class="clearfix"></div>
               <div class="progress no-margin progress-bar-mini">
                  <div class="progress-bar progress-bar-success no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_total_leads_converted; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_total_leads_converted; ?>">
                  </div>
               </div>
            </div>
         </div>
         <?php } ?>
         <div class="col-xs-12 col-md-6 col-sm-6 <?php echo $initial_column; ?>">
            <div class="top_stats_wrapper">
               <?php
                  $_where = '';
                  $project_status = get_project_status_by_id(2);
                  if(!has_permission('projects','','view')){
                    $_where = 'id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id='.get_staff_user_id().')';
                  }
                  $total_projects = total_rows('tblprojects',$_where);
                  $where = ($_where == '' ? '' : $_where.' AND ').'status = 2';
                  $total_projects_in_progress = total_rows('tblprojects',$where);
                  $percent_in_progress_projects = ($total_projects > 0 ? number_format(($total_projects_in_progress * 100) / $total_projects,2) : 0);
                  ?>
               <p class="text-uppercase mtop5"><i class="hidden-sm fa fa-cubes"></i> <?php echo _l('projects') . ' ' . $project_status['name']; ?><span class="pull-right"><?php echo $total_projects_in_progress; ?> / <?php echo $total_projects; ?></span></p>
               <div class="clearfix"></div>
               <div class="progress no-margin progress-bar-mini">
                  <div class="progress-bar no-percent-text not-dynamic" style="background:<?php echo $project_status['color']; ?>" role="progressbar" aria-valuenow="<?php echo $percent_in_progress_projects; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_in_progress_projects; ?>">
                  </div>
               </div>
            </div>
         </div>
         <div class="col-xs-12 col-md-6 col-sm-6 <?php echo $initial_column; ?>">
            <div class="top_stats_wrapper">
               <?php
                  $_where = '';
                  if (!has_permission('tasks', '', 'view')) {
                    $_where = 'tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid = ' . get_staff_user_id() . ')';
                  }
                  $total_tasks = total_rows('tblstafftasks',$_where);
                  $where = ($_where == '' ? '' : $_where.' AND ').'status != 5';
                  $total_not_finished_tasks = total_rows('tblstafftasks',$where);
                  $percent_not_finished_tasks = ($total_tasks > 0 ? number_format(($total_not_finished_tasks * 100) / $total_tasks,2) : 0);
                  ?>
               <p class="text-uppercase mtop5"><i class="hidden-sm fa fa-tasks"></i> <?php echo _l('tasks_not_finished'); ?> <span class="pull-right">
                  <?php echo $total_not_finished_tasks; ?> / <?php echo $total_tasks; ?>
                  </span>
               </p>
               <div class="clearfix"></div>
               <div class="progress no-margin progress-bar-mini">
                  <div class="progress-bar progress-bar-default no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_not_finished_tasks; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_not_finished_tasks; ?>">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
