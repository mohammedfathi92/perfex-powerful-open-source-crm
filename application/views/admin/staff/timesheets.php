<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <?php if(!isset($view_all)){ ?>
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body no-padding-bottom">
           <?php $this->load->view('admin/staff/stats'); ?>
         </div>
       </div>
     </div>
     <?php } ?>
     <div class="col-md-12">
      <div class="panel_s">
        <div class="panel-body">
          <?php if(is_admin()){ ?>
          <?php if(isset($view_all) && get_option('show_timesheets_overview_all_members_notice_admins') == 1){ ?>
          <div class="alert alert-info alert-dismissible" role="alert">
          <button type="button" class="close" onclick="window.location.href= '<?php echo admin_url('misc/dismiss_timesheets_notice_admins'); ?>';" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo _l('timesheets_overview_all_members_notice_admins'); ?>
          </div>
          <?php } ?>
          <a href="<?php echo site_url($this->uri->uri_string().(!isset($view_all) ? '?view=all' : '')); ?>" class="btn btn-info"><i class="fa fa-clock-o"></i>
            <?php
            echo (isset($view_all) ? _l('my_timesheets') :  _l('view_members_timesheets'));
            ?>
          </a>
          <hr />
          <?php } ?>
          <canvas id="timesheetsChart" style="max-height:400px;" width="350" height="350"></canvas>
          <hr />
          <div class="clearfix"></div>
          <div class="row">
           <div class="col-md-3">
           <div class="select-placeholder">
              <select name="range" id="range" class="selectpicker" data-width="100%">
             <option value="today" selected><?php echo _l('today'); ?></option>
             <option value="this_month"><?php echo _l('staff_stats_this_month_total_logged_time'); ?></option>
             <option value="last_month"><?php echo _l('staff_stats_last_month_total_logged_time'); ?></option>
             <option value="this_week"><?php echo _l('staff_stats_this_week_total_logged_time'); ?></option>
             <option value="last_week"><?php echo _l('staff_stats_last_week_total_logged_time'); ?></option>
             <option value="period"><?php echo _l('period_datepicker'); ?></option>
           </select>
           </div>
           <div class="row mtop15">
             <div class="col-md-12 period hide">
              <?php echo render_date_input('period-from'); ?>
            </div>
            <div class="col-md-12 period hide">
              <?php echo render_date_input('period-to'); ?>
            </div>
          </div>
        </div>
        <?php if(isset($view_all)){ ?>
        <div class="col-md-3">
       <div class="select-placeholder">
           <select name="staff_id" id="staff_id" class="selectpicker" data-width="100%">
           <option value=""><?php echo _l('all_staff_members'); ?></option>
           <option value="<?php echo get_staff_user_id(); ?>"><?php echo get_staff_full_name(get_staff_user_id()); ?></option>
           <?php foreach($staff_members_with_timesheets as $staff){ ?>
           <option value="<?php echo $staff['staff_id']; ?>"><?php echo get_staff_full_name($staff['staff_id']); ?></option>
           <?php } ?>
         </select>
       </div>
       </div>
       <?php } ?>
       <div class="col-md-3">
         <div class="select-placeholder">
           <select data-empty-title="<?php echo _l('project'); ?>" name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%">
         </select>
         </div>
       </div>
       <div class="col-md-3">
         <a href="#" id="apply_filters_timesheets" class="btn btn-default p7"><?php echo _l('apply'); ?></a>
       </div>
       <div class="mtop10 hide relative pull-right" id="group_by_tasks_wrapper">
        <span><?php echo _l('group_by_task'); ?></span>
        <div class="onoffswitch">
          <input type="checkbox" name="group_by_task" class="onoffswitch-checkbox" id="group_by_task">
          <label class="onoffswitch-label" for="group_by_task"></label>
        </div>
      </div>
      <div class="col-md-12">
        <hr class="no-mtop"/>
      </div>
    </div>
    <div class="clearfix"></div>
    <table class="table table-timesheets-report">
      <thead>
        <tr>
          <?php if(isset($view_all)){ ?>
          <th><?php echo _l('staff_member'); ?></th>
          <?php } ?>
          <th><?php echo _l('project_timesheet_task'); ?></th>
          <th><?php echo _l('timesheet_tags'); ?></th>
          <th class="t-start-time"><?php echo _l('project_timesheet_start_time'); ?></th>
          <th class="t-end-time"><?php echo _l('project_timesheet_end_time'); ?></th>
          <th width="150px;"><?php echo _l('note'); ?></th>
          <th><?php echo _l('task_relation'); ?></th>
          <th><?php echo _l('time_h'); ?></th>
          <th><?php echo _l('time_decimal'); ?></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
         <?php if(isset($view_all)){ ?>
         <td></td>
         <?php } ?>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td class="total_logged_time_timesheets_staff_h"></td>
         <td class="total_logged_time_timesheets_staff_d"></td>
       </tr>
     </tfoot>
   </table>
 </div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
 var staff_member_select = $('select[name="staff_id"]');
 $(function() {

  init_ajax_projects_search();
  var ctx = document.getElementById("timesheetsChart");
  var chartOptions = {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        label: '',
        data: [],
        backgroundColor: [],
        borderColor: [],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      tooltips: {
        enabled: true,
        mode: 'single',
        callbacks: {
          label: function(tooltipItems, data) {
            return decimalToHM(tooltipItems.yLabel);
          }
        }
      },
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true,
            min: 0,
            userCallback: function(label, index, labels) {
              return decimalToHM(label);
            },
          }
        }]
      },
    }
  };

  var timesheetsTable = $('.table-timesheets-report');
  $('#apply_filters_timesheets').on('click', function(e) {
    e.preventDefault();
    timesheetsTable.DataTable().ajax.reload();
  });

  $('body').on('change','#group_by_task',function(){
    var tApi = timesheetsTable.DataTable();
    var visible = $(this).prop('checked') == false;
    var tEndTimeIndex = $('.t-end-time').index();
    var tStartTimeIndex = $('.t-start-time').index();
    if(tEndTimeIndex == -1 && tStartTimeIndex == -1) {
      tStartTimeIndex = $(this).attr('data-start-time-index');
      tEndTimeIndex = $(this).attr('data-end-time-index');
    } else {
      $(this).attr('data-start-time-index',tStartTimeIndex);
      $(this).attr('data-end-time-index',tEndTimeIndex);
    }
    tApi.column(tEndTimeIndex).visible(visible, false).columns.adjust();
    tApi.column(tStartTimeIndex).visible(visible, false).columns.adjust();
    tApi.ajax.reload();
  });

  var timesheetsChart;
  var Timesheets_ServerParams = {};
  Timesheets_ServerParams['range'] = '[name="range"]';
  Timesheets_ServerParams['period-from'] = '[name="period-from"]';
  Timesheets_ServerParams['period-to'] = '[name="period-to"]';
  Timesheets_ServerParams['staff_id'] = '[name="staff_id"]';
  Timesheets_ServerParams['project_id'] = '[name="project_id"]';
  Timesheets_ServerParams['group_by_task'] = '[name="group_by_task"]:checked';
  initDataTable('.table-timesheets-report', window.location.href, undefined, undefined, Timesheets_ServerParams, [<?php if(isset($view_all)){echo 3;} else {echo 2;} ?>, 'DESC']);

  timesheetsTable.on('init.dt',function(){
    var $dtFilter = $('body').find('.dataTables_filter');
    var $gr = $('#group_by_tasks_wrapper').clone()
    $('#group_by_tasks_wrapper').remove();
    $gr.removeClass('hide');
    $gr.find('span').css('position','absolute');
    $gr.find('span').css('top','2px');
    $gr.find('span').css((isRTL == 'true' ? 'right' : 'left'),'-90px');
    $dtFilter.before($gr,'<div class="clearfix"></div>');
  });
  timesheetsTable.on('draw.dt', function() {
    var TimesheetsTable = $(this).DataTable();
    var logged_time = TimesheetsTable.ajax.json().logged_time;
    var chartResponse = TimesheetsTable.ajax.json().chart;
    var chartType = TimesheetsTable.ajax.json().chart_type;
    $(this).find('tfoot').addClass('bold');
    $(this).find('tfoot td.total_logged_time_timesheets_staff_h').html("<?php echo _l('total_logged_hours_by_staff'); ?>: " + logged_time.total_logged_time_h);
    $(this).find('tfoot td.total_logged_time_timesheets_staff_d').html("<?php echo _l('total_logged_hours_by_staff'); ?>: " + logged_time.total_logged_time_d);
    if (typeof(timesheetsChart) !== 'undefined') {
      timesheetsChart.destroy();
    }
    if (chartType != 'month') {
      chartOptions.data.labels = chartResponse.labels;
    } else {
      chartOptions.data.labels = [];
      for (var i in chartResponse.labels) {
        chartOptions.data.labels.push(moment(chartResponse.labels[i]).format("MMM Do YY"));
      }
    }
    chartOptions.data.datasets[0].data = [];
    chartOptions.data.datasets[0].backgroundColor = [];
    chartOptions.data.datasets[0].borderColor = [];
    for (var i in chartResponse.data) {
      chartOptions.data.datasets[0].data.push(chartResponse.data[i]);
      if (chartResponse.data[i] == 0) {
        chartOptions.data.datasets[0].backgroundColor.push('rgba(167, 167, 167, 0.6)');
        chartOptions.data.datasets[0].borderColor.push('rgba(167, 167, 167, 1)');
      } else {
        chartOptions.data.datasets[0].backgroundColor.push('rgba(132, 197, 41, 0.6)');
        chartOptions.data.datasets[0].borderColor.push('rgba(132, 197, 41, 1)');
      }
    }

    var selected_staff_member = staff_member_select.val();
    var selected_staff_member_name = staff_member_select.find('option:selected').text();
    chartOptions.data.datasets[0].label = $('select[name="range"] option:selected').text() + (selected_staff_member != '' && selected_staff_member != undefined ? ' - ' + selected_staff_member_name : '');
    setTimeout(function() {
      timesheetsChart = new Chart(ctx, chartOptions);
    }, 30);
    do_timesheets_title();
  });
});
function do_timesheets_title(){
  var _temp;
  var range = $('select[name="range"]');
  var _range_heading = range.find('option:selected').text();
  if(range.val() != 'period'){
    _temp = _range_heading;
  } else {
    _temp = _range_heading + ' ('+$('input[name="period-from"]').val() +' - '+$('input[name="period-to"]').val()+') ';
  }
  $('head title').html( _temp + (staff_member_select.find('option:selected').text() != '' ? ' - ' + staff_member_select.find('option:selected').text() : ''));
}
</script>
</body>
</html>
