$(function() {
    // Add predefined reply click
    $('#insert_predefined_reply').on('change', function(e) {
        e.preventDefault();
        var selectpicker = $(this);
        var id = selectpicker.val();
        if (id != '') {
            requestGetJSON('tickets/get_predefined_reply_ajax/' + id).done(function(response) {
                tinymce.activeEditor.execCommand('mceInsertContent', false, response.message);
                selectpicker.selectpicker('val', '');
            });
        }
    });

    $('.block-sender').on('click', function() {
        var sender = $(this).data('sender');
        if (sender == '') {
            alert('No Sender Found');
            return false;
        }
        $.post(admin_url + 'tickets/block_sender', {
            sender: sender
        }).done(function() {
            window.location.reload();
        });
    });

    // Admin ticket note add
    $('.add_note_ticket').on('click', function(e) {
        e.preventDefault();
        var note_description = $('textarea[name="note_description"]').val();
        var ticketid = $('input[name="ticketid"]').val();
        if (note_description == '') { return; }
        $.post(admin_url + 'misc/add_note/' + ticketid + '/ticket', {
            description: note_description
        }).done(function() {
            window.location.reload();
        });
    });

    // Update ticket settings from settings tab
    $('.save_changes_settings_single_ticket').on('click', function(e) {
        e.preventDefault();
        var data = {};
        data = $('#settings *').serialize();
        data += '&ticketid=' + $('input[name="ticketid"]').val();
        if (typeof(csrfData) !== 'undefined') {
            data += '&'+csrfData['token_name'] +'='+ csrfData['hash'];
        }
        $.post(admin_url + 'tickets/update_single_ticket_settings', data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                if (typeof(response.department_reassigned) !== 'undefined') {
                    window.location.href = admin_url + 'tickets/';
                } else {
                    window.location.reload();
                }
            }
        });
    });

    $('#new_ticket_form').submit(function() {
        $('#project_id').prop('disabled', false);
        return true;
    });

    // Change ticket status without replying
    $('select[name="status_top"]').on('change', function() {
        var status = $(this).val();
        var ticketid = $('input[name="ticketid"]').val();
        requestGetJSON('tickets/change_status_ajax/' + ticketid + '/' + status).done(function(response) {
            alert_float(response.alert, response.message);
        });
    });

    // Select ticket user id
    $('body.ticket select[name="contactid"]').on('change', function() {
        var contactid = $(this).val();

        var projectAjax = $('select[name="project_id"]');
        var projectAutoSelected = projectAjax.attr('data-auto-project');
        var projectsWrapper = $('.projects-wrapper');
        if (!projectAjax.attr('disabled')) {
            var clonedProjectsAjaxSearchSelect;
            if (!projectAutoSelected) {
                clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();
            } else {
                clonedProjectsAjaxSearchSelect = projectAjax.clone();
                clonedProjectsAjaxSearchSelect.prop('disabled', true);
            }
            projectAjax.selectpicker('destroy').remove();
            projectAjax = clonedProjectsAjaxSearchSelect;
            $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);
            init_ajax_search('project', projectAjax, {
                customer_id: function() {
                    return $('input[name="userid"]').val();
                }
            });
        }
        if (contactid != '') {
            $.post(admin_url + 'tickets/ticket_change_data/', {
                contact_id: contactid,
            }).done(function(response) {
                response = JSON.parse(response);
                if (response.contact_data) {
                    $('input[name="to"]').val(response.contact_data.firstname + ' ' + response.contact_data.lastname);
                    $('input[name="email"]').val(response.contact_data.email);
                    $('input[name="userid"]').val(response.contact_data.userid);
                }
                if (!projectAutoSelected) {
                    if (response.customer_has_projects) {
                        projectsWrapper.removeClass('hide');
                    } else {
                        projectsWrapper.addClass('hide');
                    }
                } else {
                    projectsWrapper.removeClass('hide');
                }
            });
        } else {
            $('input[name="to"]').val('');
            $('input[name="email"]').val('');
            $('input[name="contactid"]').val('');
            if (!projectAutoSelected) {
                projectsWrapper.addClass('hide');
            } else {
                projectsWrapper.removeClass('hide');
            }
        }
    });
});

// Insert ticket knowledge base link modal
function insert_ticket_knowledgebase_link(e) {
    var id = $(e).val();
    if (id == '') { return }
    requestGetJSON('knowledge_base/get_article_by_id_ajax/' + id).done(function(response) {
        var textarea = $('textarea[name="message"]');
        tinymce.activeEditor.execCommand('mceInsertContent', false, '<a href="' + site_url + 'knowledge_base/' + response.slug + '">' + response.subject + '</a>');
        $(e).selectpicker('val', '');
    });
}

function tickets_bulk_action(event) {
    if (confirm_delete()) {
        var mass_delete = $('#mass_delete').prop('checked');
        var ids = [];
        var data = {};
        if (mass_delete == false || typeof(mass_delete) == 'undefined') {
            data.status = $('#move_to_status_tickets_bulk').val();
            data.department = $('#move_to_department_tickets_bulk').val();
            data.priority = $('#move_to_priority_tickets_bulk').val();
            data.service = $('#move_to_service_tickets_bulk').val();
            data.tags = $('#tags_bulk').tagit('assignedTags');
            if (data.status == '' && data.department == '' && data.priority == '' && data.service == '' && data.tags == '') {
                return;
            }
        } else {
            data.mass_delete = true;
        }
        var rows = $('.table-tickets').find('tbody tr');
        $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') == true) {
                ids.push(checkbox.val());
            }
        });
        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function() {
            $.post(admin_url + 'tickets/bulk_action', data).done(function() {
                window.location.reload();
            });
        }, 50);
    }
}
