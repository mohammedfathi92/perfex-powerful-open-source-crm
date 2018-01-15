<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_118 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query('UPDATE `tbloptions` SET `value` = replace(value, "Ticket Pipe Log", "ticket_pipe_log") WHERE name="aside_menu_active"');
        $this->db->query('UPDATE `tbloptions` SET `value` = replace(value, "Ticket Pipe Log", "ticket_pipe_log") WHERE name="aside_menu_inactive"');
        $key = add_encryption_key_old();
        $key_found = true;
        if($key == false){
            $key = $this->config->item('encryption_key');
            if($key != ''){
                if(strlen($key) != 32){
                    $key_found = false;
                }
            }
        }
        if($key_found == true){
            $this->encryption->initialize(array('key'=>$key));
            $this->session->set_userdata(array('update_encryption_key'=>$key));

            $smtp_pass = get_option('smtp_password');
            $paymentmethod_paypal_username = get_option('paymentmethod_paypal_username');
            $paymentmethod_paypal_password = get_option('paymentmethod_paypal_password');
            $paymentmethod_paypal_signature = get_option('paymentmethod_paypal_signature');
            $paymentmethod_stripe_api_secret_key = get_option('paymentmethod_stripe_api_secret_key');
            $paymentmethod_stripe_api_publishable_key = get_option('paymentmethod_stripe_api_publishable_key');

            if(!empty($smtp_pass)){
                 update_option('smtp_password',$this->encryption->encrypt($smtp_pass));
            }
            if(!empty($paymentmethod_paypal_username)){
                 update_option('paymentmethod_paypal_username',$this->encryption->encrypt($paymentmethod_paypal_username));
            }

            if(!empty($paymentmethod_paypal_password)){
                 update_option('paymentmethod_paypal_password',$this->encryption->encrypt($paymentmethod_paypal_password));
            }
            if(!empty($paymentmethod_paypal_signature)){
                 update_option('paymentmethod_paypal_signature',$this->encryption->encrypt($paymentmethod_paypal_signature));
            }
            if(!empty($paymentmethod_stripe_api_secret_key)){
                 update_option('paymentmethod_stripe_api_secret_key',$this->encryption->encrypt($paymentmethod_stripe_api_secret_key));
            }
            if(!empty($paymentmethod_stripe_api_publishable_key)){
                 update_option('paymentmethod_stripe_api_publishable_key',$this->encryption->encrypt($paymentmethod_stripe_api_publishable_key));
            }
            $this->db->where('id',1);
            $leads_email = $this->db->get('tblleadsemailintegration')->row();
            if($leads_email){
                if(!empty($leads_email->password)){
                    $this->db->where('id',1);
                    $this->db->update('tblleadsemailintegration',array('password'=>$this->encryption->encrypt($leads_email->password)));
                }
            }
        }
     $menu = get_option('aside_menu_active');
        $menu = json_decode($menu);
        if(is_object($menu)){
            $i = 0;
            foreach($menu->aside_menu_active as $item){
                if($item->id == 'tickets') {
                    $menu->aside_menu_active[$i]->url = 'tickets';
                    if(isset($item->children)){
                        unset($menu->aside_menu_active[$i]->children);
                    }
                } else if($item->id == 'child-new-ticket' || $item->id == 'child-open' || $item->id == 'child-in-progress' || $item->id == 'child-answered' || $item->id == 'child-on-hold' || $item->id == '"child-closed' || $item->id == 'child-all-tickets'){
                    unset($menu->aside_menu_active[$i]);
                }
                $i++;
            }
        }

        $menu = json_encode($menu);
        update_option('aside_menu_active',$menu);

        $menu = get_option('aside_menu_inactive');
        $menu = json_decode($menu);
        if(is_object($menu)){
            $i = 0;
            foreach($menu->aside_menu_inactive as $item){
                if($item->id == 'tickets') {
                    $menu->aside_menu_inactive[$i]->url = 'tickets';
                    if(isset($item->children)){
                        unset($menu->aside_menu_inactive[$i]->children);
                    }
                } else if($item->id == 'child-new-ticket' || $item->id == 'child-open' || $item->id == 'child-in-progress' || $item->id == 'child-answered' || $item->id == 'child-on-hold' || $item->id == '"child-closed' || $item->id == 'child-all-tickets'){
                    unset($menu->aside_menu_inactive[$i]);
                }
                $i++;
            }
        }
        $menu = json_encode($menu);
        update_option('aside_menu_inactive',$menu);

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblcustomeradmins` (
                  `staff_id` int(11) NOT NULL,
                  `customer_id` int(11) NOT NULL,
                  `date_assigned` text NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        $default_format = get_option('dateformat');
        if($default_format == 'Y-m-d|yyyy-mm-dd'){
            $f = 'Y-m-d|%Y-%m-%d';
        } else if($default_format == 'm/d/Y|mm/dd/yyyy'){
            $f = 'm/d/Y|%m/%d/%Y';
        } else if($default_format == 'Y/m/d|yyyy/mm/dd'){
            $f = 'Y/m/d|%Y/%m/%d';
        } else if($default_format == 'd.m.Y|dd.mm.yyyy'){
            $f = 'd.m.Y|%d.%m.%Y';
        } else if($default_format == 'd/m/Y|dd/mm/yyyy'){
            $f = 'd/m/Y|%d/%m/%Y';
        } else {
            $f = 'Y-m-d|%Y-%m-%d';
        }
        update_option('dateformat',$f);

        add_option('estimate_expiry_reminder_enabled',0);
        add_option('send_estimate_expiry_reminder_before',4);
        add_option('leads_default_source','');
        add_option('leads_default_status','');
        add_option('proposal_expiry_reminder_enabled',0);
        add_option('send_proposal_expiry_reminder_before',4);
        add_option('default_contact_permissions','a:6:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"5";i:5;s:1:"6";}');
        add_option('pdf_logo_width',120);

        $this->db->query("ALTER TABLE `tbltickets` ADD `assigned` INT NOT NULL DEFAULT '0' AFTER `ip`;");
        $assignments = $this->db->get('tblticketassignments')->result_array();
        foreach($assignments as $as){
            $this->db->where('ticketid',$as['ticketid']);
            $this->db->update('tbltickets',array('assigned'=>$as['staffid']));
        }
         $this->db->query("DROP TABLE tblticketassignments");
        $this->db->query("ALTER TABLE `tbltickets` ADD `project_id` INT NOT NULL DEFAULT '0' AFTER `date`;");

        $this->db->where('name','show_invoice_reminders_on_calendarsettings');
        $this->db->update('tbloptions',array('name'=>'show_invoice_reminders_on_calendar'));

        $this->db->query("ALTER TABLE `tbldepartments` ADD `host` VARCHAR(150) NULL AFTER `email`, ADD `password` VARCHAR(192) NULL AFTER `host`, ADD `encryption` VARCHAR(3) NULL AFTER `password`;");

        $this->db->query("ALTER TABLE `tbldepartments` CHANGE `password` `password` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
        $this->db->query("ALTER TABLE `tbldepartments` ADD `delete_after_import` INT NOT NULL DEFAULT '0' AFTER `encryption`;");

        $this->db->query("ALTER TABLE `tblleadsemailintegration` DROP `port`;");
        $this->db->query("ALTER TABLE `tblleadsemailintegration` ADD `delete_after_import` INT NOT NULL DEFAULT '0' AFTER `only_loop_on_unseen_emails`;");

        $this->db->query("ALTER TABLE `tblstaff` CHANGE `profile_image` `profile_image` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");

        $this->db->query("ALTER TABLE `tblestimates` ADD `is_expiry_notified` INT NOT NULL DEFAULT '0' AFTER `pipeline_order`;");
        $this->db->query("ALTER TABLE `tblproposals` ADD `is_expiry_notified` INT NOT NULL DEFAULT '0' AFTER `pipeline_order`;");

        $this->db->query("ALTER TABLE `tblmilestones` ADD `color` VARCHAR(10) NULL AFTER `project_id`;");
        $this->db->query("ALTER TABLE `tblcustomfields` ADD `only_admin` BOOLEAN NOT NULL DEFAULT FALSE AFTER `show_on_pdf`;");



        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('project', 'new-project-discussion-created-to-staff', 'New Project Discussion (Sent to project members)', 'New Project Discussion Created', '<p>Hello&nbsp;{staff_firstname}&nbsp;{staff_lastname}</p>\r\n<p>New project discussion created from&nbsp;{discussion_creator}</p>\r\n<p>Subject:&nbsp;{discussion_subject}</p>\r\n<p>Description:&nbsp;{discussion_description}</p>\r\n<p>You can view the discussion on the following link:&nbsp;{discussion_link}</p>\r\n<p>Kind Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('project', 'new-project-discussion-created-to-customer', 'New Project Discussion (Sent to customer contacts)', 'New Project Discussion Created', '<p>Hello&nbsp;{contact_firstname}&nbsp;{contact_lastname}</p>\r\n<p>New project discussion created from&nbsp;{discussion_creator}</p>\r\n<p>Subject:&nbsp;{discussion_subject}</p>\r\n<p>Description:&nbsp;{discussion_description}</p>\r\n<p>You can view the discussion on the following link:&nbsp;{discussion_link}</p>\r\n<p>Kind Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('project', 'new-project-file-uploaded-to-customer', 'New Project File Uploaded (Sent to customer contacts)', 'New Project File Uploaded', '<p>Hello&nbsp;{contact_firstname}&nbsp;{contact_lastname}</p>\r\n<p>New project&nbsp;file is uploaded on&nbsp;{project_name} from&nbsp;{file_creator}</p>\r\n<p>You can view the project on the following link:&nbsp;{project_link}</p>\r\n<p>Kind Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('project', 'new-project-file-uploaded-to-staff', 'New Project File Uploaded (Sent to project members)', 'New Project File Uploaded', '<p>Hello&nbsp;{staff_firstname}&nbsp;{staff_lastname}</p>\r\n<p>New project&nbsp;file is uploaded on&nbsp;{project_name} from&nbsp;{file_creator}</p>\r\n<p>You can view the project on the following link:&nbsp;{project_link}</p>\r\n<p>Kind Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('project', 'new-project-discussion-comment-to-customer', 'New Discussion Comment (Sent to customer contacts)', 'New Discussion Comment', '<p>Hello&nbsp;{contact_firstname}&nbsp;{contact_lastname}</p>\r\n<p>New discussion comment has been made on {discussion_subject} from&nbsp;{comment_creator}</p>\r\n<p>Discussion subject:&nbsp;{discussion_subject}</p>\r\n<p>Comment:&nbsp;{discussion_comment}</p>\r\n<p>You can view the discussion on the following link:&nbsp;{discussion_link}</p>\r\n<p>Kind Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('project', 'new-project-discussion-comment-to-staff', 'New Discussion Comment (Sent to project members)', 'New Discussion Comment', '<p>Hello&nbsp;{staff_firstname}&nbsp;{staff_lastname}</p>\r\n<p>New discussion comment has been made on {discussion_subject} from&nbsp;{comment_creator}</p>\r\n<p>Discussion subject:&nbsp;{discussion_subject}</p>\r\n<p>Comment:&nbsp;{discussion_comment}</p>\r\n<p>You can view the discussion on the following link:&nbsp;{discussion_link}</p>\r\n<p>Kind Regards,</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('project', 'staff-added-as-project-member', 'Staff added as project member', 'New project assigned to you', '<p>Hello&nbsp;{staff_firstname}&nbsp;{staff_lastname}</p>\r\n<p>New project has been assigned to you.</p>\r\n<p>You can view the project on the following link&nbsp;{project_link}</p>\r\n<p>Project name:&nbsp;{project_name}</p>\r\n<p>&nbsp;</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('estimate', 'estimate-expiry-reminder', 'Estimate Expiration Reminder', 'Estimate Expiration Reminder', '<p>Hello&nbsp;{client_company}</p>\r\n<p>The estimate with&nbsp;{estimate_number} will expire on&nbsp;{estimate_expirydate}</p>\r\n<p>You can view the estimate on the following link:&nbsp;{estimate_link}</p>\r\n<p>Regards,</p>\r\n<p>{email_signature}</p>', 'Estimate Expiration Reminder | CRM', '', 0, 1, 0),
('proposals', 'proposal-expiry-reminder', 'Proposal Expiration Reminder', 'Proposal Expiration Reminder', '<p>Hello&nbsp;{proposal_proposal_to}</p>\r\n<p>The proposal {proposal_subject} will expire on&nbsp;{proposal_open_till}</p>\r\n<p>You can view the proposal on the following link:&nbsp;{proposal_link}</p>\r\n<p>Regards,</p>\r\n<p>{email_signature}</p>', 'Proposal Expiration Reminder | CRM', '', 0, 1, 0);
");

 update_option('update_info_message', '<script>window.location.reload();</script>');
    }
}
