<?php
$i = 0;
foreach ($statuses as $status) {
 $total_pages = ceil($this->proposals_model->do_kanban_query($status,$this->input->get('search'),1,array(),true)/get_option('proposals_pipeline_limit'));
 ?>
 <ul class="kan-ban-col" data-col-status-id="<?php echo $status; ?>" data-total-pages="<?php echo $total_pages; ?>">
  <li class="kan-ban-col-wrapper">
    <div class="border-right panel_s no-mbot">
      <div class="panel-heading-bg <?php echo proposal_status_color_class($status); ?>-bg">
       <div class="kan-ban-step-indicator<?php if($i == count($statuses) -1){ echo ' kan-ban-step-indicator-full'; } ?>"></div>
       <?php echo format_proposal_status($status,'',false); ?>
     </div>
     <div class="kan-ban-content-wrapper">
      <div class="kan-ban-content">
        <ul class="sortable<?php if(has_permission('proposals','','edit')){echo ' status pipeline-status'; } ?>" data-status-id="<?php echo $status; ?>">
          <?php
          $proposals = $this->proposals_model->do_kanban_query($status,$this->input->get('search'),1,array('sort_by'=>$this->input->get('sort_by'),'sort'=>$this->input->get('sort')));
          $total_proposals = count($proposals);
          foreach ($proposals as $proposal) {
            $this->load->view('admin/proposals/pipeline/_kanban_card',array('proposal'=>$proposal,'status'=>$status));
          }
          ?>
          <?php if($total_proposals > 0 ){ ?>
          <li class="text-center not-sortable kanban-load-more" data-load-status="<?php echo $status; ?>">
            <a href="#" class="btn btn-default btn-block<?php if($total_pages <= 1){echo ' disabled';} ?>" data-page="1" onclick="kanban_load_more(<?php echo $status; ?>,this,'proposals/pipeline_load_more',347,360); return false;";><?php echo _l('load_more'); ?></a>
          </li>
          <?php } ?>
          <li class="text-center not-sortable mtop30 kanban-empty<?php if($total_proposals > 0){echo ' hide';} ?>">
            <h4 class="text-muted">
              <i class="fa fa-circle-o-notch" aria-hidden="true"></i><br /><br />
              <?php echo _l('no_proposals_found'); ?></h4>
            </li>
          </ul>
        </div>
      </div>
    </li>
  </ul>
  <?php $i++;} ?>
