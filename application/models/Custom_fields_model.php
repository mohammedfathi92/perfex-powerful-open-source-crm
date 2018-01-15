<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Custom_fields_model extends CRM_Model
{
    private $pdf_fields = array('estimate', 'invoice', 'credit_note');

    private $client_portal_fields = array('customers', 'estimate', 'invoice', 'proposal', 'contracts', 'tasks', 'projects', 'contacts', 'tickets', 'company', 'credit_note');

    private $client_editable_fields = array('customers', 'contacts', 'tasks');

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single custom field
     */
    public function get($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcustomfields')->row();
        }

        return $this->db->get('tblcustomfields')->result_array();
    }

    /**
     * Add new custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function add($data)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['show_on_pdf'])) {
            if (in_array($data['fieldto'], $this->pdf_fields)) {
                $data['show_on_pdf'] = 1;
            } else {
                $data['show_on_pdf'] = 0;
            }
        } else {
            $data['show_on_pdf'] = 0;
        }

        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        if (isset($data['disalow_client_to_edit'])) {
            $data['disalow_client_to_edit'] = 1;
        } else {
            $data['disalow_client_to_edit'] = 0;
        }
        if (isset($data['show_on_table'])) {
            $data['show_on_table'] = 1;
        } else {
            $data['show_on_table'] = 0;
        }

        if (isset($data['only_admin'])) {
            $data['only_admin'] = 1;
        } else {
            $data['only_admin'] = 0;
        }
        if (isset($data['show_on_client_portal'])) {
            if (in_array($data['fieldto'], $this->client_portal_fields)) {
                $data['show_on_client_portal'] = 1;
            } else {
                $data['show_on_client_portal'] = 0;
            }
        } else {
            $data['show_on_client_portal'] = 0;
        }
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }

        $data['slug'] = slug_it($data['fieldto'] . '_' . $data['name'], array(
            'separator' => '_'
        ));
        $slugs_total = total_rows('tblcustomfields', array('slug'=>$data['slug']));

        if ($slugs_total > 0) {
            $data['slug'] .= '_'.($slugs_total + 1);
        }

        if ($data['fieldto'] == 'company') {
            $data['show_on_pdf']            = 1;
            $data['show_on_client_portal']  = 1;
            $data['show_on_table']          = 1;
            $data['only_admin']             = 0;
            $data['disalow_client_to_edit'] = 0;
        }

        $this->db->insert('tblcustomfields', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Custom Field Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update custom field
     * @param mixed $data All $_POST data
     * @return  boolean
     */
    public function update($data, $id)
    {
        $original_field = $this->get($id);

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        if (isset($data['disalow_client_to_edit'])) {
            $data['disalow_client_to_edit'] = 1;
        } else {
            $data['disalow_client_to_edit'] = 0;
        }

        if (isset($data['only_admin'])) {
            $data['only_admin'] = 1;
        } else {
            $data['only_admin'] = 0;
        }

        if (isset($data['required'])) {
            $data['required'] = 1;
        } else {
            $data['required'] = 0;
        }
        if (isset($data['show_on_pdf'])) {
            if (in_array($data['fieldto'], $this->pdf_fields)) {
                $data['show_on_pdf'] = 1;
            } else {
                $data['show_on_pdf'] = 0;
            }
        } else {
            $data['show_on_pdf'] = 0;
        }
        if ($data['field_order'] == '') {
            $data['field_order'] = 0;
        }
        if (isset($data['show_on_client_portal'])) {
            if (in_array($data['fieldto'], $this->client_portal_fields)) {
                $data['show_on_client_portal'] = 1;
            } else {
                $data['show_on_client_portal'] = 0;
            }
        } else {
            $data['show_on_client_portal'] = 0;
        }
        if (isset($data['show_on_table'])) {
            $data['show_on_table'] = 1;
        } else {
            $data['show_on_table'] = 0;
        }

        if (!isset($data['display_inline'])) {
            $data['display_inline'] = 0;
        }
        if (!isset($data['show_on_ticket_form'])) {
            $data['show_on_ticket_form'] = 0;
        }

        if ($data['fieldto'] == 'company') {
            $data['show_on_pdf']            = 1;
            $data['show_on_client_portal']  = 1;
            $data['show_on_table']          = 1;
            $data['only_admin']             = 0;
            $data['disalow_client_to_edit'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update('tblcustomfields', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Custom Field Updated [' . $data['name'] . ']');

            if ($data['type'] == 'checkbox' || $data['type'] == 'select' || $data['type'] == 'multiselect') {
                if (trim($data['options']) != trim($original_field->options)) {
                    $options_now = explode(',', $data['options']);
                    foreach ($options_now as $key => $val) {
                        $options_now[$key] = trim($val);
                    }
                    $options_before = explode(',', $original_field->options);
                    foreach ($options_before as $key => $val) {
                        $options_before[$key] = trim($val);
                    }
                    $removed_options_in_use = array();
                    foreach ($options_before as $option) {
                        if (!in_array($option, $options_now) && total_rows('tblcustomfieldsvalues', array(
                            'fieldid' => $id,
                            'value' => $option
                        ))) {
                            array_push($removed_options_in_use, $option);
                        }
                    }
                    if (count($removed_options_in_use) > 0) {
                        $this->db->where('id', $id);
                        $this->db->update('tblcustomfields', array(
                            'options' => implode(',', $options_now) . ',' . implode(',', $removed_options_in_use)
                        ));

                        return array(
                            'cant_change_option_custom_field' => true
                        );
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param  integer
     * @return boolean
     * Delete Custom fields
     * All values for this custom field will be deleted from database
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblcustomfields');
        if ($this->db->affected_rows() > 0) {
            // Delete the values
            $this->db->where('fieldid', $id);
            $this->db->delete('tblcustomfieldsvalues');
            logActivity('Custom Field Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Change custom field status  / active / inactive
     * @param  mixed $id     customfield id
     * @param  integer $status active or inactive
     */
    public function change_custom_field_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblcustomfields', array(
            'active' => $status
        ));
        logActivity('Custom Field Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }

    /**
     * Return field where Shown on PDF is allowed
     * @return array
     */
    public function get_pdf_allowed_fields()
    {
        return $this->pdf_fields;
    }

    /**
     * Return fields where Show on customer portal is allowed
     * @return array
     */
    public function get_client_portal_allowed_fields()
    {
        return $this->client_portal_fields;
    }

    /**
     * Return fields where are editable in customers area
     * @return array
     */
    public function get_client_editable_fields()
    {
        return $this->client_editable_fields;
    }
}
