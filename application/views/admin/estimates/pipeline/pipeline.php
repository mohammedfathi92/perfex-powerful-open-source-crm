<?php
$i = 0;
$has_permission_edit = has_permission('estimates','','edit');
foreach ($estimate_statuses as $status) {
 $total_pages = ceil($this->estimates_model->do_kanban_query($status,$this->input->get('search'),1,array(),true)/get_option('estimates_pipeline_limit'));
 ?>
 <ul class="kan-ban-col" data-col-status-id="<?php echo $status; ?>" data-total-pages="<?php echo $total_pages; ?>">
    <li class="kan-ban-col-wrapper">
        <div class="border-right panel_s no-mbot">
            <div class="panel-heading-bg <?php echo estimate_status_color_class($status); ?>-bg estimate-status-pipeline-<?php echo estimate_status_color_class($status); ?>">
                <div class="kan-ban-step-indicator<?php if($i == count($estimate_statuses) -1){ echo ' kan-ban-step-indicator-full'; } ?>"></div>
                <?php echo estimate_status_by_id($status); ?>
            </div>
            <div class="kan-ban-content-wrapper">
                <div class="kan-ban-content">
                    <ul class="sortable<?php if($has_permission_edit){echo ' status pipeline-status';} ?>" data-status-id="<?php echo $status; ?>">
                        <?php
                        $estimates = $this->estimates_model->do_kanban_query($status,$this->input->get('search'),1,array('sort_by'=>$this->input->get('sort_by'),'sort'=>$this->input->get('sort')));
                        $total_estimates = count($estimates);
                        foreach ($estimates as $estimate) {
                            $this->load->view('admin/estimates/pipeline/_kanban_card',array('estimate'=>$estimate,'status'=>$status));
                        } ?>
                        <?php if($total_estimates > 0 ){ ?>
                        <li class="text-center not-sortable kanban-load-more" data-load-status="<?php echo $status; ?>">
                            <a href="#" class="btn btn-default btn-block<?php if($total_pages <= 1){echo ' disabled';} ?>" data-page="1" onclick="kanban_load_more(<?php echo $status; ?>,this,'estimates/pipeline_load_more',310,360); return false;";><?php echo _l('load_more'); ?></a>
                        </li>
                        <?php } ?>
                        <li class="text-center not-sortable mtop30 kanban-empty<?php if($total_estimates > 0){echo ' hide';} ?>">
                            <h4 class="text-muted">
                              <i class="fa fa-circle-o-notch" aria-hidden="true"></i><br /><br />
                              <?php echo _l('no_estimates_found'); ?></h4>
                          </li>
                      </ul>
                  </div>
              </div>
          </li>
      </ul>
      <?php $i++; } ?>
