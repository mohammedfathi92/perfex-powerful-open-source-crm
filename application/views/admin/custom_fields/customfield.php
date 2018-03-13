<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                            <?php if(isset($custom_field)){ ?>
                            <a href="<?php echo admin_url('custom_fields/field'); ?>" class="btn btn-success pull-right"><?php echo _l('new_custom_field'); ?></a>
                            <div class="clearfix"></div>
                            <?php } ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <div class="company_field_info mbot25 alert alert-info<?php if(isset($custom_field) && $custom_field->fieldto != 'company' || !isset($custom_field)){echo ' hide';} ?>">
                           <?php echo _l('custom_field_info_format_embed_info',array(
                            _l('custom_field_company'),
                            '<a href="'.admin_url('settings?group=company#settings[company_info_format]').'" target="_blank">'.admin_url('settings?group=company').'</a>'
                            )); ?>
                        </div>
                        <div class="customers_field_info mbot25 alert alert-info<?php if(isset($custom_field) && $custom_field->fieldto != 'customers' || !isset($custom_field)){echo ' hide';} ?>">
                            <?php echo _l('custom_field_info_format_embed_info',array(
                                _l('clients'),
                                '<a href="'.admin_url('settings?group=clients#settings[customer_info_format]').'" target="_blank">'.admin_url('settings?group=clients').'</a>'
                                )); ?>
                            </div>
                             <div class="items_field_info mbot25 alert alert-warning<?php if(isset($custom_field) && $custom_field->fieldto != 'items' || !isset($custom_field)){echo ' hide';} ?>">
                                Custom fields for items can't be included in calculation of totals.
                            </div>
                            <div class="proposal_field_info mbot25 alert alert-info<?php if(isset($custom_field) && $custom_field->fieldto != 'proposal' || !isset($custom_field)){echo ' hide';} ?>">
                                <?php echo _l('custom_field_info_format_embed_info',array(
                                    _l('proposals'),
                                    '<a href="'.admin_url('settings?group=sales&tab=proposals#settings[proposal_info_format]').'" target="_blank">'.admin_url('settings?group=sales&tab=proposals').'</a>'
                                    )); ?>
                                </div>

                                <?php echo form_open($this->uri->uri_string()); ?>
                                <?php
                                $disable = '';
                                if(isset($custom_field)){
                                  if(total_rows('tblcustomfieldsvalues',array('fieldid'=>$custom_field->id,'fieldto'=>$custom_field->fieldto)) > 0){
                                    $disable = 'disabled';
                                }
                            }
                            ?>
                          <div class="select-placeholder">
                                <label for="fieldto"><?php echo _l('custom_field_add_edit_belongs_top'); ?></label>
                            <select name="fieldto" id="fieldto" class="selectpicker" data-width="100%" <?php echo $disable; ?> data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <option value="company" <?php if(isset($custom_field) && $custom_field->fieldto == 'company'){echo 'selected';} ?>><?php echo _l('custom_field_company'); ?></option>
                                <option value="leads" <?php if(isset($custom_field) && $custom_field->fieldto == 'leads'){echo 'selected';} ?>><?php echo _l('custom_field_leads'); ?></option>
                                <option value="customers" <?php if(isset($custom_field) && $custom_field->fieldto == 'customers'){echo 'selected';} ?>><?php echo _l('custom_field_customers'); ?></option>
                                <option value="contacts" <?php if(isset($custom_field) && $custom_field->fieldto == 'contacts'){echo 'selected';} ?>><?php echo _l('custom_field_contacts'); ?></option>
                                <option value="staff" <?php if(isset($custom_field) && $custom_field->fieldto == 'staff'){echo 'selected';} ?>><?php echo _l('custom_field_staff'); ?></option>
                                <option value="contracts" <?php if(isset($custom_field) && $custom_field->fieldto == 'contracts'){echo 'selected';} ?>><?php echo _l('custom_field_contracts'); ?></option>
                                <option value="tasks" <?php if(isset($custom_field) && $custom_field->fieldto == 'tasks'){echo 'selected';} ?>><?php echo _l('custom_field_tasks'); ?></option>
                                <option value="expenses" <?php if(isset($custom_field) && $custom_field->fieldto == 'expenses'){echo 'selected';} ?>><?php echo _l('custom_field_expenses'); ?></option>
                                <option value="invoice" <?php if(isset($custom_field) && $custom_field->fieldto == 'invoice'){echo 'selected';} ?>><?php echo _l('custom_field_invoice'); ?></option>
                                <option value="items" <?php if(isset($custom_field) && $custom_field->fieldto == 'items'){echo 'selected';} ?>><?php echo _l('items'); ?></option>
                                <option value="credit_note" <?php if(isset($custom_field) && $custom_field->fieldto == 'credit_note'){echo 'selected';} ?>><?php echo _l('credit_note'); ?></option>
                                <option value="estimate" <?php if(isset($custom_field) && $custom_field->fieldto == 'estimate'){echo 'selected';} ?>><?php echo _l('custom_field_estimate'); ?></option>
                                <option value="proposal" <?php if(isset($custom_field) && $custom_field->fieldto == 'proposal'){echo 'selected';} ?>><?php echo _l('proposal'); ?></option>
                                <option value="projects" <?php if(isset($custom_field) && $custom_field->fieldto == 'projects'){echo 'selected';} ?>><?php echo _l('projects'); ?></option>
                                <option value="tickets" <?php if(isset($custom_field) && $custom_field->fieldto == 'tickets'){echo 'selected';} ?>><?php echo _l('tickets'); ?></option>
                            </select>
                          </div>
                            <div class="clearfix mbot15"></div>
                            <?php $value = (isset($custom_field) ? $custom_field->name : ''); ?>
                            <?php echo render_input('name','custom_field_name',$value); ?>
                           <div class="select-placeholder">
                                <label for="type"><?php echo _l('custom_field_add_edit_type'); ?></label>
                            <select name="type" id="type" class="selectpicker"<?php if(isset($custom_field) && total_rows('tblcustomfieldsvalues',array('fieldid'=>$custom_field->id,'fieldto'=>$custom_field->fieldto)) > 0){echo ' disabled';} ?> data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true">
                                <option value=""></option>
                                <option value="input" <?php if(isset($custom_field) && $custom_field->type == 'input'){echo 'selected';} ?>>Input</option>
                                <option value="number" <?php if(isset($custom_field) && $custom_field->type == 'number'){echo 'selected';} ?>>Number</option>
                                <option value="textarea" <?php if(isset($custom_field) && $custom_field->type == 'textarea'){echo 'selected';} ?>>Textarea</option>
                                <option value="select" <?php if(isset($custom_field) && $custom_field->type == 'select'){echo 'selected';} ?>>Select</option>
                                <option value="multiselect" <?php if(isset($custom_field) && $custom_field->type == 'multiselect'){echo 'selected';} ?>>Multi Select</option>
                                <option value="checkbox" <?php if(isset($custom_field) && $custom_field->type == 'checkbox'){echo 'selected';} ?>>Checkbox</option>
                                <option value="date_picker" <?php if(isset($custom_field) && $custom_field->type == 'date_picker'){echo 'selected';} ?>>Date Picker</option>
                                <option value="date_picker_time" <?php if(isset($custom_field) && $custom_field->type == 'date_picker_time'){echo 'selected';} ?>>Datetime Picker</option>
                                <option value="colorpicker" <?php if(isset($custom_field) && $custom_field->type == 'colorpicker'){echo 'selected';} ?>>Color Picker</option>
                                <option value="link" <?php if(isset($custom_field) && $custom_field->type == 'link'){echo 'selected';} ?><?php if(isset($custom_field) && $custom_field->fieldto == 'items'){echo 'disabled';} ?>>Hyperlink</option>
                            </select>
                           </div>
                            <div class="clearfix mbot15"></div>
                            <div id="options_wrapper" class="<?php if(!isset($custom_field) || isset($custom_field) && $custom_field->type != 'select' && $custom_field->type != 'checkbox' && $custom_field->type != 'multiselect'){echo 'hide';} ?>">
                                <span class="pull-left fa fa-question-circle" data-toggle="tooltip" title="<?php echo _l('custom_field_add_edit_options_tooltip'); ?>"></span>
                                <?php $value = (isset($custom_field) ? $custom_field->options : ''); ?>
                                <?php echo render_textarea('options','custom_field_add_edit_options',$value,array('rows'=>3)); ?>
                            </div>
                            <?php $value = (isset($custom_field) ? $custom_field->field_order : ''); ?>
                            <?php echo render_input('field_order','custom_field_add_edit_order',$value,'number'); ?>
                            <div class="form-group">
                                <label for="bs_column"><?php echo _l('custom_field_column'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-addon">col-md-</span>
                                    <input type="number" max="12" class="form-control" name="bs_column" id="bs_column" value="<?php if(!isset($custom_field)){echo 12;} else{echo $custom_field->bs_column;} ?>">
                                </div>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="disabled" id="disabled" <?php if(isset($custom_field) && $custom_field->active == 0){echo 'checked';} ?>>
                                <label for="disabled"><?php echo _l('custom_field_add_edit_disabled'); ?></label>
                            </div>
                            <div class="display-inline-checkbox checkbox checkbox-primary<?php if(!isset($custom_field) || isset($custom_field) && $custom_field->type != 'checkbox'){echo ' hide';} ?>">
                                <input type="checkbox" value="1" name="display_inline" id="display_inline" <?php if(isset($custom_field) && $custom_field->display_inline == 1){echo 'checked';} ?>>
                                <label for="display_inline"><?php echo _l('display_inline'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="only_admin" id="only_admin" <?php if(isset($custom_field) && $custom_field->only_admin == 1){echo 'checked';} ?> <?php if(isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items')){echo 'disabled';} ?>>
                                <label for="only_admin"><?php echo _l('custom_field_only_admin'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary disalow_client_to_edit <?php if(!isset($custom_field) || (isset($custom_field) && !in_array($custom_field->fieldto,$client_portal_fields)) || (isset($custom_field) && !in_array($custom_field->fieldto,$client_editable_fields))){echo 'hide';} ?>">
                                <input type="checkbox" name="disalow_client_to_edit" id="disalow_client_to_edit" <?php if(isset($custom_field) && $custom_field->disalow_client_to_edit == 1){echo 'checked';} ?> <?php if(isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->only_admin == '1')){echo 'disabled';} ?>>
                                <label for="disalow_client_to_edit"> <?php echo _l('custom_field_disallow_customer_to_edit'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary" id="required_wrap">
                                <input type="checkbox" name="required" id="required" <?php if(isset($custom_field) && $custom_field->required == 1){echo 'checked';} ?> <?php if(isset($custom_field) && $custom_field->fieldto == 'company'){echo 'disabled';} ?>>
                                <label for="required"><?php echo _l('custom_field_required'); ?></label>
                            </div>
                            <p class="bold text-info"><?php echo _l('custom_field_visibility'); ?></p>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="show_on_table" id="show_on_table" <?php if(isset($custom_field) && $custom_field->show_on_table == 1){echo 'checked';} ?> <?php if(isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items')){echo 'disabled';} ?>>
                                <label for="show_on_table"><?php echo _l('custom_field_show_on_table'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary show-on-pdf <?php if(!isset($custom_field) || (isset($custom_field) && !in_array($custom_field->fieldto,$pdf_fields))){echo 'hide';} ?>">
                                <input type="checkbox" name="show_on_pdf" id="show_on_pdf" <?php if(isset($custom_field) && $custom_field->show_on_pdf == 1){echo 'checked';} ?> <?php if(isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items')){echo 'disabled';} ?>>
                                <label for="show_on_pdf"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('custom_field_pdf_html_help'); ?>"></i> <?php echo _l('custom_field_show_on_pdf'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary show-on-client-portal <?php if(!isset($custom_field) || (isset($custom_field) && !in_array($custom_field->fieldto,$client_portal_fields))){echo 'hide';} ?>">
                                <input type="checkbox" name="show_on_client_portal" id="show_on_client_portal" <?php if(isset($custom_field) && $custom_field->show_on_client_portal == 1){echo 'checked';} ?> <?php if(isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->only_admin == '1')){echo 'disabled';} ?>>
                                <label for="show_on_client_portal"><i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('custom_field_show_on_client_portal_help'); ?>"></i> <?php echo _l('custom_field_show_on_client_portal'); ?></label>
                            </div>

                            <div class="show-on-ticket-form checkbox checkbox-primary<?php if(!isset($custom_field) || isset($custom_field) && $custom_field->fieldto != 'tickets'){echo ' hide';} ?>">
                                <input type="checkbox" value="1" name="show_on_ticket_form" id="show_on_ticket_form" <?php if(isset($custom_field) && $custom_field->show_on_ticket_form == 1){echo 'checked';} ?>>
                                <label for="show_on_ticket_form"><?php echo _l('show_on_ticket_form'); ?></label>
                            </div>
                            <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
<script>
var pdf_fields = <?php echo json_encode($pdf_fields); ?>;
var client_portal_fields = <?php echo json_encode($client_portal_fields); ?>;
var client_editable_fields = <?php echo json_encode($client_editable_fields); ?>;
$(function () {
    _validate_form($('form'), {
        fieldto: 'required',
        name: 'required',
        type: 'required',
        bs_column: 'required',
        options: {
            required: {
                depends: function (element) {
                    var type = $('#type').val();
                    return type == 'select' || type == 'checkbox' || type == 'multiselect';
                }
            }
        }
    });
    $('form').on('submit', function () {
        $('#fieldto,#type').removeAttr('disabled');
        return true;
    });
    $('select[name="fieldto"]').on('change', function () {
        var field = $(this).val();
        if ($.inArray(field, pdf_fields) !== -1) {
            $('.show-on-pdf').removeClass('hide');
        } else {
            $('.show-on-pdf').addClass('hide');
        }

        if ($.inArray(field, client_portal_fields) !== -1) {
            $('.show-on-client-portal').removeClass('hide');
            $('.disalow_client_to_edit').removeClass('hide');

            if ($.inArray(field, client_editable_fields) !== -1) {
                $('.disalow_client_to_edit').removeClass('hide');
            } else {
                $('.disalow_client_to_edit').addClass('hide');
                $('.disalow_client_to_edit input').prop('checked', false);
            }
        } else {
            $('.show-on-client-portal').addClass('hide');
            $('.disalow_client_to_edit').addClass('hide');
        }
        if (field == 'tickets') {
            $('.show-on-ticket-form').removeClass('hide');
        } else {
            $('.show-on-ticket-form').addClass('hide');
            $('.show-on-ticket-form input').prop('checked', false);
        }

        if (field == 'customers') {
            $('.customers_field_info').removeClass('hide');
        } else {
            $('.customers_field_info').addClass('hide');
        }

        if (field == 'items') {
            $('.items_field_info').removeClass('hide');
        } else {
            $('.items_field_info').addClass('hide');
        }

        if (field == 'company') {
            $('.company_field_info').removeClass('hide');
        } else {
            $('.company_field_info').addClass('hide');
        }

        if (field == 'proposal') {
            $('.proposal_field_info').removeClass('hide');
        } else {
            $('.proposal_field_info').addClass('hide');
        }

        if (field == 'company') {
            $('#only_admin').prop('disabled', true).prop('checked', false);
            $('input[name="required"]').prop('disabled', true).prop('checked', false);
            $('#show_on_table').prop('disabled', true).prop('checked', false);
            $('#show_on_client_portal').prop('disabled', true).prop('checked', true);
        } else if(field =='items'){
            $('#type option[value="link"]').prop('disabled', true);
            $('#show_on_table').prop('disabled', true).prop('checked', true);
            $('#show_on_pdf').prop('disabled', true).prop('checked', true);
            $('#only_admin').prop('disabled', true).prop('checked', false);
        } else {
            $('#only_admin').prop('disabled', false).prop('checked',false);
            $('input[name="required"]').prop('disabled', false).prop('checked',false);
            $('#show_on_table').prop('disabled', false).prop('checked',false);
            $('#show_on_client_portal').prop('disabled', false).prop('checked',false);
            $('#show_on_pdf').prop('disabled', false).prop('checked',false);
            $('#type option[value="link"]').prop('disabled', false);
        }
        $('#type').selectpicker('refresh');
    });
    $('select[name="type"]').on('change', function () {
        var type = $(this).val();
        var options_wrapper = $('#options_wrapper');
        var display_inline = $('.display-inline-checkbox')
        if (type == 'select' || type == 'checkbox' || type == 'multiselect') {
            options_wrapper.removeClass('hide');
            if (type == 'checkbox') {
                display_inline.removeClass('hide');
            } else {
                display_inline.addClass('hide');
                display_inline.find('input').prop('checked', false);
            }
        } else {
            options_wrapper.addClass('hide');
            display_inline.addClass('hide');
            display_inline.find('input').prop('checked', false);
        }
    });

    $('body').on('change', 'input[name="only_admin"]', function () {
        $('#show_on_client_portal').prop('disabled', $(this).prop('checked')).prop('checked', false);
        $('#disalow_client_to_edit').prop('disabled', $(this).prop('checked')).prop('checked', false);
    });
});
</script>
</body>
</html>
