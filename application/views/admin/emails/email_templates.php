<?php init_head(); ?>
<div id="wrapper">
    <div class="content email-templates">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('email_templates'); ?></h4>
                                 <hr class="hr-panel-heading" />
                                 <h4 class="bold well email-template-heading">
                                    <?php echo _l('email_template_ticket_fields_heading'); ?>
                                    <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/ticket'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/ticket'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>
                                 </h4>
                                 <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($tickets as $ticket_template){ ?>
                                            <tr>
                                                <td class="<?php if($ticket_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$ticket_template['emailtemplateid']); ?>"><?php echo $ticket_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $ticket_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($ticket_template['active'] == '1' ? 'disable/' : 'enable/').$ticket_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($ticket_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('estimates'); ?>
                                     <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/estimate'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/estimate'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($estimate as $estimate_template){ ?>
                                            <tr>
                                                <td class="<?php if($estimate_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$estimate_template['emailtemplateid']); ?>"><?php echo $estimate_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $estimate_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($estimate_template['active'] == '1' ? 'disable/' : 'enable/').$estimate_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($estimate_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('email_template_contracts_fields_heading'); ?>
                                       <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/contract'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/contract'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($contracts as $contract_template){ ?>
                                            <tr>
                                                <td class="<?php if($contract_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$contract_template['emailtemplateid']); ?>"><?php echo $contract_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $contract_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($contract_template['active'] == '1' ? 'disable/' : 'enable/').$contract_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($contract_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('email_template_invoices_fields_heading'); ?>
                                      <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/invoice'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/invoice'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($invoice as $invoice_template){ ?>
                                            <tr>
                                                <td class="<?php if($invoice_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$invoice_template['emailtemplateid']); ?>"><?php echo $invoice_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $invoice_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($invoice_template['active'] == '1' ? 'disable/' : 'enable/').$invoice_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($invoice_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                              <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('credit_note'); ?>
                                      <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/credit_note'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/credit_note'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($credit_notes as $credit_note_template){ ?>
                                            <tr>
                                                <td class="<?php if($credit_note_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$credit_note_template['emailtemplateid']); ?>"><?php echo $credit_note_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $credit_note_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($credit_note_template['active'] == '1' ? 'disable/' : 'enable/').$credit_note_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($credit_note_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('tasks'); ?>
                                       <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/tasks'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/tasks'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($tasks as $task_template){ ?>
                                            <tr>
                                                <td class="<?php if($task_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$task_template['emailtemplateid']); ?>"><?php echo $task_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $task_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($task_template['active'] == '1' ? 'disable/' : 'enable/').$task_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($task_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('email_template_clients_fields_heading'); ?>
                                      <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/client'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/client'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($client as $client_template){ ?>
                                            <tr>
                                                <td class="<?php if($client_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$client_template['emailtemplateid']); ?>"><?php echo $client_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $client_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($client_template['active'] == '1' ? 'disable/' : 'enable/').$client_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($client_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('email_template_proposals_fields_heading'); ?>
                                     <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/proposals'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/proposals'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($proposals as $proposal_template){ ?>
                                            <tr>
                                                <td class="<?php if($proposal_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$proposal_template['emailtemplateid']); ?>"><?php echo $proposal_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $proposal_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($proposal_template['active'] == '1' ? 'disable/' : 'enable/').$proposal_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($proposal_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('projects'); ?>
                                      <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/project'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/project'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>
                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($projects as $project_template){ ?>
                                            <tr>
                                                <td class="<?php if($project_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$project_template['emailtemplateid']); ?>"><?php echo $project_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $project_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($project_template['active'] == '1' ? 'disable/' : 'enable/').$project_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($project_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('staff_members'); ?>
                                       <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/staff'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/staff'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($staff as $staff_template){ ?>
                                            <tr>
                                                <td class="<?php if($staff_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$staff_template['emailtemplateid']); ?>"><?php echo $staff_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $staff_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($staff_template['active'] == '1' ? 'disable/' : 'enable/').$staff_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($staff_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h4 class="bold well email-template-heading">
                                    <?php echo _l('leads'); ?>
                                       <?php if($hasPermissionEdit){ ?>
                                      <a href="<?php echo admin_url('emails/disable_by_type/leads'); ?>" class="pull-right mleft5 mright25"><small><?php echo _l('disable_all'); ?></small></a>
                                      <a href="<?php echo admin_url('emails/enable_by_type/leads'); ?>" class="pull-right"><small><?php echo _l('enable_all'); ?></small></a>
                                     <?php } ?>

                                    </h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('email_templates_table_heading_name'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($leads as $lead_template){ ?>
                                            <tr>
                                                <td class="<?php if($lead_template['active'] == 0){echo 'text-throught';} ?>">
                                                    <a href="<?php echo admin_url('emails/email_template/'.$lead_template['emailtemplateid']); ?>"><?php echo $lead_template['name']; ?></a>
                                                    <?php if(ENVIRONMENT !== 'production'){ ?>
                                                    <br/><small><?php echo $lead_template['slug']; ?></small>
                                                    <?php } ?>
                                                    <?php if($hasPermissionEdit){ ?>
                                                    <a href="<?php echo admin_url('emails/'.($lead_template['active'] == '1' ? 'disable/' : 'enable/').$lead_template['emailtemplateid']); ?>" class="pull-right"><small><?php echo _l($lead_template['active'] == 1 ? 'disable' : 'enable'); ?></small></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
