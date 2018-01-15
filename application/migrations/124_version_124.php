<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_124 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $config_path = APPPATH . 'config/config.php';
        $this->load->helper('file');
        @chmod($config_path, FILE_WRITE_MODE);
        $config_file = read_file($config_path);
        $config_file = trim($config_file);

        $config_file = str_replace("\$config['global_xss_filtering'] = FALSE;", "\$config['global_xss_filtering'] = TRUE;", $config_file);
        $fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE);
        flock($fp, LOCK_EX);
        fwrite($fp, $config_file, strlen($config_file));
        flock($fp, LOCK_UN);
        fclose($fp);
        @chmod($config_path, FILE_READ_MODE);

        $cfields_values = $this->db->get('tblcustomfieldsvalues')->result_array();
        foreach($cfields_values as $cf){
            $this->db->where('id',$cf['fieldid']);
            $row = $this->db->get('tblcustomfields')->row();
            if($row){
                if($row->type == 'date_picker'){
                    $date = $cf['value'];
                    if(!empty($date)){
                        $d = date('Y-m-d',strtotime($date));
                        if(_startsWith($d,'197') || _startsWith($d,'196')){
                            $d = @date_format(date_create_from_format(get_current_date_format(true), $date), 'Y-m-d');
                            if(!$d){
                                if(strpos($date,'.') !== false){
                                    $_temp = explode('.',$date);
                                    $d = $_temp[2] . '-'.$_temp[0] . '-' .$_temp[1];
                                } else if(strpos($date,'/') !== false){
                                    $_temp = explode('/',$date);
                                    $d = $_temp[2] . '-'.$_temp[1] . '-' .$_temp[0];
                                }
                            }
                        }
                        if($d){
                            $this->db->where('id',$cf['id']);
                            $this->db->update('tblcustomfieldsvalues',array('value'=>$d));
                        }
                    }
                }
            }
        }

        $this->db->query("ALTER TABLE `tblleads` ADD `client_id` INT NOT NULL DEFAULT '0' AFTER `is_public`;");
        $customers = $this->db->get('tblclients')->result_array();

        foreach($customers as $c){
            if(!is_null($c['leadid'])){
                $this->db->where('id',$c['leadid']);
                $this->db->update('tblleads',array('client_id'=>$c['userid']));
            }
        }

        $this->db->where('status',0);
        $proposals = $this->db->get('tblproposals')->result_array();

        foreach($proposals as $proposal){
            $this->db->where('id',$proposal['id']);
            $this->db->update('tblproposals',array('status'=>6));
        }

        $this->db->query("ALTER TABLE `tblestimates` ADD INDEX(`project_id`);");
        $this->db->query("ALTER TABLE `tblinvoices` ADD INDEX(`project_id`);");

        $this->db->query("ALTER TABLE `tblstafftasks` CHANGE `hourly_rate` `hourly_rate` DECIMAL(11,2) NOT NULL DEFAULT '0.00';");

        $this->db->query('ALTER TABLE `tblestimates` ADD `prefix` VARCHAR(50) NULL AFTER `number`;');
        $this->db->query('ALTER TABLE `tblinvoices` ADD `prefix` VARCHAR(50) NULL AFTER `number`;');

        $this->db->query("ALTER TABLE `tblemailtemplates` ADD `language` VARCHAR(40) NULL AFTER `slug`;");

        $email_templates = $this->db->get('tblemailtemplates')->result_array();

        foreach($email_templates as $template){
            $temp_data = array('language'=>'english');
            $this->db->update('tblemailtemplates',$temp_data);
        }

        $email_templates = $this->db->get('tblemailtemplates')->result_array();

        foreach(list_folders(APPPATH .'language') as $language){
            if($language != 'english'){
                foreach($email_templates as $template){
                    if(total_rows('tblemailtemplates',array('slug'=>$template['slug'],'language'=>$language)) == 0){
                        $data = array();
                        $data['slug'] = $template['slug'];
                        $data['type'] = $template['type'];
                        $data['language'] = $language;
                        $data['name'] = $template['name'] . ' ['.$language.']';
                        $data['subject'] = $template['subject'];
                        $data['message'] = '';
                        if(get_option('active_language') != 'english' && get_option('active_language') == $language){
                            $data['message'] = $template['message'];
                        }
                        $data['fromname'] = $template['fromname'];
                        $data['plaintext'] = $template['plaintext'];
                        $data['active'] = $template['active'];
                        $data['order'] = $template['order'];

                        $this->db->insert('tblemailtemplates',$data);
                    }
                }
            }
        }

        $invoices = $this->db->get('tblinvoices')->result_array();
        foreach($invoices as $invoice){
            $this->db->where('id',$invoice['id']);
            $this->db->update('tblinvoices',array('prefix'=>get_option('invoice_prefix')));
        }

        $estimates = $this->db->get('tblestimates')->result_array();
        foreach($estimates as $estimate){
            $this->db->where('id',$estimate['id']);
            $this->db->update('tblestimates',array('prefix'=>get_option('estimate_prefix')));
        }

        add_option('show_expense_reminders_on_calendar',1);

        $this->db->query("UPDATE `tbloptions` SET `value` = replace(value, '\"invoice_items\",\"permission\":\"is_admin\"', '\"invoice_items\",\"permission\":\"items\"')");

        $this->db->query("ALTER TABLE `tblstafftasks` ADD `recurring_type` VARCHAR(10) NULL AFTER `finished`, ADD `repeat_every` INT NULL AFTER `recurring_type`, ADD `recurring` INT NOT NULL DEFAULT '0' AFTER `repeat_every`, ADD `recurring_ends_on` BOOLEAN NOT NULL DEFAULT FALSE AFTER `recurring`, ADD `custom_recurring` BOOLEAN NOT NULL DEFAULT FALSE AFTER `recurring_ends_on`, ADD `last_recurring_date` DATE NULL AFTER `custom_recurring`;");

        $this->db->query("ALTER TABLE `tblinvoicepaymentsmodes` ADD `invoices_only` INT NOT NULL DEFAULT '0' AFTER `description`, ADD `expenses_only` INT NOT NULL DEFAULT '0' AFTER `invoices_only`;");

        $this->db->query("ALTER TABLE `tblstafftasks` CHANGE `recurring_ends_on` `recurring_ends_on` DATE NULL;");


        $this->db->query("ALTER TABLE `tblstaff` ADD `direction` VARCHAR(3) NULL AFTER `default_language`;");
        $this->db->query("ALTER TABLE `tblcontacts` ADD `direction` VARCHAR(3) NULL AFTER `profile_image`;");

        $this->db->query("ALTER TABLE `tblinvoices` ADD `cancel_overdue_reminders` INT NOT NULL DEFAULT '0' AFTER `last_overdue_reminder`;");

        $this->db->query("ALTER TABLE `tblprojectdiscussioncomments` ADD `discussion_type` VARCHAR(10) NOT NULL AFTER `discussion_id`;");

        $this->db->query("ALTER TABLE `tblprojectfiles` ADD `last_activity` DATETIME NULL AFTER `dateadded`;");
        $this->db->query("ALTER TABLE `tblprojectfiles` ADD `subject` VARCHAR(500) NULL AFTER `file_name`;");
        $this->db->query("ALTER TABLE `tblprojectfiles` ADD `description` TEXT NULL AFTER `subject`;");
        $this->db->query("INSERT INTO `tblpermissions` (`name`, `shortname`) VALUES ('Items', 'items');");

        $discussion_comments = $this->db->get('tblprojectdiscussioncomments')->result_array();
        foreach($discussion_comments as $comment){
            $this->db->where('id',$comment['id']);
            $this->db->update('tblprojectdiscussioncomments',array('discussion_type'=>'regular'));
        }

        $project_files = $this->db->get('tblprojectfiles')->result_array();
        foreach($project_files as $file){
            $this->db->where('id',$file['id']);
            $this->db->update('tblprojectfiles',array('subject'=>$file['file_name']));
        }

        add_option('only_show_contact_tickets',0);
        add_option('exclude_invoice_from_client_area_with_draft_status',1);

        if(get_option('last_cron_run') != ''){
            add_option('cron_has_run_from_cli',1);
            add_option('hide_cron_is_required_message',1);
        } else {
            add_option('cron_has_run_from_cli',0);
            add_option('hide_cron_is_required_message',0);
        }


        update_option('update_info_message',' <script>
            setTimeout(function(){
                window.location.reload();
            },1000);
        </script>');

    }
}
