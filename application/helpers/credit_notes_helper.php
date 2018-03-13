<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Sum total credits applied for invoice
 * @param  mixed $id invoice id
 * @return mixed
 */
function total_credits_applied_to_invoice($id)
{
    $total = sum_from_table('tblcredits', array('field'=>'amount', 'where'=>array('invoice_id'=>$id)));

    if ($total == 0) {
        return false;
    }

    return $total;
}

/**
 * Return credit note status color RGBA for pdf
 * @param  mixed $status_id current credit note status id
 * @return string
 */
function credit_note_status_color_pdf($status_id)
{
    $statusColor = '';

    if ($status_id == 1) {
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 2) {
        $statusColor = '132, 197, 41';
    } else {
        // Status VOID
        $statusColor = '119, 119, 119';
    }

    return $statusColor;
}

/**
 * Return array with invoices IDs statuses which can be applied credits
 * @return array
 */
function invoices_statuses_available_for_credits()
{
    return array(1, 3, 6, 4);
}

/**
 * Check if credits can be applied to invoice based on the invoice status
 * @param  mixed $status_id invoice status id
 * @return boolean
 */
function credits_can_be_applied_to_invoice($status_id)
{
    return in_array($status_id, invoices_statuses_available_for_credits());
}

/**
 * Check if is last credit note created
 * @param  mixed  $id credit note id
 * @return boolean
 */
function is_last_credit_note($id)
{
    $CI =& get_instance();
    $CI->db->select('id')->from('tblcreditnotes')->order_by('id', 'desc')->limit(1);
    $query           = $CI->db->get();
    $last_credit_note = $query->row();

    if ($last_credit_note && $last_credit_note->id == $id) {
        return true;
    }

    return false;
}

/**
 * Function that format credit note number based on the prefix option and the credit note number
 * @param  mixed $id credit note id
 * @return string
 */
function format_credit_note_number($id)
{
    $CI =& get_instance();
    $CI->db->select('date,number,prefix,number_format')
    ->from('tblcreditnotes')
    ->where('id', $id);
    $credit_note = $CI->db->get()->row();

    if (!$credit_note) {
        return '';
    }

    $prefix = $credit_note->prefix;
    $number = $credit_note->number;
    $format = $credit_note->number_format;
    $date = $credit_note->date;
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
    $hook_data['credit_note'] = $credit_note;
    $hook_data['formatted_number'] = $number;
    $hook_data = do_action('format_credit_note_number',$hook_data);
    $number = $hook_data['formatted_number'];

    return $number;
}

/**
 * Format credit note status
 * @param  mixed  $status credit note current status
 * @param  boolean $text   to return text or with applied styles
 * @return string
 */
function format_credit_note_status($status, $text = false)
{
    $CI = &get_instance();
    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $statuses = $CI->credit_notes_model->get_statuses();
    $statusArray = false;
    foreach ($statuses as $s) {
        if ($s['id'] == $status) {
            $statusArray = $s;
            break;
        }
    }

    if (!$statusArray) {
        return $status;
    }

    if ($text) {
        return $statusArray['name'];
    }

    $style = 'border: 1px solid '.$statusArray['color'].';color:'.$statusArray['color'].';';
    $class = 'label s-status';

    return '<span class="'.$class.'" style="'.$style.'">' . $statusArray['name'] . '</span>';
}

/**
 * Function that return credit note item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_credit_note_item_taxes($itemid)
{
    $CI =& get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'credit_note');
    $taxes = $CI->db->get('tblitemstax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}
