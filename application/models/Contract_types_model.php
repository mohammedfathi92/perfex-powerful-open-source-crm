<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Contract_types_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Add new contract type
    * @param mixed $data All $_POST data
    */
    public function add($data)
    {
        $this->db->insert('tblcontracttypes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Contract Type Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Edit contract type
     * @param mixed $data All $_POST data
     * @param mixed $id Contract type id
     */
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblcontracttypes', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Contract Type Updated [' . $data['name'] . ', ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID (optional)
     * @return mixed
     * Get contract type object based on passed id if not passed id return array of all types
     */
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcontracttypes')->row();
        }

        $types = $this->object_cache->get('contract-types');

        if(!$types && !is_array($types)){
            $types = $this->db->get('tblcontracttypes')->result_array();
            $this->object_cache->add('contract-types',$types);
        }

        return $types;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete contract type from database, if used return array with key referenced
     */
    public function delete($id)
    {
        if (is_reference_in_table('contract_type', 'tblcontracts', $id)) {
            return array(
                'referenced' => true,
            );
        }
        $this->db->where('id', $id);
        $this->db->delete('tblcontracttypes');
        if ($this->db->affected_rows() > 0) {
            logActivity('Contract Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get contract types data for chart
     * @return array
     */
    public function get_chart_data()
    {
        $labels = array();
        $totals = array();
        $types  = $this->get();
        foreach ($types as $type) {
            $total_rows_where = array(
                'contract_type' => $type['id'],
                'trash' => 0,
            );
            if (is_client_logged_in()) {
                $total_rows_where['client']                = get_client_user_id();
                $total_rows_where['not_visible_to_client'] = 0;
            } else {
                if (!has_permission('contracts', '', 'view')) {
                    $total_rows_where['addedfrom'] = get_staff_user_id();
                }
            }
            $total_rows = total_rows('tblcontracts', $total_rows_where);
            if ($total_rows == 0 && is_client_logged_in()) {
                continue;
            }
            array_push($labels, $type['name']);
            array_push($totals, $total_rows);
        }
        $chart = array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => _l('contract_summary_by_type'),
                    'backgroundColor' => 'rgba(3,169,244,0.2)',
                    'borderColor' => "#03a9f4",
                    'borderWidth' => 1,
                    'data' => $totals,
                ),
            ),
        );

        return $chart;
    }

    /**
     * Get contract types values for chart
     * @return array
     */
    public function get_values_chart_data()
    {
        $labels = array();
        $totals = array();
        $types  = $this->get();
        foreach ($types as $type) {
            array_push($labels, $type['name']);

            $where = array(
                'where' => array(
                    'contract_type' => $type['id'],
                    'trash' => 0,
                ),
                'field' => 'contract_value',
            );

            if (!has_permission('contracts', '', 'view')) {
                $where['where']['addedfrom'] = get_staff_user_id();
            }

            $total = sum_from_table('tblcontracts', $where);
            if ($total == null) {
                $total = 0;
            }
            array_push($totals, $total);
        }
        $chart = array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => _l('contract_summary_by_type_value'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor' => "#84c529",
                    'tension' => false,
                    'borderWidth' => 1,
                    'data' => $totals,
                ),
            ),
        );

        return $chart;
    }
}
