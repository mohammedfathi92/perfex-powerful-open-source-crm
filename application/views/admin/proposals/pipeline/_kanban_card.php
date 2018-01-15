<?php if ($proposal['status'] == $status) { ?>
<li data-proposal-id="<?php echo $proposal['id']; ?>" class="<?php if($proposal['invoice_id'] != NULL || $proposal['estimate_id'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading">
               <a href="<?php echo admin_url('proposals/list_proposals/'.$proposal['id']); ?>" data-toggle="tooltip" data-title="<?php echo $proposal['subject']; ?>" onclick="proposal_pipeline_open(<?php echo $proposal['id']; ?>); return false;"><?php echo format_proposal_number($proposal['id']); ?></a>
               <?php if(has_permission('estimates','','edit')){ ?>
               <a href="<?php echo admin_url('proposals/proposal/'.$proposal['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="mbot10 inline-block full-width">
            <?php
               if($proposal['rel_type'] == 'lead'){
                 echo '<a href="'.admin_url('leads/index/'.$proposal['rel_id']).'" onclick="init_lead('.$proposal['rel_id'].'); return false;" data-toggle="tooltip" data-title="'._l('lead').'">' .$proposal['proposal_to'].'</a><br />';
               } else if($proposal['rel_type'] == 'customer'){
                 echo '<a href="'.admin_url('clients/client/'.$proposal['rel_id']).'" data-toggle="tooltip" data-title="'._l('client').'">' .$proposal['proposal_to'].'</a><br />';
               }
               ?>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <?php if($proposal['total'] != 0){ ?>
                  <span class="bold"><?php echo _l('proposal_total'); ?>: <?php echo format_money($proposal['total'],$this->currencies_model->get($proposal['currency'])->symbol); ?></span>
                  <br />
                  <?php } ?>
                  <?php echo _l('proposal_date'); ?>: <?php echo _d($proposal['date']); ?>
                  <?php if(is_date($proposal['open_till'])){ ?>
                  <br />
                  <?php echo _l('proposal_open_till'); ?>: <?php echo _d($proposal['open_till']); ?>
                  <?php } ?>
                  <br />
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-comments" aria-hidden="true"></i> <?php echo _l('proposal_comments'); ?>: <?php echo total_rows('tblproposalcomments', array(
                     'proposalid' => $proposal['id']
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($proposal['id'],'proposal');
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
