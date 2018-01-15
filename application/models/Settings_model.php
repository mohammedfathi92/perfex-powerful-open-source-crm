<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Settings_model extends CRM_Model
{
    private $encrypted_fields = array('smtp_password');

    public function __construct()
    {
        parent::__construct();
        $payment_gateways = $this->payment_modes_model->get_online_payment_modes(true);
        foreach ($payment_gateways as $gateway) {
            $class_name = $gateway['id'] . '_gateway';
            $settings   = $this->$class_name->get_settings();
            foreach ($settings as $option) {
                if (isset($option['encrypted']) && $option['encrypted'] == true) {
                    array_push($this->encrypted_fields, $option['name']);
                }
            }
        }
    }

    /**
     * Update all settings
     * @param  array $data all settings
     * @return integer
     */
    public function update($data)
    {
        $original_encrypted_fields = array();
        foreach ($this->encrypted_fields as $ef) {
            $original_encrypted_fields[$ef] = get_option($ef);
        }
        $affectedRows = 0;
        $data         = do_action('before_settings_updated', $data);
        if (isset($data['tags'])) {
            foreach ($data['tags'] as $id=>$name) {
                $this->db->where('id', $id);
                $this->db->update('tbltags', array('name'=>$name));
                $affectedRows += $this->db->affected_rows();
            }
        } else {
            if (!isset($data['settings']['default_tax']) && isset($data['finance_settings'])) {
                $data['settings']['default_tax'] = array();
            }
            $all_settings_looped = array();
            foreach ($data['settings'] as $name => $val) {

                // Do not trim thousand separator option
                // There is an option of white space there and if will be trimmed wont work as configured
                if (is_string($val) && $name != 'thousand_separator') {
                    $val = trim($val);
                }

                array_push($all_settings_looped, $name);

                $hook_data['name']  = $name;
                $hook_data['value'] = $val;
                $hook_data          = do_action('before_single_setting_updated_in_loop', $hook_data);
                $name               = $hook_data['name'];
                $val                = $hook_data['value'];

                // Check if the option exists
                $this->db->where('name', $name);
                $exists = $this->db->count_all_results('tbloptions');
                if ($exists == 0) {
                    continue;
                }

                if ($name == 'default_contact_permissions') {
                    $val = serialize($val);
                } elseif ($name == 'visible_customer_profile_tabs') {

                    if($val == '' || (is_array($val) && count($val) == 1 && $val[0] == 'all')) {
                        $val = 'all';
                    } else {
                        $val = serialize($val);
                    }
                } elseif ($name == 'email_signature') {
                    $val = nl2br_save_html($val);
                } elseif ($name == 'default_tax') {
                    $val = array_filter($val, function ($value) {
                        return $value !== '';
                    });
                    $val = serialize($val);
                } elseif($name == 'company_info_format' || $name == 'customer_info_format' || $name == 'proposal_info_format'){

                    $val = strip_tags($val);
                    $val = nl2br($val);

                } elseif (in_array($name, $this->encrypted_fields)) {
                    // Check if not empty $val password
                    // Get original
                    // Decrypt original
                    // Compare with $val password
                    // If equal unset
                    // If not encrypt and save
                    if (!empty($val)) {
                        $or_decrypted = $this->encryption->decrypt($original_encrypted_fields[$name]);
                        if ($or_decrypted == $val) {
                            continue;
                        } else {
                            $val = $this->encryption->encrypt($val);
                        }
                    }
                }

                $this->db->where('name', $name);
                $this->db->update('tbloptions', array(
                    'value' => $val
                ));

                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }

            // Contact permission default none
            if (!in_array('default_contact_permissions', $all_settings_looped) && in_array('customer_settings', $all_settings_looped)) {
                $this->db->where('name', 'default_contact_permissions');
                $this->db->update('tbloptions', array(
                'value' => serialize(array())
            ));
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            } elseif (!in_array('visible_customer_profile_tabs', $all_settings_looped) && in_array('customer_settings', $all_settings_looped)) {
                $this->db->where('name', 'visible_customer_profile_tabs');
                $this->db->update('tbloptions', array(
                'value' => 'all'
            ));
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }

            if (isset($data['custom_fields'])) {
                if (handle_custom_fields_post(0, $data['custom_fields'])) {
                    $affectedRows++;
                }
            }
        }


        return $affectedRows;
    }

    public function add_new_company_pdf_field($data)
    {
        $field = 'custom_company_field_' . trim($data['field']);
        $field = preg_replace('/\s+/', '_', $field);
        if (add_option($field, $data['value'])) {
            return true;
        }

        return false;
    }
}
