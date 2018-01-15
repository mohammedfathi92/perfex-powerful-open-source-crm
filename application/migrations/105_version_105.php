<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_105 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Add task attachment filetype
        $this->db->query("ALTER TABLE `tblstafftasksattachments` ADD `filetype` VARCHAR(50) NULL AFTER `original_file_name`;");

        // Merge tickets
        if (file_exists(APPPATH . 'views/admin/tickets/predefined_replies')) {
            delete_dir(APPPATH . 'views/admin/predefined_replies');
            unlink(APPPATH . 'controllers/admin/Predifined_replies.php');
            unlink(APPPATH . 'models/Predefined_replies_model.php');
        }

        if (file_exists(APPPATH . 'views/admin/tickets/priorities')) {
            delete_dir(APPPATH . 'views/admin/priorities');
            unlink(APPPATH . 'controllers/admin/Priorities.php');
            unlink(APPPATH . 'models/Priority_model.php');
        }

        if (file_exists(APPPATH . 'views/admin/tickets/services')) {
            delete_dir(APPPATH . 'views/admin/services');
            unlink(APPPATH . 'controllers/admin/Services.php');
            unlink(APPPATH . 'models/Services_model.php');
        }
        if (file_exists(APPPATH . 'views/admin/tickets/tickets_statuses')) {
            delete_dir(APPPATH . 'views/admin/tickets_statuses');
            unlink(APPPATH . 'controllers/admin/Ticket_statuses.php');
            unlink(APPPATH . 'models/Ticket_statuses_model.php');
        }
        // Merge media to utilities
        if (file_exists(APPPATH . 'views/admin/utilities')) {
            unlink(APPPATH . 'controllers/admin/Media.php');
            delete_dir(APPPATH . 'views/admin/media');
        }

        if (file_exists(APPPATH . 'helpers/db_autoload_helper.php')) {
            unlink(APPPATH . 'helpers/db_autoload_helper.php');
        }
        $current_format = get_option('dateformat');
        if($current_format == 'Y.m.d|yyyy.mm.dd' || $current_format == 'd.m.Y|dd.mm.yyyy'){
            update_option('dateformat','Y-m-d|yyyy-mm-dd');
        }

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES ('contract', 'contract-expiration', 'Contract Expiration', 'Contract Expiration Reminder', '', 'Perfex', NULL, '0', '1', '1');");

        // estimate items
        $this->db->query("ALTER TABLE `tblestimateitems` ADD `description` MEDIUMTEXT NOT NULL , ADD `long_description` TEXT NULL , ADD `rate` DECIMAL(11,2) NOT NULL , ADD `taxid` INT NULL , ADD `item_order` INT NULL ;");
        // invoice items
        $this->db->query("ALTER TABLE `tblinvoiceitems` ADD `description` MEDIUMTEXT NOT NULL , ADD `long_description` TEXT NULL , ADD `rate` DECIMAL(11,2) NOT NULL , ADD `taxid` INT NULL , ADD `item_order` INT NULL ;");

        $this->db->order_by('id', 'asc');
        $items = $this->db->get('tblinvoiceitems')->result_array();

        if (count($items) > 0) {
            $i = 1;
            foreach ($items as $item) {
                    if ($item['expenseid'] == 0) {
                        $this->db->where('id', $item['itemid']);
                        $main_item = $this->db->get('tblinvoiceitemslist')->row();

                        $this->db->where('id', $item['id']);
                        $this->db->update('tblinvoiceitems', array(
                            'item_order' => $i,
                            'taxid' => $main_item->tax,
                            'rate' => $main_item->rate,
                            'long_description' => $main_item->long_description,
                            'description' => $main_item->description
                        ));
                    } else {
                        $this->db->where('id', $item['expenseid']);
                        $main_expense = $this->db->get('tblexpenses')->row();

                        $this->db->where('id', $main_expense->category);
                        $category = $this->db->get('tblexpensescategories')->row();

                        $this->db->where('id', $item['id']);
                        $this->db->update('tblinvoiceitems', array(
                            'item_order' => 1,
                            'taxid' => $main_expense->tax,
                            'rate' => $main_expense->amount,
                            'long_description' => $category->description,
                            'description' => $category->name
                        ));
                    }
                $i++;
            }
        }

        $this->db->query("ALTER TABLE `tblinvoiceitems` DROP `expenseid`;");
        $this->db->query("ALTER TABLE `tblinvoiceitems` DROP `itemid`;");
        // Estimates
        $this->db->order_by('id', 'asc');
        $items = $this->db->get('tblestimateitems')->result_array();
        if (count($items) > 0) {
            $i = 1;
            foreach ($items as $item) {
                    $this->db->where('id', $item['itemid']);
                    $main_item = $this->db->get('tblinvoiceitemslist')->row();

                    $this->db->where('id', $item['id']);
                    $this->db->update('tblestimateitems', array(
                        'item_order' => $i,
                        'taxid' => $main_item->tax,
                        'rate' => $main_item->rate,
                        'long_description' => $main_item->long_description,
                        'description' => $main_item->description
                    ));
                $i++;
            }
        }
        $this->db->query("ALTER TABLE `tblestimateitems` DROP `itemid`;");

    }
}
