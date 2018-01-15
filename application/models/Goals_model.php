<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Goals_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single goal
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblgoals')->row();
        }
        if ($exclude_notified == true) {
            $this->db->where('notified', 0);
        }

        return $this->db->get('tblgoals')->result_array();
    }

    public function get_staff_goals($staff_id, $exclude_notified = true)
    {
        $this->db->where('staff_id', $staff_id);
        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date','asc');
        $goals = $this->db->get('tblgoals')->result_array();

        foreach ($goals as $key=>$val) {
            $goals[$key]['achievement'] = $this->calculate_goal_achievement($val['id']);
            $goals[$key]['goal_type_name'] = format_goal_type($val['goal_type']);
        }

        return $goals;
    }

    /**
     * Add new goal
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data['notify_when_fail'] = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;

        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id'] = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['start_date'] = to_sql_date($data['start_date']);
        $data['end_date']   = to_sql_date($data['end_date']);
        $this->db->insert('tblgoals', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Goal Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update goal
     * @param  mixed $data All $_POST data
     * @param  mixed $id   goal id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['notify_when_fail'] = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;

        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id'] = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['start_date'] = to_sql_date($data['start_date']);
        $data['end_date']   = to_sql_date($data['end_date']);
        $this->db->where('id', $id);
        $this->db->update('tblgoals', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Goal Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete goal
     * @param  mixed $id goal id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblgoals');
        if ($this->db->affected_rows() > 0) {
            logActivity('Goal Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Notify staff members about goal result
     * @param  mixed $id          goal id
     * @param  string $notify_type is success or failed
     * @param  mixed $achievement total achievent (Option)
     * @return boolean
     */
    public function notify_staff_members($id, $notify_type, $achievement = '')
    {
        $goal = $this->get($id);
        if ($achievement == '') {
            $achievement = $this->calculate_goal_achievement($id);
        }
        if ($notify_type == 'success') {
            $goal_desc = 'not_goal_message_success';
        } else {
            $goal_desc = 'not_goal_message_failed';
        }

        if ($goal->staff_id == 0) {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', 1);
        } else {
            $this->db->where('active', 1)
            ->where('staffid', $goal->staff_id);
            $staff = $this->db->get('tblstaff')->result_array();
        }

        $notifiedUsers = array();
        foreach ($staff as $member) {
            if (is_staff_member($member['staffid'])) {
                $notified = add_notification(array(
                    'fromcompany' => 1,
                    'touserid' => $member['staffid'],
                    'description' => $goal_desc,
                    'additional_data' => serialize(array(
                        format_goal_type($goal->goal_type),
                        $goal->achievement,
                        $achievement['total'],
                        _d($goal->start_date),
                        _d($goal->end_date),
                    )),
                ));
                if ($notified) {
                    array_push($notifiedUsers, $member['staffid']);
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
        $this->db->where('id', $goal->id);
        $this->db->update('tblgoals', array(
            'notified' => 1,
        ));

        if (count($staff) > 0 && $this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Calculate goal achievement
     * @param  mixed $id goal id
     * @return array
     */
    public function calculate_goal_achievement($id)
    {
        $goal       = $this->get($id);
        $start_date = $goal->start_date;
        $end_date   = $goal->end_date;
        $type       = $goal->goal_type;
        $total      = 0;
        $percent    = 0;
        if ($type == 1) {
            $sql = "SELECT SUM(amount) as total FROM tblinvoicepaymentrecords";

            if ($goal->staff_id != 0) {
                $sql .= " JOIN tblinvoices ON tblinvoices.id = tblinvoicepaymentrecords.invoiceid";
            }

            $sql .= " WHERE tblinvoicepaymentrecords.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($goal->staff_id != 0) {
                $sql .= " AND (tblinvoices.addedfrom=".$goal->staff_id.' OR sale_agent='.$goal->staff_id.')';
            }
        } elseif ($type == 2) {
            $sql = "SELECT COUNT(tblleads.id) as total FROM tblleads WHERE DATE(date_converted) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND status = 1 AND tblleads.id IN (SELECT leadid FROM tblclients WHERE leadid=tblleads.id)";
            if ($goal->staff_id != 0) {
                $sql .= " AND CASE WHEN assigned=0 THEN addedfrom=".$goal->staff_id." ELSE assigned=".$goal->staff_id." END";
            }
        } elseif ($type == 3) {
            $sql = "SELECT COUNT(tblclients.userid) as total FROM tblclients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND leadid IS NULL";
            if ($goal->staff_id != 0) {
                $sql .= " AND addedfrom=".$goal->staff_id;
            }
        } elseif ($type == 4) {
            $sql = "SELECT COUNT(tblclients.userid) as total FROM tblclients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            if ($goal->staff_id != 0) {
                $sql .= " AND addedfrom=".$goal->staff_id;
            }
        } elseif ($type == 5 || $type == 7) {
            $column = 'dateadded';
            if ($type == 7) {
                $column = 'datestart';
            }
            $sql = "SELECT count(id) as total FROM tblcontracts WHERE " . $column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND contract_type = " . $goal->contract_type . " AND trash = 0";
            if ($goal->staff_id != 0) {
                $sql .= " AND addedfrom=".$goal->staff_id;
            }
        } elseif ($type == 6) {
            $sql = "SELECT count(id) as total FROM tblestimates WHERE DATE(invoiced_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND invoiceid IS NOT NULL AND invoiceid NOT IN (SELECT id FROM tblinvoices WHERE status=5)";
            if ($goal->staff_id != 0) {
                $sql .= " AND (addedfrom=".$goal->staff_id.' OR sale_agent='.$goal->staff_id.')';
            }
        } else {
            return;
        }
        $total = floatval($this->db->query($sql)->row()->total);
        if ($total >= floatval($goal->achievement)) {
            $percent = 100;
        } else {
            if ($total !== 0) {
                $percent = number_format(($total * 100) / $goal->achievement, 2);
            }
        }
        $progress_bar_percent = $percent / 100;

        return array(
            'total' => $total,
            'percent' => $percent,
            'progress_bar_percent' => $progress_bar_percent,
        );
    }
}
