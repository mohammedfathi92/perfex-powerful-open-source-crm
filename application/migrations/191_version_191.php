<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_191 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Nothing to do here just update database number
        update_option('update_info_message', '<div class="col-md-12">
        <div class="alert alert-success bold">
            <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.9.1</h4>
            <p>
                This window will reload automaticaly in 10 seconds and will try to clear your browser/cloudflare cache, however its recomended to clear your browser cache manually.
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
