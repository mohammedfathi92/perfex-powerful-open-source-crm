<div class="widget" id="widget-<?php echo basename(__FILE__,".php"); ?>" data-name="<?php echo _l('finance_overview'); ?>">
   <?php if(has_permission('invoices','','view') || has_permission('proposals','','view') || has_permission('estimates','','view') || has_permission('estimates','','view_own') || has_permission('proposals','','view_own') || has_permission('invoices','','view_own') || (get_option('allow_staff_view_proposals_assigned') == 1 && total_rows('tblproposals',array('assigned'=>get_staff_user_id())) > 0)){ ?>
   <div class="finance-summary">
      <div class="panel_s">
         <div class="panel-body">
            <div class="widget-dragger"></div>
            <div class="row home-summary">
               <?php if(has_permission('invoices','','view') || has_permission('invoices','','view_own')){
                  $total_invoices = total_rows('tblinvoices','status NOT IN(5)'.(!has_permission('invoices','','view') ? ' AND addedfrom='.get_staff_user_id() : ''));
                  ?>
                  <div class="col-md-6 col-lg-4 col-sm-6">
                     <div class="row">
                        <div class="col-md-12">
                           <p class="text-dark text-uppercase"><?php echo _l('home_invoice_overview'); ?></p>
                           <hr class="mtop15" />
                        </div>
                        <?php $percent_data = get_invoices_percent_by_status(6,$total_invoices); ?>
                        <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo admin_url('invoices/list_invoices?status=6'); ?>" class="text-muted mbot15 inline-block">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_invoice_status(6,'',false); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-default no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                        <?php $percent_data = get_invoices_percent_by_status('not_sent',$total_invoices); ?>
                        <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo admin_url('invoices/list_invoices?filter=not_sent'); ?>" class="text-muted inline-block mbot15">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo _l('not_sent_indicator'); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-default no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                        <?php $percent_data = get_invoices_percent_by_status(1,$total_invoices); ?>
                        <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo admin_url('invoices/list_invoices?status=1'); ?>" class="text-danger mbot15 inline-block">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_invoice_status(1,'',false); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-danger no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                        <?php $percent_data = get_invoices_percent_by_status(3,$total_invoices); ?>
                        <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo admin_url('invoices/list_invoices?status=3'); ?>" class="text-warning mbot15 inline-block">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_invoice_status(3,'',false); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-danger no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                        <?php $percent_data = get_invoices_percent_by_status(4,$total_invoices); ?>
                        <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo admin_url('invoices/list_invoices?status=4'); ?>" class="text-warning mbot15 inline-block">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_invoice_status(4,'',false); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-warning no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                        <?php $percent_data = get_invoices_percent_by_status(2,$total_invoices); ?>
                        <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo admin_url('invoices/list_invoices?status=2'); ?>" class="text-success mbot15 inline-block">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_invoice_status(2,'',false); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-success no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php } ?>
                  <?php if(has_permission('estimates','','view') || has_permission('estimates','','view_own')){ ?>
                  <div class="col-md-6 col-lg-4 col-sm-6">
                     <div class="row">
                        <div class="col-md-12 text-stats-wrapper">
                           <p class="text-dark text-uppercase"><?php echo _l('home_estimate_overview'); ?></p>
                           <hr class="mtop15" />
                        </div>
                        <?php
                           // Add not sent
                        array_splice( $estimate_statuses, 1, 0, 'not_sent' );
                        foreach($estimate_statuses as $status){
                          $url = admin_url('estimates/list_estimates?status='.$status);
                          if(!is_numeric($status)){
                            $url = admin_url('estimates/list_estimates?filter='.$status);
                         }
                         $percent_data = get_estimates_percent_by_status($status);
                         ?>
                         <div class="col-md-12 text-stats-wrapper">
                           <a href="<?php echo $url; ?>" class="text-<?php echo estimate_status_color_class($status,true); ?> mbot15 inline-block estimate-status-dashboard-<?php echo estimate_status_color_class($status,true); ?>">
                              <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span>
                              <?php echo format_estimate_status($status,'',false); ?>
                           </a>
                        </div>
                        <div class="col-md-12 text-right progress-finance-status">
                           <?php echo $percent_data['percent']; ?>%
                           <div class="progress no-margin progress-bar-mini">
                              <div class="progress-bar progress-bar-<?php echo estimate_status_color_class($status); ?> no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                              </div>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php if(has_permission('proposals','','view') || has_permission('proposals','','view_own') || get_option('allow_staff_view_proposals_assigned') == 1 && total_rows('tblproposals',array('assigned'=>get_staff_user_id())) > 0){ ?>
                  <div class="col-md-12 col-sm-6 col-lg-4">
                     <div class="row">
                        <div class="col-md-12 text-stats-wrapper">
                           <p class="text-dark text-uppercase"><?php echo _l('home_proposal_overview'); ?></p>
                           <hr class="mtop15" />
                        </div>
                        <?php foreach($proposal_statuses as $status){
                           $url = admin_url('proposals/list_proposals?status='.$status);
                           $percent_data = get_proposals_percent_by_status($status);
                           ?>
                           <div class="col-md-12 text-stats-wrapper">
                              <a href="<?php echo $url; ?>" class="text-<?php echo proposal_status_color_class($status,true); ?> mbot15 inline-block">
                                 <span class="_total bold"><?php echo $percent_data['total_by_status']; ?></span> <?php echo format_proposal_status($status,'',false); ?>
                              </a>
                           </div>
                           <div class="col-md-12 text-right progress-finance-status">
                              <?php echo $percent_data['percent']; ?>%
                              <div class="progress no-margin progress-bar-mini">
                                 <div class="progress-bar progress-bar-<?php echo proposal_status_color_class($status); ?> no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $percent_data['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                                 </div>
                              </div>
                           </div>
                           <?php } ?>
                           <div class="clearfix"></div>
                        </div>
                     </div>
                     <?php } ?>
                  </div>
                  <?php if(has_permission('invoices','','view') || has_permission('invoices','','view_own')){ ?>
                  <hr />
                  <a href="#" class="hide invoices-total initialized"></a>
                  <div id="invoices_total" class="invoices-total-inline">
                     <?php load_invoices_total_template(); ?>
                  </div>
                  <?php } ?>
               </div>
            </div>
         </div>
         <?php } ?>
      </div>

