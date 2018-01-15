<?php if(isset($contracts_by_type_chart)){ ?>
<script>
    var contracts_by_type = '<?php echo $contracts_by_type_chart; ?>';
</script>
<?php } ?>
<script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables/datatables.min.js'); ?>"></script>
<?php app_jquery_validation_plugin_js($locale); ?>
<?php app_select_plugin_js($locale); ?>
<script src="<?php echo base_url('assets/plugins/datetimepicker/jquery.datetimepicker.full.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/Chart.js/Chart.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js'); ?>"></script>
<?php echo app_script(template_assets_path().'/js','global.js'); ?>
<script src="<?php echo base_url('assets/plugins/lightbox/js/lightbox.min.js'); ?>"></script>
<?php if(is_client_logged_in()){ ?>
<script src="<?php echo base_url('assets/plugins/dropzone/min/dropzone.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/app-build/moment.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/jquery-comments/js/jquery-comments.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/jquery-circle-progress/circle-progress.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/gantt/js/jquery.fn.gantt.min.js'); ?>"></script>
<?php if(get_option('dropbox_app_key') != ''){ ?>
<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="<?php echo get_option('dropbox_app_key'); ?>"></script>
<?php } ?>
<script src="<?php echo base_url('assets/plugins/fullcalendar/fullcalendar.min.js'); ?>"></script>
<?php if(file_exists(FCPATH.'assets/plugins/fullcalendar/locale/'.$locale.'.js')){ ?>
<script src="<?php echo base_url('assets/plugins/fullcalendar/locale/'.$locale.'.js'); ?>"></script>
<?php } ?>
<?php echo app_script(template_assets_path().'/js','clients.js'); ?>
<?php } ?>
<?php
// DONT REMOVE THIS LINE
do_action('customers_after_js_scripts_load');
?>
<?php
$alertclass = "";
if($this->session->flashdata('message-success')){
    $alertclass = "success";
} else if ($this->session->flashdata('message-warning')){
    $alertclass = "warning";
} else if ($this->session->flashdata('message-info')){
    $alertclass = "info";
} else if ($this->session->flashdata('message-danger')){
    $alertclass = "danger";
}
if($alertclass != ''){
    $alert_message = '';
    $alert = $this->session->flashdata('message-'.$alertclass);
    if(is_array($alert)){
        foreach($alert as $alert_data){
            $alert_message.= '<span>'.$alert_data . '</span><br />';
        }
    } else {
        $alert_message .= $alert;
    }
    ?>
    <script>
        $(function(){
            alert_float('<?php echo $alertclass; ?>','<?php echo $alert_message; ?>');
        });
    </script>
    <?php } ?>
