<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Payment_modes_model extends CRM_Model
{
    private $online_payment_modes = array();

    public function __construct()
    {
        $online_payment_modes       = array();
        $online_payment_modes       = do_action('before_add_online_payment_modes', $online_payment_modes);
        $this->online_payment_modes = $online_payment_modes;
        parent::__construct();
    }

    /**
     * Get payment mode
     * @param  integer $id payment mode id
     * @return mixed    if id passed return object else array
     */
    public function get($id = '', $where = array(), $all = false, $force = false)
    {
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblinvoicepaymentsmodes')->row();
        } elseif (!empty($id)) {
            foreach ($this->online_payment_modes as $online_mode) {
                if ($online_mode['id'] == $id) {
                    if ($online_mode['active'] == 0 && $force == false) {
                        continue;
                    }
                    $mode                      = new stdCLass();
                    $mode->id                  = $id;
                    $mode->name                = $online_mode['name'];
                    $mode->description         = $online_mode['description'];
                    $mode->selected_by_default = $online_mode['selected_by_default'];
                    $mode->show_on_pdf         = 0;

                    return $mode;
                }
            }

            return false;
        }
        if ($all !== true) {
            $this->db->where('active', 1);
        }
        $modes = $this->db->get('tblinvoicepaymentsmodes')->result_array();
        $modes = array_merge($modes, $this->get_online_payment_modes($all));

        return $modes;
    }

    /**
     * Add new payment mode
     * @param array $data payment mode $_POST data
     */
    public function add($data)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }
        if (!isset($data['active'])) {
            $data['active'] = 0;
        } else {
            $data['active'] = 1;
        }

        if (!isset($data['invoices_only'])) {
            $data['invoices_only'] = 0;
        } else {
            $data['invoices_only'] = 1;
        }
        if (!isset($data['expenses_only'])) {
            $data['expenses_only'] = 0;
        } else {
            $data['expenses_only'] = 1;
        }

        if (!isset($data['show_on_pdf'])) {
            $data['show_on_pdf'] = 0;
        } else {
            $data['show_on_pdf'] = 1;
        }

        if (!isset($data['selected_by_default'])) {
            $data['selected_by_default'] = 0;
        } else {
            $data['selected_by_default'] = 1;
        }

        $this->db->insert('tblinvoicepaymentsmodes', array(
            'name' => $data['name'],
            'description' => nl2br_save_html($data['description']),
            'active' => $data['active'],
            'expenses_only' => $data['expenses_only'],
            'invoices_only' => $data['invoices_only'],
            'show_on_pdf' => $data['show_on_pdf'],
            'selected_by_default' => $data['selected_by_default']
        ));
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Payment Mode Added [ID: ' . $insert_id . ', Name:' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Update payment mode
     * @param  array $data payment mode $_POST data
     * @return boolean
     */
    public function edit($data)
    {
        $id = $data['paymentmodeid'];
        unset($data['paymentmodeid']);
        if (!isset($data['active'])) {
            $data['active'] = 0;
        } else {
            $data['active'] = 1;
        }


        if (!isset($data['show_on_pdf'])) {
            $data['show_on_pdf'] = 0;
        } else {
            $data['show_on_pdf'] = 1;
        }


        if (!isset($data['selected_by_default'])) {
            $data['selected_by_default'] = 0;
        } else {
            $data['selected_by_default'] = 1;
        }


        if (!isset($data['invoices_only'])) {
            $data['invoices_only'] = 0;
        } else {
            $data['invoices_only'] = 1;
        }
        if (!isset($data['expenses_only'])) {
            $data['expenses_only'] = 0;
        } else {
            $data['expenses_only'] = 1;
        }

        $this->db->where('id', $id);
        $this->db->update('tblinvoicepaymentsmodes', array(
            'name' => $data['name'],
            'description' => nl2br_save_html($data['description']),
            'active' => $data['active'],
            'expenses_only' => $data['expenses_only'],
            'invoices_only' => $data['invoices_only'],
            'show_on_pdf' => $data['show_on_pdf'],
            'selected_by_default' => $data['selected_by_default']
        ));

        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Updated [ID: ' . $id . ', Name:' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete payment mode from database
     * @param  mixed $id payment mode id
     * @return mixed / if referenced array else boolean
     */
    public function delete($id)
    {
        // Check if the payment mode is using in the invoiec payment records table.
        if (is_reference_in_table('paymentmode', 'tblinvoicepaymentrecords', $id) || is_reference_in_table('paymentmode', 'tblexpenses', $id)) {
            return array(
                'referenced' => true
            );
        }
        $this->db->where('id', $id);
        $this->db->delete('tblinvoicepaymentsmodes');
        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get all online payment modes
     * @since   1.0.1
     * @return array payment modes
     */
    public function get_online_payment_modes($all = false)
    {
        $modes = array();
        foreach ($this->online_payment_modes as $mode) {
            if ($all !== true) {
                if ($mode['active'] == 0) {
                    continue;
                }
            }
            $modes[] = $mode;
        }

        return $modes;
    }

    /**
     * @since  Version 1.0.1
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update payment mode status Active/Inactive
     */
    public function change_payment_mode_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblinvoicepaymentsmodes', array(
            'active' => $status
        ));
        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Status Changed [ModeID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @since  Version 1.0.1
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update payment mode show to client Active/Inactive
     */
    public function change_payment_mode_show_to_client_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblinvoicepaymentsmodes', array(
            'showtoclient' => $status
        ));
        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Show to Client Changed [ModeID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }
}
