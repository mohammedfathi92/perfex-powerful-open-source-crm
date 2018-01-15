 <div class="widget" id="widget-<?php echo basename(__FILE__,".php"); ?>" data-name="<?php echo _l('s_chart',_l('projects')); ?>">
    <div class="row">
        <div class="col-md-12">
           <div class="panel_s">
               <div class="panel-body padding-10">
                <div class="widget-dragger"></div>
                <p class="padding-5"><?php echo _l('home_stats_by_project_status'); ?></p>
                <hr class="hr-panel-heading-dashboard">
                <div class="relative" style="height:250px">
                   <canvas class="chart" height="250" id="projects_status_stats"></canvas>
               </div>
           </div>
       </div>
   </div>
</div>
</div>
