<div class="widget<?php if(!is_staff_member()){echo ' hide';} ?>" id="widget-<?php echo basename(__FILE__,".php"); ?>" data-name="<?php echo _l('s_chart',_l('leads')); ?>">
   <?php if(is_staff_member()){ ?>
   <div class="row">
      <div class="col-md-12">
         <div class="panel_s">
            <div class="panel-body padding-10">
               <div class="widget-dragger"></div>
               <p class="padding-5"><?php echo _l('home_lead_overview'); ?></p>
               <hr class="hr-panel-heading-dashboard">
               <div class="relative" style="height:250px">
                  <canvas class="chart" height="250" id="leads_status_stats"></canvas>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php } ?>
</div>
