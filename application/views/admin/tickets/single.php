<?php init_head(); ?>
<style>
#ribbon_<?php echo $ticket->ticketid; ?> span::before {
	border-top: 3px solid <?php echo $ticket->statuscolor; ?>;
	border-left: 3px solid <?php echo $ticket->statuscolor; ?>;
}
</style>
<?php set_ticket_open($ticket->adminread,$ticket->ticketid); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<?php echo '<div class="ribbon" id="ribbon_'.$ticket->ticketid.'"><span style="background:'.$ticket->statuscolor.'">'.ticket_status_translate($ticket->ticketstatusid).'</span></div>'; ?>
						<ul class="nav nav-tabs no-margin" role="tablist">
							<li role="presentation" class="<?php if(!$this->session->flashdata('active_tab')){echo 'active';} ?>">
								<a href="#addreply" aria-controls="addreply" role="tab" data-toggle="tab">
									<?php echo _l('ticket_single_add_reply'); ?>
								</a>
							</li>
							<li role="presentation">
								<a href="#note" aria-controls="note" role="tab" data-toggle="tab">
									<?php echo _l('ticket_single_add_note'); ?>
								</a>
							</li>
							<li role="presentation">
								<a href="#othertickets" onclick="init_table_tickets(true);" aria-controls="othertickets" role="tab" data-toggle="tab">
									<?php echo _l('ticket_single_other_user_tickets'); ?>
								</a>
							</li>
							<li role="presentation">
								<a href="#tasks" onclick="init_rel_tasks_table(<?php echo $ticket->ticketid; ?>,'ticket'); return false;" aria-controls="tasks" role="tab" data-toggle="tab">
									<?php echo _l('tasks'); ?>
								</a>
							</li>
							<li role="presentation" class="<?php if($this->session->flashdata('active_tab_settings')){echo 'active';} ?>">
								<a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">
									<?php echo _l('ticket_single_settings'); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-8">
								<h3 class="mtop5 mbot10">
									<span id="ticket_subject">
										#<?php echo $ticket->ticketid; ?> - <?php echo $ticket->subject; ?>
									</span>
									<?php if($ticket->project_id != 0){
										echo '<br /><small>'._l('ticket_linked_to_project','<a href="'.admin_url('projects/view/'.$ticket->project_id).'">'.get_project_name_by_id($ticket->project_id).'</a>') .'</small>';
									} ?>
								</h3>
							</div>
							<div class="col-md-4 text-right">
								<div class="row">
									<div class="col-md-6 col-md-offset-6">
										<?php echo render_select('status_top',$statuses,array('ticketstatusid','name'),'',$ticket->status,array(),array(),'','',false); ?>
									</div>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane <?php if(!$this->session->flashdata('active_tab')){echo 'active';} ?>" id="addreply">
								<hr class="no-mtop" />
								<?php $tags = get_tags_in($ticket->ticketid,'ticket'); ?>
								<?php if(count($tags) > 0){ ?>
								<div class="row">
									<div class="col-md-12">
										<?php echo '<b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b><br /><br /> ' . render_tags($tags); ?>
										<hr />
									</div>
								</div>
								<?php } ?>
								<?php if(sizeof($ticket->ticket_notes) > 0){ ?>
								<div class="row">
									<div class="col-md-12 mbot15">
										<h4 class="bold"><?php echo _l('ticket_single_private_staff_notes'); ?></h4>
										<div class="ticketstaffnotes">
											<div class="table-responsive">
												<table>
													<tbody>
														<?php foreach($ticket->ticket_notes as $note){ ?>
														<tr>
															<td>
																<span class="bold">
																	<?php echo staff_profile_image($note['addedfrom'],array('staff-profile-xs-image')); ?> <a href="<?php echo admin_url('staff/profile/'.$note['addedfrom']); ?>"><?php echo _l('ticket_single_ticket_note_by',get_staff_full_name($note['addedfrom'])); ?>
																	</a>
																</span>
																<?php
																if($note['addedfrom'] == get_staff_user_id() || is_admin()){ ?>
																<div class="pull-right">
																	<a href="#" class="btn btn-default btn-icon" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
																	<a href="<?php echo admin_url('misc/delete_note/'.$note["id"]); ?>" class="mright10 _delete btn btn-danger btn-icon">
																		<i class="fa fa-remove"></i>
																	</a>
																</div>
																<?php } ?>
																<hr class="hr-10" />
																<div data-note-description="<?php echo $note['id']; ?>">
																	<?php echo $note['description']; ?>
																</div>
																<div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide inline-block full-width">
																	<textarea name="description" class="form-control" rows="4"><?php echo clear_textarea_breaks($note['description']); ?></textarea>
																	<div class="text-right mtop15">
																		<button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
																		<button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
																	</div>
																</div>
																<small class="bold">
																	<?php echo _l('ticket_single_note_added',_dt($note['dateadded'])); ?>
																</small>
															</td>
														</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
								<?php } ?>
								<div>
									<?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'single-ticket-form','novalidate'=>true)); ?>
									<a href="<?php echo admin_url('tickets/delete/'.$ticket->ticketid); ?>" class="btn btn-danger _delete btn-ticket-label mright5">
										<i class="fa fa-remove"></i>
									</a>
									<?php if(!empty($ticket->priority_name)){ ?>
									<span class="ticket-label label label-default inline-block">
										<?php echo _l('ticket_single_priority',ticket_priority_translate($ticket->priorityid)); ?>
									</span>
									<?php } ?>
									<?php if(!empty($ticket->service_name)){ ?>
									<span class="ticket-label label label-default inline-block">
										<?php echo _l('service'). ': ' . $ticket->service_name; ?>
									</span>
									<?php } ?>
									<?php echo form_hidden('ticketid',$ticket->ticketid); ?>
									<span class="ticket-label label label-default inline-block">
										<?php echo _l('department') . ': '. $ticket->department_name; ?>
									</span>
									<?php if($ticket->assigned != 0){ ?>
									<span class="ticket-label label label-info inline-block">
										<?php echo _l('ticket_assigned'); ?>: <?php echo get_staff_full_name($ticket->assigned); ?>
									</span>
									<?php } ?>
									<?php if($ticket->lastreply !== NULL){ ?>
									<span class="ticket-label label label-success inline-block" data-toggle="tooltip" title="<?php echo _dt($ticket->lastreply); ?>">
										<span class="text-has-action">
											<?php echo _l('ticket_single_last_reply',time_ago($ticket->lastreply)); ?>
										</span>
									</span>
									<?php } ?>
									<div class="mtop15">
										<?php echo render_textarea('message','','',array(),array(),'','tinymce'); ?>
									</div>
									<div class="panel_s ticket-reply-tools">
										<div class="btn-bottom-toolbar text-right">
											<button type="submit" class="btn btn-info" data-form="#single-ticket-form" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>">
												<?php echo _l('ticket_single_add_response'); ?>
											</button>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-5">
													<?php echo render_select('status',$statuses,array('ticketstatusid','name'),'ticket_single_change_status',get_option('default_ticket_reply_status'),array(),array(),'','',false); ?>
													<?php echo render_input('cc','CC'); ?>
													<?php if($ticket->assigned !== get_staff_user_id()){ ?>
													<div class="checkbox">
														<input type="checkbox" name="assign_to_current_user" id="assign_to_current_user">
														<label for="assign_to_current_user"><?php echo _l('ticket_single_assign_to_me_on_update'); ?></label>
													</div>
													<?php } ?>
													<div class="checkbox">
														<input type="checkbox" <?php echo do_action('ticket_add_response_and_back_to_list_default','checked'); ?> name="ticket_add_response_and_back_to_list" value="1" id="ticket_add_response_and_back_to_list">
														<label for="ticket_add_response_and_back_to_list"><?php echo _l('ticket_add_response_and_back_to_list'); ?></label>
													</div>
												</div>
												<?php
												$use_knowledge_base = get_option('use_knowledge_base');
												?>
												<div class="col-md-7 _buttons mtop20">
													<?php
													$use_knowledge_base = get_option('use_knowledge_base');
													?>
													<select id="insert_predefined_reply" data-live-search="true" class="selectpicker mleft10 pull-right" data-title="<?php echo _l('ticket_single_insert_predefined_reply'); ?>">

														<?php foreach($predefined_replies as $predefined_reply){ ?>
														<option value="<?php echo $predefined_reply['id']; ?>"><?php echo $predefined_reply['name']; ?></option>
														<?php } ?>
													</select>
													<?php if($use_knowledge_base == 1){ ?>
													<?php $groups = get_all_knowledge_base_articles_grouped(); ?>
													<select id="insert_knowledge_base_link" class="selectpicker pull-right" data-live-search="true" onchange="insert_ticket_knowledgebase_link(this);" data-title="<?php echo _l('ticket_single_insert_knowledge_base_link'); ?>">
														<option value=""></option>
														<?php foreach($groups as $group){ ?>
														<?php if(count($group['articles']) > 0){ ?>
														<optgroup label="<?php echo $group['name']; ?>">
															<?php foreach($group['articles'] as $article) { ?>
															<option value="<?php echo $article['articleid']; ?>">
																<?php echo $article['subject']; ?>
															</option>
															<?php } ?>
														</optgroup>
														<?php } ?>
														<?php } ?>
													</select>
													<?php } ?>
												</div>
											</div>
											<hr />
											<div class="row attachments">
												<div class="attachment">
													<div class="col-md-5 mbot15">
														<div class="form-group">
															<label for="attachment" class="control-label">
																<?php echo _l('ticket_single_attachments'); ?>
															</label>
															<div class="input-group">
																<input type="file" extension="<?php echo str_replace('.','',get_option('ticket_attachments_file_extensions')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[0]" accept="<?php echo get_ticket_form_accepted_mimes(); ?>">
																<span class="input-group-btn">
																	<button class="btn btn-success add_more_attachments p8-half" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
																</span>
															</div>
														</div>
													</div>
													<div class="clearfix"></div>
												</div>
											</div>


										</div>
									</div>
									<?php echo form_close(); ?>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="note">
								<hr class="no-mtop" />
								<div class="form-group">
									<label for="note_description"><?php echo _l('ticket_single_note_heading'); ?></label>
									<textarea class="form-control" name="note_description" rows="5"></textarea>
								</div>
								<a class="btn btn-info pull-right add_note_ticket"><?php echo _l('ticket_single_add_note'); ?></a>
							</div>
							<div role="tabpanel" class="tab-pane" id="othertickets">
								<hr class="no-mtop" />
								<div class="_filters _hidden_inputs hidden tickets_filters">
									<?php echo form_hidden('filters_ticket_id',$ticket->ticketid); ?>
									<?php echo form_hidden('filters_email',$ticket->email); ?>
									<?php echo form_hidden('filters_userid',$ticket->userid); ?>
								</div>
								<?php echo AdminTicketsTableStructure(); ?>
							</div>
							<div role="tabpanel" class="tab-pane" id="tasks">
								<hr class="no-mtop" />
								<?php init_relation_tasks_table(array('data-new-rel-id'=>$ticket->ticketid,'data-new-rel-type'=>'ticket')); ?>
							</div>
							<div role="tabpanel" class="tab-pane <?php if($this->session->flashdata('active_tab_settings')){echo 'active';} ?>" id="settings">
								<hr class="no-mtop" />
								<div class="row">
									<div class="col-md-6">
										<?php echo render_input('subject','ticket_settings_subject',$ticket->subject); ?>
										<div class="form-group">
											<label for="contactid"><?php echo _l('contact'); ?></label>
											<select name="contactid" id="contactid" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
												<?php
												$rel_data = get_relation_data('contact',$ticket->contactid);
												$rel_val = get_relation_values($rel_data,'contact');
												echo '<option value="'.$rel_val['id'].'" selected data-subtext="'.$rel_val['subtext'].'">'.$rel_val['name'].'</option>';
												?>
											</select>
											<?php echo form_hidden('userid',$ticket->userid); ?>
										</div>

										<div class="row">
											<div class="col-md-6">
												<?php echo render_input('to','ticket_settings_to',$ticket->submitter,'text',array('disabled'=>true)); ?>
											</div>
											<div class="col-md-6">
												<?php
												if($ticket->userid != 0){
													echo render_input('email','ticket_settings_email',$ticket->email,'email',array('disabled'=>true));
												} else {
													echo render_input('email','ticket_settings_email',$ticket->ticket_email,'email',array('disabled'=>true));
												}
												?>
											</div>
										</div>
										<?php echo render_select('department',$departments,array('departmentid','name'),'ticket_settings_departments',$ticket->department); ?>
									</div>
									<div class="col-md-6">
										<div class="form-group mbot20">
											<label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
											<input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo prep_tags_input(get_tags_in($ticket->ticketid,'ticket')); ?>" data-role="tagsinput">
										</div>

										<div class="form-group">
											<label for="assigned" class="control-label">
												<?php echo _l('ticket_settings_assign_to'); ?>
											</label>
											<select name="assigned" id="assigned" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
												<option value=""><?php echo _l('ticket_settings_none_assigned'); ?></option>
												<?php foreach($staff as $member){ ?>
												<option value="<?php echo $member['staffid']; ?>" <?php if($ticket->assigned == $member['staffid']){echo 'selected';} ?>>
													<?php echo $member['firstname'] . ' ' . $member['lastname'] ; ?>
												</option>
												<?php } ?>
											</select>
										</div>

										<div class="row">
											<div class="col-md-<?php if(get_option('services') == 1){ echo 6; }else{echo 12;} ?>">
												<?php
												$priorities['callback_translate'] = 'ticket_priority_translate';
												echo render_select('priority',$priorities,array('priorityid','name'),'ticket_settings_priority',$ticket->priority); ?>
											</div>
											<?php if(get_option('services') == 1){ ?>
											<div class="col-md-6">
												<?php if(is_admin() || get_option('staff_members_create_inline_ticket_services') == '1'){
													echo render_select_with_input_group('service',$services,array('serviceid','name'),'ticket_settings_service',$ticket->service,'<a href="#" onclick="new_service();return false;"><i class="fa fa-plus"></i></a>');
												} else {
													echo render_select('service',$services,array('serviceid','name'),'ticket_settings_service',$ticket->service);
												}
												?>
											</div>
											<?php } ?>
										</div>
										<div class="form-group projects-wrapper<?php if($ticket->userid == 0){echo ' hide';} ?>">
											<label for="project_id"><?php echo _l('project'); ?></label>
											<div id="project_ajax_search_wrapper">
												<select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
													<?php if($ticket->project_id != 0){ ?>
													<option value="<?php echo $ticket->project_id; ?>"><?php echo get_project_name_by_id($ticket->project_id); ?></option>
													<?php } ?>
												</select>
											</div>
										</div>

									</div>
									<div class="col-md-12">
										<?php echo render_custom_fields('tickets',$ticket->ticketid); ?>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12 text-center">
										<hr />
										<a href="#" class="btn btn-info save_changes_settings_single_ticket">
											<?php echo _l('submit'); ?>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel_s mtop20">
					<div class="panel-body <?php if($ticket->admin == NULL){echo 'client-reply';} ?>">
						<div class="row">
							<div class="col-md-3 border-right">
								<p>
									<?php if($ticket->admin == NULL || $ticket->admin == 0){ ?>
									<?php if($ticket->userid != 0){ ?>
									<a href="<?php echo admin_url('clients/client/'.$ticket->userid.'?contactid='.$ticket->contactid); ?>"
										><?php echo $ticket->submitter; ?>
									</a>
									<?php } else {
										echo $ticket->submitter;
										?>
										<br />
										<a href="mailto:<?php echo $ticket->ticket_email; ?>"><?php echo $ticket->ticket_email; ?></a>
										<hr />
										<?php
										if(total_rows('tblticketsspamcontrol',array('type'=>'sender','value'=>$ticket->ticket_email)) == 0){ ?>
										<button type="button" data-sender="<?php echo $ticket->ticket_email; ?>" class="btn btn-danger block-sender btn-xs"><?php echo _l('block_sender'); ?></button>
										<?php
									} else {
										echo '<span class="label label-danger">'._l('sender_blocked').'</span>';
									}
								}
							} else {  ?>
							<a href="<?php echo admin_url('profile/'.$ticket->admin); ?>"><?php echo $ticket->opened_by; ?></a>
							<?php } ?>
						</p>
						<p class="text-muted">
							<?php if($ticket->admin !== NULL || $ticket->admin != 0){
								echo _l('ticket_staff_string');
							} else {
								if($ticket->userid != 0){
									echo _l('ticket_client_string');
								}
							}
							?>
						</p>
						<?php if(has_permission('tasks','','create')){ ?>
						<a href="#" class="btn btn-default btn-xs" onclick="convert_ticket_to_task(<?php echo $ticket->ticketid; ?>,'ticket'); return false;"><?php echo _l('convert_to_task'); ?></a>
						<?php } ?>
					</div>
					<div class="col-md-9">
						<div class="row">
							<div class="col-md-12 text-right">
								<a href=""></a>
								<a href="#" onclick="edit_ticket_message(<?php echo $ticket->ticketid; ?>,'ticket'); return false;"><i class="fa fa-pencil-square-o"></i></a>

							</div>
						</div>
						<div data-ticket-id="<?php echo $ticket->ticketid; ?>" class="tc-content">
							<?php echo check_for_links($ticket->message); ?>
						</div><br />
						<p>-----------------------------</p>
						<?php if(filter_var($ticket->ip, FILTER_VALIDATE_IP)){ ?>
						<p>IP: <?php echo $ticket->ip; ?></p>
						<?php } ?>

						<?php if(count($ticket->attachments) > 0){
							echo '<hr />';
							foreach($ticket->attachments as $attachment){

								$path = get_upload_path_by_type('ticket').$ticket->ticketid.'/'.$attachment['file_name'];
								$is_image = is_image($path);

								if($is_image){
									echo '<div class="preview_image">';
								}
								?>
								<a href="<?php echo site_url('download/file/ticket/'. $attachment['id']); ?>" class="display-block mbot5">
									<i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i> <?php echo $attachment['file_name']; ?>
									<?php if($is_image){ ?>
									<img class="mtop5" src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>">
									<?php } ?>

								</a>
								<?php if($is_image){
									echo '</div>';
								}
								echo '<hr />';
								?>
								<?php }
							} ?>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<?php echo _l('ticket_posted',_dt($ticket->date)); ?>
				</div>
			</div>
			<?php foreach($ticket_replies as $reply){ ?>
			<div class="panel_s">
				<div class="panel-body <?php if($reply['admin'] == NULL){echo 'client-reply';} ?>">
					<div class="row">
						<div class="col-md-3 border-right">
							<p>
								<?php if($reply['admin'] == NULL || $reply['admin'] == 0){ ?>
								<?php if($reply['userid'] != 0){ ?>
								<a href="<?php echo admin_url('clients/client/'.$reply['userid'].'?contactid='.$reply['contactid']); ?>"><?php echo $reply['submitter']; ?></a>
								<?php } else { ?>
								<?php echo $reply['submitter']; ?>
								<br />
								<a href="mailto:<?php echo $reply['reply_email']; ?>"><?php echo $reply['reply_email']; ?></a>
								<?php } ?>
								<?php }  else { ?>
								<a href="<?php echo admin_url('profile/'.$reply['admin']); ?>"><?php echo $reply['submitter']; ?></a>
								<?php } ?>
							</p>
							<p class="text-muted">
								<?php if($reply['admin'] !== NULL || $reply['admin'] != 0){
									echo _l('ticket_staff_string');
								} else {
									if($reply['userid'] != 0){
										echo _l('ticket_client_string');
									}
								}
								?>
							</p>
							<hr />
							<a href="<?php echo admin_url('tickets/delete_ticket_reply/'.$ticket->ticketid .'/'.$reply['id']); ?>" class="btn btn-danger pull-left _delete mright5 btn-xs"><?php echo _l('delete_ticket_reply'); ?></a>
							<div class="clearfix"></div>
							<?php if(has_permission('tasks','','create')){ ?>
							<a href="#" class="pull-left btn btn-default mtop5 btn-xs" onclick="convert_ticket_to_task(<?php echo $reply['id']; ?>,'reply'); return false;"><?php echo _l('convert_to_task'); ?>
							</a>
							<div class="clearfix"></div>
							<?php } ?>
						</div>
						<div class="col-md-9">
							<div class="row">
								<div class="col-md-12 text-right">
									<a href="#" onclick="edit_ticket_message(<?php echo $reply['id']; ?>,'reply'); return false;"><i class="fa fa-pencil-square-o"></i></a>

								</div>
							</div>
							<div class="clearfix"></div>
							<div data-reply-id="<?php echo $reply['id']; ?>" class="tc-content">
								<?php echo check_for_links($reply['message']); ?>
							</div><br />
							<p>-----------------------------</p>
							<?php if(filter_var($reply['ip'], FILTER_VALIDATE_IP)){ ?>
							<p>IP: <?php echo $reply['ip']; ?></p>
							<?php } ?>
							<?php if(count($reply['attachments']) > 0){
								echo '<hr />';
								foreach($reply['attachments'] as $attachment){
									$path = get_upload_path_by_type('ticket').$ticket->ticketid.'/'.$attachment['file_name'];
									$is_image = is_image($path);

									if($is_image){
										echo '<div class="preview_image">';
									}
									?>
									<a href="<?php echo site_url('download/file/ticket/'. $attachment['id']); ?>" class="display-block mbot5">
										<i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i> <?php echo $attachment['file_name']; ?>
										<?php if($is_image){ ?>
										<img class="mtop5" src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>">
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
					<span><?php echo _l('ticket_posted',_dt($reply['date'])); ?></span>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="btn-bottom-pusher"></div>
	<?php if(count($ticket_replies) > 1){ ?>
	<a href="#top" id="toplink">↑</a>
	<a href="#bot" id="botlink">↓</a>
	<?php } ?>
</div>
</div>
<!-- Modal -->
<div class="modal fade" id="ticket-message" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<?php echo form_open(admin_url('tickets/edit_message')); ?>
		<div class="modal-content">
			<div id="edit-ticket-message-additional"></div>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _l('ticket_message_edit'); ?></h4>
			</div>
			<div class="modal-body">
				<?php echo render_textarea('data','','',array(),array(),'','tinymce-ticket-edit'); ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
<script>
	var _ticket_message;
</script>
<?php $this->load->view('admin/tickets/services/service'); ?>
<?php init_tail(); ?>
<?php echo app_script('assets/js','tickets.js'); ?>
<?php do_action('ticket_admin_single_page_loaded',$ticket); ?>
<script>
	$(function(){
		$('#single-ticket-form').validate();
		init_ajax_search('contact','#contactid.ajax-search',{tickets_contacts:true});
		init_ajax_search('project', 'select[name="project_id"]', {
			customer_id: function() {
				return $('input[name="userid"]').val();
			}
		});
		$('body').on('shown.bs.modal', '#_task_modal', function() {
			if(typeof(_ticket_message) != 'undefined') {
				// Init the task description editor
				$(this).find('textarea[name="description"]').click();
				tinymce.activeEditor.execCommand('mceInsertContent', false, _ticket_message);
				$('body #_task_modal input[name="name"]').val($('#ticket_subject').text().trim());
			}
		});

		$("a[href='#top']").on("click", function(e) {
			e.preventDefault();
			$("html,body").animate({scrollTop:0}, 1000);
			e.preventDefault();
		});

				// Smooth scroll to bottom.
				$("a[href='#bot']").on("click", function(e) {
					e.preventDefault();
					$("html,body").animate({scrollTop:$(document).height()}, 1000);
					e.preventDefault();
				});
			});


	var Ticket_message_editor;
	var edit_ticket_message_additional = $('#edit-ticket-message-additional');


	function edit_ticket_message(id,type){
		edit_ticket_message_additional.empty();
		if(type == 'ticket'){
			_ticket_message = $('[data-ticket-id="'+id+'"]').html();
		} else {
			_ticket_message = $('[data-reply-id="'+id+'"]').html();
		}

		init_ticket_edit_editor();
		tinyMCE.activeEditor.setContent(_ticket_message);
		$('#ticket-message').modal('show');
		edit_ticket_message_additional.append(hidden_input('type',type));
		edit_ticket_message_additional.append(hidden_input('id',id));
		edit_ticket_message_additional.append(hidden_input('main_ticket',$('input[name="ticketid"]').val()));
	}

	function init_ticket_edit_editor(){
		if(typeof(Ticket_message_editor) !== 'undefined'){
			return true;
		}
		Ticket_message_editor = init_editor('.tinymce-ticket-edit');
	}
	<?php if(has_permission('tasks','','create')){ ?>
		function convert_ticket_to_task(id, type){
			if(type == 'ticket'){
				_ticket_message = $('[data-ticket-id="'+id+'"]').html();
			} else {
				_ticket_message = $('[data-reply-id="'+id+'"]').html();
			}
			new_task_from_relation(undefined,'ticket',<?php echo $ticket->ticketid; ?>);
		}
		<?php } ?>

	</script>

</body>
</html>
