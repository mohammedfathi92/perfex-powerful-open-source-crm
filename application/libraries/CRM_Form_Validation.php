<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class CRM_Form_validation extends CI_Form_validation
{
    protected $CI;
    // Custom
    protected $cfk_hidden = array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Run the Validator
     *
     * This function does all the work.
     *
     * @param   string  $group
     * @return  bool
     */
    public function run($group = '')
    {
        // Custom
        $cf_found = false;
        if ($this->CI->input->post('custom_fields')) {
            foreach ($this->CI->input->post('custom_fields') as $_k => $_f) {
                foreach ($_f as $k=>$v) {
                    if (is_array($v)) {
                        if (!isset($this->cfk_hidden[$_k])) {
                            $this->cfk_hidden[$_k] = array();
                        }
                        $this->cfk_hidden[$_k][$k] = 0;
                        foreach ($v as $cf_key => $cf_value) {
                            if ($cf_value == 'cfk_hidden') {
                                $cf_found = true;
                                $this->cfk_hidden[$_k][$k]++;
                                unset($_POST['custom_fields'][$_k][$k][$cf_key]);
                                if (count($_POST['custom_fields'][$_k][$k]) == 0) {
                                    unset($_POST['custom_fields'][$_k][$k]);
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($cf_found == false) {
            $this->cfk_hidden = array();
        }

        $validation_array = empty($this->validation_data)
            ? $_POST
            : $this->validation_data;

        // Does the _field_data array containing the validation rules exist?
        // If not, we look to see if they were assigned via a config file
        if (count($this->_field_data) === 0) {
            // No validation rules?  We're done...
            if (count($this->_config_rules) === 0) {
                return false;
            }

            if (empty($group)) {
                // Is there a validation rule for the particular URI being accessed?
                $group = trim($this->CI->uri->ruri_string(), '/');
                isset($this->_config_rules[$group]) or $group = $this->CI->router->class.'/'.$this->CI->router->method;
            }

            $this->set_rules(isset($this->_config_rules[$group]) ? $this->_config_rules[$group] : $this->_config_rules);

            // Were we able to set the rules correctly?
            if (count($this->_field_data) === 0) {
                log_message('debug', 'Unable to find validation rules');

                return false;
            }
        }

        // Load the language file containing error messages
        $this->CI->lang->load('form_validation');

        // Cycle through the rules for each field and match the corresponding $validation_data item
        foreach ($this->_field_data as $field => &$row) {
            // Fetch the data from the validation_data array item and cache it in the _field_data array.
            // Depending on whether the field name is an array or a string will determine where we get it from.
            if ($row['is_array'] === true) {
                $this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
            } elseif (isset($validation_array[$field])) {
                $this->_field_data[$field]['postdata'] = $validation_array[$field];
            }
        }

        // Execute validation rules
        // Note: A second foreach (for now) is required in order to avoid false-positives
        //   for rules like 'matches', which correlate to other validation fields.
        foreach ($this->_field_data as $field => &$row) {
            // Don't try to validate if we have no rules set
            if (empty($row['rules'])) {
                continue;
            }

            $this->_execute($row, $row['rules'], $row['postdata']);
        }

        // Did we end up with any errors?
        $total_errors = count($this->_error_array);
        if ($total_errors > 0) {
            $this->_safe_form_data = true;
        }

        // Now we need to re-set the POST data with the new, processed data
        empty($this->validation_data) && $this->_reset_post_array();

        // Custom
        foreach ($this->cfk_hidden as $type => $total) {
            foreach ($total as $key =>$_total) {
                if (!isset($_POST['custom_fields'][$type][$key])) {
                    $_POST['custom_fields'][$type][$key] = array();
                }
                for ($i = 0; $i < $_total; $i++) {
                    array_push($_POST['custom_fields'][$type][$key], 'cfk_hidden');
                }
                $_POST['custom_fields'][$type][$key] =  array_values($_POST['custom_fields'][$type][$key]);
            }
        }

        return ($total_errors === 0);
    }

    /**
     * Valid Email
     *
     * @param   string
     * @return  bool
     */
  /*  public function valid_email($str)
    {
        if (function_exists('idn_to_ascii') && preg_match('#\A([^@]+)@(.+)\z#', $str, $matches))
        {
            $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : INTL_IDNA_VARIANT_2003;
            $str = $matches[1].'@'.idn_to_ascii($matches[2], 0, $variant);
        }
        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }*/

    /**
     * Custom method for error messages in array
     * @return mixed
     */
    public function errors_array()
    {
        if (count($this->_error_array) === 0) {
            return false;
        } else {
            return $this->_error_array;
        }
    }

}
