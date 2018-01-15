<?php
$lead_already_client_tooltip = '';
$lead_is_client = $lead['is_lead_client'] !== '0';
if ($lead_is_client) {
 $lead_already_client_tooltip = ' data-toggle="tooltip" title="' . _l('lead_have_client_profile') . '"';
}
if ($lead['status'] == $status['id']) { ?>
<li data-lead-id="<?php echo $lead['id']; ?>"<?php echo $lead_already_client_tooltip; ?> class="lead-kan-ban<?php if ($lead['assigned'] == get_staff_user_id()) {echo ' current-user-lead'; } ?><?php if($lead_is_client && get_option('lead_lock_after_convert_to_customer') == 1 && !$is_admin){echo ' not-sortable';} ?>">
   <div class="panel-body lead-body">
      <div class="row">
         <div class="col-md-12 lead-name">
            <?php if ($lead['assigned'] != 0) { ?>
            <a href="<?php echo admin_url('profile/' . $lead['assigned']); ?>" data-placement="right" data-toggle="tooltip" title="<?php echo get_staff_full_name($lead['assigned']); ?>" class="pull-left mtop8 mright5">
               <?php echo staff_profile_image($lead['assigned'], array(
                  'staff-profile-image-xs'
                  )); ?></a>
                  <?php  } ?>
                  <a href="<?php echo admin_url('leads/index/'.$lead['id']); ?>" onclick="init_lead(<?php echo $lead['id']; ?>);return false;" class="pull-left">
                     <span class="inline-block mtop10 mbot10">#<?php echo $lead['id'] . ' - ' . $lead['lead_name']; ?></span>
                  </a>
               </div>
               <div class="col-md-6 text-muted">
                  <small  class="text-dark"><?php echo _l('leads_canban_source', $lead['source_name']); ?></small>
               </div>
               <div class="col-md-6 text-right text-muted">
                  <?php if(is_date($lead['lastcontact']) && $lead['lastcontact'] != '0000-00-00 00:00:00'){ ?>
                     <small class="text-dark"><?php echo _l('leads_dt_last_contact'); ?> <span class="bold">
                        <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($lead['lastcontact']); ?>">
                           <?php echo time_ago($lead['lastcontact']); ?>
                        </span>
                     </span>
                  </small><br />
                  <?php } ?>
                  <small class="text-dark"><?php echo _l('lead_created'); ?>: <span class="bold">
                    <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($lead['dateadded']); ?>">
                       <?php echo time_ago($lead['dateadded']); ?>
                    </span>
                 </span>
              </small><br />
              <span class="mright5 mtop5 inline-block text-muted" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('leads_canban_notes',$lead['total_notes']); ?>">
               <i class="fa fa-sticky-note-o"></i> <?php echo $lead['total_notes']; ?>
            </span>
            <span class="mtop5 inline-block text-muted" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('lead_kan_ban_attachments',$lead['total_files']); ?>">
               <i class="fa fa-paperclip"></i>
               <?php echo $lead['total_files']; ?>
            </span>
         </div>
         <?php if($lead['tags']){ ?>
         <div class="col-md-12">
            <div class="mtop5 kanban-tags">
               <?php echo render_tags($lead['tags']); ?>
            </div>
         </div>
         <?php } ?>
      </div>
   </div>
</li>
<?php }
