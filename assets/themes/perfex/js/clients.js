var project_id = $('input[name="project_id"]').val();
var discussion_user_profile_image_url = $('input[name="discussion_user_profile_image_url"]').val();
var discussion_id = $('input[name="discussion_id"]').val();

Dropzone.options.projectFilesUpload = false;
Dropzone.options.taskFileUpload = false;
Dropzone.options.filesUpload = false;

var salesChart;
$(function() {

    fix_phases_height();

    initDataTable();
    // Set moment locale
    moment.locale(locale);
    // Set timezone locale
    moment().tz(timezone).format();

    var file_id = get_url_param('file_id');
    if (file_id) {
        view_project_file(file_id, project_id);
    }

    $("a[href='#top']").on("click", function(e) {
        e.preventDefault();
        $("html,body").animate({ scrollTop: 0 }, 1000);
        e.preventDefault();
    });

    $("a[href='#bot']").on("click", function(e) {
        e.preventDefault();
        $("html,body").animate({ scrollTop: $(document).height() }, 1000);
        e.preventDefault();
    });

    client_home_chart();
    $('select[name="currency"],select[name="payments_years"]').on('change', function() {
        client_home_chart();
    });

    if (typeof(discussion_id != 'undefined')) {
        discussion_comments('#discussion-comments', discussion_id, 'regular');
    }

    $('body').on('show.bs.modal', '._project_file', function() {
        discussion_comments('#project-file-discussion', discussion_id, 'file');
    });

    if (typeof(Dropbox) != 'undefined') {
        if ($('#dropbox-chooser-task').length > 0) {
            document.getElementById("dropbox-chooser-task").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    $.post(site_url + 'clients/project/' + project_id, {
                        files: files,
                        task_id: $('input[name="task_id"]').val(),
                        external: 'dropbox',
                        action: 'add_task_external_file'
                    }).done(function() {
                        window.location.reload();
                    });
                },
                linkType: "preview",
                extensions: allowed_files.split(','),
            }));
        }

        if ($('#files-upload').length > 0) {
            document.getElementById("dropbox-chooser-files").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    $.post(site_url + 'clients/upload_files', {
                        files: files,
                        external: 'dropbox',
                    }).done(function() {
                        window.location.reload();
                    });
                },
                linkType: "preview",
                extensions: allowed_files.split(','),
            }));
        }

        if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser-project-files').length > 0) {
            document.getElementById("dropbox-chooser-project-files").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    $.post(site_url + 'clients/project/' + project_id, {
                        files: files,
                        external: 'dropbox',
                        action: 'project_file_dropbox',
                    }).done(function() {
                        var location = window.location.href;
                        window.location.href = location.split('?')[0] + '?group=project_files';
                    });
                },
                linkType: "preview",
                extensions: allowed_files.split(','),
            }));
        }
    }

    if ($('#files-upload').length > 0) {
        new Dropzone('#files-upload', {
            paramName: "file",
            dictFileTooBig: file_exceeds_maxfile_size_in_form,
            dictDefaultMessage: drop_files_here_to_upload,
            dictFallbackMessage: browser_not_support_drag_and_drop,
            maxFilesize: (max_php_ini_upload_size_bytes / (1024 * 1024)).toFixed(0),
            accept: function(file, done) {
                done();
            },
            success: function(file, response) {
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    window.location.reload();
                }
            },
            acceptedFiles: allowed_files,
            error: function(file, response) {
                alert_float('danger', response);
            }
        });
    }


    if ($('#calendar').length) {
        var settings = {
            themeSystem: 'bootstrap3',
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            eventLimit: parseInt(calendar_events_limit) + 1,
            views: {
                day: {
                    eventLimit: false
                }
            },
            defaultView: default_view_calendar,
            eventLimitClick: function(cellInfo, jsEvent) {
                $('#calendar').fullCalendar('gotoDate', cellInfo.date);
                $('#calendar').fullCalendar('changeView', 'basicDay');
            },
            loading: function(isLoading, view) {
                isLoading && $('#calendar .fc-header-toolbar .btn-default').addClass('btn-info').removeClass('btn-default').css('display', 'block');
                !isLoading ? $('.dt-loader').addClass('hide') : $('.dt-loader').removeClass('hide');
            },
            isRTL: (isRTL == 'true' ? true : false),
            eventStartEditable: false,
            firstDay: parseInt(calendar_first_day),
            eventSources: [{
                url: site_url + 'clients/get_calendar_data',
                type: 'GET',
                error: function() {
                    console.error('There was error fetching calendar data')
                },
            }, ],
            eventRender: function(event, element) {
                element.attr('title', event._tooltip);
                element.attr('onclick', event.onclick);
                element.attr('data-toggle', 'tooltip');
            },
        }
        // Init calendar
        $('#calendar').fullCalendar(settings);
    }

    var tab_group = get_url_param('group');
    if (tab_group) {
        $('body').find('.nav-tabs li').removeClass('active');
        $('body').find('.nav-tabs [data-group="' + tab_group + '"]').parents('li').addClass('active');
    }

    for (var i = -10; i < $('.task-phase').not('.color-not-auto-adjusted').length / 2; i++) {
        var r = 120;
        var g = 169;
        var b = 56;
        $('.task-phase:eq(' + (i + 10) + ')').not('.color-not-auto-adjusted').css('background', color(r - (i * 13), g - (i * 13), b - (i * 13))).css('border', '1px solid ' + color(r - (i * 13), g - (i * 13), b - (i * 13)));
    };

    var circle = $('.project-progress').circleProgress({
        fill: {
            gradient: ["#84c529", "#84c529"]
        }
    }).on('circle-animation-progress', function(event, progress, stepValue) {
        $(this).find('strong.project-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
    });

    $('.toggle-change-ticket-status').on('click', function() {
        $('.ticket-status,.ticket-status-inline').toggleClass('hide');
    });


    $('#ticket_status_single').on('change', function() {
        data = {};
        data.status_id = $(this).val();
        data.ticket_id = $('input[name="ticket_id"]').val();
        $.post(site_url + 'clients/change_ticket_status/', data).done(function() {
            window.location.reload();
        });
    });


    if (typeof(contracts_by_type) != 'undefined') {
        new Chart($('#contracts-by-type-chart'), {
            type: 'bar',
            data: JSON.parse(contracts_by_type),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        display: true,
                        ticks: {
                            beginAtZero: true,
                        }
                    }]
                }
            }
        });
    }
    if ($('#task-file-upload').length > 0) {
        new Dropzone('#task-file-upload', {
            uploadMultiple: true,
            parallelUploads: 20,
            maxFiles: 20,
            paramName: 'file',
            dictFileTooBig: file_exceeds_maxfile_size_in_form,
            dictDefaultMessage: drop_files_here_to_upload,
            maxFilesize: (max_php_ini_upload_size_bytes / (1024 * 1024)).toFixed(0),
            dictFallbackMessage: browser_not_support_drag_and_drop,
            accept: function(file, done) {
                done();
            },
            sending: function(file, xhr, formData) {
                formData.append("action", 'upload_task_file');
                formData.append("task_id", $('input[name="task_id"]').val());
            },
            success: function(file, response) {
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    window.location.reload();
                }
            },
            acceptedFiles: allowed_files,
            error: function(file, response) {
                alert_float('danger', response);
            }
        });
    }

    if ($('#project-files-upload').length > 0) {
        new Dropzone('#project-files-upload', {
            paramName: "file",
            uploadMultiple: true,
            parallelUploads: 20,
            maxFiles: 20,
            dictFileTooBig: file_exceeds_maxfile_size_in_form,
            dictDefaultMessage: drop_files_here_to_upload,
            dictFallbackMessage: browser_not_support_drag_and_drop,
            maxFilesize: (max_php_ini_upload_size_bytes / (1024 * 1024)).toFixed(0),
            accept: function(file, done) {
                done();
            },
            sending: function(file, xhr, formData) {
                formData.append("action", 'upload_file');
            },
            success: function(file, response) {
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    window.location.reload();
                }
            },
            acceptedFiles: allowed_files,
            error: function(file, response) {
                alert_float('danger', response);
            }
        });
    }

    $('#open-new-ticket-form').validate();
    $('#ticket-reply').validate();
    $('#task-form').validate();

    var ticketAttachmentKey = 1;
    $('.add_more_attachments').on('click', function() {
        if ($(this).hasClass('disabled')) {
            return false;
        }

        var total_attachments = $('.attachments input[name*="attachments"]').length;
        if ($(this).data('ticket') && total_attachments >= maximum_allowed_ticket_attachments) {
            return false;
        }

        var newattachment = $('.attachments').find('.attachment').eq(0).clone().appendTo('.attachments');
        newattachment.find('input').val('').attr('name', 'attachments[' + ticketAttachmentKey + ']');
        newattachment.find('input').removeAttr('aria-describedby');
        newattachment.find('input').removeAttr('aria-invalid');
        newattachment.find('span[id*="error"]').remove();
        newattachment.find('i').removeClass('fa-plus').addClass('fa-minus');
        newattachment.find('button').removeClass('add_more_attachments').addClass('remove_attachment').removeClass('btn-success').addClass('btn-danger');
        ticketAttachmentKey++;
    });

    $('body').on('click', '.remove_attachment', function() {
        $(this).parents('.attachment').remove();
    });

    $('.single-ticket-add-reply').on('click', function(e) {
        e.preventDefault()
        var reply_area = $('.single-ticket-reply-area');
        reply_area.slideToggle();
    });
    // User cant add more money then the invoice total remaining
    $('body.viewinvoice input[name="amount"]').on('keyup', function() {
        var original_total = $(this).data('total');
        var val = $(this).val();
        var form_group = $(this).parents('.form-group');
        if (val > original_total) {
            form_group.addClass('has-error');
            if (form_group.find('p.text-danger').length == 0) {
                form_group.append('<p class="text-danger">Maximum pay value passed</p>');
                $(this).parents('form').find('input[name="make_payment"]').attr('disabled', true);
            }
        } else {
            form_group.removeClass('has-error');
            form_group.find('p.text-danger').remove();
            $(this).parents('form').find('input[name="make_payment"]').attr('disabled', false);
        }
    });

    $('#discussion_form').validate({
        rules: {
            subject: 'required',
        }
    });


    $('#discussion').on('hidden.bs.modal', function(event) {
        $('#discussion input[name="subject"]').val('');
        $('#discussion textarea[name="description"]').val('');
        $('#discussion .add-title').removeClass('hide');
        $('#discussion .edit-title').removeClass('hide');
    });

});

// Generate float alert
function alert_float(type, message) {

    var aId, el;
    aId = $('body').find('float-alert').length;
    aId++;
    aId = 'alert_float_' + aId;
    el = $('<div id="' + aId + '" class="float-alert animated fadeInRight col-xs-11 col-sm-4 alert alert-' + type + '"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span>' + message + '</span></div>');
    $('body').append(el);

    setTimeout(function() {
        $('#' + aId).remove();
    }, 3000);
}

function new_discussion() {
    $('#discussion').modal('show');
    $('#discussion .edit-title').addClass('hide');
}

function manage_discussion(form) {
    var data = $(form).serialize();
    var url = form.action;
    $.post(url, data).done(function(response) {
        response = JSON.parse(response);
        if (response.success == true) {
            alert_float('success', response.message);
        }
        $('.table-project-discussions').DataTable().ajax.reload(null, false);
        $('#discussion').modal('hide');
    });
    return false;
}

function remove_task_comment(commentid) {
    $.get(site_url + 'clients/remove_task_comment/' + commentid, function(response) {
        if (response.success == true) {
            window.location.reload();
        }
    }, 'json');
}

function edit_task_comment(id) {
    var edit_wrapper = $('[data-edit-comment="' + id + '"]');
    edit_wrapper.removeClass('hide');
    $('[data-comment-content="' + id + '"]').addClass('hide');
}

function cancel_edit_comment(id) {
    var edit_wrapper = $('[data-edit-comment="' + id + '"]');
    edit_wrapper.addClass('hide');
    $('[data-comment-content="' + id + '"]').removeClass('hide');
}

function save_edited_comment(id) {
    var data = {};
    data.id = id;
    data.content = $('[data-edit-comment="' + id + '"]').find('textarea').val();
    $.post(site_url + 'clients/edit_comment', data).done(function(response) {
        response = JSON.parse(response);
        if (response.success == true) {
            window.location.reload();
        } else {
            cancel_edit_comment(id);
        }
    });
}

function DataTablesOfflineLazyLoadImages(nRow, aData, iDisplayIndex) {
    var img = $('img.img-table-loading', nRow);
    img.attr('src', img.data('orig'));
    img.prev('div').addClass('hide');
    return nRow;
}

function initDataTable() {

    var length_options = [10, 25, 50, 100];
    var length_options_names = [10, 25, 50, 100];

    tables_pagination_limit = parseFloat(tables_pagination_limit);

    if ($.inArray(tables_pagination_limit, length_options) == -1) {
        length_options.push(tables_pagination_limit)
        length_options_names.push(tables_pagination_limit)
    }

    length_options.sort(function(a, b) {
        return a - b;
    });

    length_options_names.sort(function(a, b) {
        return a - b;
    });

    length_options.push(-1);
    length_options_names.push(dt_length_menu_all);

    var options, order_col, order_type;
    var _options = {
        "language": dt_lang,
        'paginate': true,
        "pageLength": tables_pagination_limit,
        "lengthMenu": [length_options, length_options_names],
        "fnRowCallback": DataTablesOfflineLazyLoadImages,
        "order": [0, 'DESC'],
        "fnDrawCallback": function(oSettings) {
            if (oSettings.aoData.length == 0 || oSettings.aiDisplay.length == 0) {
                $(oSettings.nTableWrapper).addClass('app_dt_empty');
            } else {
                $(oSettings.nTableWrapper).removeClass('app_dt_empty');
            }
        },
        "initComplete": function(settings, json) {
            this.wrap('<div class="table-responsive"></div>');
        }
    };
    var tables = $('.dt-table');
    $.each(tables, function() {
        options = _options;
        order_col = $(this).attr('data-order-col');
        order_type = $(this).attr('data-order-type');
        if (order_col && order_type) {
            options.order = [
                [order_col, order_type]
            ]
        }
        $(this).DataTable(options);
    });
}

function get_url_param(param) {
    var vars = {};
    window.location.href.replace(location.hash, '').replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function(m, key, value) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );
    if (param) {
        return vars[param] ? vars[param] : null;
    }
    return vars;
}

function fix_phases_height() {
    if (is_mobile()) {
        return;
    }
    var maxPhaseHeight = Math.max.apply(null, $("div.tasks-phases .panel-body").map(function() {
        return $(this).outerHeight();
    }).get());
    $('div.tasks-phases .panel-body').css('min-height', maxPhaseHeight + 'px');
}

function color(r, g, b) {
    return 'rgb(' + r + ',' + g + ',' + b + ')';
}

function taskTable() {
    $('.tasks-table').toggleClass('hide');
    $('.tasks-phases').toggleClass('hide');
}

function dt_custom_view(table, column, val) {
    var tableApi = $(table).DataTable();
    tableApi.column(column).search(val).draw();
}

function discussion_comments(selector, discussion_id, discussion_type) {
    $(selector).comments({
        roundProfilePictures: true,
        textareaRows: 4,
        textareaRowsOnFocus: 6,
        enableDeleting: true,
        profilePictureURL: discussion_user_profile_image_url,
        enableUpvoting: false,
        enableAttachments: true,
        popularText: '',
        enableDeletingCommentWithReplies: true,
        textareaPlaceholderText: discussions_lang.discussion_add_comment,
        newestText: discussions_lang.discussion_newest,
        oldestText: discussions_lang.discussion_oldest,
        attachmentsText: discussions_lang.discussion_attachments,
        sendText: discussions_lang.discussion_send,
        replyText: discussions_lang.discussion_reply,
        editText: discussions_lang.discussion_edit,
        editedText: discussions_lang.discussion_edited,
        youText: discussions_lang.discussion_you,
        saveText: discussions_lang.discussion_save,
        deleteText: discussions_lang.discussion_delete,
        viewAllRepliesText: discussions_lang.discussion_view_all_replies + ' (__replyCount__)',
        hideRepliesText: discussions_lang.discussion_hide_replies,
        noCommentsText: discussions_lang.discussion_no_comments,
        noAttachmentsText: discussions_lang.discussion_no_attachments,
        attachmentDropText: discussions_lang.discussion_attachments_drop,

        getComments: function(success, error) {
            $.post(site_url + 'clients/project/' + project_id, {
                action: 'discussion_comments',
                discussion_id: discussion_id,
                discussion_type: discussion_type,
            }).done(function(response) {
                response = JSON.parse(response);
                success(response);
            });
        },
        postComment: function(commentJSON, success, error) {
            commentJSON.action = 'new_discussion_comment';
            commentJSON.discussion_id = discussion_id;
            commentJSON.discussion_type = discussion_type;
            $.ajax({
                type: 'post',
                url: site_url + 'clients/project/' + project_id,
                data: commentJSON,
                success: function(comment) {
                    comment = JSON.parse(comment);
                    success(comment)
                },
                error: error
            });
        },
        putComment: function(commentJSON, success, error) {
            commentJSON.action = 'update_discussion_comment';
            $.ajax({
                type: 'post',
                url: site_url + 'clients/project/' + project_id,
                data: commentJSON,
                success: function(comment) {
                    comment = JSON.parse(comment);
                    success(comment)
                },
                error: error
            });
        },
        deleteComment: function(commentJSON, success, error) {
            $.ajax({
                type: 'post',
                url: site_url + 'clients/project/' + project_id,
                success: success,
                error: error,
                data: {
                    id: commentJSON.id,
                    action: 'delete_discussion_comment'
                }
            });
        },
        timeFormatter: function(time) {
            return moment(time).fromNow();
        },
        uploadAttachments: function(commentArray, success, error) {
            var responses = 0;
            var successfulUploads = [];

            var serverResponded = function() {
                responses++;
                // Check if all requests have finished
                if (responses == commentArray.length) {
                    // Case: all failed
                    if (successfulUploads.length == 0) {
                        error();
                        // Case: some succeeded
                    } else {
                        successfulUploads = JSON.parse(successfulUploads);
                        success(successfulUploads)
                    }
                }
            }
            $(commentArray).each(function(index, commentJSON) {
                if (commentJSON.file.size && commentJSON.file.size > max_php_ini_upload_size_bytes) {
                    alert_float('danger', file_exceeds_max_filesize);
                    serverResponded();
                } else {
                    // Create form data
                    var formData = new FormData();
                    $(Object.keys(commentJSON)).each(function(index, key) {
                        var value = commentJSON[key];
                        if (value) formData.append(key, value);
                    });

                    formData.append('action', 'new_discussion_comment');
                    formData.append('discussion_id', discussion_id);
                    formData.append('discussion_type', discussion_type);

                    if (typeof(csrfData) !== 'undefined') {
                        formData.append(csrfData['token_name'], csrfData['hash']);
                    }

                    $.ajax({
                        url: site_url + 'clients/project/' + project_id,
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(commentJSON) {
                            successfulUploads.push(commentJSON);
                            serverResponded();
                        },
                        error: function(data) {
                            var error = JSON.parse(data.responseText);
                            alert_float('danger', error.message);
                            serverResponded();
                        },
                    });
                }
            });
        }
    });
}

function view_project_file(id, project_id) {
    $.post(site_url + 'clients/project/' + project_id, {
        action: 'get_file',
        id: id,
        project_id: project_id
    }).done(function(response) {
        $('#project_file_data').html(response);
    }).fail(function(error) {
        alert_float('danger', error.statusText);
    });
}

function update_file_data(id) {
    var data = {};
    data.id = id;
    data.subject = $('body input[name="file_subject"]').val();
    data.description = $('body textarea[name="file_description"]').val();
    data.action = 'update_file_data';
    $.post(site_url + 'clients/project/' + project_id, data);
}
// Function to close modal manually... needed in some modals where the data is flexible.
function close_modal_manually(modal) {
    $(modal).fadeOut('slow', function() {
        $(modal).remove();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
}

function render_customer_statement() {
    var $statementPeriod = $('#range');
    var value = $statementPeriod.selectpicker('val');
    var period = new Array();
    var isPeriod = false;
    if (value != 'period') {
        period = JSON.parse(value);
    } else {

        period[0] = $('input[name="period-from"]').val();
        period[1] = $('input[name="period-to"]').val();

        if (period[0] == '' || period[1] == '') {
            return false;
        }
        isPeriod = true;
    }
    var statementUrl = site_url + 'clients/statement';
    var statementUrlParams = new Array();
    statementUrlParams['from'] = period[0];
    statementUrlParams['to'] = period[1];
    if (isPeriod) {
        statementUrlParams['custom_period'] = true;
    }
    window.location.href = buildUrl(statementUrl, statementUrlParams);
}

function client_home_chart() {
    // Check if chart canvas exists.
    var chart = $('#client-home-chart');
    if (chart.length == 0) {
        return;
    }
    if (typeof(salesChart) !== 'undefined') {
        salesChart.destroy();
    }
    var data = {};
    var currency = $('#currency');
    var year = $('#payments_year');
    if (currency.length > 0) {
        data.report_currency = $('select[name="currency"]').val();
    }
    if (year.length > 0) {
        data.year = $('#payments_year').val();
    }

    $.post(site_url + 'clients/client_home_chart', data).done(function(response) {
        response = JSON.parse(response);
        salesChart = new Chart(chart, {
            type: 'bar',
            data: response,
            options: { responsive: true, maintainAspectRatio: false }
        });
    });
}
