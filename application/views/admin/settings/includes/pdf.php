<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#general" aria-controls="general" role="tab" data-toggle="tab"><?php echo _l('settings_group_general'); ?></a>
    </li>
    <li role="presentation">
        <a href="#signature" aria-controls="general" role="tab" data-toggle="tab"><?php echo _l('signature'); ?></a>
    </li>
    <li role="presentation">
        <a href="#formats" aria-controls="invoice" role="tab" data-toggle="tab"><?php echo _l('pdf_formats'); ?></a>
    </li>
</ul>
<div class="tab-content mtop30">

    <div role="tabpanel" class="tab-pane active" id="general">

        <?php $fonts = $this->pdf->get_fonts_list(); ?>
        <label class="control-label"><?php echo _l('settings_pdf_font'); ?></label>
        <select name="settings[pdf_font]" class="selectpicker" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <?php
            foreach($fonts as $font){
                $selected = '';
                if(get_option('pdf_font') == $font){
                    $selected = 'selected';
                }
                echo '<option value="'.$font.'" '.$selected.'>'.$font.'</option>';
            }
            ?>
        </select>
        <hr />
        <?php render_yes_no_option('swap_pdf_info','swap_pdf_info'); ?>
        <hr />
        <?php echo render_input('settings[pdf_font_size]','settings_pdf_font_size',get_option('pdf_font_size'),'number'); ?>
        <hr />
        <?php echo render_color_picker('settings[pdf_table_heading_color]',_l('settings_pdf_table_heading_color'),get_option('pdf_table_heading_color')); ?>
        <hr />
        <?php echo render_color_picker('settings[pdf_table_heading_text_color]',_l('settings_pdf_table_heading_text_color'),get_option('pdf_table_heading_text_color')); ?>
        <hr />
        <?php echo render_input('settings[custom_pdf_logo_image_url]','settings_custom_pdf_logo_image_url',get_option('custom_pdf_logo_image_url'),'text',array('data-toggle'=>'tooltip','title'=>'settings_custom_pdf_logo_image_url_tooltip')); ?>
        <hr />
        <?php echo render_input('settings[pdf_logo_width]','pdf_logo_width',get_option('pdf_logo_width'),'number'); ?>
        <hr />
        <?php render_yes_no_option('show_status_on_pdf_ei',_l('show_status_on_pdf',_l('invoice').'/'._l('estimate').'/'._l('credit_note'))); ?>
        <hr />

        <?php render_yes_no_option('show_pay_link_to_invoice_pdf','show_pay_link_to_invoice_pdf'); ?>
        <hr />
        <?php render_yes_no_option('show_transactions_on_invoice_pdf','show_transactions_on_invoice_pdf'); ?>
        <hr />
        <?php render_yes_no_option('show_page_number_on_pdf','show_page_number_on_pdf'); ?>
    </div>
    <div role="tabpanel" class="tab-pane" id="signature">
     <?php render_yes_no_option('show_pdf_signature_invoice','show_pdf_signature_invoice'); ?>
     <hr />
     <?php render_yes_no_option('show_pdf_signature_estimate','show_pdf_signature_estimate'); ?>
     <hr />
     <?php render_yes_no_option('show_pdf_signature_credit_note','show_pdf_signature_credit_note'); ?>
     <hr />
     <?php $signature = get_option('signature_image'); ?>
     <?php if($signature != ''){ ?>
     <div class="form-group">
        <div class="row">
            <div class="col-md-9">
                <img src="<?php echo base_url('uploads/company/'.$signature); ?>" class="img img-responsive">
            </div>
            <?php if(has_permission('settings','','delete')){ ?>
            <div class="col-md-3 text-right">
                <a href="<?php echo admin_url('settings/remove_signature_image'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php } else { ?>
    <div class="form-group">
        <label for="signature_image" class="control-label"><?php echo _l('signature_image'); ?></label>
        <input type="file" name="signature_image" class="form-control">
    </div>
    <?php } ?>
</div>
<div role="tabpanel" class="tab-pane" id="formats">
  <label for="pdf_format_invoice" class="control-label"><?php echo _l('invoice'); ?></label>
  <select name="settings[pdf_format_invoice]" id="pdf_format_invoice" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_invoice') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_invoice') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>
    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_invoice') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_invoice') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>

</select>
<hr />
<label for="pdf_format_estimate" class="control-label"><?php echo _l('estimate'); ?></label>
<select name="settings[pdf_format_estimate]" id="pdf_format_estimate" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_estimate') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_estimate') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>

    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_estimate') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_estimate') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>

</select>
<hr />
<label for="pdf_format_proposal" class="control-label"><?php echo _l('proposal'); ?></label>
<select name="settings[pdf_format_proposal]" id="pdf_format_proposal" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_proposal') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_proposal') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>

    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_proposal') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_proposal') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>

</select>
<hr />
<label for="pdf_format_payment" class="control-label"><?php echo _l('payment'); ?></label>
<select name="settings[pdf_format_payment]" id="pdf_format_payment" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_payment') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_payment') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>
    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_payment') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_payment') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>
</select>
<hr />
<label for="pdf_format_credit_note" class="control-label"><?php echo _l('credit_note'); ?></label>
<select name="settings[pdf_format_credit_note]" id="pdf_format_credit_note" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_credit_note') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_credit_note') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>
    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_credit_note') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_credit_note') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>
</select>
<hr />
<label for="pdf_format_contract" class="control-label"><?php echo _l('contract'); ?></label>
<select name="settings[pdf_format_contract]" id="pdf_format_contract" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_contract') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_contract') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>
    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_contract') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_contract') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>
</select>
<hr />
<label for="pdf_format_statement" class="control-label"><?php echo _l('customer_statement'); ?></label>
<select name="settings[pdf_format_statement]" id="pdf_format_statement" class="selectpicker" data-width="100%">
    <option value="A4-PORTRAIT" <?php if(get_option('pdf_format_statement') == 'A4-PORTRAIT'){echo 'selected'; }?>>A4 <?php echo _l('format_a4_portrait_size'); ?></option>
    <option value="A4-LANDSCAPE" <?php if(get_option('pdf_format_statement') == 'A4-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_size'); ?></option>
    <option value="LETTER-PORTRAIT" <?php if(get_option('pdf_format_statement') == 'LETTER-PORTRAIT'){echo 'selected'; }?>><?php echo _l('format_letter_portrait'); ?></option>
    <option value="LETTER-LANDSCAPE" <?php if(get_option('pdf_format_statement') == 'LETTER-LANDSCAPE'){echo 'selected'; }?>><?php echo _l('format_letter_landscape'); ?></option>
</select>

</div>
</div>
