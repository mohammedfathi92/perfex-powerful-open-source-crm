<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_101 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Leads
        $fields = array(
            'leadid' => array(
                'type' => 'INT',
                'default' => NULL
                )
            );

        $this->dbforge->add_column('tblclients', $fields);

        // Payment modes
        $fields = array(
            'description' => array(
                'type' => 'text',
                'default' => NULL
                ),
            'active' => array(
                'type' => 'BOOLEAN',
                'default' => 1
                )
            );

        $this->dbforge->add_column('tblinvoicepaymentsmodes', $fields);

        // Modify payment mode to integer so we can add strings
        $this->db->query('ALTER TABLE `tblinvoicepaymentrecords` CHANGE `paymentmode` `paymentmode` VARCHAR(40) NULL DEFAULT NULL');
        // Add invoice payment records additional fields
        $fields = array(
            'transactionid' => array(
                'type' => 'MEDIUMTEXT',
                'default' => NULL
                )
            );
        $this->dbforge->add_column('tblinvoicepaymentrecords', $fields);

        // Set field addedfrom so can be null;
        $this->db->query('ALTER TABLE `tblinvoicepaymentrecords` CHANGE `addedfrom` `addedfrom` INT(11) NULL DEFAULT NULL');
        // Change field staffid so can be null in case client recorded invoice
        $this->db->query('ALTER TABLE `tblinvoiceactivity` CHANGE `staffid` `staffid` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
        // Set added from to null default so cron can add invoices
        $this->db->query('ALTER TABLE `tblinvoices` CHANGE `addedfrom` `addedfrom` INT(11) NULL;');
        // Invoices
        $fields = array(
            'allowed_payment_modes' => array(
                'type' => 'MEDIUMTEXT',
                'default' => NULL
                ),
            'token' => array(
                'type' => 'MEDIUMTEXT',
                'default' => NULL
                ),
            'recurring' => array(
                'type' => 'INT',
                'default' => 0
                ),
            'is_recurring_from' => array(
                'type' => 'INT',
                'default' => 0
                ),
            'last_recurring_date' => array(
                'type' => 'DATE',
                'default' => NULL
                )
            );

        $this->dbforge->add_column('tblinvoices', $fields);

        // Add new table tblstafftasksattachments
        $this->db->query('CREATE TABLE IF NOT EXISTS `tblstafftasksattachments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `file_name` mediumtext NOT NULL,
          `original_file_name` mediumtext NOT NULL,
          `dateadded` datetime NOT NULL,
          `taskid` int(11) NOT NULL,
          PRIMARY KEY (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
        // Add additional options

        // Invoices settings cron job
        add_option('create_invoice_from_recurring_only_on_paid_invoices', 0);
        add_option('send_renewed_invoice_from_recurring_to_email', 0);

        add_option('limit_top_search_bar_results_to', 10);

        // Create the tasks attachments folder
        if(!is_dir(TASKS_ATTACHMENTS_FOLDER)){
            mkdir(TASKS_ATTACHMENTS_FOLDER);
            fopen(TASKS_ATTACHMENTS_FOLDER . '.htaccess', 'w');
            $fp = fopen(TASKS_ATTACHMENTS_FOLDER.'.htaccess','a+');
            if($fp)
            {
                fwrite($fp,'Order Deny,Allow'.PHP_EOL.'Deny from all');
                fclose($fp);
            }
        }

    }
}
