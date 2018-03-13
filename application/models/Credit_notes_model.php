<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Credit_notes_model extends CRM_Model
{
    private $shipping_fields = array('shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
    }

    public function get_statuses()
    {
        $statuses = array(
            array(
                'id'=>1,
                'color'=>'#03a9f4',
                'name'=>_l('credit_note_status_open'),
                'order'=>1,
                'filter_default'=>true,
                ),
             array(
                'id'=>2,
                'color'=>'#84c529',
                'name'=>_l('credit_note_status_closed'),
                'order'=>2,
                'filter_default'=>true,
             ),
             array(
                'id'=>3,
                'color'=>'#777',
                'name'=>_l('credit_note_status_void'),
                'order'=>3,
                'filter_default'=>false,
             ),
        );

        return do_action('before_get_credit_notes_statuses', $statuses);
    }

    public function get_available_creditable_invoices($credit_note_id)
    {
        $has_permission_view = has_permission('invoices', '', 'view');

        $invoices_statuses_available_for_credits = invoices_statuses_available_for_credits();
        $this->db->select('clientid');
        $this->db->where('id', $credit_note_id);
        $credit_note = $this->db->get('tblcreditnotes')->row();

        $this->db->select('tblinvoices.id as id, status, total, date, symbol');
        $this->db->where('clientid', $credit_note->clientid);
        $this->db->where('status IN ('.implode(', ', $invoices_statuses_available_for_credits).')');
        if (!$has_permission_view) {
            $this->db->where('addedfrom', get_staff_user_id());
        }
        $this->db->join('tblcurrencies', 'tblcurrencies.id = tblinvoices.currency');
        $invoices = $this->db->get('tblinvoices')->result_array();

        foreach ($invoices as $key=>$invoice) {
            $invoices[$key]['total_left_to_pay'] = get_invoice_total_left_to_pay($invoice['id'], $invoice['total']);
        }

        return $invoices;
    }

    /**
    * Send credit note to client
    * @param  mixed  $id        credit note id
    * @param  string  $template  email template to sent
    * @param  boolean $attachpdf attach credit note pdf or not
    * @return boolean
    */
    public function send_credit_note_to_client($id, $attachpdf = true, $cc = '', $manually = false)
    {
        $this->load->model('emails_model');

        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('credit_note');

        $credit_note = $this->get($id);
        $template = 'credit-note-send-to-client';
        $number = format_credit_note_number($credit_note->id);

        $sent                = false;
        $sent_to             = $this->input->post('sent_to');
        if ($manually === true) {
            $sent_to  = array();
            $contacts = $this->clients_model->get_contacts($credit_note->clientid, array('active'=>1, 'credit_note_emails'=>1));
            foreach ($contacts as $contact) {
                array_push($sent_to, $contact['id']);
            }
        }

        if (is_array($sent_to) && count($sent_to) > 0) {
            if ($attachpdf) {
                $pdf    = credit_note_pdf($credit_note);
                $attach = $pdf->Output($number . '.pdf', 'S');
            }
            $i = 0;
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    if ($attachpdf) {
                        $this->emails_model->add_attachment(array(
                            'attachment' => $attach,
                            'filename' => $number . '.pdf',
                            'type' => 'application/pdf',
                        ));
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if ($this->input->post('email_attachments')) {
                        $_other_attachments = $this->input->post('email_attachments');
                        foreach ($_other_attachments as $attachment) {
                            $_attachment = $this->misc_model->get_file($attachment);
                            $this->emails_model->add_attachment(array(
                                'attachment' => get_upload_path_by_type('credit_note') . $id . '/' . $_attachment->file_name,
                                'filename' => $_attachment->file_name,
                                'type' => $_attachment->filetype,
                                'read' => true,
                            ));
                        }
                    }

                    $merge_fields = array();
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($credit_note->clientid, $contact_id));
                    $merge_fields = array_merge($merge_fields, get_credit_note_merge_fields($credit_note->id));
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }
                    if ($this->emails_model->send_email_template($template, $contact->email, $merge_fields, '', $cc)) {
                        $sent = true;
                    }
                }
                $i++;
            }
        } else {
            return false;
        }

        if ($sent) {
            do_action('credit_note_sent', $id);

            return true;
        }

        return false;
    }

    /**
     * Get credit note/s
     * @param  mixed $id    credit note id
     * @param  array  $where perform where
     * @return mixed
     */
    public function get($id = '', $where = array())
    {
        $this->db->select('*,tblcurrencies.id as currencyid, tblcreditnotes.id as id, tblcurrencies.name as currency_name');
        $this->db->from('tblcreditnotes');
        $this->db->join('tblcurrencies', 'tblcurrencies.id = tblcreditnotes.currency', 'left');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('tblcreditnotes.id', $id);
            $credit_note = $this->db->get()->row();
            if ($credit_note) {
                $credit_note->applied_credits = $this->get_applied_credits($id);
                $credit_note->remaining_credits = $this->total_remaining_credits_by_credit_note($id);
                $credit_note->credits_used = $this->total_credits_used_by_credit_note($id);
                $credit_note->items = get_items_by_type('credit_note', $id);
                $credit_note->client = $this->clients_model->get($credit_note->clientid);
                $credit_note->attachments                           = $this->get_attachments($id);
            }

            return $credit_note;
        }

        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    public function add($data)
    {
        $save_and_send = isset($data['save_and_send']);

        $data['prefix']        = get_option('credit_note_prefix');
        $data['number_format'] = get_option('credit_note_number_format');
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();

        $items               = array();
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data = $this->map_shipping_columns($data);

        $hook_data = do_action('before_create_credit_note', array('data'=>$data, 'items'=>$items));

        $data = $hook_data['data'];
        $items = $hook_data['items'];

        $this->db->insert('tblcreditnotes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {

            // Update next credit note number in settings
            $this->db->where('name', 'next_credit_note_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update('tbloptions');

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'credit_note')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'credit_note');
                }
            }

            update_sales_total_tax_column($insert_id, 'credit_note', 'tblcreditnotes');

            logActivity('Credit Note Created [ID: '.$insert_id.']');

            do_action('after_create_credit_note', $insert_id);

            if ($save_and_send === true) {
                $this->send_credit_note_to_client($insert_id, true, '', true);
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * Update proposal
     * @param  mixed $data $_POST data
     * @param  mixed $id   proposal id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows     = 0;
        $save_and_send = isset($data['save_and_send']);

        $items = array();
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = array();
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

         if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $data = $this->map_shipping_columns($data);

        $hook_data = do_action('before_update_credit_note', array(
            'data'=>$data,
            'id'=>$id,
            'items'=>$items,
            'newitems'=>$newitems,
            'removed_items'=>isset($data['removed_items']) ? $data['removed_items'] : array(),
        ));

        $data = $hook_data['data'];
        $data['removed_items'] = $hook_data['removed_items'];

        $newitems = $hook_data['newitems'];
        $items = $hook_data['items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            if (handle_removed_sales_item_post($remove_item_id, 'credit_note')) {
                $affectedRows++;
            }
        }
        unset($data['removed_items']);

        $this->db->where('id', $id);
        $this->db->update('tblcreditnotes', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        foreach ($items as $key => $item) {
            if (update_sales_item_post($item['itemid'], $item)) {
                $affectedRows++;
            }

            if(isset($item['custom_fields'])) {
                if(handle_custom_fields_post($item['itemid'], $item['custom_fields'])) {
                    $affectedRows++;
                }
            }

            if (!isset($item['taxname']) || (isset($item['taxname']) && count($item['taxname']) == 0)) {
                if (delete_taxes_from_item($item['itemid'], 'credit_note')) {
                    $affectedRows++;
                }
            } else {
                $item_taxes        = get_credit_note_item_taxes($item['itemid']);
                $_item_taxes_names = array();
                foreach ($item_taxes as $_item_tax) {
                    array_push($_item_taxes_names, $_item_tax['taxname']);
                }

                $i = 0;
                foreach ($_item_taxes_names as $_item_tax) {
                    if (!in_array($_item_tax, $item['taxname'])) {
                        $this->db->where('id', $item_taxes[$i]['id'])
                            ->delete('tblitemstax');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                    $i++;
                }
                if (_maybe_insert_post_item_tax($item['itemid'], $item, $id, 'credit_note')) {
                    $affectedRows++;
                }
            }
        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_sales_item_post($item, $id, 'credit_note')) {
                _maybe_insert_post_item_tax($new_item_added, $item, $id, 'credit_note');
                $affectedRows++;
            }
        }

        if ($save_and_send === true) {
            $this->send_credit_note_to_client($id, true, '', true);
        }

        if ($affectedRows > 0) {
            $this->update_credit_note_status($id);
            update_sales_total_tax_column($id, 'credit_note', 'tblcreditnotes');
        }

        if($affectedRows > 0){
            logActivity('Credit Note Updated [ID:' . $id . ']');
            do_action('after_update_credit_note', $id);

            return true;
        }

        return false;
    }

    /**
    *  Delete credit note attachment
    * @param   mixed $id  attachmentid
    * @return  boolean
    */
    public function delete_attachment($id)
    {
        $attachment = $this->misc_model->get_file($id);

        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('credit_note') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                logActivity('credit_note Attachment Deleted [Credite Note: ' . format_credit_note_number($attachment->rel_id) . ']');
            }
            if (is_dir(get_upload_path_by_type('credit_note') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('credit_note') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('credit_note') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function get_attachments($credit_note_id)
    {
        $this->db->where('rel_id', $credit_note_id);
        $this->db->where('rel_type', 'credit_note');

        return $this->db->get('tblfiles')->result_array();
    }

    /**
    * Delete credit note
    * @param  mixed $id credit note id
    * @return boolean
    */
    public function delete($id)
    {
        do_action('before_credit_note_deleted', $id);
        $this->db->where('id', $id);
        $this->db->delete('tblcreditnotes');
        if ($this->db->affected_rows() > 0) {
            $current_credit_note_number = get_option('next_credit_note_number');

            if ($current_credit_note_number > 1 && is_last_credit_note($id)) {
                // Decrement next credit note number
                $this->db->where('name', 'next_credit_note_number');
                $this->db->set('value', 'value-1', false);
                $this->db->update('tbloptions');
            }

            // Delete the custom field values
            $this->db->where('relid IN (SELECT id from tblitems_in WHERE rel_type="credit_note" AND rel_id="'.$id.'")');
            $this->db->where('fieldto','items');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'credit_note');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('credit_id', $id);
            $this->db->delete('tblcredits');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'credit_note');
            $this->db->delete('tblitems_in');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'credit_note');
            $this->db->delete('tblitemstax');

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            do_action('after_credit_note_deleted', $id);

            return true;
        }

        return false;
    }

    public function mark($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblcreditnotes', array('status'=>$status));

        return $this->db->affected_rows() > 0 ? true : false;
    }

    public function total_remaining_credits_by_customer($customer_id)
    {
        $has_permission_view = has_permission('credit_notes', '', 'view');
        $this->db->select('total,id');
        $this->db->where('clientid', $customer_id);
        $this->db->where('status', 1);
        if (!$has_permission_view) {
            $this->db->where('addedfrom', get_staff_user_id());
        }
        $credits = $this->db->get('tblcreditnotes')->result_array();

        $total = $this->calc_remaining_credits($credits);

        return $total;
    }

    public function total_remaining_credits_by_credit_note($credit_note_id)
    {
        $this->db->select('total,id');
        $this->db->where('id', $credit_note_id);
        $credits = $this->db->get('tblcreditnotes')->result_array();

        $total = $this->calc_remaining_credits($credits);

        return $total;
    }

    private function calc_remaining_credits($credits)
    {
        $total = 0;
        $credits_ids = array();

        $bcadd = function_exists('bcadd');
        foreach ($credits as $credit) {
            if ($bcadd) {
                $total = bcadd($total, $credit['total'], get_decimal_places());
            } else {
                $total += $credit['total'];
            }
            array_push($credits_ids, $credit['id']);
        }

        if (count($credits_ids) > 0) {
            $this->db->where('credit_id IN ('.implode(', ', $credits_ids).')');
            $applied_credits = $this->db->get('tblcredits')->result_array();
            $bcsub = function_exists('bcsub');
            foreach ($applied_credits as $credit) {
                if ($bcsub) {
                    $total = bcsub($total, $credit['amount'], get_decimal_places());
                } else {
                    $total -= $credit['amount'];
                }
            }
        }

        return $total;
    }

    public function delete_applied_credit($id, $credit_id, $invoice_id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblcredits');
        if ($this->db->affected_rows() > 0) {
            $this->update_credit_note_status($credit_id);
            update_invoice_status($invoice_id);
        }
    }

    public function credit_note_from_invoice($invoice_id)
    {
        $_invoice = $this->invoices_model->get($invoice_id);

        $new_credit_note_data             = array();
        $new_credit_note_data['clientid'] = $_invoice->clientid;
        $new_credit_note_data['number']   = get_option('next_credit_note_number');
        $new_credit_note_data['date']     = _d(date('Y-m-d'));

        $new_credit_note_data['show_quantity_as']  = $_invoice->show_quantity_as;
        $new_credit_note_data['currency']          = $_invoice->currency;
        $new_credit_note_data['subtotal']          = $_invoice->subtotal;
        $new_credit_note_data['total']             = $_invoice->total;
        $new_credit_note_data['adminnote']         = $_invoice->adminnote;
        $new_credit_note_data['adjustment']        = $_invoice->adjustment;
        $new_credit_note_data['discount_percent']  = $_invoice->discount_percent;
        $new_credit_note_data['discount_total']    = $_invoice->discount_total;
        $new_credit_note_data['discount_type']     = $_invoice->discount_type;


        $new_credit_note_data['billing_street']    = clear_textarea_breaks($_invoice->billing_street);
        $new_credit_note_data['billing_city']      = $_invoice->billing_city;
        $new_credit_note_data['billing_state']     = $_invoice->billing_state;
        $new_credit_note_data['billing_zip']       = $_invoice->billing_zip;
        $new_credit_note_data['billing_country']   = $_invoice->billing_country;
        $new_credit_note_data['shipping_street']   = clear_textarea_breaks($_invoice->shipping_street);
        $new_credit_note_data['shipping_city']     = $_invoice->shipping_city;
        $new_credit_note_data['shipping_state']    = $_invoice->shipping_state;
        $new_credit_note_data['shipping_zip']      = $_invoice->shipping_zip;
        $new_credit_note_data['shipping_country']  = $_invoice->shipping_country;
        $new_credit_note_data['reference_no']  = format_invoice_number($_invoice->id);
        if ($_invoice->include_shipping == 1) {
            $new_credit_note_data['include_shipping'] = $_invoice->include_shipping;
        }
        $new_credit_note_data['show_shipping_on_credit_note'] = $_invoice->show_shipping_on_invoice;
        $new_credit_note_data['clientnote']               = get_option('predefined_clientnote_credit_note');
        $new_credit_note_data['terms']             = get_option('predefined_terms_credit_note');
        $new_credit_note_data['adminnote']                = '';
        $new_credit_note_data['newitems']                 = array();

        $custom_fields_items = get_custom_fields('items');
        $key                                          = 1;
        foreach ($_invoice->items as $item) {
            $new_credit_note_data['newitems'][$key]['description']      = $item['description'];
            $new_credit_note_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_credit_note_data['newitems'][$key]['qty']              = $item['qty'];
            $new_credit_note_data['newitems'][$key]['unit']             = $item['unit'];
            $new_credit_note_data['newitems'][$key]['taxname']          = array();
            $taxes                                                  = get_invoice_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_credit_note_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_credit_note_data['newitems'][$key]['rate']  = $item['rate'];
            $new_credit_note_data['newitems'][$key]['order'] = $item['item_order'];
            foreach($custom_fields_items as $cf) {

            $new_credit_note_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'],$cf['id'],'items',false);

                if(!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST',true);
                }

            }
            $key++;
        }
        $id = $this->add($new_credit_note_data);
        if ($id) {
            if ($_invoice->status != 2) {
                if ($this->apply_credits($id, array('invoice_id'=>$invoice_id, 'amount'=>$_invoice->total_left_to_pay))) {
                    update_invoice_status($invoice_id, true);
                }
            }

            logActivity('Created Credit Note From Invoice [Invoice: ' . format_invoice_number($_invoice->id).', Credit Note: '.format_credit_note_number($id).']');

            do_action('created_credit_note_from_invoice', array('invoice_id'=>$invoice_id, 'credit_note_id'=>$id));

            return $id;
        }

        return false;
    }

    public function apply_credits($id, $data)
    {
        if ($data['amount'] == 0) {
            return false;
        }

        $this->db->insert('tblcredits', array(
            'invoice_id'=>$data['invoice_id'],
            'credit_id'=>$id,
            'staff_id'=>get_staff_user_id(),
            'date'=>date('Y-m-d'),
            'date_applied'=>date('Y-m-d H:i:s'),
            'amount'=>$data['amount'],
        ));

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->update_credit_note_status($id);
            $this->db->select('symbol');
            $this->db->join('tblcurrencies', 'tblcurrencies.id = tblinvoices.currency');
            $this->db->where('tblinvoices.id', $data['invoice_id']);

            $invoice = $this->db->get('tblinvoices')->row();

            $inv_number = format_invoice_number($data['invoice_id']);
            $credit_note_number = format_credit_note_number($id);

            $this->invoices_model->log_invoice_activity($data['invoice_id'], 'invoice_activity_applied_credits', false, serialize(array(
                   format_money($data['amount'], $invoice->symbol),
                   $credit_note_number,
             )));
            do_action('credits_applied', array('data'=>$data, 'credit_note_id'=>$id));
            logActivity('Credit Applied to Invoice [ Invoice: '.$inv_number.', Credit: '.$credit_note_number.' ]');
        }

        return $insert_id;
    }

    private function total_credits_used_by_credit_note($id)
    {
        return sum_from_table('tblcredits', array(
                'field'=>'amount',
                'where'=>array('credit_id'=>$id),
            ));
    }

    public function update_credit_note_status($id)
    {
        $applied_credits = $this->get_applied_credits($id);

        $total_credits_used = $this->total_credits_used_by_credit_note($id);

        $status = 1;

        // sum from table returns null if nothing found
        if ($total_credits_used) {
            $this->db->select('total');
            $this->db->where('id', $id);
            $credit = $this->db->get('tblcreditnotes')->row();

            if ($credit) {
                if (function_exists('bccomp')) {
                    if (bccomp($credit->total, $total_credits_used, get_decimal_places()) === 0) {
                        $status = 2;
                    }
                } else {
                    if ($credit->total == $total_credits_used) {
                        $status = 2;
                    }
                }
            }
        }

        $this->db->where('id', $id);
        $this->db->update('tblcreditnotes', array('status'=>$status));

        return $this->db->affected_rows() > 0 ? true : false;
    }

    public function get_open_credits($customer_id)
    {
        $has_permission_view = has_permission('credit_notes', '', 'view');
        $this->db->where('status', 1);
        $this->db->where('clientid', $customer_id);
        if (!$has_permission_view) {
            $this->db->where('addedfrom', get_staff_user_id());
        }
        $credits = $this->db->get('tblcreditnotes')->result_array();

        foreach ($credits as $key => $credit) {
            $credits[$key]['available_credits'] = $this->calculate_available_credits($credit['id'], $credit['total']);
        }

        return $credits;
    }

    public function get_applied_invoice_credits($invoice_id)
    {
        $this->db->order_by('date', 'desc');
        $this->db->where('invoice_id', $invoice_id);

        return $this->db->get('tblcredits')->result_array();
    }

    public function get_applied_credits($credit_id)
    {
        $this->db->where('credit_id', $credit_id);
        $this->db->order_by('date', 'desc');

        return $this->db->get('tblcredits')->result_array();
    }

    private function calculate_available_credits($credit_id, $credit_amount = false)
    {
        if ($credit_amount === false) {
            $this->db->select('total')
            ->from('tblcreditnotes')
            ->where('id', $credit_id);

            $credit_amount = $this->db->get()->row()->total;
        }

        $available_total = $credit_amount;

        $bcsub = function_exists('bcsub');
        $applied_credits = $this->get_applied_credits($credit_id);

        foreach ($applied_credits as $credit) {
            if ($bcsub) {
                $available_total = bcsub($available_total, $credit['amount'], get_decimal_places());
            } else {
                $available_total -= $credit['amount'];
            }
        }

        return $available_total;
    }

    public function get_credits_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM tblcreditnotes ORDER BY year DESC')->result_array();
    }

    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_credit_note'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_credit_note']) && ($data['show_shipping_on_credit_note'] == 1 || $data['show_shipping_on_credit_note'] == 'on')) {
                $data['show_shipping_on_credit_note'] = 1;
            } else {
                $data['show_shipping_on_credit_note'] = 0;
            }
        }

        return $data;
    }
}
