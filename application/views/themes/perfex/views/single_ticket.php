<div class="row">
    <?php if($ticket->project_id != 0){ ?>
    <div class="col-md-12">
        <div class="alert alert-info">
            <?php echo _l('ticket_linked_to_project','<a href="'.site_url('clients/project/'.$ticket->project_id).'">'.get_project_name_by_id($ticket->project_id).'</a>') ;?>
        </div>
    </div>
    <?php } ?>
    <?php set_ticket_open($ticket->clientread,$ticket->ticketid,false); ?>
    <?php echo form_hidden('ticket_id',$ticket->ticketid); ?>
    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-heading">
                <?php echo _l('clients_single_ticket_information_heading'); ?>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4>
                            #<?php echo $ticket->ticketid; ?> - <?php echo $ticket->subject; ?>
                        </h4>
                        <hr />
                        <p>
                            <?php echo _l('clients_ticket_single_department', '<span class="pull-right bold">'.$ticket->department_name.'</span>'); ?>
                        </p>
                        <hr />
                        <p>
                            <?php echo _l('clients_ticket_single_submitted','<span class="pull-right bold">'._dt($ticket->date).'</span>'); ?>
                        </p>
                        <hr />
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo _l('clients_ticket_single_status'); ?>
                            </div>
                            <div class="col-md-8">
                                <div class="ticket-status-inline">
                                    <span class="label pull-right bold" style="background:<?php echo $ticket->statuscolor; ?>">
                                        <?php echo ticket_status_translate($ticket->ticketstatusid); ?>
                                        <?php if(get_option('allow_customer_to_change_ticket_status') == 1){ ?>
                                        <i class="fa fa-pencil-square-o pointer toggle-change-ticket-status"></i></span>
                                        <?php } ?>
                                    </div>
                                    <?php if(get_option('allow_customer_to_change_ticket_status') == 1){ ?>
                                    <div class="ticket-status hide">
                                        <div class="input-group">
                                            <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" id="ticket_status_single" class="form-control" name="ticket_status_single">
                                                <?php foreach($ticket_statuses as $status){
                                                    if($status['ticketstatusid'] == 3 || $status['ticketstatusid'] == 4){continue;}
                                                    ?>
                                                    <option value="<?php echo $status['ticketstatusid']; ?>" <?php if($status['ticketstatusid'] == $ticket->ticketstatusid){echo 'selected';}?>>
                                                        <?php echo ticket_status_translate($status['ticketstatusid']); ?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                                <span class="input-group-addon"><i class="fa fa-remove pointer toggle-change-ticket-status"></i></span>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <hr />
                                <p>
                                    <?php echo _l('clients_ticket_single_priority','<span class="pull-right bold">'.ticket_priority_translate($ticket->priorityid).'</span>'); ?>
                                </p>
                                <?php
                                $custom_fields = get_custom_fields('tickets',array('show_on_client_portal'=>1));
                                foreach($custom_fields as $field){ ?>
                                <hr />
                                <p class="bold"><?php echo $field['name']; ?>: <span class="pull-right bold"><?php echo get_custom_field_value($ticket->ticketid,$field['id'],'tickets'); ?></span></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <a href="#" class="btn btn-info btn-block single-ticket-add-reply"><?php echo _l('clients_ticket_single_add_reply_btn'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'ticket-reply')); ?>
                <input type="hidden" name="userid" value="<?php echo $client->userid; ?>">
                <div class="panel_s single-ticket-reply-area" <?php if (form_error('message') == ''){ ?>style="display:none;" <?php } ?>>
                    <div class="panel-heading">
                        <?php echo _l('clients_ticket_single_add_reply_heading'); ?>
                    </div>
                    <div class="panel-body">
                        <textarea name="message" class="form-control" rows="15"></textarea>
                        <?php echo form_error('message'); ?>
                    </div>
                    <div class="panel-footer attachments_area">
                        <div class="row attachments">
                            <div class="attachment">
                                <div class="col-md-6 col-md-offset-3">
                                  <div class="form-group">
                                    <label for="attachment" class="control-label"><?php echo _l('clients_ticket_attachments'); ?></label>
                                    <div class="input-group">
                                     <input type="file" extension="<?php echo str_replace('.','',get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[0]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>">
                                     <span class="input-group-btn">
                                        <button class="btn btn-success add_more_attachments p8-half" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mtop20 text-center">
                    <button class="btn btn-info" type="submit" data-form="#ticket-reply" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('ticket_single_add_reply'); ?></button>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
        <div class="panel_s">
            <div class="panel-heading">
                <?php echo _l('clients_single_ticket_string'); ?>
            </div>
            <div class="panel-body <?php if($ticket->admin == NULL){echo 'client-reply';} ?>">
                <div class="row">
                    <div class="col-md-3 border-right">
                        <?php if($ticket->admin == NULL || $ticket->admin == 0){ ?>
                        <p><?php echo $ticket->submitter; ?></p>
                        <?php } else { ?>
                        <p><?php echo $ticket->opened_by; ?></p>
                        <p class="text-muted">
                            <?php echo _l('ticket_staff_string'); ?>
                        </p>
                        <?php } ?>
                    </div>
                    <div class="col-md-9">
                        <?php echo check_for_links($ticket->message); ?><br />
                        <p>-----------------------------</p>
                        <?php if(count($ticket->attachments) > 0){
                            echo '<hr />';
                            foreach($ticket->attachments as $attachment){ ?>
                            <?php
                            $path = get_upload_path_by_type('ticket').$ticket->ticketid.'/'.$attachment['file_name'];
                            $is_image = is_image($path);

                            if($is_image){
                                echo '<div class="preview_image">';
                            }
                            ?>
                            <a href="<?php echo site_url('download/file/ticket/'. $attachment['id']); ?>" class="display-block mbot5">
                                <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i> <?php echo $attachment['file_name']; ?>
                                <?php if($is_image){ ?>
                                <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" class="mtop5">
                                <?php } ?>
                            </a>
                            <?php if($is_image){
                                echo '</div>';
                            }
                            echo '<hr />';
                        }
                    } ?>
                </div>
            </div>
        </div>
    </div>
    <?php foreach($ticket_replies as $reply){ ?>
    <div class="panel_s">
        <div class="panel-body <?php if($reply['admin'] == NULL){echo 'client-reply';} ?>">
            <div class="row">
                <div class="col-md-3 border-right">
                    <p><?php echo $reply['submitter']; ?></p>
                    <p class="text-muted">
                        <?php if($reply['admin'] !== NULL){
                            echo _l('ticket_staff_string');
                        }
                        ?>
                    </p>
                </div>
                <div class="col-md-9">
                    <?php echo check_for_links($reply['message']); ?><br />
                    <p>-----------------------------</p>
                    <?php if(count($reply['attachments']) > 0){
                        echo '<hr />';
                        foreach($reply['attachments'] as $attachment){
                          $path = get_upload_path_by_type('ticket').$ticket->ticketid.'/'.$attachment['file_name'];
                          $is_image = is_image($path);
                          if($is_image){
                            echo '<div class="preview_image">';
                        }
                        ?>
                        <a href="<?php echo site_url('download/file/ticket/'. $attachment['id']); ?>" class="inline-block mbot5">
                            <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i> <?php echo $attachment['file_name']; ?>
                            <?php if($is_image){ ?>
                            <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" class="mtop5">
                            <?php } ?>
                        </a>
                        <?php if($is_image){
                            echo '</div>';
                        }
                        echo '<hr />';
                    }
                } ?>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <span><?php echo _l('clients_single_ticket_replied',_dt($reply['date'])); ?></span>
    </div>
</div>
<?php } ?>
</div>

</div>
<?php if(count($ticket_replies) > 1){ ?>
<a href="#top" id="toplink">↑</a>
<a href="#bot" id="botlink">↓</a>
<?php } ?>
