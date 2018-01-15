<div class="widget<?php if(count($goals) == 0 || !is_staff_member()){echo ' hide';} ?>" id="widget-<?php echo basename(__FILE__,".php"); ?>">
   <?php if(is_staff_member()){ ?>
   <div class="row">
      <div class="col-md-12">
         <div class="panel_s">
            <div class="panel-body padding-10">
               <div class="widget-dragger"></div>
               <p class="padding-5">
                  <?php echo _l('goals'); ?>
               </p>
               <hr class="hr-panel-heading-dashboard">
               <?php foreach($goals as $goal){
                  ?>
                  <div class="goal padding-5 no-padding-top">
                     <h4 class="pull-left font-medium no-mtop">
                        <?php echo $goal['goal_type_name']; ?>
                        <br />
                        <small><?php echo $goal['subject']; ?></small>
                     </h4>
                     <h4 class="pull-right bold no-mtop text-success text-right">
                        <?php echo $goal['achievement']['total']; ?>
                        <br />
                        <small><?php echo _l('goal_achievement'); ?></small>
                     </h4>
                     <div class="clearfix"></div>
                     <div class="progress no-margin progress-bar-mini">
                        <div class="progress-bar progress-bar-danger no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $goal['achievement']['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $goal['achievement']['percent']; ?>">
                        </div>
                     </div>
                     <p class="text-muted pull-left mtop5"><?php echo _l('goal_progress'); ?></p>
                     <p class="text-muted pull-right mtop5"><?php echo $goal['achievement']['percent']; ?>%</p>
                  </div>
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
      <?php } ?>
   </div>
