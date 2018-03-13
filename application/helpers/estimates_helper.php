<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check estimate restrictions - hash, clientid
 * @param  mixed $id   estimate id
 * @param  string $hash estimate hash
 */
function check_estimate_restrictions($id, $hash)
{
    $CI =& get_instance();
    $CI->load->model('estimates_model');
    if (!$hash || !$id) {
        show_404();
    }
    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_estimate_only_logged_in') == 1) {
            redirect_after_login_to_current_url();
            redirect(site_url('clients/login'));
        }
    }
    $estimate = $CI->estimates_model->get($id);
    if (!$estimate || ($estimate->hash != $hash)) {
        show_404();
    }
    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_estimate_only_logged_in') == 1) {
            if ($estimate->clientid != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Check if estimate email template for expiry reminders is enabled
 * @return boolean
 */
function is_estimates_email_expiry_reminder_enabled(){
    return total_rows('tblemailtemplates',array('slug'=>'estimate-expiry-reminder','active'=>1)) > 0;
}

/**
 * Check if there are sources for sending estimate expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_estimates_expiry_reminders_enabled(){
    return is_estimates_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_ESTIMATE_EXP_REMINDER);
}

/**
 * Return RGBa estimate status color for PDF documents
 * @param  mixed $status_id current estimate status
 * @return string
 */
function estimate_status_color_pdf($status_id)
{
    if ($status_id == 1) {
        $statusColor = '119, 119, 119';
    } elseif ($status_id == 2) {
        // Sent
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 3) {
        //Declines
        $statusColor = '252, 45, 66';
    } elseif ($status_id == 4) {
        //Accepted
        $statusColor = '0, 191, 54';
    } else {
        // Expired
        $statusColor = '255, 111, 0';
    }

    return $statusColor;
}

/**
 * Format estimate status
 * @param  integer  $status
 * @param  string  $classes additional classes
 * @param  boolean $label   To include in html label or not
 * @return mixed
 */
function format_estimate_status($status, $classes = '', $label = true)
{
    $id          = $status;
    $label_class = estimate_status_color_class($status);
    $status      = estimate_status_by_id($status);
    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status estimate-status-' . $id . ' estimate-status-' . $label_class . '">' . $status . '</span>';
    } else {
        return $status;
    }
}

/**
 * Return estimate status translated by passed status id
 * @param  mixed $id estimate status id
 * @return string
 */
function estimate_status_by_id($id)
{
    $status = '';
    if ($id == 1) {
        $status = _l('estimate_status_draft');
    } elseif ($id == 2) {
        $status = _l('estimate_status_sent');
    } elseif ($id == 3) {
        $status = _l('estimate_status_declined');
    } elseif ($id == 4) {
        $status = _l('estimate_status_accepted');
    } elseif ($id == 5) {
        // status 5
        $status = _l('estimate_status_expired');
    } else {
        if (!is_numeric($id)) {
            if ($id == 'not_sent') {
                $status = _l('not_sent_indicator');
            }
        }
    }

    $hook_data = do_action('estimate_status_label', array(
        'id' => $id,
        'label' => $status,
    ));
    $status    = $hook_data['label'];

    return $status;
}

/**
 * Return estimate status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function estimate_status_color_class($id, $replace_default_by_muted = false)
{
    $class = '';
    if ($id == 1) {
        $class = 'default';
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    } elseif ($id == 2) {
        $class = 'info';
    } elseif ($id == 3) {
        $class = 'danger';
    } elseif ($id == 4) {
        $class = 'success';
    } elseif ($id == 5) {
        // status 5
        $class = 'warning';
    } else {
        if (!is_numeric($id)) {
            if ($id == 'not_sent') {
                $class = 'default';
                if ($replace_default_by_muted == true) {
                    $class = 'muted';
                }
            }
        }
    }

    $hook_data = do_action('estimate_status_color_class', array(
        'id' => $id,
        'class' => $class,
    ));
    $class     = $hook_data['class'];

    return $class;
}

/**
 * Check if the estimate id is last invoice
 * @param  mixed  $id estimateid
 * @return boolean
 */
function is_last_estimate($id)
{
    $CI =& get_instance();
    $CI->db->select('id')->from('tblestimates')->order_by('id', 'desc')->limit(1);
    $query            = $CI->db->get();
    $last_estimate_id = $query->row()->id;
    if ($last_estimate_id == $id) {
        return true;
    }

    return false;
}

/**
 * Format estimate number based on description
 * @param  mixed $id
 * @return string
 */
function format_estimate_number($id)
{
    $CI =& get_instance();
    $CI->db->select('date,number,prefix,number_format')->from('tblestimates')->where('id', $id);
    $estimate = $CI->db->get()->row();

    if (!$estimate) {
        return '';
    }

    $format = $estimate->number_format;
    $prefix = $estimate->prefix;
    $number = $estimate->number;
    $date = $estimate->date;
    $prefixPadding = get_option('number_padding_prefixes');


    if ($format == 1) {
        // Number based
        return $prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } elseif ($format == 2) {
        // Year based
        return $prefix . date('Y', strtotime($date)) . '/' . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } else if($format == 3) {
        // Number-yy based
        return $prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT)  . '-' . date('y', strtotime($date));
    } else if($format == 4) {
        // Number-mm-yyyy based
        return $prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT)  . '/' . date('m', strtotime($date)) . '/' . date('Y', strtotime($date));
    }

    $hook_data['id'] = $id;
    $hook_data['estimate'] = $estimate;
    $hook_data['formatted_number'] = $number;
    $hook_data = do_action('format_estimate_number',$hook_data);
    $number = $hook_data['formatted_number'];

    return $number;
}


/**
 * Function that return estimate item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_estimate_item_taxes($itemid)
{
    $CI =& get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'estimate');
    $taxes = $CI->db->get('tblitemstax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}

/**
 * Calculate estimates percent by status
 * @param  mixed $status          estimate status
 * @param  mixed $total_estimates in case the total is calculated in other place
 * @return array
 */
function get_estimates_percent_by_status($status, $total_estimates = '')
{
    $has_permission_view = has_permission('estimates', '', 'view');

    if (!is_numeric($total_estimates)) {
        $where_total = array();
        if (!$has_permission_view) {
            $where_total['addedfrom'] = get_staff_user_id();
        }
        $total_estimates = total_rows('tblestimates', $where_total);
    }

    $data            = array();
    $total_by_status = 0;

    if (!is_numeric($status)) {
        if ($status == 'not_sent') {
            $total_by_status = total_rows('tblestimates', 'sent=0 AND status NOT IN(2,3,4)' . (!$has_permission_view ? ' AND addedfrom=' . get_staff_user_id() : ''));
        }
    } else {
        $where = array(
            'status' => $status,
        );
        if (!$has_permission_view) {
            $where = array_merge($where, array(
                'addedfrom' => get_staff_user_id(),
            ));
        }
        $total_by_status = total_rows('tblestimates', $where);
    }

    $percent                 = ($total_estimates > 0 ? number_format(($total_by_status * 100) / $total_estimates, 2) : 0);
    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_estimates;

    return $data;
}
