<?php
defined('BASEPATH') or exit('No direct script access allowed');

define('SMS_TRIGGER_INVOICE_OVERDUE', 'invoice_overdue_notice');
define('SMS_TRIGGER_PAYMENT_RECORDED', 'invoice_payment_recorded');
define('SMS_TRIGGER_ESTIMATE_EXP_REMINDER', 'estimate_expiration_reminder');
define('SMS_TRIGGER_PROPOSAL_EXP_REMINDER', 'proposal_expiration_reminder');
define('SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_CUSTOMER', 'proposal_new_comment_to_customer');
define('SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_STAFF', 'proposal_new_comment_to_staff');
define('SMS_TRIGGER_CONTRACT_EXP_REMINDER', 'contract_expiration_reminder');
define('SMS_TRIGGER_STAFF_REMINDER', 'staff_reminder');

class Sms
{
    private $gateways;

    private $triggers = array();

    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->set_default_triggers();
    }

    public function add_gateway($id, $data = array())
    {
        if (!$this->is_initialized($id) || $this->is_options_page()) {
            foreach ($data['options'] as $option) {
                add_option($this->option_name($id, $option['name']), (isset($option['default_value']) ? $option['default_value'] : ''), 0);
            }

            add_option($this->option_name($id, 'active'), 0);
            add_option($this->option_name($id, 'initialized'), 1);
        }

        $data['id'] = $id;
        $this->gateways[$id] = $data;
    }

    public function get_option($id, $option)
    {
        return get_option($this->option_name($id, $option));
    }

    public function get_gateway($id)
    {
        $gateway = isset($this->gateways[$id]) ? $this->gateways[$id] : null;

        return $gateway;
    }

    public function get_gateways()
    {
        return do_action('get_sms_gateways', $this->gateways);
    }

    public function get_trigger_value($trigger)
    {
        $oc_name = 'sms-trigger-'.$trigger.'-value';
        $message = $this->ci->object_cache->get($oc_name);
        if (!$message) {
            $message = get_option($this->trigger_option_name($trigger));
            $this->ci->object_cache->add($oc_name, $message);
        }

        return $message;
    }

    public function add_trigger($trigger)
    {
        $this->triggers[] = $trigger;
    }

    public function get_available_triggers()
    {
        $triggers = do_action('sms_gateway_available_triggers', $this->triggers);

        foreach ($triggers as $trigger_id => $triger) {
            if ($this->is_options_page()) {
                add_option($this->trigger_option_name($trigger_id), '', 0);
            }
            $triggers[$trigger_id]['value'] = $this->get_trigger_value($trigger_id);
        }

        return $triggers;
    }

    public function trigger($trigger, $phone, $merge_fields = array())
    {
        if ($phone == '') {
            return false;
        }

        $gateway = $this->get_activate_gateway();

        if ($gateway !== false) {
            $callable = $gateway['id'].'_trigger_send_sms';
            if ($this->is_trigger_active($trigger) && function_exists($callable)) {
                $message = $this->parse_merge_fields($merge_fields, $this->get_trigger_value($trigger));

                $retval = call_user_func_array($callable, array($phone, clear_textarea_breaks($message), $trigger));
                do_action('sms_trigger_triggered', array('message'=>$message, 'trigger'=>$trigger, 'phone'=>$phone));

                return $retval;
            }
        }

        return false;
    }

    /**
     * Parse sms gateway merge fields
     * We will use the email templates merge fields function because they are the same
     * @param  array $merge_fields merge fields
     * @param  string $message      the message to bind the merge fields
     * @return string
     */
    public function parse_merge_fields($merge_fields, $message)
    {
        $template = new stdClass();
        $template->message = $message;
        $template->subject= '';
        $template->fromname = '';

        return _parse_email_template_merge_fields($template, $merge_fields)->message;
    }

    public function option_name($id, $option)
    {
        return 'sms_' . $id . '_' . $option;
    }

    public function trigger_option_name($trigger)
    {
        return 'sms_trigger_' . $trigger;
    }

    public function is_any_trigger_active()
    {
        $triggers = $this->get_available_triggers();
        $active = false;
        foreach ($triggers as $trigger_id=>$trigger_opts) {
            if ($this->_is_trigger_message_empty($this->get_trigger_value($trigger_id))) {
                $active = true;
                break;
            }
        }

        return $active;
    }

    private function _is_trigger_message_empty($message)
    {
        if (trim($message) === '') {
            return false;
        }

        return true;
    }

    public function is_trigger_active($trigger)
    {
        if ($trigger != '') {
            if (!$this->_is_trigger_message_empty($this->get_trigger_value($trigger))) {
                return false;
            }
        } else {
            return $this->is_any_trigger_active();
        }

        return true;
    }

    public function get_activate_gateway()
    {
        $active = false;

        foreach ($this->gateways as $gateway) {
            if ($this->get_option($gateway['id'], 'active') == '1') {
                $active = $gateway;
                break;
            }
        }

        return $active;
    }

    /**
     * Check if is settings page in admin area
     * @return boolean
     */
    private function is_options_page()
    {
        return $this->ci->input->get('group') == 'sms' && $this->ci->uri->segment(2) == 'settings';
    }

    /**
     * Check if sms gateway is initialized and options are added into database
     * @return boolean
     */
    private function is_initialized($id)
    {
        return $this->get_option($id, 'initialized') == '' ? false : true;
    }

    private function set_default_triggers()
    {
        $customer_merge_fields = array(
            '{contact_firstname}',
            '{contact_lastname}',
            '{client_company}',
            '{client_vat_number}',
            '{client_id}',
        );

        $invoice_merge_fields = array(
            '{invoice_link}',
            '{invoice_number}',
            '{invoice_duedate}',
            '{invoice_date}',
            '{invoice_status}',
            '{invoice_subtotal}',
            '{invoice_total}',
        );

        $proposal_merge_fields = array(
            '{proposal_number}',
            '{proposal_id}',
            '{proposal_subject}',
            '{proposal_open_till}',
            '{proposal_subtotal}',
            '{proposal_total}',
            '{proposal_proposal_to}',
            '{proposal_link}',
        );

        $triggers = array(
            SMS_TRIGGER_INVOICE_OVERDUE=> array(
                'merge_fields'=>array_merge($customer_merge_fields, $invoice_merge_fields),
                'label'=>'Invoice Overdue Notice',
                'info'=>'Trigger when invoice overdue notice is sent to customer contacts.',
            ),
            SMS_TRIGGER_PAYMENT_RECORDED=>array(
                'merge_fields'=>array_merge($customer_merge_fields, $invoice_merge_fields, array('{payment_total}', '{payment_date}')),
                'label'=>'Invoice Payment Recorded',
                'info'=>'Trigger when invoice payment is recorded.',
            ),
            SMS_TRIGGER_ESTIMATE_EXP_REMINDER => array(
                'merge_fields'=>array_merge(
                    $customer_merge_fields,
                    array(
                        '{estimate_link}',
                        '{estimate_number}',
                        '{estimate_date}',
                        '{estimate_status}',
                        '{estimate_subtotal}',
                        '{estimate_total}',
                    )
                ),
                'label'=>'Estimate Expiration Reminder',
                'info'=>'Trigger when expiration reminder should be send to customer contacts.',
            ),
            SMS_TRIGGER_PROPOSAL_EXP_REMINDER=>array(
                'merge_fields'=>$proposal_merge_fields,
                'label'=>'Proposal Expiration Reminder',
                'info'=>'Trigger when expiration reminder should be send to proposal.',
            ),
            SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_CUSTOMER=>array(
                'merge_fields'=>$proposal_merge_fields,
                'label'=>'New Comment on Proposal (to customer)',
                'info'=>'Trigger when staff member comments on proposal, SMS will be sent to proposal number (customer/lead).',
            ),
            SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_STAFF=>array(
                'merge_fields'=>$proposal_merge_fields,
                'label'=>'New Comment on Proposal (to staff)',
                'info'=>'Trigger when customer/lead comments on proposal, SMS will be sent to proposal creator and assigned staff member.',
            ),
            SMS_TRIGGER_CONTRACT_EXP_REMINDER=>array(
                'merge_fields'=>array_merge($customer_merge_fields, array(
                    '{contract_id}',
                    '{contract_subject}',
                    '{contract_datestart}',
                    '{contract_dateend}',
                    '{contract_contract_value}',
                )),
                'label'=>'Contract Expiration Reminder',
                'info'=>'Trigger when expiration reminder should be send via Cron Job to customer contacts.',
            ),
            SMS_TRIGGER_STAFF_REMINDER=>array(
                'merge_fields'=>array(
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{staff_reminder_description}',
                    '{staff_reminder_date}',
                    '{staff_reminder_relation_name}',
                    '{staff_reminder_relation_link}',
                ),
                'label'=>'Staff Reminder',
                'info'=>'Trigger when staff is notified for a specific custom <a href="'.admin_url('misc/reminders').'">reminder</a>.',
            ),
        );
        $this->triggers = $triggers;
    }
}
