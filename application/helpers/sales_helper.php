<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if company using invoice with different currencies
 * @param  string  $table table to check
 * @return boolean
 */
function is_using_multiple_currencies($table = 'tblinvoices')
{
    $CI =& get_instance();
    $CI->load->model('currencies_model');
    $currencies            = $CI->currencies_model->get();
    $total_currencies_used = 0;
    $other_then_base       = false;
    $base_found            = false;
    foreach ($currencies as $currency) {
        $CI->db->where('currency', $currency['id']);
        $total = $CI->db->count_all_results($table);
        if ($total > 0) {
            $total_currencies_used++;
            if ($currency['isdefault'] == 0) {
                $other_then_base = true;
            } else {
                $base_found = true;
            }
        }
    }

    if ($total_currencies_used > 1 && $base_found == true && $other_then_base == true) {
        return true;
    } elseif ($total_currencies_used == 1 && $base_found == false && $other_then_base == true) {
        return true;
    } elseif ($total_currencies_used == 0 || $total_currencies_used == 1) {
        return false;
    }

    return true;
}
/**
 * Forat number with 2 decimals
 * @param  mixed $total
 * @return string
 */
function _format_number($total, $force_checking_zero_decimals = false)
{
    if (!is_numeric($total)) {
        return $total;
    }
    $decimal_separator  = get_option('decimal_separator');
    $thousand_separator = get_option('thousand_separator');

    $d = get_decimal_places();
    if (get_option('remove_decimals_on_zero') == 1 || $force_checking_zero_decimals == true) {
        if (!is_decimal($total)) {
            $d = 0;
        }
    }

    return do_action('number_after_format', number_format($total, $d, $decimal_separator, $thousand_separator));
}
/**
 * Unformat formatted number. THIS FUNCTION IS NOT WELL TESTED
 * @param  mixed  $number
 * @param  boolean $force_number
 * @return mixed
 */
function number_unformat($number, $force_number = true)
{
    if ($force_number) {
        $number = preg_replace('/^[^\d]+/', '', $number);
    } elseif (preg_match('/^[^\d]+/', $number)) {
        return false;
    }
    $dec_point     = get_option('decimal_separator');
    $thousands_sep = get_option('thousand_separator');
    $type          = (strpos($number, $dec_point) === false) ? 'int' : 'float';
    $number        = str_replace(array(
        $dec_point,
        $thousands_sep,
    ), array(
        '.',
        '',
    ), $number);
    settype($number, $type);

    return $number;
}
/**
 * Format money with 2 decimal based on symbol
 * @param  mixed $total
 * @param  string $symbol Money symbol
 * @return string
 */
function format_money($total, $symbol = '')
{
    if (!is_numeric($total) && $total != 0) {
        return $total;
    }

    $decimal_separator  = get_option('decimal_separator');
    $thousand_separator = get_option('thousand_separator');
    $currency_placement = get_option('currency_placement');
    $d                  = get_decimal_places();

    if (get_option('remove_decimals_on_zero') == 1) {
        if (!is_decimal($total)) {
            $d = 0;
        }
    }

    $total = number_format($total, $d, $decimal_separator, $thousand_separator);
    $total = do_action('money_after_format_without_currency', $total);

    if ($currency_placement === 'after') {
        $_formatted = $total . '' . $symbol;
    } else {
        $_formatted = $symbol . '' . $total;
    }

    $_formatted = do_action('money_after_format_with_currency', $_formatted);

    return $_formatted;
}
/**
 * Check if passed number is decimal
 * @param  mixed  $val
 * @return boolean
 */
function is_decimal($val)
{
    return is_numeric($val) && floor($val) != $val;
}
/**
 * Function that will loop through taxes and will check if there is 1 tax or multiple
 * @param  array $taxes
 * @return boolean
 */
function mutiple_taxes_found_for_item($taxes)
{
    $names = array();
    foreach ($taxes as $t) {
        array_push($names, $t['taxname']);
    }
    $names = array_map("unserialize", array_unique(array_map("serialize", $names)));
    if (count($names) == 1) {
        return false;
    }

    return true;
}

/**
 * If there is more then 200 items in the script the search when creating eq invoice, estimate, proposal
 * will be ajax based
 * @return int
 */
function ajax_on_total_items()
{
    return do_action('ajax_on_total_items', 200);
}

/**
 * Helper function to get tax by passedid
 * @param  integer $id taxid
 * @return object
 */
function get_tax_by_id($id)
{
    $CI =& get_instance();
    $CI->db->where('id', $id);

    return $CI->db->get('tbltaxes')->row();
}
/**
 * Helper function to get tax by passed name
 * @param  string $name tax name
 * @return object
 */
function get_tax_by_name($name)
{
    $CI =& get_instance();
    $CI->db->where('name', $name);

    return $CI->db->get('tbltaxes')->row();
}
/**
 * This function replace <br /> only nothing exists in the line and first line other then <br />
 *  Replace first <br /> lines to prevent new spaces
 * @param  string $text The text to perform the action
 * @return string
 */
function _maybe_remove_first_and_last_br_tag($text)
{
    $text = preg_replace('/^<br ?\/?>/is', '', $text);
    // Replace last <br /> lines to prevent new spaces while there is new line
    while (preg_match('/<br ?\/?>$/', $text)) {
        $text = preg_replace('/<br ?\/?>$/is', '', $text);
    }

    return $text;
}

/**
 * Helper function to replace info format merge fields
 * Info format = Address formats for customers, proposals, company information
 * @param  string $mergeCode merge field to check
 * @param  mixed $val       value to replace
 * @param  string $txt       from format
 * @return string
 */
function _info_format_replace($mergeCode, $val, $txt)
{
    $tmpVal = strip_tags($val);

    if ($tmpVal != '') {
        $result = preg_replace('/({'.$mergeCode.'})/i', $val, $txt);
    } else {
        $re = '/\s{0,}{'.$mergeCode.'}(<br ?\/?>(\n))?/i';
        $result = preg_replace($re, '', $txt);
    }

    return $result;
}

/**
 * Helper function to replace info format custom field merge fields
 * Info format = Address formats for customers, proposals, company information
 * @param  mixed $id    custom field id
 * @param  string $label custom field label
 * @param  mixed $value custom field value
 * @param  string $txt   from format
 * @return string
 */
function _info_format_custom_field($id, $label, $value, $txt)
{
    if ($value != '') {
        $result = preg_replace('/({cf_'.$id.'})/i', $label .': ' . $value, $txt);
    } else {
        $re = '/\s{0,}{cf_'.$id.'}(<br ?\/?>(\n))?/i';
        $result = preg_replace($re, '', $txt);
    }

    return $result;
}

/**
 * Perform necessary checking for custom fields info format
 * @param  array $custom_fields custom fields
 * @param  string $txt           info format text
 * @return string
 */
function _info_format_custom_fields_check($custom_fields, $txt)
{
    if (count($custom_fields) == 0 || preg_match_all('/({cf_[0-9]{1,}})/i', $txt, $matches, PREG_SET_ORDER, 0) > 0) {
        $txt = preg_replace('/\s{0,}{cf_[0-9]{1,}}(<br ?\/?>(\n))?/i', '', $txt);
    }

    return $txt;
}

if (!function_exists('format_customer_info')) {
    /**
     * Format customer address info
     * @param  object  $data        customer object from database
     * @param  string  $for         where this format will be used? Eq statement invoice etc
     * @param  string  $type        billing/shipping
     * @param  boolean $companyLink company link to be added on customer company/name, this is used in admin area only
     * @return string
     */
    function format_customer_info($data, $for, $type, $companyLink = false)
    {
        $format = get_option('customer_info_format');
        $clientId = '';

        if ($for == 'statement') {
            $clientId = $data->userid;
        } elseif ($type == 'billing') {
            $clientId = $data->clientid;
        }

        $companyName = '';
        if ($for == 'statement') {
            $companyName = get_company_name($clientId);
        } elseif ($type == 'billing') {
            $companyName = $data->client->company;
        }

        if ($for == 'invoice' || $for == 'estimate' || $for == 'payment' || $for == 'credit_note') {
            if ($data->client->show_primary_contact == 1) {
                $primaryContactId = get_primary_contact_user_id($clientId);
                if ($primaryContactId) {
                    $companyName = get_contact_full_name($primaryContactId) .'<br />' . $companyName;
                }
            }
        }

        $street = '';
        if ($type == 'billing') {
            $street = $data->billing_street;
        } elseif ($type == 'shipping') {
            $street = $data->shipping_street;
        }

        $city = '';
        if ($type == 'billing') {
            $city = $data->billing_city;
        } elseif ($type == 'shipping') {
            $city = $data->shipping_city;
        }
        $state = '';
        if ($type == 'billing') {
            $state = $data->billing_state;
        } elseif ($type == 'shipping') {
            $state = $data->shipping_state;
        }
        $zipCode = '';
        if ($type == 'billing') {
            $zipCode = $data->billing_zip;
        } elseif ($type == 'shipping') {
            $zipCode = $data->shipping_zip;
        }

        $countryCode = '';
        $countryName = '';
        $country = null;
        if ($type == 'billing') {
            $country = get_country($data->billing_country);
        } elseif ($type == 'shipping') {
            $country = get_country($data->shipping_country);
        }

        if ($country) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }

        $phone = '';
        if ($for == 'statement') {
            $phone = $data->phonenumber;
        } elseif ($type == 'billing') {
            $phone = $data->client->phonenumber;
        }

        $vat = '';
        if ($for == 'statement') {
            $vat = $data->vat;
        } elseif ($type == 'billing') {
            $vat = $data->client->vat;
        }

        if ($companyLink) {
            $companyName = '<a href="'.admin_url('clients/client/'.$clientId).'" target="_blank"><b>'.$companyName.'</b></a>';
        } elseif ($companyName != '') {
            $companyName  = '<b>'.$companyName.'</b>';
        }

        $format = _info_format_replace('company_name', $companyName, $format);
        $format = _info_format_replace('customer_id', $clientId, $format);
        $format = _info_format_replace('street', $street, $format);
        $format = _info_format_replace('city', $city, $format);
        $format = _info_format_replace('state', $state, $format);
        $format = _info_format_replace('zip_code', $zipCode, $format);
        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);
        $format = _info_format_replace('phone', $phone, $format);
        $format = _info_format_replace('vat_number', $vat, $format);
        $format = _info_format_replace('vat_number_with_label', $vat == '' ? '' : _l('client_vat_number').': '. $vat, $format);

        $customFieldsCustomer = array();

        // On shipping address no custom fields are shown
        if ($type != 'shipping') {
            $whereCF = array();

            if (is_custom_fields_for_customers_portal()) {
                $whereCF['show_on_client_portal'] = 1;
            }

            $customFieldsCustomer = get_custom_fields('customers', $whereCF);
        }

        foreach ($customFieldsCustomer as $field) {
            $value = get_custom_field_value($clientId, $field['id'], 'customers');
            $format = _info_format_custom_field($field['id'], $field['name'], $value, $format);
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _info_format_custom_fields_check($customFieldsCustomer, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return do_action('customer_info_text', $format);
    }
}


if (!function_exists('format_proposal_info')) {
    /**
     * Format proposal info format
     * @param  object $proposal proposal from database
     * @param  string $for      where this info will be used? Admin area, HTML preview?
     * @return string
     */
    function format_proposal_info($proposal, $for = '')
    {
        $format = get_option('proposal_info_format');

        $countryCode = '';
        $countryName = '';
        $country = get_country($proposal->country);

        if ($country) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }

        $proposalTo = '<b>'.$proposal->proposal_to.'</b>';
        $phone = $proposal->phone;
        $email = $proposal->email;

        if ($for == 'admin') {
            $hrefAttrs = '';
            if ($proposal->rel_type == 'lead') {
                $hrefAttrs = ' href="#" onclick="init_lead('.$proposal->rel_id.'); return false;" data-toggle="tooltip" data-title="'._l('lead').'"';
            } else {
                $hrefAttrs = ' href="'.admin_url('clients/client/'.$proposal->rel_id).'" data-toggle="tooltip" data-title="'._l('client').'"';
            }
            $proposalTo = '<a' . $hrefAttrs . '>' . $proposalTo . '</a>';
        }

        if ($for == 'html' || $for == 'admin') {
            $phone = '<a href="tel:'.$proposal->phone.'">'.$proposal->phone.'</a>';
            $email = '<a href="mailto:'.$proposal->email.'">'.$proposal->email.'</a>';
        }

        $format = _info_format_replace('proposal_to', $proposalTo, $format);
        $format = _info_format_replace('address', $proposal->address, $format);
        $format = _info_format_replace('city', $proposal->city, $format);
        $format = _info_format_replace('state', $proposal->state, $format);

        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);

        $format = _info_format_replace('zip_code', $proposal->zip, $format);
        $format = _info_format_replace('phone', $phone, $format);
        $format = _info_format_replace('email', $email, $format);

        $whereCF = array();
        if (is_custom_fields_for_customers_portal()) {
            $whereCF['show_on_client_portal'] = 1;
        }
        $customFieldsProposals = get_custom_fields('proposal', $whereCF);

        foreach ($customFieldsProposals as $field) {
            $value = get_custom_field_value($proposal->id, $field['id'], 'proposal');
            $format = _info_format_custom_field($field['id'], $field['name'], $value, $format);
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _info_format_custom_fields_check($customFieldsProposals, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return do_action('proposal_info_text', $format);
    }
}

if (!function_exists('format_organization_info')) {
    /**
     * Format company info/address format
     * @return string
     */
    function format_organization_info()
    {
        $format = get_option('company_info_format');
        $vat = get_option('company_vat');

        $format = _info_format_replace('company_name', '<b style="color:black" class="company-name-formatted">'.get_option('invoice_company_name').'</b>', $format);
        $format = _info_format_replace('address', get_option('invoice_company_address'), $format);
        $format = _info_format_replace('city', get_option('invoice_company_city'), $format);
        $format = _info_format_replace('state', get_option('company_state'), $format);

        $format = _info_format_replace('zip_code', get_option('invoice_company_postal_code'), $format);
        $format = _info_format_replace('country_code', get_option('invoice_company_country_code'), $format);
        $format = _info_format_replace('phone', get_option('invoice_company_phonenumber'), $format);
        $format = _info_format_replace('vat_number', $vat, $format);
        $format = _info_format_replace('vat_number_with_label', $vat == '' ? '':_l('company_vat_number').': '.$vat, $format);

        $custom_company_fields = get_company_custom_fields();

        foreach ($custom_company_fields as $field) {
            $format = _info_format_custom_field($field['id'], $field['label'], $field['value'], $format);
        }

        $format = _info_format_custom_fields_check($custom_company_fields, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return do_action('organization_info_text', $format);
    }
}

/**
 * Return decimal places
 * The srcipt do not support more then 2 decimal places but developers can use action hook to change the decimal places
 * @return [type] [description]
 */
function get_decimal_places()
{
    return do_action('app_decimal_places', 2);
}

/**
 * Get all items by type eq. invoice, proposal, estimates, credit note
 * @param  string $type rel_type value
 * @return array
 */
function get_items_by_type($type, $id)
{
    $CI = &get_instance();
    $CI->db->select();
    $CI->db->from('tblitems_in');
    $CI->db->where('rel_id', $id);
    $CI->db->where('rel_type', $type);
    $CI->db->order_by('item_order', 'asc');

    return $CI->db->get()->result_array();
}
/**
* Function that update total tax in sales table eq. invoice, proposal, estimates, credit note
* @param  mixed $id
* @return void
*/
function update_sales_total_tax_column($id, $type, $table)
{
    $CI = &get_instance();
    $CI->db->select('discount_percent, discount_type, discount_total, subtotal');
    $CI->db->from($table);
    $CI->db->where('id',$id);

    $data = $CI->db->get()->row();

    $items = get_items_by_type($type, $id);

    $total_tax         = 0;
    $taxes             = array();
    $_calculated_taxes = array();

    $func_taxes = 'get_'.$type.'_item_taxes';

    foreach ($items as $item) {
        $item_taxes = call_user_func($func_taxes, $item['id']);
        if (count($item_taxes) > 0) {
            foreach ($item_taxes as $tax) {
                $calc_tax     = 0;
                $tax_not_calc = false;
                if (!in_array($tax['taxname'], $_calculated_taxes)) {
                    array_push($_calculated_taxes, $tax['taxname']);
                    $tax_not_calc = true;
                }

                if ($tax_not_calc == true) {
                    $taxes[$tax['taxname']]          = array();
                    $taxes[$tax['taxname']]['total'] = array();
                    array_push($taxes[$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                    $taxes[$tax['taxname']]['tax_name'] = $tax['taxname'];
                    $taxes[$tax['taxname']]['taxrate']  = $tax['taxrate'];
                } else {
                    array_push($taxes[$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                }
            }
        }
    }

    foreach ($taxes as $tax) {
        $total = array_sum($tax['total']);
        if ($data->discount_percent != 0 && $data->discount_type == 'before_tax') {
            $total_tax_calculated = ($total * $data->discount_percent) / 100;
            $total                = ($total - $total_tax_calculated);
        } elseif($data->discount_total != 0 && $data->discount_type == 'before_tax') {
            $t = ($data->discount_total / $data->subtotal) * 100;
            $total = ($total - $total*$t/100);
        }
        $total_tax += $total;
    }

    $CI->db->where('id', $id);
    $CI->db->update($table, array(
            'total_tax' => $total_tax,
    ));
}

/**
 * Function used for sales eq. invoice, estimate, proposal, credit note
 * @param  mixed $item_id   item id
 * @param  array $post_item $item from $_POST
 * @param  mixed $rel_id    rel_id
 * @param  string $rel_type  where this item tax is related
 */
function _maybe_insert_post_item_tax($item_id, $post_item, $rel_id, $rel_type)
{
    $affectedRows = 0;
    if (isset($post_item['taxname']) && is_array($post_item['taxname'])) {
        $CI = &get_instance();
        foreach ($post_item['taxname'] as $taxname) {
            if ($taxname != '') {
                $tax_array    = explode('|', $taxname);
                if (isset($tax_array[0]) && isset($tax_array[1])) {
                    $tax_name = trim($tax_array[0]);
                    $tax_rate = trim($tax_array[1]);
                    if (total_rows('tblitemstax', array(
                        'itemid'=>$item_id,
                        'taxrate'=>$tax_rate,
                        'taxname'=>$tax_name,
                        'rel_id'=>$rel_id,
                        'rel_type'=>$rel_type,
                    )) == 0) {
                        $CI->db->insert('tblitemstax', array(
                                'itemid' => $item_id,
                                'taxrate' => $tax_rate,
                                'taxname' => $tax_name,
                                'rel_id' => $rel_id,
                                'rel_type' => $rel_type,
                        ));
                        $affectedRows++;
                    }
                }
            }
        }
    }

    return $affectedRows > 0 ? true : false;
}

/**
 * Add new item do database, used for proposals,estimates,credit notes,invoices
 * This is repetitive action, that's why this function exists
 * @param array $item     item from $_POST
 * @param mixed $rel_id   relation id eq. invoice id
 * @param string $rel_type relation type eq invoice
 */
function add_new_sales_item_post($item, $rel_id, $rel_type)
{
    $custom_fields = false;

    if (isset($item['custom_fields'])) {
        $custom_fields = $item['custom_fields'];
    }

    $CI = &get_instance();

    $CI->db->insert('tblitems_in', array(
                    'description' => $item['description'],
                    'long_description' => nl2br($item['long_description']),
                    'qty' => $item['qty'],
                    'rate' => number_format($item['rate'], get_decimal_places(), '.', ''),
                    'rel_id' => $rel_id,
                    'rel_type' => $rel_type,
                    'item_order' => $item['order'],
                    'unit' => $item['unit'],
                ));

    $id = $CI->db->insert_id();

    if ($custom_fields !== false) {
        handle_custom_fields_post($id, $custom_fields);
    }

    return $id;
}

/**
 * Update sales item from $_POST, eq invoice item, estimate item
 * @param  mixed $item_id item id to update
 * @param  array $data    item $_POST data
 * @param  string $field   field is require to be passed for long_description,rate,item_order to do some additional checkings
 * @return boolean
 */
function update_sales_item_post($item_id, $data, $field = '')
{
    $update = array();
    if ($field !== '') {
        if ($field == 'long_description') {
            $update[$field] = nl2br($data[$field]);
        } elseif ($field == 'rate') {
            $update[$field] = number_format($data[$field], get_decimal_places(), '.', '');
        } elseif ($field == 'item_order') {
            $update[$field] = $data['order'];
        } else {
            $update[$field] = $data[$field];
        }
    } else {
        $update = array(
            'item_order' => $data['order'],
            'description' => $data['description'],
            'long_description' => nl2br($data['long_description']),
            'rate' => number_format($data['rate'], get_decimal_places(), '.', ''),
            'qty' => $data['qty'],
            'unit' => $data['unit'],
        );
    }

    $CI = &get_instance();
    $CI->db->where('id', $item_id);
    $CI->db->update('tblitems_in', $update);

    return $CI->db->affected_rows() > 0 ? true : false;
}

/**
 * When item is removed eq from invoice will be stored in removed_items in $_POST
 * With foreach loop this function will remove the item from database and it's taxes
 * @param  mixed $id       item id to remove
 * @param  string $rel_type item relation eq. invoice, estimate
 * @return boolena
 */
function handle_removed_sales_item_post($id, $rel_type)
{
    $CI = &get_instance();

    $CI->db->where('id', $id);
    $CI->db->delete('tblitems_in');
    if ($CI->db->affected_rows() > 0) {
        delete_taxes_from_item($id, $rel_type);

        $CI->db->where('relid', $id);
        $CI->db->where('fieldto', 'items');
        $CI->db->delete('tblcustomfieldsvalues');

        return true;
    }

    return false;
}

/**
 * Remove taxes from item
 * @param  mixed $item_id  item id
 * @param  string $rel_type relation type eq. invoice, estimate etc.
 * @return boolean
 */
function delete_taxes_from_item($item_id, $rel_type)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $item_id)
    ->where('rel_type', $rel_type)
    ->delete('tblitemstax');

    return $CI->db->affected_rows() > 0 ? true : false;
}

function is_sale_discount_applied($data)
{
    return $data->discount_total > 0;
}

function is_sale_discount($data, $is)
{
    if($data->discount_percent == 0 && $data->discount_total == 0) {
        return false;
    }

    $discount_type = 'fixed';
    if ($data->discount_percent != 0) {
        $discount_type = 'percent';
    }

    return $discount_type == $is;
}

if (!function_exists('get_table_items_and_taxes')) {
    /**
     * Function for all table items HTML and PDF
     * @param  array  $items         all items
     * @param  [type]  $type          where do items come form, eq invoice,estimate,proposal etc..
     * @param  boolean $admin_preview in admin preview add additional sortable classes
     * @return array
     */
    function get_table_items_and_taxes($items, $type, $admin_preview = false)
    {
        $cf = count($items) > 0 ? get_items_custom_fields_for_table_html($items[0]['rel_id'], $type) : array();

        static $rel_data = null;

        $result['html']    = '';
        $result['taxes']   = array();
        $_calculated_taxes = array();
        $i                 = 1;
        foreach ($items as $item) {
            $_item             = '';
            $tr_attrs       = '';
            $td_first_sortable = '';

            if ($admin_preview == true) {
                $tr_attrs       = ' class="sortable" data-item-id="' . $item['id'] . '"';
                $td_first_sortable = ' class="dragger item_no"';
            }

            if (class_exists('pdf')) {
                $font_size = get_option('pdf_font_size');
                if ($font_size == '') {
                    $font_size = 10;
                }

                $tr_attrs .= ' style="font-size:'.($font_size+4).'px;"';
            }

            $_item .= '<tr' . $tr_attrs . '>';
            $_item .= '<td' . $td_first_sortable . ' align="center">' . $i . '</td>';
            $_item .= '<td class="description" align="left;"><span style="font-size:'.(isset($font_size) ? $font_size+4 : '').'px;"><strong>' . $item['description'] . '</strong></span><br /><span style="color:#424242;">' . $item['long_description'] . '</span></td>';

            foreach ($cf as $custom_field) {
                $_item .= '<td align="left">' . get_custom_field_value($item['id'], $custom_field['id'], 'items') . '</td>';
            }

            $_item .= '<td align="right">' . floatVal($item['qty']);
            if ($item['unit']) {
                $_item .= ' ' . $item['unit'];
            }

            $_item .= '</td>';
            $_item .= '<td align="right">' . _format_number($item['rate']) . '</td>';
            if (get_option('show_tax_per_item') == 1) {
                $_item .= '<td align="right">';
            }

            $item_taxes = array();

            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
            $func_taxes = 'get_'.$type.'_item_taxes';
            if (function_exists($func_taxes)) {
                $item_taxes = call_user_func($func_taxes, $item['id']);
            }

            if (count($item_taxes) > 0) {

                if (!$rel_data) {
                    $rel_data = get_relation_data($item['rel_type'], $item['rel_id']);
                }

                foreach ($item_taxes as $tax) {
                    $calc_tax     = 0;
                    $tax_not_calc = false;
                    if (!in_array($tax['taxname'], $_calculated_taxes)) {
                        array_push($_calculated_taxes, $tax['taxname']);
                        $tax_not_calc = true;
                    }
                    if ($tax_not_calc == true) {
                        $result['taxes'][$tax['taxname']]          = array();
                        $result['taxes'][$tax['taxname']]['total'] = array();
                        array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                        $result['taxes'][$tax['taxname']]['tax_name'] = $tax['taxname'];
                        $result['taxes'][$tax['taxname']]['taxrate']  = $tax['taxrate'];
                    } else {
                        array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                    }
                    if (get_option('show_tax_per_item') == 1) {
                        $item_tax = '';
                        if ((count($item_taxes) > 1 && get_option('remove_tax_name_from_item_table') == false) || get_option('remove_tax_name_from_item_table') == false || mutiple_taxes_found_for_item($item_taxes)) {
                            $tmp = explode('|', $tax['taxname']);
                            $item_tax = $tmp[0] . ' ' . _format_number($tmp[1]) . '%<br />';
                        } else {
                            $item_tax .= _format_number($tax['taxrate']) . '%';
                        }
                        $hook_data = array('final_tax_html'=>$item_tax, 'item_taxes'=>$item_taxes, 'item_id'=>$item['id']);
                        $hook_data = do_action('item_tax_table_row', $hook_data);
                        $item_tax = $hook_data['final_tax_html'];
                        $_item .= $item_tax;
                    }
                }
            } else {
                if (get_option('show_tax_per_item') == 1) {
                    $hook_data = array('final_tax_html'=>'0%', 'item_taxes'=>$item_taxes, 'item_id'=>$item['id']);
                    $hook_data = do_action('item_tax_table_row', $hook_data);
                    $_item .= $hook_data['final_tax_html'];
                }
            }

            if (get_option('show_tax_per_item') == 1) {
                $_item .= '</td>';
            }

            /**
             * Since @version 1.7.0
             * Possible action hook user to include tax in item total amount calculated with the quantiy
             * eq Rate * QTY + TAXES APPLIED
             */

            $hook_data = do_action('final_item_amount', array(
                'amount'=>($item['qty'] * $item['rate']),
                'item_taxes'=>$item_taxes,
                'item'=>$item,
            ));

            $item_amount_with_quantity = _format_number($hook_data['amount']);

            $_item .= '<td class="amount" align="right">' . $item_amount_with_quantity . '</td>';
            $_item .= '</tr>';
            $result['html'] .= $_item;
            $i++;
        }

        foreach ($result['taxes'] as $tax) {

            $total_tax = array_sum($tax['total']);
            if ($rel_data->discount_percent != 0 && $rel_data->discount_type == 'before_tax') {
                $total_tax_tax_calculated = ($total_tax * $rel_data->discount_percent) / 100;
                $total_tax = ($total_tax - $total_tax_tax_calculated);
            } elseif ($rel_data->discount_total != 0 && $rel_data->discount_type == 'before_tax') {
                $t = ($rel_data->discount_total / $rel_data->subtotal) * 100;
                $total_tax = ($total_tax - $total_tax*$t/100);
            }

            $result['taxes'][$tax['tax_name']]['total_tax'] = $total_tax;
            // Tax name is in format NAME|PERCENT
            $tax_name_array = explode('|', $tax['tax_name']);
            $result['taxes'][$tax['tax_name']]['taxname'] = $tax_name_array[0];
        }

        // Order taxes by taxrate
        // Lowest tax rate will be on top (if multiple)
        usort($result['taxes'], function($a, $b) {
            return $a['taxrate'] - $b['taxrate'];
        });

        return do_action('before_return_table_items_html_and_taxes', $result);
    }
}
