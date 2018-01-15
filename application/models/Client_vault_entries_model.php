<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client_vault_entries_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get single vault entry
     * @param  mixed $id vault entry id
     * @return object
     */
    public function get($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblvault')->row();
    }

    /**
     * Get customer vault entries
     * @param  mixed $customer_id
     * @param  array  $where       additional wher
     * @return array
     */
    public function get_by_customer_id($customer_id, $where = array())
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->order_by('date_created', 'desc');

        $this->db->where($where);

        return $this->db->get('tblvault')->result_array();
    }

    /**
     * Create new vault entry
     * @param  array $data        $_POST data
     * @param  mixed $customer_id customer id
     * @return boolean
     */
    public function create($data, $customer_id)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['customer_id'] = $customer_id;
        $data['share_in_projects'] = isset($data['share_in_projects']) ? 1 : 0;
        $this->db->insert('tblvault', $data);

        logActivity('Vault Entry Created [Customer ID: '.$customer_id.']');
    }

    /**
     * Update vault entry
     * @param  mixed $id   vault entry id
     * @param  array $data $_POST data
     * @return boolean
     */
    public function update($id, $data)
    {
        $vault = $this->get($id);

        $last_updated_from = $data['last_updated_from'];
        unset($data['last_updated_from']);
        $data['share_in_projects'] = isset($data['share_in_projects']) ? 1 : 0;

        $this->db->where('id', $id);
        $this->db->update('tblvault', $data);

        if ($this->db->affected_rows() > 0) {
            $this->db->where('id', $id);
            $this->db->update('tblvault', array('last_updated'=>date('Y-m-d H:i:s'), 'last_updated_from'=>$last_updated_from));
            logActivity('Vault Entry Updated [Customer ID: '.$vault->customer_id.']');

            return true;
        }

        return false;
    }

    /**
     * Delete vault entry
     * @param  mixed $id entry id
     * @return boolean
     */
    public function delete($id)
    {
        $vault = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete('tblvault');

        if ($this->db->affected_rows() > 0) {
            logActivity('Vault Entry Deleted [Customer ID: '.$vault->customer_id.']');

            return true;
        }

        return false;
    }
}
