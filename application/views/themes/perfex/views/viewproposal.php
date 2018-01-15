<?php ob_start(); ?>
<table class="table items no-margin">
  <thead>
    <tr>
      <th>#</th>
      <th class="description" width="50%"><?php echo _l('estimate_table_item_heading'); ?></th>
      <?php
      $qty_heading = _l('estimate_table_quantity_heading');
      if($proposal->show_quantity_as == 2){
        $qty_heading = _l('estimate_table_hours_heading');
      } else if($proposal->show_quantity_as == 3){
        $qty_heading = _l('estimate_table_quantity_heading') .'/'._l('estimate_table_hours_heading');
      }
      ?>
      <th><?php echo $qty_heading; ?></th>
      <th><?php echo _l('estimate_table_rate_heading'); ?></th>
      <?php if(get_option('show_tax_per_item') == 1){ ?>
      <th><?php echo _l('estimate_table_tax_heading'); ?></th>
      <?php } ?>
      <th><?php echo _l('estimate_table_amount_heading'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $items_data = get_table_items_and_taxes($proposal->items,'proposal');
    $taxes = $items_data['taxes'];
    echo $items_data['html'];
    ?>
  </tbody>
</table>
<div class="row mtop15">
  <div class="col-md-6 col-md-offset-6">
    <table class="table text-right">
      <tbody>
        <tr id="subtotal">
          <td><span class="bold"><?php echo _l('estimate_subtotal'); ?></span>
          </td>
          <td class="subtotal">
            <?php echo format_money($proposal->subtotal,$proposal->symbol); ?>
          </td>
        </tr>
        <?php if($proposal->discount_percent != 0){ ?>
        <tr>
          <td>
            <span class="bold"><?php echo _l('estimate_discount'); ?> (<?php echo _format_number($proposal->discount_percent,true); ?>%)</span>
          </td>
          <td class="discount">
            <?php echo '-' . format_money($proposal->discount_total,$proposal->symbol); ?>
          </td>
        </tr>
        <?php } ?>
        <?php
        foreach($taxes as $tax){
          $total = array_sum($tax['total']);
          if($proposal->discount_percent != 0 && $proposal->discount_type == 'before_tax'){
            $total_tax_calculated = ($total * $proposal->discount_percent) / 100;
            $total = ($total - $total_tax_calculated);
          }
          $_tax_name = explode('|',$tax['tax_name']);
          echo '<tr class="tax-area"><td class="bold">'.$_tax_name[0].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($total,$proposal->symbol).'</td></tr>';
        }
        ?>
        <?php if((int)$proposal->adjustment != 0){ ?>
        <tr>
          <td>
            <span class="bold"><?php echo _l('estimate_adjustment'); ?></span>
          </td>
          <td class="adjustment">
            <?php echo format_money($proposal->adjustment,$proposal->symbol); ?>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <td><span class="bold"><?php echo _l('estimate_total'); ?></span>
          </td>
          <td class="total">
            <?php echo format_money($proposal->total,$proposal->symbol); ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php
if(get_option('total_to_words_enabled') == 1){ ?>
<div class="col-md-12 text-center">
 <p class="bold"><?php echo  _l('num_word').': '.$this->numberword->convert($proposal->total,$proposal->currency_name); ?></p>
</div>
<?php }
$items = ob_get_contents();
ob_end_clean();
$proposal->content = str_replace('{proposal_items}',$items,$proposal->content);
?>
<div id="proposal-wrapper">
  <div class="row proposal-wrapper">
    <div class="col-md-9 proposal-left tc-content proposal-content">
      <div class="row">
        <?php echo form_hidden('proposal_id',$proposal->id); ?>
        <div class="mtop30">
          <h1 class="bold"># <?php echo format_proposal_number($proposal->id); ?></h1>
          <h3><?php echo $proposal->subject; ?></h3>
          <hr />
          <?php echo $proposal->content; ?>
        </div>
      </div>
    </div>
    <div class="col-md-3 proposal-right">
      <div class="row proposal-right-content">
        <div class="col-md-12 mtop30">
          <?php if(is_staff_logged_in()){ ?>
          <a href="<?php echo admin_url('proposals/list_proposals/'.$proposal->id); ?>" class="btn btn-info pull-right"><?php echo _l('goto_admin_area'); ?></a>
          <?php } else if(is_client_logged_in() && has_contact_permission('proposals')){ ?>
          <a href="<?php echo site_url('clients/proposals/'); ?>" class="btn btn-info pull-right"><?php echo _l('client_go_to_dashboard'); ?></a>
          <?php } ?>
          <?php echo '<a href="'.site_url().'"><img src="'.base_url('uploads/company/'.get_option('company_logo')).'"></a>'; ?>
          <div class="row mtop10">
            <div class="col-md-12">
              <address>
                <?php echo format_organization_info(); ?>
              </address>
              <hr />
              <address class="no-margin">
                <?php echo format_proposal_info($proposal,'html'); ?>
              </address>
            </div>
          </div>
          <?php if(count($proposal->attachments) > 0 && $proposal->visible_attachments_to_customer_found == true){ ?>
          <div class="clearfix"></div>
          <hr />
          <p class="bold mbot15"><?php echo _l('proposal_files'); ?></p>
          <?php foreach($proposal->attachments as $attachment){
            // Do not show hidden attachments to customer
            if($attachment['visible_to_customer'] == 0){continue;}
            $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
            if(!empty($attachment['external'])){
              $attachment_url = $attachment['external_link'];
            }
            ?>
            <div class="col-md-12 row mbot15">
              <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
              <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
            </div>
            <?php } ?>
            <?php } ?>
            <?php if($proposal->total != 0){ ?>
            <h4 class="bold"><?php echo _l('proposal_total_info',format_money($proposal->total,$this->currencies_model->get($proposal->currency)->symbol)); ?></h4>
            <?php } ?>
          </div>
          <div class="col-md-8 mtop15">
            <?php if(($proposal->status != 2 && $proposal->status != 3)){
              if(!empty($proposal->open_till) && date('Y-m-d',strtotime($proposal->open_till)) < date('Y-m-d')){
                echo '<span class="warning-bg proposal-status">'._l('proposal_expired').'</span>';
              } else { ?>
              <?php if($identity_confirmation_enabled == '1'){ ?>
              <button type="button" id="accept_action" class="btn btn-success btn-block mtop10"><?php echo _l('proposal_accept_info'); ?></button>
              <?php } else { ?>
              <?php echo form_open($this->uri->uri_string()); ?>
              <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-success btn-block"><?php echo _l('proposal_accept_info'); ?></button>
              <?php echo form_hidden('action','accept_proposal'); ?>
              <?php echo form_close(); ?>
              <?php } ?>
              <?php echo form_open($this->uri->uri_string()); ?>
              <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-danger btn-block mtop10"><?php echo _l('proposal_decline_info'); ?></button>
              <?php echo form_hidden('action','decline_proposal'); ?>
              <?php echo form_close(); ?>
              <?php } ?>
              <!-- end expired proposal -->
              <?php } else {
                if($proposal->status == 2){
                  echo '<span class="danger-bg proposal-status">'._l('proposal_status_declined').'</span>';
                } else if($proposal->status == 3){
                  echo '<span class="success-bg proposal-status">'._l('proposal_status_accepted').'</span>';
                }
              } ?>
              <?php echo form_open($this->uri->uri_string()); ?>
              <button type="submit" class="btn btn-default btn-block mtop10"><i class="fa fa-file-pdf-o"></i> <?php echo _l('proposal_pdf_info'); ?></button>
              <?php echo form_hidden('action','proposal_pdf'); ?>
              <?php echo form_close(); ?>
            </div>
            <div class="clearfix"></div>
            <?php if($proposal->allow_comments == 1){ ?>
            <?php
            $proposal_comments = '';
            echo '<hr />';
            foreach ($comments as $comment) {
              $proposal_comments .= '<div class="col-md-12 proposal_comment mtop10 mbot10" data-commentid="' . $comment['id'] . '">';
              if($comment['staffid'] != 0){
               $proposal_comments .= staff_profile_image($comment['staffid'], array(
                'staff-profile-image-small',
                'media-object img-circle pull-left mright10'
              ));
             }
             $proposal_comments .= '<div class="media-body">';
             if($comment['staffid'] != 0){
              $proposal_comments .= get_staff_full_name($comment['staffid']).'<br />';
            }
            $proposal_comments .= check_for_links($comment['content']) . '<br />';
            $proposal_comments .= '<small class="mtop10 text-muted">' . _dt($comment['dateadded']) . '</small>';
            $proposal_comments .= '</div>';
            $proposal_comments .= '</div>';

          }
          echo $proposal_comments;
          ?>
          <div class="clearfix"></div>
          <div class="col-md-12">
            <?php echo form_open($this->uri->uri_string()) ;?>
            <div class="proposal-comment">
              <textarea name="content" id="content" rows="4" class="form-control mtop15"></textarea>
              <button type="submit" class="btn btn-info mtop10 pull-right"><?php echo _l('proposal_add_comment'); ?></button>
              <?php echo form_hidden('action','proposal_comment'); ?>
            </div>
            <?php echo form_close(); ?>
            <div class="clearfix"></div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <?php
    if($identity_confirmation_enabled == '1'){
        get_template_part('identity_confirmation_form',array('formData'=>form_hidden('action','accept_proposal')));
    }
  ?>
  <script>
    // Create lightbox for proposal content images
    $(function(){
      $('.proposal-content img').wrap( function(){ return '<a href="' + $(this).attr('src') + '" data-lightbox="proposal"></a>'; });
    });
  </script>
