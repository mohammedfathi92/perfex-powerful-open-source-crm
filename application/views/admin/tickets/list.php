<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
        <div class="panel_s">
         <div class="panel-body">
          <div class="_buttons">
            <a href="<?php echo admin_url('tickets/add'); ?>" class="btn btn-info pull-left display-block mright5">
              <?php echo _l('new_ticket'); ?>
            </a>
            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('tickets_chart_weekly_opening_stats'); ?>" onclick="slideToggle('.weekly-ticket-opening',init_tickets_weekly_chart); return false;"><i class="fa fa-bar-chart"></i></a>
            <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-right width300">
                <li>
                  <a href="#" data-cview="all" onclick="dt_custom_view('','.tickets-table',''); return false;">
                    <?php echo _l('task_list_all'); ?>
                  </a>
                </li>
                <li>
                  <a href="" data-cview="my_tickets" onclick="dt_custom_view('my_tickets','.tickets-table','my_tickets'); return false;"><?php echo _l('my_tickets_assigned'); ?></a>
                </li>
                <li class="divider"></li>
                <?php foreach($statuses as $status){ ?>
                <li class="<?php if($status['ticketstatusid'] == $chosen_ticket_status || $chosen_ticket_status == '' && in_array($status['ticketstatusid'], $default_tickets_list_statuses)){echo 'active';} ?>">
                  <a href="#" data-cview="ticket_status_<?php echo $status['ticketstatusid']; ?>" onclick="dt_custom_view('ticket_status_<?php echo $status['ticketstatusid']; ?>','.tickets-table','ticket_status_<?php echo $status['ticketstatusid']; ?>'); return false;">
                    <?php echo ticket_status_translate($status['ticketstatusid']); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if(count($ticket_assignees) > 0 && is_admin()){ ?>
                <div class="clearfix"></div>
                <li class="divider"></li>
                <li class="dropdown-submenu pull-left">
                 <a href="#" tabindex="-1"><?php echo _l('filter_by_assigned'); ?></a>
                 <ul class="dropdown-menu dropdown-menu-left">
                  <?php foreach($ticket_assignees as $as){ ?>
                  <li>
                    <a href="#" data-cview="ticket_assignee_<?php echo $as['assigned']; ?>" onclick="dt_custom_view(<?php echo $as['assigned']; ?>,'.tickets-table','ticket_assignee_<?php echo $as['assigned']; ?>'); return false;"><?php echo get_staff_full_name($as['assigned']); ?></a>
                  </li>
                  <?php } ?>
                </ul>
              </li>
              <?php } ?>
            </ul>
          </div>
        </div>
        <div class="panel_s weekly-ticket-opening" style="display:none;">
          <hr class="hr-panel-heading" />
          <div class="panel-heading-bg">
            <?php echo _l('home_weekend_ticket_opening_statistics'); ?>
          </div>
          <div class="panel-body">
            <div class="relative" style="max-height:350px;">
              <canvas class="chart" id="weekly-ticket-openings-chart" height="350"></canvas>
            </div>
          </div>
        </div>
        <hr class="hr-panel-heading" />
        <?php do_action('before_render_tickets_list_table'); ?>
        <?php $this->load->view('admin/tickets/summary'); ?>
        <a href="#" data-toggle="modal" data-target="#tickets_bulk_actions" class="bulk-actions-btn table-btn hide" data-table=".table-tickets"><?php echo _l('bulk_actions'); ?></a>
        <div class="clearfix"></div>
        <?php echo AdminTicketsTableStructure('',true); ?>
      </div>
    </div>
  </div>
</div>
</div>
</div>
<div class="modal fade bulk_actions" id="tickets_bulk_actions" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
      </div>
      <div class="modal-body">
        <?php if(is_admin()){ ?>
        <div class="checkbox checkbox-danger">
          <input type="checkbox" name="mass_delete" id="mass_delete">
          <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
        </div>
        <hr class="mass_delete_separator" />
        <?php } ?>
        <div id="bulk_change">
         <?php echo render_select('move_to_status_tickets_bulk',$statuses,array('ticketstatusid','name'),'ticket_single_change_status'); ?>
         <?php echo render_select('move_to_department_tickets_bulk',$departments,array('departmentid','name'),'department'); ?>
         <?php echo render_select('move_to_priority_tickets_bulk',$priorities,array('priorityid','name'),'ticket_priority'); ?>
         <div class="form-group">
          <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
          <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value="" data-role="tagsinput">
        </div>
        <?php if(get_option('services') == 1){ ?>
        <?php echo render_select('move_to_service_tickets_bulk',$services,array('serviceid','name'),'service'); ?>
        <?php } ?>

      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
      <a href="#" class="btn btn-info" onclick="tickets_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<?php echo app_script('assets/js','tickets.js'); ?>
<script>
  var chart;
  var chart_data = <?php echo $weekly_tickets_opening_statistics; ?>;
  function init_tickets_weekly_chart() {
    if (typeof(chart) !== 'undefined') {
      chart.destroy();
    }
    // Weekly ticket openings statistics
    chart = new Chart($('#weekly-ticket-openings-chart'),{
    	type:'line',
    	data:chart_data,
    	options:{
        responsive:true,
        maintainAspectRatio:false,
        legend: {
          display: false,
        },
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
            }
          }]
        }
      }
    });
  }
</script>
</body>
</html>
