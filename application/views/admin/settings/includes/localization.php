<?php
$date_formats = get_available_date_formats();
?>
<div class="form-group">
    <label for="dateformat" class="control-label"><?php echo _l('settings_localization_date_format'); ?></label>
    <select name="settings[dateformat]" id="dateformat" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
        <?php foreach($date_formats as $key => $val){ ?>
        <option value="<?php echo $key; ?>" <?php if($key == get_option('dateformat')){echo 'selected';} ?>><?php echo $val; ?></option>
        <?php } ?>
    </select>
</div>
<div class="form-group">
    <label for="time_format" class="control-label"><?php echo _l('time_format'); ?></label>
    <select name="settings[time_format]" id="time_format" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
        <option value="24" <?php if('24' == get_option('time_format')){echo 'selected';} ?>><?php echo _l('time_format_24'); ?></option>
        <option value="12" <?php if('12' == get_option('time_format')){echo 'selected';} ?>><?php echo _l('time_format_12'); ?></option>
    </select>
</div>
<hr />
<div class="form-group">
    <label for="timezones" class="control-label"><?php echo _l('settings_localization_default_timezone'); ?></label>
    <select name="settings[default_timezone]" id="timezones" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
        <?php foreach(get_timezones_list() as $key => $timezones){ ?>
        <optgroup label="<?php echo $key; ?>">
            <?php foreach($timezones as $timezone){ ?>
            <option value="<?php echo $timezone; ?>" <?php if(get_option('default_timezone') == $timezone){echo 'selected';} ?>><?php echo $timezone; ?></option>
            <?php } ?>
        </optgroup>
        <?php } ?>
    </select>
</div>
<hr />
<div class="form-group">
    <label for="active_language" class="control-label"><?php echo _l('settings_localization_default_language'); ?></label>
    <select name="settings[active_language]" data-live-search="true" id="active_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
        <?php foreach($this->app->get_available_languages() as $language){
            $subtext = '';
            $_data['language'] = $language;
            $_data['subtext'] = $subtext;
            $_data = do_action('settings_language_subtext',$_data);
            $language = $_data['language'];
            $subtext = $_data['subtext'];
            ?>
            <option value="<?php echo $language; ?>" data-subtext="<?php echo $subtext; ?>" <?php if($language == get_option('active_language')){echo ' selected'; } ?>><?php echo ucfirst($language); ?></option>
            <?php } ?>
        </select>
    </div>
    <hr />
    <?php render_yes_no_option('disable_language','disable_languages'); ?>
    <hr />
    <?php render_yes_no_option('output_client_pdfs_from_admin_area_in_client_language','settings_output_client_pdfs_from_admin_area_in_client_language','settings_output_client_pdfs_from_admin_area_in_client_language_help'); ?>
