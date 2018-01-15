<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * For more readable code created this function to render only yes or not values for settings
 * @param  string $option_value option from database to compare
 * @param  string $label        input label
 * @param  string $tooltip      tooltip
 */
function render_yes_no_option($option_value, $label, $tooltip = '', $replace_yes_text = '', $replace_no_text = '', $replace_1 = '', $replace_0 = '')
{
    ob_start(); ?>
    <div class="form-group">
        <label for="<?php echo $option_value; ?>" class="control-label clearfix">
            <?php echo($tooltip != '' ? '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="'. _l($tooltip, '', false) .'"></i> ': '') . _l($label, '', false); ?>
        </label>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_1_<?php echo $label; ?>" name="settings[<?php echo $option_value; ?>]" value="<?php echo $replace_1 == '' ? 1 : $replace_1; ?>" <?php if (get_option($option_value) == ($replace_1 == '' ? '1' : $replace_1)) {
        echo 'checked';
    } ?>>
            <label for="y_opt_1_<?php echo $label; ?>">
                <?php echo $replace_yes_text == '' ? _l('settings_yes') : $replace_yes_text; ?>
            </label>
        </div>
        <div class="radio radio-primary radio-inline">
                <input type="radio" id="y_opt_2_<?php echo $label; ?>" name="settings[<?php echo $option_value; ?>]" value="<?php echo $replace_0 == '' ? 0 : $replace_0; ?>" <?php if (get_option($option_value) == ($replace_0 == '' ? '0' : $replace_0)) {
        echo 'checked';
    } ?>>
                <label for="y_opt_2_<?php echo $label; ?>">
                    <?php echo $replace_no_text == '' ? _l('settings_no') : $replace_no_text; ?>
                </label>
        </div>
    </div>
    <?php
    $settings = ob_get_contents();
    ob_end_clean();
    echo $settings;
}

/**
 * Function that renders input for admin area based on passed arguments
 * @param  string $name             input name
 * @param  string $label            label name
 * @param  string $value            default value
 * @param  string $type             input type eq text,number
 * @param  array  $input_attrs      attributes on <input
 * @param  array  $form_group_attr  <div class="form-group"> html attributes
 * @param  string $form_group_class additional form group class
 * @param  string $input_class      additional class on input
 * @return string
 */
function render_input($name, $label = '', $value = '', $type = 'text', $input_attrs = array(), $form_group_attr = array(), $form_group_class = '', $input_class = '')
{
    $input            = '';
    $_form_group_attr = '';
    $_input_attrs     = '';
    foreach ($input_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_input_attrs .= $key . '=' . '"' . $val . '" ';
    }

    $_input_attrs = rtrim($_input_attrs);

    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '=' . '"' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (!empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    if (!empty($input_class)) {
        $input_class = ' ' . $input_class;
    }
    $input .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $input .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }
    $input .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" class="form-control' . $input_class . '" ' . $_input_attrs . ' value="' . set_value($name, $value) . '">';
    $input .= '</div>';

    return $input;
}
/**
 * Render color picker input
 * @param  string $name        input name
 * @param  string $label       field name
 * @param  string $value       default value
 * @param  array  $input_attrs <input sttributes
 * @return string
 */
function render_color_picker($name, $label = '', $value = '', $input_attrs = array())
{
    $_input_attrs = '';
    foreach ($input_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_input_attrs .= $key . '=' . '"' . $val . '"';
    }

    $picker = '';
    $picker .= '<div class="form-group" app-field-wrapper="'.$name.'">';
    $picker .= '<label for="' . $name . '" class="control-label">' . $label . '</label>';
    $picker .= '<div class="input-group mbot15 colorpicker-input">
    <input type="text" value="' . set_value($name, $value) . '" name="' . $name . '" id="' . $name . '" class="form-control" ' . $_input_attrs . ' />
    <span class="input-group-addon"><i></i></span>
</div>';
    $picker .= '</div>';

    return $picker;
}
/**
 * Render date picker input for admin area
 * @param  [type] $name             input name
 * @param  string $label            input label
 * @param  string $value            default value
 * @param  array  $input_attrs      input attributes
 * @param  array  $form_group_attr  <div class="form-group"> div wrapper html attributes
 * @param  string $form_group_class form group div wrapper additional class
 * @param  string $input_class      <input> additional class
 * @return string
 */
function render_date_input($name, $label = '', $value = '', $input_attrs = array(), $form_group_attr = array(), $form_group_class = '', $input_class = '')
{
    $input            = '';
    $_form_group_attr = '';
    $_input_attrs     = '';
    foreach ($input_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_input_attrs .= $key . '=' . '"' . $val . '" ';
    }

    $_input_attrs = rtrim($_input_attrs);

    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '=' . '"' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (!empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    if (!empty($input_class)) {
        $input_class = ' ' . $input_class;
    }
    $input .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $input .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }
    $input .= '<div class="input-group date">';
    $input .= '<input type="text" id="' . $name . '" name="' . $name . '" class="form-control datepicker' . $input_class . '" ' . $_input_attrs . ' value="' . set_value($name, $value) . '">';
    $input .= '<div class="input-group-addon">
    <i class="fa fa-calendar calendar-icon"></i>
</div>';
    $input .= '</div>';
    $input .= '</div>';

    return $input;
}
/**
 * Render date time picker input for admin area
 * @param  [type] $name             input name
 * @param  string $label            input label
 * @param  string $value            default value
 * @param  array  $input_attrs      input attributes
 * @param  array  $form_group_attr  <div class="form-group"> div wrapper html attributes
 * @param  string $form_group_class form group div wrapper additional class
 * @param  string $input_class      <input> additional class
 * @return string
 */
function render_datetime_input($name, $label = '', $value = '', $input_attrs = array(), $form_group_attr = array(), $form_group_class = '', $input_class = '')
{
    $html = render_date_input($name, $label, $value, $input_attrs, $form_group_attr, $form_group_class, $input_class);
    $html = str_replace('datepicker', 'datetimepicker', $html);

    return $html;
}
/**
 * Render textarea for admin area
 * @param  [type] $name             textarea name
 * @param  string $label            textarea label
 * @param  string $value            default value
 * @param  array  $textarea_attrs      textarea attributes
 * @param  array  $form_group_attr  <div class="form-group"> div wrapper html attributes
 * @param  string $form_group_class form group div wrapper additional class
 * @param  string $textarea_class      <textarea> additional class
 * @return string
 */
function render_textarea($name, $label = '', $value = '', $textarea_attrs = array(), $form_group_attr = array(), $form_group_class = '', $textarea_class = '')
{
    $textarea         = '';
    $_form_group_attr = '';
    $_textarea_attrs  = '';
    if (!isset($textarea_attrs['rows'])) {
        $textarea_attrs['rows'] = 4;
    }

    if (isset($textarea_attrs['class'])) {
        $textarea_class .= ' '. $textarea_attrs['class'];
        unset($textarea_attrs['class']);
    }

    foreach ($textarea_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_textarea_attrs .= $key . '=' . '"' . $val . '" ';
    }

    $_textarea_attrs = rtrim($_textarea_attrs);

    $form_group_attr['app-field-wrapper'] = $name;

    foreach ($form_group_attr as $key => $val) {
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '=' . '"' . $val . '" ';
    }

    $_form_group_attr = rtrim($_form_group_attr);

    if (!empty($textarea_class)) {
        $textarea_class = trim($textarea_class);
        $textarea_class = ' ' . $textarea_class;
    }
    if (!empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    $textarea .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $textarea .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }

    $v = clear_textarea_breaks($value);
    if (strpos($textarea_class, 'tinymce') !== false) {
        $v = $value;
    }
    $textarea .= '<textarea id="' . $name . '" name="' . $name . '" class="form-control' . $textarea_class . '" ' . $_textarea_attrs . '>' . set_value($name, $v) . '</textarea>';

    $textarea .= '</div>';

    return $textarea;
}
/**
 * Render <select> field optimized for admin area and bootstrap-select plugin
 * @param  string  $name             select name
 * @param  array  $options          option to include
 * @param  array   $option_attrs     additional options attributes to include, attributes accepted based on the bootstrap-selectp lugin
 * @param  string  $label            select label
 * @param  string  $selected         default selected value
 * @param  array   $select_attrs     <select> additional attributes
 * @param  array   $form_group_attr  <div class="form-group"> div wrapper html attributes
 * @param  string  $form_group_class <div class="form-group"> additional class
 * @param  string  $select_class     additional <select> class
 * @param  boolean $include_blank    do you want to include the first <option> to be empty
 * @return string
 */
function render_select($name, $options, $option_attrs = array(), $label = '', $selected = '', $select_attrs = array(), $form_group_attr = array(), $form_group_class = '', $select_class = '', $include_blank = true)
{
    $callback_translate = '';
    if (isset($options['callback_translate'])) {
        $callback_translate = $options['callback_translate'];
        unset($options['callback_translate']);
    }
    $select           = '';
    $_form_group_attr = '';
    $_select_attrs    = '';
    if (!isset($select_attrs['data-width'])) {
        $select_attrs['data-width'] = '100%';
    }
    if (!isset($select_attrs['data-none-selected-text'])) {
        $select_attrs['data-none-selected-text'] = _l('dropdown_non_selected_tex');
    }
    foreach ($select_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_select_attrs .= $key . '=' . '"' . $val . '" ';
    }

    $_select_attrs = rtrim($_select_attrs);

    $form_group_attr['app-field-wrapper'] = $name;
    foreach ($form_group_attr as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = _l($val);
        }
        $_form_group_attr .= $key . '=' . '"' . $val . '" ';
    }
    $_form_group_attr = rtrim($_form_group_attr);
    if (!empty($select_class)) {
        $select_class = ' ' . $select_class;
    }
    if (!empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    $select .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $select .= '<label for="' . $name . '" class="control-label">' . _l($label, '', false) . '</label>';
    }
    $select .= '<select id="' . $name . '" name="' . $name . '" class="selectpicker' . $select_class . '" ' . $_select_attrs . ' data-live-search="true">';
    if ($include_blank == true) {
        $select .= '<option value=""></option>';
    }
    foreach ($options as $option) {
        $val       = '';
        $_selected = '';
        $key       = '';
        if (isset($option[$option_attrs[0]]) && !empty($option[$option_attrs[0]])) {
            $key = $option[$option_attrs[0]];
        }
        if (!is_array($option_attrs[1])) {
            $val = $option[$option_attrs[1]];
        } else {
            foreach ($option_attrs[1] as $_val) {
                $val .= $option[$_val] . ' ';
            }
        }
        $val = trim($val);

        if ($callback_translate != '') {
            if (function_exists($callback_translate) && is_callable($callback_translate)) {
                $val = call_user_func($callback_translate, $key);
            }
        }

        $data_sub_text = '';
        if (!is_array($selected)) {
            if ($selected != '') {
                if ($selected == $key) {
                    $_selected = ' selected';
                }
            }
        } else {
            foreach ($selected as $id) {
                if ($key == $id) {
                    $_selected = ' selected';
                }
            }
        }
        if (isset($option_attrs[2])) {
            if (strpos($option_attrs[2], ',') !== false) {
                $sub_text = '';
                $_temp    = explode(',', $option_attrs[2]);
                foreach ($_temp as $t) {
                    if (isset($option[$t])) {
                        $sub_text .= $option[$t] . ' ';
                    }
                }
            } else {
                if (isset($option[$option_attrs[2]])) {
                    $sub_text = $option[$option_attrs[2]];
                } else {
                    $sub_text = $option_attrs[2];
                }
            }
            $data_sub_text = ' data-subtext=' . '"' . $sub_text . '"';
        }
        $data_content = '';
        if (isset($option['option_attributes'])) {
            foreach ($option['option_attributes'] as $_opt_attr_key => $_opt_attr_val) {
                $data_content .= $_opt_attr_key . '=' . '"' . $_opt_attr_val . '"';
            }
            if ($data_content != '') {
                $data_content = ' ' .$data_content;
            }
        }
        $select .= '<option value="' . $key . '"' . $_selected . $data_content . $data_sub_text . '>' . $val . '</option>';
    }
    $select .= '</select>';
    $select .= '</div>';

    return $select;
}

function render_select_with_input_group($name, $options, $option_attrs = array(), $label = '', $selected = '', $input_group_contents = '', $select_attrs = array(), $form_group_attr = array(), $form_group_class = '', $select_class = '', $include_blank = true)
{
    $select_class .= ' _select_input_group';
    $select = render_select($name, $options, $option_attrs, $label, $selected, $select_attrs, $form_group_attr, $form_group_class, $select_class, $include_blank);
    $select = str_replace('form-group', 'input-group input-group-select select-'.$name, $select);
    $select = str_replace('</select>', '</select><div class="input-group-addon">'.$input_group_contents.'</div>', $select);

    $re = '/<label.*<\/label>/i';
    preg_match($re, $select, $label);

    if (count($label) > 0) {
        $select = preg_replace($re, '', $select);
        $select = '<div class="form-group form-group-select-input-'.$name.'">' . $label[0] . $select . '</div>';
    }

    return $select;
}


if (!function_exists('render_form_builder_field')) {
    /**
     * Used for customer forms eq. leads form, builded from the form builder plugin
     * @param  object $field field from database
     * @return mixed
     */
    function render_form_builder_field($field)
    {
        $type = $field->type;
        $classNameCol = 'col-md-12';
        if (isset($field->className)) {
            if (strpos($field->className, 'form-col') !== false) {
                $classNames = explode(' ', $field->className);
                if (is_array($classNames)) {
                    $classNameColArray = array_filter($classNames, function ($class) {
                        return _startsWith($class, 'form-col');
                    });

                    $classNameCol = implode(' ', $classNameColArray);
                    $classNameCol = trim($classNameCol);

                    $classNameCol = str_replace('form-col-xs', 'col-xs', $classNameCol);
                    $classNameCol = str_replace('form-col-sm', 'col-sm', $classNameCol);
                    $classNameCol = str_replace('form-col-md', 'col-md', $classNameCol);
                    $classNameCol = str_replace('form-col-lg', 'col-lg', $classNameCol);

                    // Default col-md-X
                    $classNameCol = str_replace('form-col', 'col-md', $classNameCol);
                }
            }
        }

        echo '<div class="'.$classNameCol.'">';
        if ($type == 'header' || $type == 'paragraph') {
            echo '<'.$field->subtype.' class="'.(isset($field->className) ? $field->className : '').'">'.nl2br($field->label).'</'.$field->subtype.'>';
        } else {
            echo '<div class="form-group" data-type="'.$type.'" data-name="'.$field->name.'" data-required="'.(isset($field->required) ? true : 'false').'">';
            echo '<label class="control-label" for="'.$field->name.'">'.(isset($field->required) ? ' <span class="text-danger">* </span> ': '').$field->label.''.(isset($field->description) ? ' <i class="fa fa-question-circle" data-toggle="tooltip" data-title="'.$field->description.'" data-placement="'.(is_rtl(true) ? 'left' : 'right').'"></i>' : '').'</label>';
            if ($type == 'file' || $type == 'text' || $type == 'email') {
                echo '<input'.(isset($field->required) ? ' required="true"': '') . (isset($field->placeholder) ? ' placeholder="'.$field->placeholder.'"' : '') . ' type="'.$type.'" name="'.$field->name.'" id="'.$field->name.'" class="'.(isset($field->className) ? $field->className : '').'" value="'.(isset($field->value) ? $field->value : '').'"'.($field->type == 'file' ? ' accept="'.get_form_accepted_mimes().'" filesize="'.file_upload_max_size().'"' : '').'>';
            } elseif ($type == 'textarea') {
                echo '<textarea'.(isset($field->required) ? ' required="true"': '').' id="'.$field->name.'" name="'.$field->name.'" rows="'.(isset($field->rows) ? $field->rows : '4').'" class="'.(isset($field->className) ? $field->className : '').'" placeholder="'.(isset($field->placeholder) ? $field->placeholder : '').'">'.(isset($field->value) ? $field->value : '').'</textarea>';
            } elseif ($type == 'date') {
                echo '<input'.(isset($field->required) ? ' required="true"': '').' placeholder="'.(isset($field->placeholder) ? $field->placeholder : '').'" type="text" class="'.(isset($field->className) ? $field->className : '').' datepicker" name="'.$field->name.'" id="'.$field->name.'" value="'.(isset($field->value) ? _d($field->value) : '').'">';
            } elseif ($type == 'datetime') {
                echo '<input'.(isset($field->required) ? ' required="true"': '').' placeholder="'.(isset($field->placeholder) ? $field->placeholder : '').'" type="text" class="'.(isset($field->className) ? $field->className : '').' datetimepicker" name="'.$field->name.'" id="'.$field->name.'" value="'.(isset($field->value) ? _dt($field->value) : '').'">';
            } elseif ($type == 'color') {
                echo '<div class="input-group colorpicker-input">
         <input'.(isset($field->required) ? ' required="true"': '').' placeholder="'.(isset($field->placeholder) ? $field->placeholder : '').'" type="text"' . (isset($field->value) ? ' value="'.$field->value.'"' : '') .  ' name="' . $field->name . '" id="' . $field->name . '" class="'.(isset($field->className) ? $field->className : '').'" />
             <span class="input-group-addon"><i></i></span>
         </div>';
            } elseif ($type == 'select') {
                echo '<select'.(isset($field->required) ? ' required="true"': '').''.(isset($field->multiple) ? ' multiple="true"' : '').' class="'.(isset($field->className) ? $field->className : '').'" name="'.$field->name.(isset($field->multiple) ? '[]' : '').'" id="'.$field->name.'"'.(isset($field->values) && count($field->values)> 10 ? 'data-live-search="true"': '').'data-none-selected-text="'.(isset($field->placeholder) ? $field->placeholder : '').'">';
                $values = array();
                if (isset($field->values) && count($field->values) > 0) {
                    foreach ($field->values as $option) {
                        echo '<option value="'.$option->value.'" '.(isset($option->selected) ? ' selected' : '').'>'.$option->label.'</option>';
                    }
                }
                echo '</select>';
            } elseif ($type == 'checkbox-group') {
                $values = array();
                if (isset($field->values) && count($field->values) > 0) {
                    $i = 0;
                    echo '<div class="chk">';
                    foreach ($field->values as $checkbox) {
                        echo '<div class="checkbox'.((isset($field->inline) && $field->inline == 'true') || (isset($field->className) && strpos($field->className, 'form-inline-checkbox') !== false) ? ' checkbox-inline' : '').'">';
                        echo '<input'.(isset($field->required) ? ' required="true"': '').' class="'.(isset($field->className) ? $field->className : '').'" type="checkbox" id="chk_'.$field->name.'_'.$i.'" value="'.$checkbox->value.'" name="'.$field->name.'[]"'.(isset($checkbox->selected) ? ' checked' : '').'>';
                        echo '<label for="chk_'.$field->name.'_'.$i.'">';
                        echo $checkbox->label;
                        echo '</label>';
                        echo '</div>';
                        $i++;
                    }
                    echo '</div>';
                }
            }
            echo '</div>';
        }
        echo '</div>';
    }
}

/**
 * The function will do the necessar checking to use custom fields in the form builder eq leads forms
 * @param  array $custom_fields custom fields to check
 * @return array
 */
function format_external_form_custom_fields($custom_fields)
{
    $cfields = array();
    foreach ($custom_fields as $f) {
        $_field_object = new stdClass();
        $type          = $f['type'];
        $className     = 'form-control';

        if ($f['type'] == 'colorpicker') {
            $type = 'color';
        } elseif ($f['type'] == 'date_picker') {
            $type = 'date';
        } elseif ($f['type'] == 'date_picker_time') {
            $type = 'datetime';
        } elseif ($f['type'] == 'checkbox') {
            $type      = 'checkbox-group';
            $className = '';
            if ($f['display_inline'] == 1) {
                $className .= 'form-inline-checkbox';
            }
        } elseif ($f['type'] == 'input') {
            $type = 'text';
        } elseif ($f['type'] == 'multiselect') {
            $type = 'select';
        }

        $field_array = array(
                'type' => $type,
                'label' => $f['name'],
                'className' => $className,
                'name' => 'form-cf-' . $f['id'],
            );

        if ($f['type'] == 'multiselect') {
            $field_array['multiple'] = true;
        }

        if ($f['required'] == 1) {
            $field_array['required'] = true;
        }

        if ($f['type'] == 'checkbox' || $f['type'] == 'select' || $f['type'] == 'multiselect') {
            $field_array['values'] = array();
            $options               = explode(',', $f['options']);
            // leave first field blank
            if ($f['type'] == 'select') {
                array_push($field_array['values'], array(
                        'label' => '',
                        'value' => '',
                    ));
            }
            foreach ($options as $option) {
                $option = trim($option);
                if ($option != '') {
                    array_push($field_array['values'], array(
                            'label' => $option,
                            'value' => $option,
                        ));
                }
            }
        }

        $_field_object->label    = $f['name'];
        $_field_object->name     = 'form-cf-' . $f['id'];
        $_field_object->fields   = array();
        $_field_object->fields[] = $field_array;
        $cfields[]               = $_field_object;
    }

    return $cfields;
}
