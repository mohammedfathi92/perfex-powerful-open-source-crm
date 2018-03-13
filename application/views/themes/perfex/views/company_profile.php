<div class="row">
    <div class="col-md-12">
        <?php echo form_open_multipart('clients/company'); ?>
        <!-- Required hidden field -->
        <?php echo form_hidden('company_form',true); ?>
        <div class="panel_s">
            <div class="panel-body">
               <h4 class="no-margin"><?php echo _l('clients_profile_heading'); ?></h4>
           </div>
       </div>
       <div class="panel_s">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="company" class="control-label"><?php echo _l('clients_company'); ?></label>
                        <?php
                        $company_val = $client->company;
                        if(!empty($company_val)){
                                // Check if is realy empty client company so we can set this field to empty
                                // The query where fetch the client auto populate firstname and lastname if company is empty
                            if(is_empty_customer_company($client->userid)){
                                $company_val = '';
                            }
                        }
                        ?>
                        <input type="text" class="form-control" name="company" value="<?php echo set_value('company',$company_val); ?>">
                        <?php echo form_error('company'); ?>
                    </div>
                    <?php if(get_option('company_requires_vat_number_field') == 1){ ?>
                    <div class="form-group">
                        <label for="vat" class="control-label"><?php echo _l('clients_vat'); ?></label>
                        <input type="text" class="form-control" name="vat" value="<?php if(isset($client)){echo $client->vat;} ?>">
                    </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="phonenumber"><?php echo _l('clients_phone'); ?></label>
                        <input type="text" class="form-control" name="phonenumber" id="phonenumber" value="<?php echo $client->phonenumber; ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="website"><?php echo _l('client_website'); ?></label>
                        <input type="text" class="form-control" name="website" id="website" value="<?php echo $client->website; ?>">
                    </div>
                    <div class="form-group">
                        <label for="lastname"><?php echo _l('clients_country'); ?></label>
                        <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true" name="country" class="form-control" id="country">
                            <option value=""></option>
                            <?php foreach(get_all_countries() as $country){ ?>
                            <?php
                            $selected = '';
                            if($client->country == $country['country_id']){echo $selected = true;}
                            ?>
                            <option value="<?php echo $country['country_id']; ?>" <?php echo set_select('country', $country['country_id'],$selected); ?>><?php echo $country['short_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="city"><?php echo _l('clients_city'); ?></label>
                        <input type="text" class="form-control" name="city" id="city" value="<?php echo $client->city; ?>">
                    </div>
                    <div class="form-group">
                        <label for="address"><?php echo _l('clients_address'); ?></label>
                        <textarea name="address" id="address" class="form-control" rows="4"><?php echo clear_textarea_breaks($client->address); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="zip"><?php echo _l('clients_zip'); ?></label>
                        <input type="text" class="form-control" name="zip" id="zip" value="<?php echo $client->zip; ?>">
                    </div>
                    <div class="form-group">
                        <label for="state"><?php echo _l('clients_state'); ?></label>
                        <input type="text" class="form-control" name="state" id="state" value="<?php echo $client->state; ?>">
                    </div>
                    <?php if(get_option('disable_language') == 0){ ?>
                    <div class="form-group">
                        <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
                        </label>
                        <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" name="default_language" id="default_language" class="form-control selectpicker">
                            <option value="" <?php if($client->default_language == ''){echo 'selected';} ?>><?php echo _l('system_default_string'); ?></option>
                            <?php foreach(list_folders(APPPATH .'language') as $language){
                                $selected = '';
                                if($client->default_language == $language){
                                  $selected = 'selected';
                              }
                              ?>
                              <option value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                              <?php } ?>
                          </select>
                      </div>
                      <?php } ?>
                  </div>
                  <div class="col-md-12">
                    <?php echo render_custom_fields( 'customers',$client->userid,array('show_on_client_portal'=>1)); ?>
                </div>
                <?php if(get_option('allow_primary_contact_to_view_edit_billing_and_shipping') == 1 && is_primary_contact()){ ?>
                <div class="col-md-12">
                    <h3><?php echo _l('billing_shipping'); ?></h3>
                    <hr class="no-mbot"/>
                </div>
                <div class="col-md-6">
                    <?php $countries= get_all_countries(); ?>
                    <h4 class="mbot15 mtop20"><?php echo _l('billing_address'); ?></h4>
                    <div class="form-group">
                        <label for="billing_street"><?php echo _l('billing_street'); ?></label>
                        <textarea name="billing_street" id="billing_street" class="form-control" rows="4"><?php echo clear_textarea_breaks($client->billing_street); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="billing_city"><?php echo _l('billing_city'); ?></label>
                        <input type="text" class="form-control" name="billing_city" id="billing_city" value="<?php echo $client->billing_city; ?>">
                    </div>
                    <div class="form-group">
                        <label for="billing_state"><?php echo _l('billing_state'); ?></label>
                        <input type="text" class="form-control" name="billing_state" id="billing_state" value="<?php echo $client->billing_state; ?>">
                    </div>
                    <div class="form-group">
                        <label for="billing_zip"><?php echo _l('billing_zip'); ?></label>
                        <input type="text" class="form-control" name="billing_zip" id="billing_zip" value="<?php echo $client->billing_zip; ?>">
                    </div>
                    <div class="form-group">
                        <label for="billing_country"><?php echo _l('billing_country'); ?></label>
                        <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" name="billing_country" id="billing_country" class="form-control">
                            <option value=""></option>
                            <?php foreach($countries as $country){ ?>
                            <option value="<?php echo $country['country_id']; ?>"<?php if($client->billing_country == $country['country_id']){echo ' selected';} ?>><?php echo $country['short_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <?php $countries= get_all_countries(); ?>
                    <h4 class="mbot15 mtop20"><?php echo _l('shipping_address'); ?></h4>
                    <div class="form-group">
                        <label for="shipping_street"><?php echo _l('shipping_street'); ?></label>
                         <textarea name="shipping_street" id="shipping_street" class="form-control" rows="4"><?php echo clear_textarea_breaks($client->shipping_street); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="shipping_city"><?php echo _l('shipping_city'); ?></label>
                        <input type="text" class="form-control" name="shipping_city" id="shipping_city" value="<?php echo $client->shipping_city; ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_state"><?php echo _l('shipping_state'); ?></label>
                        <input type="text" class="form-control" name="shipping_state" id="shipping_state" value="<?php echo $client->shipping_state; ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_zip"><?php echo _l('shipping_zip'); ?></label>
                        <input type="text" class="form-control" name="shipping_zip" id="shipping_zip" value="<?php echo $client->shipping_zip; ?>">
                    </div>
                    <div class="form-group">
                        <label for="shipping_country"><?php echo _l('shipping_country'); ?></label>
                        <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" name="shipping_country" id="shipping_country" class="form-control">
                            <option value=""></option>
                            <?php foreach($countries as $country){ ?>
                            <option value="<?php echo $country['country_id']; ?>"<?php if($client->shipping_country == $country['country_id']){echo ' selected';} ?>><?php echo $country['short_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <?php } ?>
                <?php if($contact->is_primary == 1){ ?>
                <div class="row p15">
                    <div class="col-md-12 text-right mtop20">
                        <div class="form-group">
                            <button type="submit" class="btn btn-info"><?php echo _l('clients_edit_profile_update_btn'); ?></button>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
</div>
