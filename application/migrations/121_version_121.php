<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_121 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->where('name','paymentmethod_stripe_api_publishable_key');
        $pb_stripe_api = $this->db->get('tbloptions')->row()->value;

        if($pb_stripe_api != ''){
            if($this->session->userdata('update_encryption_key') != ''){
                $this->encryption->initialize(array('key'=>$this->session->userdata('update_encryption_key')));
                update_option('paymentmethod_stripe_api_publishable_key',trim($this->encryption->decrypt($pb_stripe_api)));
            }
        }
        $content = "THIS FOLDER IS DEPRECATED AND IS NOT USED ANYMORE. IF YOU DONT HAVE ANY CUSTOM WORK YOU CAN DELETE THIS FOLDER.";
        $fp = fopen(APPPATH . "controllers/getaways/README.txt","wb");
        @fwrite($fp,$content);
        fclose($fp);

        $fp = fopen(APPPATH . "libraries/getaways/README.txt","wb");
        @fwrite($fp,$content);
        fclose($fp);

        update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.2.1</h4>
                <p>
                    This window will reload automaticaly in 8 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },8000);
        </script>
        ');

    }
}
