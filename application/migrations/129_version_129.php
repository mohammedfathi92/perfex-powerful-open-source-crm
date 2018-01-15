<?php
defined('BASEPATH') OR exit('No direct script access allowed');

@ini_set('memory_limit', '128M');
@ini_set('max_execution_time', 240);

class Migration_Version_129 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Merged to main.js
        if (file_exists(FCPATH . 'assets/js/leads.js')) {
            @unlink(FCPATH . 'assets/js/leads.js');
        }
        // Merged to main.js
        if (file_exists(FCPATH . 'assets/js/newsfeed.js')) {
            @unlink(FCPATH . 'assets/js/newsfeed.js');
        }

        $this->db->query("ALTER TABLE `tblcustomeradmins` ADD INDEX(`customer_id`);");
        $this->db->query("ALTER TABLE `tblcustomeradmins` ADD INDEX(`staff_id`);");
        $this->db->query("ALTER TABLE `tblclients` ADD INDEX(`country`);");
        add_option('purchase_key', '');
        add_option('estimates_pipeline_limit', 50);
        add_option('proposals_pipeline_limit', 50);
        $this->db->query("ALTER TABLE `tblmaillistscustomfieldvalues` ADD INDEX(`listid`);");
        $this->db->query("ALTER TABLE `tblmaillistscustomfieldvalues` ADD INDEX(`customfieldid`);");

        $this->db->query("ALTER TABLE `tblactivitylog` CHANGE `staffid` `staffid` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");

        $this->db->query("ALTER TABLE `tblannouncements` CHANGE `userid` `userid` VARCHAR(100) NOT NULL;");

        $announcements = $this->db->get('tblannouncements')->result_array();

        foreach ($announcements as $an) {
            $full = get_staff_full_name($an['userid']);
            $this->db->where('announcementid', $an['announcementid']);
            $this->db->update('tblannouncements', array(
                'userid' => $full
            ));
        }
        $activity_log = $this->db->get('tblactivitylog')->result_array();
        foreach ($activity_log as $ac) {
            if (is_numeric($ac['staffid'])) {
                $full = get_staff_full_name($ac['staffid']);
                $this->db->where('id', $ac['id']);
                $this->db->update('tblactivitylog', array(
                    'staffid' => $full
                ));
            }
        }

        $this->db->query("ALTER TABLE `tblcontractrenewals` CHANGE `renewed_by` `renewed_by` VARCHAR(100) NOT NULL;");
        $renewals_contracts = $this->db->get('tblcontractrenewals')->result_array();

        foreach ($renewals_contracts as $r) {
            $full = get_staff_full_name($r['renewed_by']);

            $this->db->where('id', $r['id']);
            $this->db->update('tblcontractrenewals', array(
                'renewed_by' => $full
            ));
        }

        $this->db->query("ALTER TABLE `tblemaillists` CHANGE `creator` `creator` VARCHAR(100) NOT NULL;");

        $mail_lists = $this->db->get('tblemaillists')->result_array();

        foreach ($mail_lists as $m) {
            $full = get_staff_full_name($m['creator']);

            $this->db->where('listid', $m['listid']);
            $this->db->update('tblemaillists', array(
                'creator' => $full
            ));
        }

        $this->db->query("ALTER TABLE `tblsurveys` DROP `creator`;");
        $this->db->query("ALTER TABLE `tblgoals` DROP `assigned`;");
        $this->db->query("ALTER TABLE `tblinvoicepaymentrecords` DROP `addedfrom`;");
        $this->db->query("ALTER TABLE `tblknowledgebase` DROP `createdby`, DROP `lasteditedby`;");

        $this->db->query("ALTER TABLE `tblleadactivitylog` ADD `full_name` VARCHAR(100) NULL AFTER `staffid`;");

        $lead_activity = $this->db->get('tblleadactivitylog')->result_array();
        foreach ($lead_activity as $l) {
            if (is_numeric($l['staffid']) && $l['staffid'] != 0) {

                $full = get_staff_full_name($l['staffid']);

                $this->db->where('id', $l['id']);
                $this->db->update('tblleadactivitylog', array(
                    'full_name' => $full
                ));
            }
        }

        $this->db->query("ALTER TABLE `tblgoals` DROP `addedfrom`;");

        $this->db->query("ALTER TABLE `tblprojectdiscussioncomments` CHANGE `full_name` `fullname` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");

        $discussion_comments = $this->db->get('tblprojectdiscussioncomments')->result_array();
        foreach ($discussion_comments as $c) {
            if ($c['staff_id'] != 0) {
                $full = get_staff_full_name($c['staff_id']);
                $this->db->where('id', $c['id']);
                $this->db->update('tblprojectdiscussioncomments', array(
                    'fullname' => $full
                ));
            }
        }

        $this->db->query("ALTER TABLE `tblprojectactivity` ADD `fullname` VARCHAR(100) NULL AFTER `contact_id`;");
        $project_activity = $this->db->get('tblprojectactivity')->result_array();
        foreach ($project_activity as $a) {
            if ($a['staff_id'] != 0) {
                $full = get_staff_full_name($a['staff_id']);
            } else if ($a['contact_id'] != 0) {
                $full = get_contact_full_name($a['contact_id']);
            } else {
                $full = '[CRON]';
            }

            $this->db->where('id', $a['id']);
            $this->db->update('tblprojectactivity', array(
                'fullname' => $full
            ));
        }

        $this->db->query("ALTER TABLE `tblnotifications` ADD `from_fullname` VARCHAR(100) NOT NULL AFTER `fromclientid`;");
        $notifications = $this->db->get('tblnotifications')->result_array();
        foreach ($notifications as $n) {
            if ($n['fromuserid'] != 0) {
                $full = get_staff_full_name($n['fromuserid']);
            } else if ($n['fromclientid'] != 0) {
                $full = get_contact_full_name($n['fromclientid']);
            }
            $this->db->where('id', $n['id']);
            $this->db->update('tblnotifications', array(
                'from_fullname' => $full
            ));
        }
        $this->db->query("ALTER TABLE `tblprojectmembers` ADD INDEX(`project_id`);");
        $this->db->query("ALTER TABLE `tblprojectmembers` ADD INDEX(`staff_id`);");

        $this->db->query("ALTER TABLE `tblleads` ADD `country` INT NOT NULL DEFAULT '0' AFTER `company`, ADD `zip` VARCHAR(15) NULL AFTER `country`, ADD `city` VARCHAR(100) NULL AFTER `zip`, ADD `state` VARCHAR(50) NULL AFTER `city`, ADD `address` VARCHAR(100) NULL AFTER `state`;");
        $this->db->query("ALTER TABLE `tblleads` ADD `title` VARCHAR(100) NULL AFTER `name`;");

        $this->db->query("ALTER TABLE `tblleads` ADD `last_lead_status` INT NOT NULL DEFAULT '0' AFTER `junk`;");

        $this->db->where('invoices_only', 1);
        $this->db->where('expenses_only', 1);
        $this->db->update('tblinvoicepaymentsmodes', array(
            'expenses_only' => 0
        ));

        $this->db->query("ALTER TABLE  `tblexpenses` ADD  `expense_name` VARCHAR( 500 ) NULL AFTER  `note` ;");
        $this->db->query("RENAME TABLE `tblinvoiceactivity` TO `tblsalesactivity`;");
        $this->db->query("ALTER TABLE  `tblsalesactivity` CHANGE  `invoiceid`  `rel_id` INT( 11 ) NOT NULL ;");
        $this->db->query("ALTER TABLE  `tblsalesactivity` ADD  `rel_type` VARCHAR( 20 ) NULL AFTER  `id` ;");

        $invoice_activity = $this->db->get('tblsalesactivity')->result_array();
        foreach ($invoice_activity as $a) {
            $this->db->where('id', $a['id']);
            $this->db->update('tblsalesactivity', array(
                'rel_type' => 'invoice'
            ));
        }

        $estimate_activity = $this->db->get('tblestimateactivity')->result_array();
        foreach ($estimate_activity as $a) {
            $this->db->insert('tblsalesactivity', array(
                'rel_id' => $a['estimateid'],
                'description' => $a['description'],
                'additional_data' => $a['additional_data'],
                'staffid' => $a['staffid'],
                'date' => $a['date'],
                'rel_type' => 'estimate'
            ));
        }



        $this->db->query("ALTER TABLE `tblsalesactivity` ADD `full_name` VARCHAR(100) NULL AFTER `staffid`;");

        $sales_activity = $this->db->get('tblsalesactivity')->result_array();

        foreach ($sales_activity as $l) {
            if (is_numeric($l['staffid']) && $l['staffid'] != 0) {
                $full = get_staff_full_name($l['staffid']);
            } else if ($l['staffid'] == NULL) {
                $full = '[CRON]';
            } else {
                $full = '';
            }

            $this->db->where('id', $l['id']);
            $this->db->update('tblsalesactivity', array(
                'full_name' => $full
            ));
        }

        $this->db->query("DROP TABLE tblestimateactivity");

        $this->db->query("RENAME TABLE  `tblsurveyquestionboxesdescription` TO  `tblformquestionboxesdescription` ;");
        $this->db->query("RENAME TABLE  `tblsurveyquestionboxes` TO  `tblformquestionboxes` ;");
        $this->db->query("RENAME TABLE  `tblsurveyquestions` TO  `tblformquestions` ;");
        $this->db->query("RENAME TABLE  `tblsurveyresults` TO  `tblformresults` ;");

        $this->db->query("ALTER TABLE  `tblformquestions` CHANGE  `surveyid`  `rel_id` INT( 11 ) NOT NULL ;");
        $this->db->query("ALTER TABLE  `tblformquestions` ADD  `rel_type` VARCHAR( 20 ) NULL AFTER  `rel_id` ;");
        $this->db->query("ALTER TABLE  `tblsurveyresultsets` DROP  `userid` ;");
        $this->db->query("ALTER TABLE  `tblformresults` CHANGE  `surveyid`  `rel_id` INT( 11 ) NOT NULL ;");
        $this->db->query("ALTER TABLE `tblformresults` ADD `rel_type` VARCHAR(20) NULL AFTER `rel_id`;");

        $questions = $this->db->get('tblformquestions')->result_array();
        foreach ($questions as $question) {
            $this->db->where('questionid', $question['questionid']);
            $this->db->update('tblformquestions', array(
                'rel_type' => 'survey'
            ));
        }

        $results = $this->db->get('tblformresults')->result_array();
        foreach ($results as $result) {
            $this->db->where('resultid', $result['resultid']);
            $this->db->update('tblformresults', array(
                'rel_type' => 'survey'
            ));
        }

        add_option('proposal_number_prefix', 'PRO-');

        $this->db->where('name', 'number_padding_invoice_and_estimate');
        $pd = $this->db->get('tbloptions')->row();
        if ($pd) {
            $pd = $pd->value;
        } else {
            $pd = 6;
        }

        add_option('number_padding_prefixes', $pd);

        $this->db->where('name', 'number_padding_invoice_and_estimate');
        $this->db->delete('tbloptions');


        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            ('tasks', 'task-marked-as-finished-to-contacts', 'english', 'Task Marked as Finished (Sent to customer contacts)', 'Task Marked as Finished - {task_name}', '<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Hi {contact_firstname} {contact_lastname}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{task_user_take_action} marked the following task as complete:</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\"><span style=\"background-color: inherit;\">Name: </span>{task_name}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\"><span style=\"background-color: inherit;\">Description: </span><span style=\"background-color: inherit;\">{task_description}</span></span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Due date: <span style=\"background-color: #ffffff;\">{task_duedate}<br /></span></span><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Please click on the following link to view: <span style=\"background-color: inherit;\">{task_link}</span></span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Kind regards,</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{email_signature}</span></div>', '{companyname} | CRM', '', 0, 1, 0);
            ");

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            ('tasks', 'task-added-attachment-to-contacts', 'english', 'New Attachment on Task (Sent to Customer Contacts)', 'New Attachment on Task - {task_name}', '<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Hi {contact_firstname} {contact_lastname}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{task_user_take_action} added an attachment on the following task:</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\"><span style=\"background-color: inherit;\">Name: </span>{task_name}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\"><span style=\"background-color: inherit;\">Description: </span><span style=\"background-color: inherit;\">{task_description}</span></span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Please click on the following link to view: <span style=\"background-color: inherit;\">{task_link}</span></span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Kind regards,</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{email_signature}</span></div>', '{companyname} | CRM', '', 0, 1, 0);
            ");


        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            ('tasks', 'task-commented-to-contacts', 'english', 'New Comment on Task (Sent to Customer Contacts)', 'New Comment on Task - {task_name}', '<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">A comment has been made on the following task:</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Name: &nbsp;{task_name}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Description: &nbsp;{task_description}<br />Comment: {task_comment}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Click on the following link to view: {task_link}</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">Kind regards,</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">&nbsp;</span></div>\r\n<div><span style=\"font-family: arial, helvetica, sans-serif; font-size: 12pt;\">{email_signature}</span></div>', '{companyname} | CRM', '', 0, 1, 0);");

        $this->db->query("ALTER TABLE  `tblinvoicepaymentrecords` ADD INDEX (  `invoiceid` ) ;");
        $this->db->query("ALTER TABLE  `tblinvoicepaymentrecords` ADD INDEX (  `paymentmethod` ) ;");

        $this->db->query("ALTER TABLE  `tblleads` ADD INDEX (  `lastcontact` ) ;");
        $this->db->query("ALTER TABLE  `tblleads` ADD INDEX (  `leadorder` ) ;");
        $this->db->query("ALTER TABLE  `tblleads` ADD INDEX (  `dateadded` ) ;");

        $this->db->query("ALTER TABLE `tblstafftasks` ADD INDEX(`milestone`);");
        $this->db->query("ALTER TABLE `tblstafftasks` ADD INDEX(`kanban_order`);");

        $this->db->query("RENAME TABLE `tblinvoiceitemslist` TO `tblitems`;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblitems_in` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `rel_id` int(11) NOT NULL,
                      `rel_type` varchar(15) NOT NULL,
                      `description` mediumtext NOT NULL,
                      `long_description` mediumtext,
                      `qty` decimal(11,2) NOT NULL,
                      `rate` decimal(11,2) NOT NULL,
                      `item_order` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `rel_id` (`rel_id`),
                      KEY `rel_type` (`rel_type`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $estimate_items = $this->db->get('tblestimateitems')->result_array();
        foreach ($estimate_items as $item) {
            $this->db->insert('tblitems_in', array(
                'rel_id' => $item['estimateid'],
                'rel_type' => 'estimate',
                'description' => $item['description'],
                'long_description' => $item['long_description'],
                'qty' => $item['qty'],
                'rate' => $item['rate'],
                'item_order' => $item['item_order']
            ));
            $item_id = $this->db->insert_id();

            $this->db->where('rel_type', 'estimate');
            $this->db->where('rel_id', $item['estimateid']);
            $this->db->where('itemid', $item['id']);
            $this->db->update('tblitemstax', array(
                'itemid' => $item_id
            ));

        }

        $this->db->query("DROP TABLE tblestimateitems");

        $invoice_items = $this->db->get('tblinvoiceitems')->result_array();
        foreach ($invoice_items as $item) {
            $this->db->insert('tblitems_in', array(
                'rel_id' => $item['invoiceid'],
                'rel_type' => 'invoice',
                'description' => $item['description'],
                'long_description' => $item['long_description'],
                'qty' => $item['qty'],
                'rate' => $item['rate'],
                'item_order' => $item['item_order']
            ));

            $item_id = $this->db->insert_id();
            $this->db->where('item_id', $item['id']);
            $this->db->update('tblitemsrelated', array(
                'item_id' => $item_id
            ));

            $this->db->where('rel_type', 'invoice');
            $this->db->where('rel_id', $item['invoiceid']);
            $this->db->where('itemid', $item['id']);
            $this->db->update('tblitemstax', array(
                'itemid' => $item_id
            ));
        }

        $this->db->query("DROP TABLE tblinvoiceitems");


        $this->db->query("ALTER TABLE `tblproposals` ADD `subtotal` DECIMAL(11,2) NOT NULL AFTER `total`, ADD `total_tax` DECIMAL(11,2) NOT NULL DEFAULT '0' AFTER `subtotal`, ADD `adjustment` DECIMAL(11,2) NULL AFTER `total_tax`, ADD `discount_percent` DECIMAL(11,2) NOT NULL AFTER `adjustment`, ADD `discount_total` DECIMAL(11,2) NOT NULL AFTER `discount_percent`, ADD `discount_type` VARCHAR(30) NULL AFTER `discount_total`, ADD `show_quantity_as` INT NOT NULL DEFAULT '1' AFTER `discount_type`;");

        $this->db->query("ALTER TABLE  `tblproposals` ADD  `country` INT NOT NULL DEFAULT  '0' AFTER  `proposal_to` ;");

        $this->db->query("ALTER TABLE `tblproposals` ADD `zip` VARCHAR(50) NULL AFTER `country`, ADD `state` VARCHAR(100) NULL AFTER `zip`, ADD `city` VARCHAR(100) NULL AFTER `state`;");

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.2.9</h4>
                <p>
                    This window will reload automaticaly in 10 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },10000);
        </script>
        ');
    }
}
