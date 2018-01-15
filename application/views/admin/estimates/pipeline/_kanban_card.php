<?php
   if ($estimate['status'] == $status) { ?>
<li data-estimate-id="<?php echo $estimate['id']; ?>" class="<?php if($estimate['invoiceid'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading"><a href="<?php echo admin_url('estimates/list_estimates/'.$estimate['id']); ?>" onclick="estimate_pipeline_open(<?php echo $estimate['id']; ?>); return false;"><?php echo format_estimate_number($estimate['id']); ?></a>
               <?php if(has_permission('estimates','','edit')){ ?>
               <a href="<?php echo admin_url('estimates/estimate/'.$estimate['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="inline-block full-width mbot10">
            <a href="<?php echo admin_url('clients/client/'.$estimate['clientid']); ?>" target="_blank">
            <?php echo $estimate['company']; ?>
            </a>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <span class="bold">
                  <?php echo _l('estimate_total') . ':' . format_money($estimate['total'],$estimate['symbol']); ?>
                  </span>
                  <br />
                  <?php echo _l('estimate_data_date') . ': ' . _d($estimate['date']); ?>
                  <?php if(is_date($estimate['expirydate']) || !empty($estimate['expirydate'])){
                     echo '<br />';
                     echo _l('estimate_data_expiry_date') . ': ' . _d($estimate['expirydate']);
                     } ?>
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-paperclip"></i> <?php echo _l('estimate_notes'); ?>: <?php echo total_rows('tblnotes', array(
                     'rel_id' => $estimate['id'],
                     'rel_type' => 'estimate',
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($estimate['id'],'estimate');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
