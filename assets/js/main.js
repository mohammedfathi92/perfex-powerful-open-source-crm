/**
 * Core JS Files
 * Not recommened to edit this file directly if you plan to upgrade the script when new versions are released.
 * Use hooks to inject custom javascript code
 */

$(window).on('load', function() {
    init_btn_with_tooltips();
});

// Set datatables error throw console log
$.fn.dataTable.ext.errMode = 'throw';
$.fn.dataTableExt.oStdClasses.sWrapper = 'dataTables_wrapper form-inline dt-bootstrap table-loading';

// Set dropzone not auto discover
Dropzone.options.newsFeedDropzone = false;
Dropzone.options.salesUpload = false;

// Check for desktop notifications permissions
if (("Notification" in window) && app_desktop_notifications == '1') {
    Notification.requestPermission();
}

// Jquery validate set default options
$.validator.setDefaults({
    highlight: function(element) {
        var $child_tab_in_form = $(element).parents('.tab-pane');
        if ($child_tab_in_form.length && !$child_tab_in_form.is(':visible')) {
            $('a[href="#' + $child_tab_in_form.attr('id') + '"]')
                .css('border-bottom', '1px solid red')
                .css('color', 'red')
                .addClass('tab-validation');
        }
        $(element).closest('.form-group').addClass('has-error');
    },
    unhighlight: function(element) {
        var $child_tab_in_form = $(element).parents('.tab-pane');
        if ($child_tab_in_form.length) {
            $('a[href="#' + $child_tab_in_form.attr('id') + '"]').removeAttr('style').removeClass('tab-validation');
        }
        $(element).closest('.form-group').removeClass('has-error');
    },
    errorElement: 'p',
    errorClass: 'text-danger',
    errorPlacement: function(error, element) {
        if (element.parent('.input-group').length || element.parents('.chk').length) {
            if (!element.parents('.chk').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element.parents('.chk'));
            }
        } else {
            error.insertAfter(element);
        }
    }
});

// New validation method filesize
$.validator.addMethod('filesize', function(value, element, param) {
    return this.optional(element) || (element.files[0].size <= param);
}, appLang.file_exceeds_max_filesize);

// New validation method extension based on app extensions
$.validator.addMethod("extension", function(value, element, param) {
    param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
    return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
}, $.validator.format(appLang.validation_extension_not_allowed));

// Custom deselect all on bootstrap ajax select input
$("body").on('loaded.bs.select change', 'select.ajax-search', function(e) {
    if ($(this).selectpicker('val')) {
        var $elmWrapper = $(this).parents('.bootstrap-select.ajax-search');
        if ($elmWrapper.find('.ajax-clear-values').length === 0) {
            $elmWrapper.addClass('ajax-remove-values-option').find('button.dropdown-toggle').after('<span class="pointer ajax-clear-values" onclick="deselect_ajax_search(this); return false;" data-id="' + $(this).attr('id') + '"><i class="fa fa-remove"></i></span>');
        }
    }
});

// On render select remove the placeholder
$("body").on('rendered.bs.select', 'select', function() {
    $(this).parents().removeClass('select-placeholder');
});

// Predefined global variables
var original_top_search_val,
    table_leads,
    table_activity_log,
    table_estimates,
    table_invoices,
    table_tasks,
    tab_active = get_url_param('tab'),
    tab_group = get_url_param('group'),
    side_bar = $('#side-menu'),
    content_wrapper = $('#wrapper'),
    setup_menu = $('#setup-menu-wrapper'),
    menu_href_selector,
    calendar_selector = $('#calendar'),
    notifications_wrapper = $('#header li.notifications-wrapper'),
    doc_initial_title = document.title,
    newsfeed_posts_page = 0,
    track_load_post_likes = 0,
    track_load_comment_likes = 0,
    post_likes_total_pages = 0,
    comment_likes_total_pages = 0,
    postid = 0,
    setup_menu_item = $('#setup-menu-item');

$(window).on("load resize", function(e) {

    if (!$("body").hasClass('page-small')) {
        // Add special class to minimalize page elements when screen is less than 768px
        set_body_small();
    }

    // Waint until metsiMenu, collapse and other effect finish and set wrapper height
    setTimeout(function() {
        mainWrapperHeightFix();
    }, e.type == 'load' ? 150 : 0);

});

$(function() {

    // Add notifications indicator on document title
    if (total_unread_notifications > 0) {
        document.title = '(' + total_unread_notifications + ') ' + doc_initial_title;
    }

    $('.screen-options-btn').on('click', function() {
        $('.screen-options-area').slideToggle();
    });

    /** Create New Customer **/
    add_hotkey('Shift+C', function() {
        var $leadModal = $('#lead-modal');
        var $taskModal = $('#task-modal');
        if ($leadModal.is(':visible')) {
            convert_lead_to_customer($leadModal.find('input[name="leadid"]').val());
        } else if ($taskModal.is(':visible')) {
            var $taskCommentsWrapper = $taskModal.find('.tasks-comments');
            if (!$taskCommentsWrapper.is(':visible')) {
                $taskCommentsWrapper.css('display', 'block');
            }
            $taskModal.find('#task_comment').click();
        } else {
            window.location.href = admin_url + 'clients/client';
        }
    });

    /** Create New Invoice **/
    add_hotkey('Shift+I', function() {
        window.location.href = admin_url + 'invoices/invoice';
    });

    /** Create New Estimate **/
    add_hotkey('Shift+E', function() {
        var $leadModal = $('#lead-modal');
        var $taskModal = $('#task-modal');
        if (!$leadModal.is(':visible') && !$taskModal.is(':visible')) {
            window.location.href = admin_url + 'estimates/estimate';
        } else {
            if ($leadModal.is(':visible')) {
                $('a[lead-edit]').click();
            } else if ($taskModal.is(':visible')) {
                edit_task($taskModal.find('[data-task-single-id]').attr('data-task-single-id'));
            }
        }
    });

    /** Marks task as finished when modal is opened **/
    add_hotkey('Shift+F', function() {
        var $taskModal = $('#task-modal');
        if ($taskModal.is(':visible')) {
            var $taskSingleBody = $taskModal.find('[data-task-single-id]');
            if ($taskSingleBody.attr('data-status') != 5) {
                mark_complete($taskSingleBody.attr('data-task-single-id'));
            }
        }
    });

    /** Create New Proposal **/
    add_hotkey('Ctrl+Shift+P', function() {
        window.location.href = admin_url + 'proposals/proposal';
    });
    /** Create New Expense **/
    add_hotkey('Ctrl+Shift+E', function() {
        window.location.href = admin_url + 'expenses/expense';
    });

    /** Create New Lead **/
    add_hotkey('Shift+L', function() {
        init_lead();
    });
    /** Create New Task **/
    add_hotkey('Shift+T', function() {
        var $newTaskRelationBtn = $('.new-task-relation');
        if ($newTaskRelationBtn.length > 0) {
            new_task(admin_url + 'tasks/task?rel_id=' + $newTaskRelationBtn.attr('data-rel-id') + '&rel_type=' + $newTaskRelationBtn.attr('data-rel-type'));
        } else if ($("body").hasClass('project')) {
            new_task(admin_url + 'tasks/task?rel_id=' + project_id + '&rel_type=project');
        } else {
            new_task();
        }
    });
    /** Create New Project **/
    add_hotkey('Shift+P', function() {
        window.location.href = admin_url + 'projects/project';
    });
    /** Create New Ticket **/
    add_hotkey('Shift+S', function() {
        window.location.href = admin_url + 'tickets/add';
    });
    /** Create New Staff Member **/
    add_hotkey('Ctrl+Shift+S', function() {
        window.location.href = admin_url + 'staff/member';
    });

    /** User logout **/
    add_hotkey('Ctrl+Shift+L', function() {
        logout();
    });

    /**
     * List shortcuts
     */

    /** Go to dashbaord **/
    add_hotkey('Alt+D', function() {
        window.location.href = admin_url;
    });
    /** List Customers **/
    add_hotkey('Alt+C', function() {
        window.location.href = admin_url + 'clients';
    });
    /** List Tasks **/
    add_hotkey('Alt+T', function() {
        window.location.href = admin_url + 'tasks/list_tasks';
    });
    /** List Invoices **/
    add_hotkey('Alt+I', function() {
        window.location.href = admin_url + 'invoices/list_invoices';
    });
    /** List Estimates **/
    add_hotkey('Alt+E', function() {
        window.location.href = admin_url + 'estimates/list_estimates';
    });
    /** List Projects **/
    add_hotkey('Alt+P', function() {
        window.location.href = admin_url + 'projects';
    });
    /** List Leads **/
    add_hotkey('Alt+L', function() {
        window.location.href = admin_url + 'leads';
    });
    /** List Tickets **/
    add_hotkey('Ctrl+Alt+T', function() {
        window.location.href = admin_url + 'tickets';
    });
    /** List Expenses **/
    add_hotkey('Ctrl+Alt+E', function() {
        window.location.href = admin_url + 'expenses/list_expenses';
    });

    /** Sales Report **/
    add_hotkey('Alt+R', function() {
        window.location.href = admin_url + 'reports/sales';
    });

    /** Settings **/
    add_hotkey('Alt+S', function() {
        window.location.href = admin_url + 'settings';
    });

    /** Top Search Focus **/
    add_hotkey('Shift+K', function() {
        $('#search_input').focus();
    });

    /* Focus on seacrh on first datatable found in the DOM */
    add_hotkey('Shift+D', function() {
        $('body .dataTables_wrapper').eq(0).find('.dataTables_filter input').focus();
    });

    // Init the shortcuts
    $.Shortcuts.start();

    /** TinyMCE modal fix */
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });

    // Custom option to show setup menu item only on hover, not applied on mobile
    if (app_show_setup_menu_item_only_on_hover == 1 && !is_mobile()) {
        side_bar.hover(
            function() {
                setTimeout(function() {
                    setup_menu_item.css("display", "block");
                }, 200);
            },
            function() {
                setTimeout(function() {
                    setup_menu_item.css("display", "none");
                }, 1000);
            }
        );
    }

    // Store navTabs, used multiple times.
    var $navTabs = $("body").find('ul.nav-tabs');
    // Check for active tab if any found in url so we can set this tab to active - Tab active is defined on top
    if (tab_active) { $navTabs.find('[href="#' + tab_active + '"]').click(); }
    // Check for active tab groups (this is custom made) and not related to boostrap - tab_group is defined on top
    if (tab_group) {
        // Do not track bootstrap default tabs
        $navTabs.find('li').not('[role="presentation"]').removeClass('active');
        // Add the class active to this group manually so the tab can be highlighted
        $navTabs.find('[data-group="' + tab_group + '"]').parents('li').addClass('active');
    }
    // Set moment locale
    moment.locale(locale);
    // Set timezone locale
    moment().tz(app_timezone).format();
    // Set datetimepicker locale
    jQuery.datetimepicker.setLocale(locale);
    // Init tinymce editors
    init_editor();
    // Dont close dropdown on timer top click
    $("body").on('click', '#started-timers-top,.popover-top-timer-note', function(e) {
        e.stopPropagation();
    });
    // Init inputs used for tags
    init_tags_inputs();
    // Init all color pickers
    init_color_pickers();
    // Init tables offline (no serverside)
    initDataTableInline();

    // Bootstrap switch active or inactive global function
    $("body").on('change', '.onoffswitch input', function(event, state) {
        var switch_url = $(this).data('switch-url');
        if (!switch_url) { return; }
        switch_field(this);
    });

    /* Custom fields hyperlink */
    custom_fields_hyperlink();
    // Init lightboxes if found
    init_lightbox();
    // Init progress bars
    init_progress_bars();
    // Init datepickers
    init_datepicker();
    // Init bootstrap selectpicker
    $("body").on('loaded.bs.select', '._select_input_group', function(e) {
        $(this).parents('.form-group').find('.input-group-select .input-group-addon').css('opacity', '1');
    });
    init_selectpicker();
    // Optimize body
    set_body_small();
    // Validate all form for reminders
    init_form_reminder();
    // On document read check and init for client ajax-search
    init_ajax_search('customer', '#clientid.ajax-search');

    // Check for active class in sidebar links
    var sidebar_links = side_bar.find('li > a');
    $.each(sidebar_links, function(i, data) {
        var href = $(data).attr('href');
        // Check if the url matches so we can add the active class
        if (location == href) {
            var $hrefSelector = side_bar.find('a[href="' + href + '"]');
            // Do not add on the top quick links
            $hrefSelector.parents('li').not('.quick-links').addClass('active');
            // Set aria expanded to true
            $hrefSelector.prop('aria-expanded', true);
            $hrefSelector.parents('ul.nav-second-level').prop('aria-expanded', true);
            $hrefSelector.parents('li').find('a:first-child').prop('aria-expanded', true);
        }
    });

    // Check for customizer active class
    if (setup_menu.hasClass('display-block')) {
        var setup_menu_links = setup_menu.find('li > a');
        $.each(setup_menu_links, function(i, data) {
            var href = $(data).attr('href');
            if (location == href) {
                var $hrefSelector = setup_menu.find('a[href="' + href + '"]');
                $hrefSelector.parents('li').addClass('active');
                $hrefSelector.prev('active');
                $hrefSelector.parents('ul.nav-second-level').prop('aria-expanded', true);
                $hrefSelector.parents('li').find('a:first-child').prop('aria-expanded', true);
            }
        });
    }

    // Init now metisMenu for the main admin sidebar
    side_bar.metisMenu();
    // Init setup menu
    setup_menu.metisMenu();

    // Handle minimalize sidebar menu
    $('.hide-menu').click(function(e) {
        e.preventDefault();

        if ($(window).width() < 769) {
            $("body").toggleClass("show-sidebar");
        } else {
            $("body").toggleClass("hide-sidebar");
        }

        if (setup_menu.hasClass('display-block')) {
            $('.close-customizer').click();
        }
    });

    // Hide sidebar on content click on mobile
    if (is_mobile()) {
        content_wrapper.on('click', function() {
            if ($("body").hasClass('show-sidebar')) {
                $("body").removeClass('show-sidebar');
            }
            if (setup_menu.hasClass('display-block')) {
                $('.close-customizer').click();
            }
        });
    }

    if (app_user_browser == 'safari') {
        $('body').on('input', '.bootstrap-select .bs-searchbox input', function() {
            $(this).trigger('keyup');
        });
    }

    // Optimize wrapper height
    mainWrapperHeightFix();
    // Top search input fetch results
    $('#search_input').on('keyup paste' + (app_user_browser == 'safari' ? ' input' : ''), function() {

        var q = $(this).val().trim();
        var search_results = $('#search_results');
        var top_search_button = $('#top_search_button button');

        if (q === '') {
            content_wrapper.unhighlight();
            search_results.html('');
            original_top_search_val = '';
            top_search_button.html('<i class="fa fa-search"></i>').removeClass('search_remove');
            return;
        }

        if (q.length < 2 &&
            (app_language.indexOf('chinese') === -1 &&
                app_language.indexOf('japanese') === -1)) {
            return;
        }

        top_search_button.html('<i class="fa fa-remove"></i>').addClass('search_remove');

        delay(function() {
            if (q == original_top_search_val) {
                return;
            }
            $.post(admin_url + 'misc/search', { q: q }).done(function(results) {
                content_wrapper.unhighlight();
                search_results.html(results);
                content_wrapper.highlight(q);
                original_top_search_val = q;
            });
        }, 700);
    });

    // Format timesheet duration type and do necesary checkings
    $('body').on('blur', '#timesheet_duration', function() {
        var that = $(this);
        var pattern = /[^0-9:]/gi;
        var val = $(this).val();
        val = val.replace(pattern, '');
        if (val.indexOf(':') > -1) {
            var duration_array = val.split(':');

            if (duration_array[0].length == 0) {
                duration_array[0] = '00';
            }

            if (duration_array[1] >= 60) {
                var subtr = Math.floor(parseInt(duration_array[1] / 60));
                duration_array[0] = subtr + parseInt(duration_array[0]);
                duration_array[1] = (duration_array[1] - (subtr * 60));
            }

            if (duration_array[0].toString().length == 1) {
                duration_array[0] = '0' + duration_array[0];
            }

            if (duration_array[1].toString().length == 1) {
                duration_array[1] = '0' + duration_array[1];
            } else if (duration_array[1].toString().length == 0) {
                duration_array[1] = '00';
            }

            val = duration_array[0] + ':' + duration_array[1];

        } else if (val.length == 1 && val.indexOf(':') == -1) {
            val = '0' + val + ':' + '00';
        } else if (val.length >= 2 && val.indexOf(':') == -1) {
            val = val + ':' + '00';
        }
        val = val == '00:00' ? '' : val;
        that.val(val);
    });

    // Switching timesheet enter type
    $('body').on('click', '.timesheet-toggle-enter-type', function(e) {
        e.preventDefault();
        var $switch_to = $(this).find('span.switch-to').removeClass('switch-to').addClass('hide');
        $(this).find('span').not($switch_to).removeClass('hide').addClass('switch-to');
        $('.timesheet-start-end-time,.timesheet-duration').toggleClass('hide');
        $('.timesheet-start-end-time input').val('');
        $('.timesheet-duration input').val('');
    });

    // On hidden modal reminder set all values to empty and set the form action to ADD in case edit was clicked
    $("body").on("hidden.bs.modal", '.modal-reminder', function(e) {
        var $this = $(this);
        var rel_id = $this.find('input[name="rel_id"]').val();
        var rel_type = $this.find('input[name="rel_type"]').val();
        $this.find('form').attr('action', admin_url + 'misc/add_reminder/' + rel_id + '/' + rel_type);
        $this.find(':input:not([type=hidden]), textarea').val('');
        $this.find('input[type="checkbox"]').prop('checked', false);
        $this.find('select').selectpicker('val', '');
    });

    // On delete reminder reload the tables
    $("body").on('click', '.delete-reminder', function() {
        if (confirm_delete()) {
            requestGetJSON($(this).attr('href')).done(function(response) {
                alert_float(response.alert_type, response.message);
                reload_reminders_tables();
            });
        }
        return false;
    });

    /* Insert new checklist items on enter press */
    $("body").on('keypress', 'textarea[name="checklist-description"]', function(event) {
        if (event.which == '13') {
            $(this).focusout();
            add_task_checklist_item($(this).attr('data-taskid'));
            return false;
        }
    });

    /* Update tasks checklist items when focusing out */
    $("body").on('blur paste', 'textarea[name="checklist-description"]', function() {
        var textArea = $(this);
        var description = textArea.val();
        description = description.trim();
        var listid = textArea.parents('.checklist').data('checklist-id');

        $.post(admin_url + 'tasks/update_checklist_item', {
            description: description,
            listid: listid
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.can_be_template === true) {
                textArea.parents('.checklist').find('.save-checklist-template').removeClass('hide');
            }
            if (description === '') {
                $('#checklist-items').find('.checklist[data-checklist-id="' + listid + '"]').remove();
            }
        });
    });

    $("body").on('rendered.bs.select,refreshed.bs.select', 'select.checklist-items-template-select', function() {

        if (has_permission_tasks_checklist_items_delete == '1') {
            setTimeout(function() {
                var itemsHtml = $("body").find('.checklist-items-template-select ul.dropdown-menu li').not(':first-child');
                var itemsSelect = $("body").find('.checklist-items-template-select select option').not(':first-child');
                $.each(itemsSelect, function(i, item) {
                    var $item = $(item);
                    if ($(itemsHtml[i]).find('.checklist-item-template-remove').length == 0) {
                        $(itemsHtml[i]).find('a > span.text').after('<small class="checklist-item-template-remove" onclick="remove_checklist_item_template(' + $item.attr('value') + '); event.stopPropagation();"><i class="fa fa-remove"></i></small>');
                    }
                });
            }, 100);
        }
    });

    // Cant update if nothing is selected
    $('body').on('change', 'select.custom-field-multi-select', function() {
        var selected = $(this).selectpicker('val');
        if (selected.length == 0 || (selected.length == 1 && selected[0] == "")) {
            $(this).selectpicker('val', "");
        } else if (selected.length > 1 && selected[0] == "") {
            selected.splice(0, 1);
            $(this).selectpicker('val', selected);
        }
    });

    // Task single modal inline changes, eq start date, due date...
    $('body').on('change', '.task-single-inline-field', function() {
        var singleDateInputs = $('body').find('.task-single-inline-field');
        var data = {};
        $.each(singleDateInputs, function() {
            var name = $(this).attr('name');
            var val = $(this).val();
            var $parentwrap = $(this).parents('.task-single-inline-wrap');
            if (name == 'startdate' && val == '') {
                $parentwrap.addClass('text-danger');
            } else if (name == 'startdate' && val != '') {
                $parentwrap.removeClass('text-danger');
            }
            if ((name == 'startdate' && val != '') || name != 'startdate') {
                data[$(this).attr('name')] = val;
                // Name is required
                if (name != 'startdate' && val == '') {
                    $parentwrap.css('opacity', 0.5);
                } else {
                    $parentwrap.css('opacity', 1);
                }
            }
        });
        var $taskModal = $('#task-modal');
        var dTaskID = $taskModal.find('[data-task-single-id]').attr('data-task-single-id');
        $.post(admin_url + 'tasks/task_single_inline_update/' + dTaskID, data);
    });

    // When user select from checklist items template add this template
    $("body").on('change', '#task-modal #checklist_items_templates', function() {
        var val = $(this).val();
        var $taskModal = $('#task-modal');
        add_task_checklist_item(
            $taskModal.find('[data-task-single-id]').attr('data-task-single-id'),
            $(this).find('option[value="' + val + '"]').html().trim());

        $(this).selectpicker('val', '');
    });

    // Used on task comment date href, can be used directly to add the link in browser for scroll.
    $("body").on('click', '.task-date-as-comment-id', function(e) {
        e.preventDefault();
        var task_comment_temp = $(this).attr('href').split('#');
        var comment_position = $('#' + task_comment_temp[task_comment_temp.length - 1]).position();
        $("#task-modal").scrollTop(comment_position.top);
    });

    // Search by tags from the tables for any tag clicked.
    $("body").on('click', 'table.dataTable tbody .tags-labels .label-tag', function() {
        $(this).parents('table').DataTable().search($(this).find('.tag').text()).draw();
        $('div.dataTables_filter input').focus();
    });

    // Search by customer groups from the tables for any group clicked.
    $("body").on('click', 'table.dataTable tbody .customer-group-list', function() {
        $(this).parents('table').DataTable().search($(this).text()).draw();
        $('div.dataTables_filter input').focus();
    });

    // Search by lead status from the tables for any status clicked.
    $("body").on('click', 'table.dataTable tbody .lead-status', function() {
        var lead_status_name = $.trim($(this).text());
        var lead_status_select = $('#leads-filter-status select');
        lead_status_select.selectpicker('val', '');
        var selected_status = [];
        lead_status_select.find('option').filter(function() {
            if (this.text == lead_status_name) {
                selected_status.push($(this).attr('value'))
            }
        });
        lead_status_select.selectpicker('val', selected_status);
        lead_status_select.selectpicker('refresh');
        table_leads.DataTable().ajax.reload();
    });

    // Permissions change, apply necessary action to disable OWN or VIEW OWN
    $('[data-can-view-own],[data-can-view]').on('change', function() {
        var is_own_attr = $(this).attr('data-can-view-own');
        view_chk_selector = $(this).parents('tr').find('td input[' + (typeof is_own_attr !== typeof undefined && is_own_attr !== false ? 'data-can-view' : 'data-can-view-own') + ']');
        view_chk_selector.prop('checked', false);
        if ($(this).prop('checked') === true) {
            view_chk_selector.prop('disabled', true);
        } else {
            view_chk_selector.prop('disabled', false);
        }
    });

    // Init single task data
    if (typeof(taskid) !== 'undefined' && taskid !== '') { init_task_modal(taskid); }

    // Task checklist mark as complete/incomplete
    $("body").on('change', 'input[name="checklist-box"]', function() {
        requestGet(admin_url + 'tasks/checkbox_action/' + ($(this).parents('.checklist').data('checklist-id')) + '/' + ($(this).prop('checked') === true ? 1 : 0));
        recalculate_checklist_items_progress();
    });

    // Fix task checklist content textarea height
    $("body").on('keyup paste click', "textarea[name='checklist-description']", function(e) {
        do_task_checklist_items_height($(this));
    });

    // On click on task comment textarea make it tinymce, by default is plain textarea
    $("body").on('click', '#task_comment', function() {
        if (tinymce.editors.task_comment) { tinymce.remove('#task_comment'); }
        init_editor('#task_comment', _simple_editor_config());
    });

    // Delete task timesheet from the task single modal
    $("body").on('click', '.task-single-delete-timesheet', function(e) {
        e.preventDefault();
        if (confirm_delete()) {
            var _delete_timesheet_task_id = $(this).data('task-id');
            requestGet($(this).attr('href')).done(function(response) {
                init_task_modal(_delete_timesheet_task_id);
                setTimeout(function() {
                    reload_tasks_tables();
                    init_timers();
                }, 20);
            });
        }
    });

    // New timesheet add manually from task single modal
    $("body").on('click', '.task-single-add-timesheet', function(e) {
        e.preventDefault();
        var start_time = $("body").find('#task-modal input[name="timesheet_start_time"]').val();
        var end_time = $("body").find('#task-modal input[name="timesheet_end_time"]').val();
        var duration = $("body").find('#task-modal input[name="timesheet_duration"]').val();
        if ((start_time != '' && end_time != '') || duration != '') {
            var data = {};
            data.timesheet_duration = duration;
            data.start_time = start_time;
            data.end_time = end_time;
            data.timesheet_task_id = $(this).data('task-id');
            data.note = $("body").find('#task_single_timesheet_note').val();
            data.timesheet_staff_id = $("body").find('#task-modal select[name="single_timesheet_staff_id"]').val();
            $.post(admin_url + 'tasks/log_time', data).done(function(response) {
                response = JSON.parse(response);
                if (response.success == true) {
                    init_task_modal(data.timesheet_task_id);
                    alert_float('success', response.message);
                    setTimeout(function() {
                        reload_tasks_tables();
                    }, 20);
                } else {
                    alert_float('warning', response.message);
                }
            });
        }
    });

    // Copy task href/button event.
    $("body").on('click', '.copy_task_action', function() {
        var data = {};
        data.copy_from = $(this).data('task-copy-from');
        data.copy_task_assignees = $("body").find('#copy_task_assignees').prop('checked');
        data.copy_task_followers = $("body").find('#copy_task_followers').prop('checked');
        data.copy_task_checklist_items = $("body").find('#copy_task_checklist_items').prop('checked');
        data.copy_task_attachments = $("body").find('#copy_task_attachments').prop('checked');
        data.copy_task_status = $("body").find('input[name="copy_task_status"]:checked').val();
        $.post(admin_url + 'tasks/copy', data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var $taskModal = $('#_task_modal');
                if ($taskModal.is(':visible')) {
                    $taskModal.modal('hide');
                }
                init_task_modal(response.new_task_id);
                reload_tasks_tables();

            }
            alert_float(response.alert_type, response.message);
        });
        return false;
    });

    // Creates new task in specific milestones, the milestone is auto selected on the new task modal
    $("body").on('click', '.new-task-to-milestone', function(e) {
        e.preventDefault();
        var milestone_id = $(this).parents('.milestone-column').data('col-status-id');
        new_task(admin_url + 'tasks/task?rel_type=project&rel_id=' + project_id + '&milestone_id=' + milestone_id);
        $('body [data-toggle="popover"]').popover('hide');
    });

    // On shown task add/edit modal
    $("body").on("shown.bs.modal", '#_task_modal', function(e) {
        if (!$(e.currentTarget).hasClass('edit')) {
            $("body").find('#_task_modal #name').focus();
        } else {
            if ($(this).find('.tinymce-task').val().trim() !== '') {
                init_editor('.tinymce-task', { height: 200 });
            }
        }
        init_tags_inputs();
    });

    // Remove the tinymce description task editor
    $("body").on("hidden.bs.modal", '#_task_modal', function() {

        tinyMCE.remove('.tinymce-task');
        // Clear _ticket_message from single tickets in case user tried to convert ticket to task to prevent populating the fields again with the last ticket message click
        if (typeof(_ticket_message) != 'undefined') {
            _ticket_message = undefined;
        }

        if ($(this).attr('data-lead-id') != undefined && !$(this).attr('data-task-created')) {
            init_lead($(this).attr('data-lead-id'));
        }

        $('body #_task_modal .datepicker').datetimepicker('destroy');
        $('#_task').empty();

    });

    // Don't allow the task modal to close if lightbox is visible in for the task attachments
    // Used when user hit the ESC button
    // Empty task data
    $("body").on('hide.bs.modal', '#task-modal', function() {
        if ($('#lightbox').is(':visible') == true) { return false; }
        if (typeof(taskAttachmentDropzone) != 'undefined') {
            taskAttachmentDropzone.destroy();
        }
        tinyMCE.remove('#task_view_description');
    });

    // On task single modal hidden remove all html data
    $("body").on("hidden.bs.modal", '#task-modal', function() {
        $(this).find('.data').empty();
    });

    // On task single modal shown perform few actions
    $("body").on("shown.bs.modal", '#task-modal', function() {
        do_task_checklist_items_height();
        init_tags_inputs();
        fix_task_modal_left_col_height();
        $(document).off('focusin.modal');
        var current_url = window.location.href;
        if (current_url.indexOf('#comment_') > -1) {
            var task_comment_id = current_url.split('#comment_');
            task_comment_id = task_comment_id[task_comment_id.length - 1];
            $('[data-task-comment-href-id="' + task_comment_id + '"]').click();
        }
    });

    // On focus out on the taks modal single update the tags in case changes are found
    $("body").on('blur', '#task-modal ul.tagit li.tagit-new input', function() {
        setTimeout(function() { task_single_update_tags(); }, 100);
    });

    // Assign task to staff member
    $("body").on('change', 'select[name="select-assignees"]', function() {
        $("body").append('<div class="dt-loader"></div>');
        var data = {};
        data.assignee = $('select[name="select-assignees"]').val();
        if (data.assignee != '') {
            data.taskid = $(this).attr('data-task-id');
            $.post(admin_url + 'tasks/add_task_assignees', data).done(function(response) {
                $("body").find('.dt-loader').remove();
                response = JSON.parse(response);
                reload_tasks_tables();
                _task_append_html(response.taskHtml);
            });
        }
    });

    // Add follower to task
    $("body").on('change', 'select[name="select-followers"]', function() {
        var data = {};
        data.follower = $('select[name="select-followers"]').val();
        if (data.follower != '') {
            data.taskid = $(this).attr('data-task-id');
            $("body").append('<div class="dt-loader"></div>');
            $.post(admin_url + 'tasks/add_task_followers', data).done(function(response) {
                response = JSON.parse(response);
                $("body").find('.dt-loader').remove();
                _task_append_html(response.taskHtml);
            });
        }
    });

    // Task single moda tracking stats close
    $("body").on('click', '.close-task-stats', function() {
        $('#task-tracking-stats-modal').modal('hide');
    });

    // Remove tracking status div because its appended automatically to the DOM on each click
    $("body").on("hidden.bs.modal", '#task-tracking-stats-modal', function() {
        $('#tracking-stats').remove();
    });

    // Task modal single chart for logged time by assigned users
    $("body").on('show.bs.modal', '#task-tracking-stats-modal', function() {
        var tracking_chart_selector = $("body").find('#task-tracking-stats-chart');
        setTimeout(function() {
            if (typeof(task_track_chart) != 'undefined') {
                task_track_chart.destroy();
            }
            task_track_chart = new Chart(tracking_chart_selector, {
                type: 'line',
                data: task_tracking_stats_data,
                options: {
                    legend: {
                        display: false,
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItems, data) {
                                return decimalToHM(tooltipItems.yLabel);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                userCallback: function(label, index, labels) {
                                    return decimalToHM(label);
                                },
                            }
                        }]
                    },
                }
            });
        }, 800);
    });

    // In lead modal issue with reminder modal height
    $("body").on("shown.bs.modal", '#sync_data_proposal_data', function() {
        if ($('#sync_data_proposal_data').data('rel-type') == 'lead') {
            $('#lead-modal .data').eq(0).css('height', ($('#sync_data_proposal_data .modal-content').height() + 80) + 'px').css('overflow-x', 'hidden');
        }
    });

    // Remove on lead modal reminder inline style
    $("body").on("hidden.bs.modal", '#sync_data_proposal_data', function() {
        if ($('#sync_data_proposal_data').data('rel-type') == 'lead') {
            $('#lead-modal .data').prop('style', '');
        }
    });

    // Maybe lead ID passed from url?
    if (typeof(c_leadid) != 'undefined' && c_leadid != '') { init_lead(c_leadid); }

    // Status color change
    $("body").on('click', '.leads-kan-ban .cpicker', function() {
        var color = $(this).data('color');
        var status_id = $(this).parents('.panel-heading-bg').data('status-id');
        $.post(admin_url + 'leads/change_status_color', {
            color: color,
            status_id: status_id
        });
    });

    // Lead edit toggle view/edit
    $("body").on('click', '[lead-edit]', function(e) {
        e.preventDefault();
        var $leadEdit = $('body .lead-edit');
        $('body .lead-view').toggleClass('hide');
        $leadEdit.toggleClass('hide');
        if (!$leadEdit.hasClass('hide')) {
            var $address = $('#lead-modal').find('#address');
            var scrollHeight = $address[0].scrollHeight;
            if ($address.is('textarea')) {
                $address.height(0).height(scrollHeight - 15);
                $address.css('padding-top', '9px');
            }
        }
    });

    // Creates new lead with pre-selected status from leads kan ban
    $("body").on('click', '.new-lead-from-status', function(e) {
        e.preventDefault();
        var status_id = $(this).parents('.kan-ban-col').data('col-status-id');
        init_lead_modal_data(undefined, admin_url + 'leads/lead?status_id=' + status_id);
        $('body [data-toggle="popover"]').popover('hide');
    });

    // When converting lead to customer, custom fields merging options.
    $("body").on('change', 'input.include_leads_custom_fields', function() {
        var val = $(this).val();
        var fieldid = $(this).data('field-id');
        val == 2 ? $('#merge_db_field_' + fieldid).removeClass('hide') : $('#merge_db_field_' + fieldid).addClass('hide')
        val == 3 ? $('#merge_db_contact_field_' + fieldid) : $('#merge_db_contact_field_' + fieldid).addClass('hide');
    });

    // Check if calendar exists in the DOM and init.
    if (calendar_selector.length > 0) {
        validate_calendar_form();
        var calendar_settings = {
            themeSystem: 'bootstrap3',
            customButtons: {},
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,viewFullCalendar,calendarFilter'
            },
            editable: false,
            eventLimit: parseInt(app_calendar_events_limit) + 1,

            views: {
                day: {
                    eventLimit: false
                }
            },
            defaultView: app_default_view_calendar,
            isRTL: (isRTL == 'true' ? true : false),
            eventStartEditable: false,
            timezone: app_timezone,
            firstDay: parseInt(app_calendar_first_day),
            year: moment.tz(app_timezone).format("YYYY"),
            month: moment.tz(app_timezone).format("M"),
            date: moment.tz(app_timezone).format("DD"),
            loading: function(isLoading, view) {
                isLoading && $('#calendar .fc-header-toolbar .btn-default').addClass('btn-info').removeClass('btn-default').css('display', 'block');
                !isLoading ? $('.dt-loader').addClass('hide') : $('.dt-loader').removeClass('hide');
            },
            eventSources: [{
                url: admin_url + 'utilities/get_calendar_data',
                data: function() {
                    var params = {};
                    $('#calendar_filters').find('input:checkbox:checked').map(function() {
                        params[$(this).attr('name')] = true;
                    }).get();
                    if (!jQuery.isEmptyObject(params)) {
                        params['calendar_filters'] = true;
                    }
                    return params;
                },
                type: 'POST',
                error: function() {
                    console.error('There was error fetching calendar data');
                },
            }, ],
            eventLimitClick: function(cellInfo, jsEvent) {
                $('#calendar').fullCalendar('gotoDate', cellInfo.date);
                $('#calendar').fullCalendar('changeView', 'basicDay');
            },
            eventRender: function(event, element) {
                element.attr('title', event._tooltip);
                element.attr('onclick', event.onclick);
                element.attr('data-toggle', 'tooltip');
                if (!event.url) {
                    element.click(function() { view_event(event.eventid); });
                }
            },
            dayClick: function(date, jsEvent, view) {
                var d = date.format();
                if (!$.fullCalendar.moment(d).hasTime()) {
                    d += ' 00:00';
                }
                var vformat = (app_time_format == 24 ? app_date_format + ' H:i' : app_date_format + ' g:i A');
                var fmt = new DateFormatter();
                var d1 = fmt.formatDate(new Date(d), vformat);
                $("input[name='start'].datetimepicker").val(d1);
                $('#newEventModal').modal('show');
                return false;
            }
        }
        if ($("body").hasClass('dashboard')) {
            calendar_settings.customButtons.viewFullCalendar = {
                text: appLang.calendar_expand,
                click: function() {
                    window.location.href = admin_url + 'utilities/calendar';
                }
            }
        }
        calendar_settings.customButtons.calendarFilter = {
            text: appLang.filter_by.toLowerCase(),
            click: function() {
                slideToggle('#calendar_filters');
            }
        }
        if (is_staff_member == 1) {
            if (google_api != '') {
                calendar_settings.googleCalendarApiKey = google_api;
            }
            if (calendarIDs != '') {
                calendarIDs = JSON.parse(calendarIDs);
                if (calendarIDs.length != 0) {
                    if (google_api != '') {
                        for (var i = 0; i < calendarIDs.length; i++) {
                            var _gcal = {};
                            _gcal.googleCalendarId = calendarIDs[i];
                            calendar_settings.eventSources.push(_gcal);
                        }
                    } else {
                        console.error('You have setup Google Calendar IDs but you dont have specified Google API key. To setup Google API key navigate to Setup->Settings->Misc');
                    }
                }
            }
        }
        // Init calendar
        calendar_selector.fullCalendar(calendar_settings);
        var new_event = get_url_param('new_event');
        if (new_event) {
            $("input[name='start'].datetimepicker").val(get_url_param('date'));
            $('#newEventModal').modal('show');
        }
    }

    // On select with name tax apply necessary actions if tax2 exists too
    $("body").on('change', 'select[name="tax"]', function() {
        var sp_tax_2 = $("body").find('select[name="tax2"]');
        var sp_tax_1 = $(this);
        if (sp_tax_1.val() != '') {
            sp_tax_2.prop('disabled', false);
        } else {
            sp_tax_2.prop('disabled', true);
            if (sp_tax_2.val() != '') {
                sp_tax_1.val(sp_tax_2.val());
                sp_tax_2.val('');
                sp_tax_1.selectpicker('refresh');
            }
        }
        sp_tax_2.selectpicker('refresh');
    });

    $('body').on('click', '#invoice_create_credit_note', function(e) {
        if ($(this).attr('data-status') == 2) {
            return true;
        } else {
            var $m = $('#confirm_credit_note_create_from_invoice');
            $m.modal('show');
            $m.find('#confirm-invoice-credit-note').attr('href', $(this).attr('href'));
            e.preventDefault();
        }
    });

    $('body').on('change blur', '.apply-credits-to-invoice .apply-credits-field', function() {

        var $applyCredits = $('#apply_credits');
        var $amountInputs = $applyCredits.find('input.apply-credits-field');
        var total = 0;
        var creditsRemaining = $applyCredits.attr('data-credits-remaining');

        $.each($amountInputs, function() {
            if ($(this).valid() == true) {
                var amount = $(this).val();
                amount = parseFloat(amount);
                if (!isNaN(amount)) {
                    total += amount;
                } else {
                    $(this).val(0)
                }
            }
        });

        $applyCredits.find('#credits-alert').remove();
        $applyCredits.find('.amount-to-credit').html(accounting.formatNumber(total));
        if (creditsRemaining < total) {
            $('.credits-table').before($('<div/>', {
                id: 'credits-alert',
                class: 'alert alert-danger',
            }).html(appLang.credit_amount_bigger_then_credit_note_remaining_credits));
            $applyCredits.find('[type="submit"]').prop('disabled', true);
        } else {
            $applyCredits.find('.credit-note-balance-due').html(accounting.formatNumber(creditsRemaining - total));
            $applyCredits.find('[type="submit"]').prop('disabled', false);
        }

    });

    $('body').on('change blur', '.apply-credits-from-invoice .apply-credits-field', function() {

        var $applyCredits = $('#apply_credits');
        var $amountInputs = $applyCredits.find('input.apply-credits-field');
        var total = 0;
        var invoiceBalanceDue = $applyCredits.attr('data-balance-due');

        $.each($amountInputs, function() {
            if ($(this).valid() == true) {
                var amount = $(this).val();
                amount = parseFloat(amount);
                if (!isNaN(amount)) {
                    total += amount;
                } else {
                    $(this).val(0)
                }
            }
        });

        $applyCredits.find('#credits-alert').remove();
        $applyCredits.find('.amount-to-credit').html(accounting.formatNumber(total));
        if (total > invoiceBalanceDue) {
            $('.credits-table').before($('<div/>', {
                id: 'credits-alert',
                class: 'alert alert-danger',
            }).html(appLang.credit_amount_bigger_then_invoice_balance));
            $applyCredits.find('[type="submit"]').prop('disabled', true);
        } else {
            $applyCredits.find('.invoice-balance-due').html(accounting.formatNumber(invoiceBalanceDue - total));
            $applyCredits.find('[type="submit"]').prop('disabled', false);
        }
    });

    // Leads integrations notify type
    $('input[name="notify_type"]').on('change', function() {
        var val = $('input[name="notify_type"]:checked').val();
        var specific_staff_notify = $('#specific_staff_notify');
        var role_notify = $('#role_notify');
        if (val == 'specific_staff') {
            specific_staff_notify.removeClass('hide');
            role_notify.addClass('hide');
        } else if (val == 'roles') {
            specific_staff_notify.addClass('hide');
            role_notify.removeClass('hide');
        } else if (val == 'assigned') {
            specific_staff_notify.addClass('hide');
            role_notify.addClass('hide');
        }
    });

    // Auto focus the lead name if user is adding new lead
    $("body").on("shown.bs.modal", '#lead-modal', function(e) {
        custom_fields_hyperlink();
        if ($("body").find('#lead-modal input[name="leadid"]').length == 0) {
            $("body").find('#lead-modal input[name="name"]').focus();
        }
    });

    // On hidden lead modal some actions need to be operated here
    $('#lead-modal').on("hidden.bs.modal", function(event) {
        $(this).data('bs.modal', null);
        $('#lead_reminder_modal').html('');
        // clear the hash
        if (!$('#lead-modal').is(':visible')) {
            history.pushState("", document.title, window.location.pathname + window.location.search);
        }
        $('body #lead-modal .datetimepicker').datetimepicker('destroy');
        if (typeof(leadAttachmentsDropzone) != 'undefined') { leadAttachmentsDropzone.destroy(); }
    });

    // + button for adding more attachments
    var addMoreAttachmentsInputKey = 1;
    $("body").on('click', '.add_more_attachments', function() {
        if ($(this).hasClass('disabled')) { return false; }
        var total_attachments = $('.attachments input[name*="attachments"]').length;
        if ($(this).data('ticket') && total_attachments >= app_maximum_allowed_ticket_attachments) {
            return false;
        }
        var newattachment = $('.attachments').find('.attachment').eq(0).clone().appendTo('.attachments');
        newattachment.find('input').removeAttr('aria-describedby aria-invalid');
        newattachment.find('.has-error').removeClass('has-error');
        newattachment.find('input').attr('name', 'attachments[' + addMoreAttachmentsInputKey + ']').val('');
        newattachment.find('p[id*="error"]').remove();
        newattachment.find('i').removeClass('fa-plus').addClass('fa-minus');
        newattachment.find('button').removeClass('add_more_attachments').addClass('remove_attachment').removeClass('btn-success').addClass('btn-danger');
        addMoreAttachmentsInputKey++;
    });

    // Remove attachment
    $("body").on('click', '.remove_attachment', function() {
        $(this).parents('.attachment').remove();
    });

    // Fix lead conver to customer modal, make the modal empty
    $("body").on("hidden.bs.modal", '#convert_lead_to_client_modal', function(event) {
        $(this).data('bs.modal', null);
    });

    // Set hash on modal tab change
    $("body").on('click', '#lead-modal a[data-toggle="tab"]', function() {
        if (this.hash == '#tab_lead_profile' ||
            this.hash == '#attachments' ||
            this.hash == '#lead_notes' ||
            this.hash == '#lead_activity') {
            window.location.hash = this.hash;
        } else {
            history.pushState("", document.title, window.location.pathname + window.location.search);
        }
        // Lead modal backdrop is showing some issues with index, is fixed after triggering document resize
        $(document).resize();
    });

    // Manually add lead activity
    $("body").on('click', '#lead_enter_activity', function() {
        var message = $('#lead_activity_textarea').val();
        var aLeadId = $('#lead-modal').find('input[name="leadid"]').val();
        if (message == '') { return; }
        $.post(admin_url + 'leads/add_activity', {
            leadid: aLeadId,
            activity: message
        }).done(function(response) {
            response = JSON.parse(response);
            _lead_init_data(response, response.id);
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
    });

    // Submit notes on lead modal do ajax not the regular request
    $("body").on('submit', '#lead-modal #lead-notes', function() {
        var form = $(this);
        var data = $(form).serialize();
        $.post(form.attr('action'), data).done(function(response) {
            response = JSON.parse(response);
            _lead_init_data(response, response.id);
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
        return false;
    });

    // Add additional server params $_POST
    var LeadsServerParams = {
        "custom_view": "[name='custom_view']",
        "assigned": "[name='view_assigned']",
        "status": "[name='view_status[]']",
        "source": "[name='view_source']",
    }

    // Init the table
    table_leads = $('table.table-leads');
    if (table_leads.length) {
        var headers_leads = table_leads.find('th');
        initDataTable(table_leads, admin_url + 'leads/table', [headers_leads.length - 1, 0], [headers_leads.length - 1, 0], LeadsServerParams, [table_leads.find('th.date-created').index(), 'DESC']);
        $.each(LeadsServerParams, function(i, obj) {
            $('select' + obj).on('change', function() {
                if ($(this).val() == 'lost' || $(this).val() == 'junk') {
                    $("[name='view_status[]']").prop('disabled', true).selectpicker('refresh');
                } else {
                    $("[name='view_status[]']").prop('disabled', false).selectpicker('refresh');
                }
                table_leads.DataTable().ajax.reload()
                    .columns.adjust()
                    .responsive.recalc();
            });
        });
    }

    // When adding if lead is contacted today
    $("body").on('change', 'input[name="contacted_today"]', function() {
        var checked = $(this).prop('checked');
        var lsdc = $('.lead-select-date-contacted');
        (checked == false ? lsdc.removeClass('hide') : lsdc.addClass('hide'));
    });

    // Lead modal show contacted indicator input
    $("body").on('change', 'input[name="contacted_indicator"]', function() {
        var lsdc = $('.lead-select-date-contacted');
        ($(this).val() == 'yes' ? lsdc.removeClass('hide') : lsdc.addClass('hide'));
    });

    // Fix for checkboxes ID duplicate when table goes responsive
    $("body").on('click', 'table.dataTable tbody td:first-child', function() {
        var tr = $(this).parents('tr');
        if ($(this).parents('table').DataTable().row(tr).child.isShown()) {
            var switchBox = $(tr).next().find('input.onoffswitch-checkbox');
            if (switchBox.length > 0) {
                var switchBoxId = Math.random().toString(16).slice(2);
                switchBox.attr('id', switchBoxId).next().attr('for', switchBoxId);
            }
        }
    });

    // Show please wait text on button where data-loading-text is added
    $("body").on('click', '[data-loading-text]', function() {
        var form = $(this).data('form');
        if (form != null) {
            if ($(form).valid()) { $(this).button('loading'); }
        } else {
            $(this).button('loading');
        }
    });

    // Custom close function for reminder modals in case is modal in modal
    $("body").on('click', '.close-reminder-modal', function() {
        $(".reminder-modal-" + $(this).data('rel-type') + '-' + $(this).data('rel-id')).modal('hide');
    });

    // Recalculate responsive for hidden tables
    $("body").on('shown.bs.tab', 'a[data-toggle="tab"]', function(e) {
        $($.fn.dataTable.tables(true)).DataTable().responsive.recalc();
    });

    // Init are you sure on forms
    $('form').not('#single-ticket-form,#calendar-event-form,#proposal-form').areYouSure();

    // For inline tinymce editors when content is blank a message is shown, on click this message should be hidden.
    $("body").on('click', '.editor-add-content-notice', function() {
        $(this).remove();
        tinymce.triggerSave();
    });

    // Global on change for mass delete to hide all other elements for bulk actions
    $('.bulk_actions').on('change', 'input[name="mass_delete"]', function() {
        var $bulkChange = $('#bulk_change');
        if ($(this).prop('checked') == true) {
            $bulkChange.find('select').selectpicker('val', '');
        }
        $bulkChange.toggleClass('hide');
        $('.mass_delete_separator').toggleClass('hide');
    });

    // Clear todo modal values when modal is hidden
    $("body").on("hidden.bs.modal", '#__todo', function() {
        var $toDo = $('#__todo');
        $toDo.find('input[name="todoid"]').val('');
        $toDo.find('textarea[name="description"]').val('');
        $toDo.find('.add-title').addClass('hide');
        $toDo.find('.edit-title').addClass('hide');
    });

    // Focus staff todo description
    $("body").on("shown.bs.modal", '#__todo', function() {
        var $toDo = $('#__todo');
        $toDo.find('textarea[name="description"]').focus();
        if ($toDo.find('input[name="todoid"]').val() != '') {
            $toDo.find('.add-title').addClass('hide');
            $toDo.find('.edit-title').removeClass('hide');
        } else {
            $toDo.find('.add-title').removeClass('hide');
            $toDo.find('.edit-title').addClass('hide');
        }
    });

    // Focus search input on click
    $('#top_search_button button').on('click', function() {
        var $searchInput = $('#search_input');
        $searchInput.focus();
        if ($(this).hasClass('search_remove')) {
            $(this).html('<i class="fa fa-search"></i>').removeClass('search_remove');
            original_top_search_val = '';
            $('#search_results').html('');
            $searchInput.val('');
        }
    });

    // Fix for dropdown search to close if user click anyhere on html except on dropdown
    $("body").click(function(e) {
        if (!$(e.target).parents('#top_search_dropdown').hasClass('search-results')) {
            $('#top_search_dropdown').remove();
        }
    });

    // Init tooltips
    $("body").tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    // Init popovers
    $("body").popover({
        selector: '[data-toggle="popover"]',
    });

    // Remove tooltip fix on body click (in case user clicked link and tooltip stays open)
    $("body").on('click', function() {
        $('.tooltip').remove();
    });

    // Close all popovers if user click on body and the click is not inside the popover content area
    $("body").on('click', function(e) {
        $('[data-toggle="popover"],.manual-popover').each(function() {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });

    // Do not close the dropdownmenu for filter when filtering
    $("body").on('click', '._filter_data ul.dropdown-menu li a,.not-mark-as-read-inline,.not_mark_all_as_read a', function(e) {
        e.stopPropagation();
        e.preventDefault();
    });

    // Add are you sure on all delete links (onclick is not included here)
    $("body").on('click', '._delete', function(e) {
        if (confirm_delete()) { return true; }
        return false;
    });

    // On shown for all modals
    $("body").on("shown.bs.modal", '.modal', function() {
        // Fix for all modals scroll..
        $("body").addClass('modal-open');
        // Close the top timers dropdown in case user click on some task
        $("body").find('#started-timers-top').parents('li').removeClass('open');
    });

    // On hidden for all modals
    $("body").on("hidden.bs.modal", '.modal', function(event) {
        $('.modal:visible').length && $(document.body).addClass('modal-open');
        $(this).data('bs.modal', null);
    });

    // Activity log datepicker on change
    $('.datepicker.activity-log-date').on('change', function() {
        table_activity_log.DataTable().ajax.reload();
    });

    // For expenses and recurring tasks
    $("body").on('change', '[name="repeat_every"],[name="recurring"]', function() {
        var val = $(this).val();
        val == 'custom' ? $('.recurring_custom').removeClass('hide') : $('.recurring_custom').addClass('hide');
        if (val != '' && val != 0) {
            $("body").find('#recurring_ends_on').removeClass('hide');
        } else {
            $("body").find('#recurring_ends_on').addClass('hide');
            $("body").find('#recurring_ends_on input').val('');
        }
    });

    $('select[name="range"]').on('change', function() {
        var $period = $('.period');
        if ($(this).val() == 'period') {
            $period.removeClass('hide');
        } else {
            $period.addClass('hide');
            $period.find('input').val('');
        }
    });

    // On mass_select all select all the availble rows in the tables.
    $("body").on('change', '#mass_select_all', function() {
        var to, rows, checked;
        to = $(this).data('to-table');

        rows = $('.table-' + to).find('tbody tr');
        checked = $(this).prop('checked');
        $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input').prop('checked', checked);
        });
    });

    // Init the editor for email templates where changing data is allowed
    $("body").on('show.bs.modal', '.modal.email-template', function() {
        init_editor($(this).data('editor-id'), {
            urlconverter_callback: 'merge_field_format_url'
        });
    });

    // Remove the editor inited for the email sending templates where changing the email template data is allowed
    $("body").on("hidden.bs.modal", '.modal.email-template', function() {
        tinymce.remove($(this).data('editor-id'));
    });

    // Customizer close and remove open from session
    $('.close-customizer').on('click', function(e) {
        e.preventDefault();

        setup_menu.addClass(isRTL == 'true' ? "fadeOutRight" : "fadeOutLeft");
        // Clear the session for setup menu so in reload wont be closed
        requestGet('misc/set_setup_menu_closed');
    });

    // Open customizer and add that is open to session
    $('.open-customizer').on('click', function(e) {
        e.preventDefault();

        if (setup_menu.hasClass(isRTL == 'true' ? "fadeOutRight" : "fadeOutLeft")) {
            setup_menu.removeClass(isRTL == 'true' ? "fadeOutRight" : "fadeOutLeft");
        }

        setup_menu.addClass('display-block ' + (isRTL == 'true' ? "fadeInRight" : "fadeInLeft"));
        // Set session that the setup menu is open in case of reload
        if (!is_mobile()) {
            requestGet('misc/set_setup_menu_open');
        }
        mainWrapperHeightFix();
    });

    // Change live the colors for colorpicker in kanban/pipeline
    $("body").on('click', '.cpicker', function() {
        var color = $(this).data('color');
        // Clicked on the same selected color
        if ($(this).hasClass('cpicker-big')) { return false; }

        $(this).parents('.cpicker-wrapper').find('.cpicker-big').removeClass('cpicker-big').addClass('cpicker-small');
        $(this).removeClass('cpicker-small', 'fast').addClass('cpicker-big', 'fast');
        if ($(this).hasClass('kanban-cpicker')) {
            $(this).parents('.panel-heading-bg').css('background', color);
            $(this).parents('.panel-heading-bg').css('border', '1px solid ' + color);
        } else if ($(this).hasClass('calendar-cpicker')) {
            $("body").find('._event input[name="color"]').val(color);
        }
    });

    // Notification profile link click
    $("body").on('click', '.notification_link', function() {
        var link = $(this).data('link');
        var not_href;
        not_href = link.split('#');
        if (!not_href[1]) { window.location.href = link; }
    });

    /* Check if postid in notification url to open the newsfeed */
    $("body").on('click', '.notifications a.notification-top, .notification_link', function(e) {

        e.preventDefault();
        var $notLink = $(this);
        var not_href, not_href_id;

        not_href = $notLink.hasClass('notification_link') ? $notLink.data('link') : e.currentTarget.href;

        not_href = not_href.split('#');
        var notRedirect = true;;
        if (not_href[1] && not_href[1].indexOf('=') > -1) {
            notRedirect = false;
            not_href_id = not_href[1].split('=')[1];
            if (not_href[1].indexOf('postid') > -1) {
                postid = not_href_id;
                $('.open_newsfeed').click();
            } else if (not_href[1].indexOf('taskid') > -1) {
                init_task_modal(not_href_id);
            } else if (not_href[1].indexOf('leadid') > -1) {
                init_lead(not_href_id);
            } else if (not_href[1].indexOf('eventid') > -1) {
                view_event(not_href_id);
            }
        }
        if (!$notLink.hasClass('desktopClick')) {
            $notLink.parent('li').find('.not-mark-as-read-inline').click();
        }
        if (notRedirect) {
            setTimeout(function() {
                window.location.href = not_href;
            }, 50);
        }
    });

    // Set notifications to read when notifictions dropdown is opened
    $('.notifications-wrapper').on('show.bs.dropdown', function() {
        var total = notifications_wrapper.find('.notifications').attr('data-total-unread');
        if (total > 0) {
            $.post(admin_url + 'misc/set_notifications_read').done(function(response) {
                response = JSON.parse(response);
                if (response.success == true) {
                    document.title = doc_initial_title;
                    $(".icon-notifications").addClass('hide');
                }
            });
        }
    });

    // Tables
    init_table_tickets();
    init_table_announcements();
    init_table_staff_projects();

    // Ticket pipe log and system activity log
    table_activity_log = $('table.table-activity-log')
    if (table_activity_log.length) {
        var ActivityLogServerParams = [];
        ActivityLogServerParams['activity_log_date'] = '[name="activity_log_date"]';
        initDataTable(table_activity_log, window.location.href, 'undefined', 'undefined', ActivityLogServerParams, [1, 'DESC']);
    }

    table_invoices = $('table.table-invoices');
    table_estimates = $('table.table-estimates');

    if (table_invoices.length > 0 || table_estimates.length > 0) {

        // Invoices additional server params
        var Invoices_Estimates_ServerParams = {};
        var Invoices_Estimates_Filter = $('._hidden_inputs._filters input');

        $.each(Invoices_Estimates_Filter, function() {
            Invoices_Estimates_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });

        if (table_invoices.length) {
            // Invoices tables
            initDataTable(table_invoices, admin_url + 'invoices/table', 'undefined', 'undefined', Invoices_Estimates_ServerParams, [
                [3, 'DESC'],
                [0, 'DESC']
            ]);
        }
        if (table_estimates.length) {
            // Estimates table
            initDataTable(table_estimates, admin_url + 'estimates/table', 'undefined', 'undefined', Invoices_Estimates_ServerParams, [
                [3, 'DESC'],
                [0, 'DESC']
            ]);
        }
    }

    table_tasks = $('.table-tasks');
    if (table_tasks.length) {
        var TasksServerParams = {},
            Tasks_Filters;
        Tasks_Filters = $('._hidden_inputs._filters._tasks_filters input');
        $.each(Tasks_Filters, function() {
            TasksServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });

        // Tasks not sortable
        var _tns = [(table_tasks.find('th').length - 1)];
        var tasksTableURL = admin_url + 'tasks/table';

        if ($("body").hasClass('tasks_page')) {
            _tns.push(0);
            tasksTableURL += '?bulk_actions=true';
        }

        _table_api = initDataTable(table_tasks, tasksTableURL, _tns, _tns, TasksServerParams, [table_tasks.find('th.duedate').index(), 'ASC']);

        if (_table_api && $("body").hasClass('dashboard')) {
            _table_api.column(3).visible(false, false)
                .column(4).visible(false, false)
                .column(table_tasks.find('.table-tasks-options').index()).visible(false, false)
                .columns.adjust();
        }
    }

    // Send file modal populate the hidden files when is shown
    $('#send_file').on('show.bs.modal', function(e) {
        var $sendFile = $('#send_file');
        $sendFile.find('input[name="filetype"]').val($($(e.relatedTarget)).data('filetype'));
        $sendFile.find('input[name="file_path"]').val($($(e.relatedTarget)).data('path'));
        $sendFile.find('input[name="file_name"]').val($($(e.relatedTarget)).data('file-name'));
        if ($('input[name="email"]').length > 0) {
            $sendFile.find('input[name="send_file_email"]').val($('input[name="email"]').val());
        }
    });

    $('#send_file form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // Set password checkbox change
    $("body").on('change', 'input[name="send_set_password_email"]', function() {
        $("body").find('.client_password_set_wrapper').toggleClass('hide');
    });

    // Todo status change checkbox click
    $("body").on('change', '.todo input[type="checkbox"]', function() {
        var finished = $(this).prop('checked') === true ? 1 : 0;
        var id = $(this).val();
        window.location.href = admin_url + 'todo/change_todo_status/' + id + '/' + finished;
    });

    var todos_sortable = $(".todos-sortable");
    if (todos_sortable.length > 0) {
        // Makes todos sortable
        todos_sortable = todos_sortable.sortable({
            connectWith: ".todo",
            items: "li",
            handle: '.dragger',
            appendTo: "body",
            update: function(event, ui) {
                if (this === ui.item.parent()[0]) {
                    update_todo_items();
                }
            }
        });
    }

    // Newsfeed close and open
    $("body").on('click', '.open_newsfeed,.close_newsfeed', function(e) {
        e.preventDefault();
        if (typeof($(this).data('close')) == 'undefined') {
            requestGet('newsfeed/get_data').done(function(response) {
                $('#newsfeed').html(response);
                load_newsfeed(postid);
                init_newsfeed_form();
                init_selectpicker();
                init_lightbox();
            });
        } else if ($(this).data('close') === true) {
            newsFeedDropzone.destroy();
            $('#newsfeed').html('');
            newsfeed_posts_page = 0;
            track_load_post_likes = 0;
            track_load_comment_likes = 0;
            postid = undefined;
        }
        $('#newsfeed').toggleClass('hide');
        $("body").toggleClass('noscroll');
    });

    if ($('[data-newsfeed-auto]').length > 0) { $('.open_newsfeed').click(); }

    // When adding comment if user press enter to submit comment too for newsfeed comments
    $("body").on('keyup', '.comment-input input', function(event) {
        if (event.keyCode == 13) { add_comment(this); }
    });

    // Showing post likes modal
    $('#modal_post_likes').on('show.bs.modal', function(e) {
        track_load_post_likes = 0;
        $('#modal_post_likes_wrapper').empty();
        $('.likes_modal .modal-footer').removeClass('hide');
        var invoker = $(e.relatedTarget);
        var postid = $(invoker).data('postid')
        post_likes_total_pages = $(invoker).data('total-pages');
        $(".load_more_post_likes").attr('data-postid', postid);
        load_post_likes(postid);
    });

    // Showing comment likes modal
    $('#modal_post_comment_likes').on('show.bs.modal', function(e) {
        $('#modal_comment_likes_wrapper').empty();
        track_load_comment_likes = 0;
        $('.likes_modal .modal-footer').removeClass('hide');
        var invoker = $(e.relatedTarget);
        var commentid = $(invoker).data('commentid');
        comment_likes_total_pages = $(invoker).data('total-pages');
        $(".load_more_post_comment_likes").attr('data-commentid', commentid);
        load_comment_likes(commentid);
    });

    // Load more post likes from modal
    $('.load_more_post_likes').on('click', function(e) {
        e.preventDefault();
        load_post_likes($(this).data('postid'));
    });

    // Load more comment likes from modal
    $('.load_more_post_comment_likes').on('click', function(e) {
        e.preventDefault();
        load_comment_likes($(this).data('commentid'));
    });

    // Add post attachment used for dropzone
    $('.add-attachments').on('click', function(e) {
        e.preventDefault();
        $('#post-attachments').toggleClass('hide');
    });

    // Set post visibility on change select by department
    $("body").on('change', '#post-visibility,#visible_customer_profile_tabs', function() {
        var value = $(this).val();
        if (value != null) {
            if (value.indexOf('all') > -1) {
                if (value.length > 1) {
                    value.splice(0, 1);
                    $(this).selectpicker('val', value);
                }
            }
        }
    });

    // Init invoices top stats
    init_invoices_total();
    // Init expenses total
    init_expenses_total();
    // Make items sortable
    init_estimates_total();
    // Make items sortable
    init_items_sortable();

    $('.settings-textarea-merge-field').on('click', function(e) {
        e.preventDefault();
        var mergeField = $(this).text().trim();
        var textArea = $('textarea[name="settings[' + $(this).data('to') + ']"]');
        textArea.val(textArea.val() + "\n" + mergeField);
    });

    if ($("body").hasClass('estimates-pipeline')) {
        var estimate_id = $('input[name="estimateid"]').val();
        estimate_pipeline_open(estimate_id);
    }

    if ($("body").hasClass('proposals-pipeline')) {
        var proposal_id = $('input[name="proposalid"]').val();
        proposal_pipeline_open(proposal_id);
    }


    $("body").on('submit', '._transaction_form', function() {

        // On submit re-calculate total and reorder the items for all cases.
        calculate_total();

        $('body').find('#items-warning').remove();
        var $itemsTable = $(this).find('table.items');
        var $previewItem = $itemsTable.find('.main');

        if ($previewItem.find('[name="description"]').val().trim().length > 0 &&
            $previewItem.find('[name="rate"]').val().trim().length > 0) {

            $itemsTable.before('<div class="alert alert-warning mbot20" id="items-warning">' + appLang.item_forgotten_in_preview + '<i class="fa fa-angle-double-down pointer pull-right fa-2x" style="margin-top:-4px;" onclick="add_item_to_table(\'undefined\',\'undefined\',undefined); return false;"></i></div>');

            $('html,body').animate({
                scrollTop: $("#items-warning").offset().top
            });

            return false;

        } else {
            if ($itemsTable.find('.item').length == 0) {
                $itemsTable.before('<div class="alert alert-warning mbot20" id="items-warning">' + appLang.no_items_warning + '</div>');
                $('html,body').animate({
                    scrollTop: $("#items-warning").offset().top
                });
                return false;
            }
        }

        reorder_items();

        // Remove the disabled attribute from the disabled fields becuase if they are disabled won't be sent with the request.
        $('select[name="currency"]').prop('disabled', false);
        $('select[name="project_id"]').prop('disabled', false);
        $('input[name="date"]').prop('disabled', false);

        // Add disabled to submit buttons
        $(this).find('.transaction-submit').prop('disabled', true);

        return true;
    });

    $("body").on('click', '.transaction-submit', function() {
        var that = $(this);
        var form = that.parents('form._transaction_form');
        if (form.valid()) {
            if (that.hasClass('save-as-draft')) {
                form.append(hidden_input('save_as_draft', 'true'));
            } else if (that.hasClass('save-and-send')) {
                form.append(hidden_input('save_and_send', 'true'));
            }
        }
        form.submit();
    });

    // add invoice/estimate note
    $("body").on('submit', '#sales-notes', function() {
        var form = $(this);
        if (form.find('textarea[name="description"]').val() == '') { return; }

        $.post(form.attr('action'), $(form).serialize()).done(function(rel_id) {
            // Reset the note textarea value
            form.find('textarea[name="description"]').val('');
            // Reload the notes
            if (form.hasClass('estimate-notes-form')) {
                get_sales_notes(rel_id, 'estimates');
            } else if (form.hasClass('invoice-notes-form')) {
                get_sales_notes(rel_id, 'invoices');
            }
        });
        return false;
    });

    // Show quantity as change we need to change on the table QTY heading for better user experience
    $("body").on('change', 'input[name="show_quantity_as"]', function() {
        $("body").find('th.qty').html($(this).data('text'));
    });

    // No duedate for credit note, separate event
    $("body").on('change', 'div.credit_note input[name="date"]', function() {
        do_prefix_year($(this).val());
    });

    $("body").on('change', 'div.invoice input[name="date"],div.estimate input[name="date"]', function() {

        var date = $(this).val();
        do_prefix_year(date);

        // This function not work on edit
        if ($('input[name="isedit"]').length > 0) {
            return;
        }

        var due_date_input_name = 'duedate';
        var due_calc_url = admin_url + 'invoices/get_due_date';

        if ($("body").find('div.estimate').length > 0) {
            due_calc_url = admin_url + 'estimates/get_due_date';
            due_date_input_name = 'expirydate';
        }

        if (date == '') {
            $('input[name="' + due_date_input_name + '"]').val('');
        }

        if (date != '') {
            $.post(due_calc_url, { date: date }).done(function(formatted) {
                if (formatted) {
                    $('input[name="' + due_date_input_name + '"]').val(formatted);
                }
            });
        }
    });

    $('#sales_attach_file').on("hidden.bs.modal", function(e) {
        $('#sales_uploaded_files_preview').empty();
        $('.dz-file-preview').empty();
    });

    if (typeof(Dropbox) != 'undefined') {
        if ($('#dropbox-chooser-sales').length > 0) {
            document.getElementById("dropbox-chooser-sales").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    var _data = {};
                    _data.rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                    _data.type = $("body").find('input[name="_attachment_sale_type"]').val();
                    _data.files = files;
                    _data.external = 'dropbox';
                    $.post(admin_url + 'misc/add_sales_external_attachment', _data).done(function() {
                        if (_data.type == 'invoice') {
                            init_invoice(_data.rel_id);
                        } else if (_data.type == 'credit_note') {
                            init_credit_note(_data.rel_id);
                        } else if (_data.type == 'estimate') {
                            if ($("body").hasClass('estimates-pipeline')) {
                                estimate_pipeline_open(_data.rel_id);
                            } else {
                                init_estimate(_data.rel_id);
                            }
                        } else if (_data.type == 'proposal') {
                            if ($("body").hasClass('proposals-pipeline')) {
                                proposal_pipeline_open(_data.rel_id);
                            } else {
                                init_proposal(_data.rel_id);
                            }
                        }
                        $('#sales_attach_file').modal('hide');
                    });
                },
                linkType: "preview",
                extensions: app_allowed_files.split(','),
            }));
        }
    }

    if ($('#sales-upload').length > 0) {
        new Dropzone('#sales-upload', $.extend({}, _dropzone_defaults(), {
            sending: function(file, xhr, formData) {
                formData.append("rel_id", $("body").find('input[name="_attachment_sale_id"]').val());
                formData.append("type", $("body").find('input[name="_attachment_sale_type"]').val());
            },
            success: function(files, response) {
                response = JSON.parse(response);
                var type = $("body").find('input[name="_attachment_sale_type"]').val();
                var dl_url, delete_function;
                dl_url = 'download/file/sales_attachment/';
                delete_function = 'delete_' + type + '_attachment';
                if (type == 'invoice') {
                    init_invoice(response.rel_id);
                } else if (type == 'credit_note') {
                    init_credit_note(response.rel_id);
                } else if (type == 'estimate') {
                    if ($("body").hasClass('estimates-pipeline')) {
                        estimate_pipeline_open(response.rel_id);
                    } else {
                        init_estimate(response.rel_id);
                    }
                } else if (type == 'proposal') {
                    if ($("body").hasClass('proposals-pipeline')) {
                        proposal_pipeline_open(response.rel_id);
                    } else {
                        init_proposal(response.rel_id);
                    }
                }
                var data = '';
                if (response.success == true) {
                    data += '<div class="display-block sales-attach-file-preview" data-attachment-id="' + response.attachment_id + '">';
                    data += '<div class="col-md-10">';
                    data += '<div class="pull-left"><i class="attachment-icon-preview fa fa-file-o"></i></div>'
                    data += '<a href="' + site_url + dl_url + response.key + '" target="_blank">' + response.file_name + '</a>';
                    data += '<p class="text-muted">' + response.filetype + '</p>';
                    data += '</div>';
                    data += '<div class="col-md-2 text-right">';
                    data += '<a href="#" class="text-danger" onclick="' + delete_function + '(' + response.attachment_id + '); return false;"><i class="fa fa-times"></i></a>';
                    data += '</div>';
                    data += '<div class="clearfix"></div><hr/>';
                    data += '</div>';
                    $('#sales_uploaded_files_preview').append(data);
                }
            },
        }));
    }

    // Show send to email invoice modal
    $("body").on('click', '.invoice-send-to-client', function(e) {
        e.preventDefault();
        $('#invoice_send_to_client_modal').modal('show');
    });

    // Show send to email estimate modal
    $("body").on('click', '.estimate-send-to-client', function(e) {
        e.preventDefault();
        $('#estimate_send_to_client_modal').modal('show');
    });

    // Send templaate modal custom close function causing problems if is on pipeline view
    $("body").on('click', '.close-send-template-modal', function() {
        $('#estimate_send_to_client_modal').modal('hide');
        $('#proposal_send_to_customer').modal('hide');
    });

    // Include shipping show/hide details
    $("body").on('change', '#include_shipping', function() {
        var $sd = $('#shipping_details');
        $(this).prop('checked') == true ? $sd.removeClass('hide') : $sd.addClass('hide');
    });

    // Init the billing and shipping details in the field - estimates and invoices
    $("body").on('click', '.save-shipping-billing', function(e) {
        init_billing_and_shipping_details();
    });

    // On change currency recalculate price and change symbol
    $("body").on('change', 'select[name="currency"]', function() {
        init_currency_symbol();
    });

    // Recaulciate total on these changes
    $("body").on('change', 'input[name="adjustment"],select.tax', function() {
        calculate_total();
    });

    $('body').on('click', '.discount-total-type', function(e) {
        e.preventDefault();
        $('#discount-total-type-dropdown').find('.discount-total-type').removeClass('selected');
        $(this).addClass('selected');
        $('.discount-total-type-selected').html($(this).text());
        if ($(this).hasClass('discount-type-percent')) {
            $('.input-discount-fixed').addClass('hide').val(0);
            $('.input-discount-percent').removeClass('hide');
        } else {
            $('.input-discount-fixed').removeClass('hide');
            $('.input-discount-percent').addClass('hide').val(0);
            $('#discount_percent-error').remove();
        }
        calculate_total();
    });

    // Discount type for estimate/invoice
    $("body").on('change', 'select[name="discount_type"]', function() {
        // if discount_type == ''
        if ($(this).val() == '') {
            $('input[name="discount_percent"]').val(0);
        }
        // Recalculate the total
        calculate_total();
    });

    // In case user enter discount percent but there is no discount type set
    $("body").on('change', 'input[name="discount_percent"],input[name="discount_total"]', function() {
        if ($('select[name="discount_type"]').val() == '' && $(this).val() != 0) {
            alert('You need to select discount type');
            $('html,body').animate({ scrollTop: 0 }, 'slow');
            $('#wrapper').highlight($('label[for="discount_type"]').text());
            setTimeout(function() {
                $('#wrapper').unhighlight();
            }, 3000);
            return false;
        }
        if ($(this).valid() == true) {
            calculate_total();
        }
    });

    $('body').on('change', '.invoice #project_id', function() {
        var project_id = $(this).selectpicker('val');
        if (project_id != '') {
            requestGetJSON('tasks/get_billable_tasks_by_project/' + project_id).done(function(tasks) {
                _init_tasks_billable_select(tasks, project_id);
            });
        } else {
            var client_id = $('#clientid').selectpicker('val');
            if(client_id != ''){
                requestGetJSON('tasks/get_billable_tasks_by_customer_id/'+client_id).done(function(tasks){
                    _init_tasks_billable_select(tasks);
                })
            } else {
                // Empty dropdown
                _init_tasks_billable_select([], '');
            }
        }
    });

    // Add task data to preview from the dropdown for invoiecs
    $("body").on('change', 'select[name="task_select"]', function() {
        ($(this).selectpicker('val') != '' ? add_task_to_preview_as_item($(this).selectpicker('val')) : '');
    });

    // When user record payment check if is online mode
    $("body").on('change', 'select[name="paymentmode"]', function() {
        var $notRedirect = $('.do_not_redirect');
        !$.isNumeric($(this).val()) ? $notRedirect.removeClass('hide') : $notRedirect.addClass('hide');
    });

    $("body").on('change', '.f_client_id select[name="clientid"]', function() {
        var val = $(this).val();
        var projectAjax = $('select[name="project_id"]');
        var clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();
        var projectsWrapper = $('.projects-wrapper');
        projectAjax.selectpicker('destroy').remove();
        projectAjax = clonedProjectsAjaxSearchSelect;
        $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);
        init_ajax_project_search_by_customer_id();
        clear_billing_and_shipping_details();
        if (!val) {
            $('#merge').empty();
            $('#expenses_to_bill').empty();
            $('#invoice_top_info').addClass('hide');
            projectsWrapper.addClass('hide');
            return false;
        }

        var currentInvoiceID = $("body").find('input[name="merge_current_invoice"]').val();
        currentInvoiceID = typeof(currentInvoiceID) == 'undefined' ? '' : currentInvoiceID;

        requestGetJSON('invoices/client_change_data/' + val + '/' + currentInvoiceID).done(function(response) {
            $('#merge').html(response.merge_info);
            var $billExpenses = $('#expenses_to_bill');
            // Invoice from project, in invoice_template this is not shown
            $billExpenses.length == 0 ? response.expenses_bill_info = '' : $billExpenses.html(response.expenses_bill_info);
            ((response.merge_info != '' || response.expenses_bill_info != '') ? $('#invoice_top_info').removeClass('hide') : $('#invoice_top_info').addClass('hide'));

            for (var f in bs_fields) {
                if (bs_fields[f].indexOf('billing') > -1) {
                    if (bs_fields[f].indexOf('country') > -1) {
                        $('select[name="' + bs_fields[f] + '"]').selectpicker('val', response['billing_shipping'][0][bs_fields[f]]);
                    } else {
                        if (bs_fields[f].indexOf('billing_street') > -1) {
                            $('textarea[name="' + bs_fields[f] + '"]').val(response['billing_shipping'][0][bs_fields[f]]);
                        } else {
                            $('input[name="' + bs_fields[f] + '"]').val(response['billing_shipping'][0][bs_fields[f]]);
                        }
                    }
                }
            }

            if (!empty(response['billing_shipping'][0]['shipping_street'])) {
                $('input[name="include_shipping"]').prop("checked", true).change();
            }

            for (var f in bs_fields) {
                if (bs_fields[f].indexOf('shipping') > -1) {
                    if (bs_fields[f].indexOf('country') > -1) {
                        $('select[name="' + bs_fields[f] + '"]').selectpicker('val', response['billing_shipping'][0][bs_fields[f]]);
                    } else {
                        if (bs_fields[f].indexOf('shipping_street') > -1) {
                            $('textarea[name="' + bs_fields[f] + '"]').val(response['billing_shipping'][0][bs_fields[f]]);
                        } else {
                            $('input[name="' + bs_fields[f] + '"]').val(response['billing_shipping'][0][bs_fields[f]]);
                        }
                    }
                }
            }

            init_billing_and_shipping_details();

            var client_currency = response['client_currency'];
            var s_currency = $("body").find('.accounting-template select[name="currency"]');
            client_currency = parseInt(client_currency);
            client_currency != 0 ? s_currency.val(client_currency) : s_currency.val(s_currency.data('base'));
            _init_tasks_billable_select(response['billable_tasks'], projectAjax.selectpicker('val'));
            response.customer_has_projects === true ? projectsWrapper.removeClass('hide') : projectsWrapper.addClass('hide');
            s_currency.selectpicker('refresh');
            init_currency_symbol();
        });

    });

    // When customer_id is passed init the data
    if ($('input[name="isedit"]').length == 0) {
        $('.f_client_id select[name="clientid"]').change();
    }

    $("body").on('click', '#get_shipping_from_customer_profile', function(e) {
        e.preventDefault();
        var include_shipping = $('#include_shipping');
        if (include_shipping.prop('checked') == false) {
            include_shipping.prop('checked', true);
            $('#shipping_details').removeClass('hide');
        }
        var clientid = $('select[name="clientid"]').val();
        if (clientid == '') { return; }
        requestGetJSON('clients/get_customer_billing_and_shipping_details/' + clientid).done(function(response) {
            $('textarea[name="shipping_street"]').val(response[0]['shipping_street']);
            $('input[name="shipping_city"]').val(response[0]['shipping_city']);
            $('input[name="shipping_state"]').val(response[0]['shipping_state']);
            $('input[name="shipping_zip"]').val(response[0]['shipping_zip']);
            $('select[name="shipping_country"]').selectpicker('val', response[0]['shipping_country']);
        });
    });

    if (typeof(accounting) != 'undefined') {
        // Used for formatting money
        accounting.settings.currency.decimal = app_decimal_separator;
        accounting.settings.currency.thousand = app_thousand_separator;
        accounting.settings.currency.precision = app_decimal_places;

        // Used for numbers
        accounting.settings.number.thousand = app_thousand_separator;
        accounting.settings.number.decimal = app_decimal_separator;
        accounting.settings.number.precision = app_decimal_places;

        calculate_total();
    }

    // Invoices to merge
    $("body").on('change', 'input[name="invoices_to_merge[]"]', function() {
        var checked = $(this).prop('checked');
        var _id = $(this).val();
        if (checked == true) {
            requestGetJSON('invoices/get_merge_data/' + _id).done(function(response) {
                $.each(response.items, function(i, obj) {
                    if (obj.rel_type != '') {
                        if (obj.rel_type == 'task') {
                            $('input[name="task_id"]').val(obj.item_related_formatted_for_input);
                        } else if (obj.rel_type == 'expense') {
                            $('input[name="expense_id"]').val(obj.item_related_formatted_for_input);
                        }
                    }
                    _set_item_preview_custom_fields_array(obj.custom_fields);
                    add_item_to_table(obj, 'undefined', _id);
                });
            });
        } else {
            // Remove the appended invoice to merge
            $("body").find('[data-merge-invoice="' + _id + '"]').remove();
        }
    });

    // Bill expenses to invooice on top
    $("body").on('change', 'input[name="bill_expenses[]"]', function() {
        var checked = $(this).prop('checked');
        var _id = $(this).val();
        if (checked == true) {
            requestGetJSON('invoices/get_bill_expense_data/' + _id).done(function(response) {
                $('input[name="expense_id"]').val(_id);
                add_item_to_table(response, 'undefined', 'undefined', _id);
            });
        } else {
            // Remove the appended expenses
            $("body").find('[data-bill-expense="' + _id + '"]').remove();
            $("body").find('#billed-expenses input[value="' + _id + '"]').remove();
        }
    });

    // Expense bill to popover data
    $("body").on('change', '.invoice_inc_expense_additional_info input', function() {
        var _data_content = $(this).attr('data-content'),
            new_desc_value,
            desc_selector = $('[data-bill-expense=' + $(this).attr('data-id') + '] .item_long_description');
        current_desc_val = desc_selector.val();
        current_desc_val = current_desc_val.trim();
        if (_data_content != '') {
            if ($(this).prop('checked') == true) {
                new_desc_value = current_desc_val + "\n" + _data_content;
                desc_selector.val(new_desc_value.trim());
            } else {
                desc_selector.val(current_desc_val.replace("\n" + _data_content, ''));
                // IN case there is no new line
                desc_selector.val(current_desc_val.replace(_data_content, ''));
            }
        }
    });
});

// For manually modals where no close is defined
$(document).keyup(function(e) {
    if (e.keyCode == 27) { // escape key maps to keycode `27`
        // Close modal if only modal is opened and there is no 2 modals opened
        // This will trigger only if there is only 1 modal visible/opened
        if ($('.modal').is(':visible') && $('.modal:visible').length == 1) {
            $("body").find('.modal:visible [onclick^="close_modal_manually"]').eq(0).click();
        }
        if ($('.popup-wrapper').is(':visible')) {
            $('.popup-wrapper').find('.system-popup-close').click();
        }
    }
});

function _init_tasks_billable_select(tasks, project_id) {
    var billable_tasks_area = $('#task_select');
    if (billable_tasks_area.length > 0) {
        var option_data;
        billable_tasks_area.find('option').filter(function() {
            return this.value || $.trim(this.value).length > 0 || $.trim(this.text).length > 0;
        }).remove();

        $.each(tasks, function(i, obj) {
            option_data = ' ';
            if (obj.started_timers == true) {
                option_data += 'disabled class="text-danger important" data-subtext="' + appLang.invoice_task_billable_timers_found + '"';
            } else if (obj.started_timers == false && obj.rel_type != 'project') {
                option_data += 'data-subtext="' + obj.rel_name + '"';
            }
            billable_tasks_area.append('<option value="' + obj.id + '"' + option_data + '>' + obj.name + '</option>');
        });

        var tasks_help_wrapper = $('.input-group-addon-bill-tasks-help');
        tasks_help_wrapper.find('.popover-invoker').popover('destroy');
        tasks_help_wrapper.empty();

        var help_tooltip = '';

        if (!empty(project_id)) {
            help_tooltip = appLang['showing_billable_tasks_from_project'] + ' ' + $('#project_id option:selected').text().trim();
        } else {
            help_tooltip = appLang['invoice_task_item_project_tasks_not_included'];
        }

        tasks_help_wrapper.html('<span class="pointer popover-invoker" data-container=".form-group-select-task_select" data-trigger="click" data-placement="top" data-toggle="popover" data-content="' + help_tooltip + '"><i class="fa fa-question-circle"></i></span>');

        delay(function() {
            if ((tasks_help_wrapper.attr('info-shown-count') < 3 || typeof(tasks_help_wrapper.attr('info-shown-count')) == 'undefined')
                && $('.projects-wrapper').is(':visible') && tasks.length > 0) {
               tasks_help_wrapper.attr('info-shown-count', typeof(tasks_help_wrapper.attr('info-shown-count')) == 'undefined' ? 1 : parseInt(tasks_help_wrapper.attr('info-shown-count')) + 1);
                tasks_help_wrapper.find('.popover-invoker').click()
            }
        }, 3500);
    }

    billable_tasks_area.selectpicker('refresh');
}
// Lightbox plugins for images
function init_lightbox(options) {
    if (typeof(lightbox) != 'undefined') {
        var _lightBoxOptions = {
            'showImageNumberLabel': false,
            resizeDuration: 200,
            positionFromTop: 25
        };
        if (typeof(options) != 'undefined') {
            jQuery.extend(_lightBoxOptions, options);
        }
        lightbox.option(_lightBoxOptions);
    }
}

// Progress bar animation load
function init_progress_bars() {
    var progress_bars = $('.progress .progress-bar');
    if (progress_bars.length) {
        progress_bars.each(function() {
            var bar = $(this);
            var perc = bar.attr("data-percent");
            bar.css('width', (perc) + '%');
            if (!bar.hasClass('no-percent-text')) { bar.text((perc) + '%'); }
        });
    }
}

// Get url params like $_GET
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

// Fix for height on the wrapper
function mainWrapperHeightFix() {
    // Get and set current height
    var headerH = 63;
    var navigationH = side_bar.height();
    var contentH = $(".content").height();

    setup_menu.css('min-height', ($(document).outerHeight(true) - (headerH * 2)) + 'px');

    content_wrapper.css('min-height', $(document).outerHeight(true) - headerH + 'px');
    // Set new height when content height is less then navigation
    if (contentH < navigationH) {
        content_wrapper.css("min-height", navigationH + 'px');
    }

    // Set new height when content height is less then navigation and navigation is less then window
    if (contentH < navigationH && navigationH < $(window).height()) {
        content_wrapper.css("min-height", $(window).height() - headerH + 'px');
    }
    // Set new height when content is higher then navigation but less then window
    if (contentH > navigationH && contentH < $(window).height()) {
        content_wrapper.css("min-height", $(window).height() - headerH + 'px');
    }
    // Fix for RTL main admin menu height
    if (is_mobile() && isRTL == 'true') {
        side_bar.css('min-height', $(document).outerHeight(true) - headerH + 'px');
    }
}

// Set body small based on device width
function set_body_small() {
    if ($(this).width() < 769) {
        $("body").addClass('page-small');
    } else {
        $("body").removeClass('page-small show-sidebar');
    }
}

// Generate random password
function generatePassword(field) {
    var length = 12,
        charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    $(field).parents().find('input.password').val(retVal)
}

// Switch field make request
function switch_field(field) {
    var status, url, id;
    status = 0;
    if ($(field).prop('checked') == true) { status = 1; }
    url = $(field).data('switch-url');
    id = $(field).data('id');
    requestGet(url + '/' + id + '/' + status);
}

// General validate form function
function _validate_form(form, form_rules, submithandler) {
    var $form = $(form);
    if ($form.length) {
        var f = $form.validate({
            rules: form_rules,
            messages: {
                email: {
                    remote: appLang.email_exists,
                },
            },
            ignore: [],
            submitHandler: function(form) {
                if ($(form).hasClass('disable-on-submit')) {
                    $(form).find('[type="submit"]').prop('disabled', true);
                }
                if (typeof(submithandler) !== 'undefined') {
                    submithandler(form);
                } else {
                    return true;
                }
            }
        });

        var custom_required_fields = $form.find('[data-custom-field-required]');
        if (custom_required_fields.length > 0) {

            $.each(custom_required_fields, function() {

                // for custom fields in tr.main, do not validate those
                if (!$(this).parents('tr.main').length) {

                    $(this).rules("add", {
                        required: true
                    });

                    var name = $(this).attr('name');
                    var label = $(this).parents('.form-group').find('[for="' + name + '"]');
                    if (label.length > 0 && label.find('.req').length == 0) {
                        label.prepend('<small class="req text-danger">* </small>');
                    }
                }
            });
        }

        $.each(form_rules, function(name, rule) {
            if ((rule == 'required' && !jQuery.isPlainObject(rule)) || (jQuery.isPlainObject(rule) && rule.hasOwnProperty('required'))) {
                var label = $form.find('[for="' + name + '"]');
                if (label.length > 0 && label.find('.req').length == 0) {
                    label.prepend(' <small class="req text-danger">* </small>');
                }
            }
        });
    }
    return false;
}

// Delete option from database AJAX
function delete_option(child, id) {
    if (confirm_delete()) {
        requestGetJSON('settings/delete_option/' + id).done(function(response) {
            if (response.success == true) {
                $(child).parents('.option').remove();
            }
        });
    }
}

// Slide toggle any selector passed
function slideToggle(selector, callback) {
    var $element = $(selector);
    if ($element.hasClass('hide')) { $element.removeClass('hide', 'slow'); }
    if ($element.length) { $element.slideToggle(); }
    // Set all progress bar to 0 percent
    var progress_bars = $('.progress-bar').not('.not-dynamic');
    if (progress_bars.length > 0) {
        progress_bars.each(function() {
            $(this).css('width', 0 + '%');
            $(this).text(0 + '%');
        });
        // Init the progress bars again
        init_progress_bars();
    }
    // Possible callback after slide toggle
    if (typeof(callback) == 'function') { callback(); }
}

// Generate float alert
function alert_float(type, message) {
    var aId, el;
    aId = $("body").find('float-alert').length;
    aId++;
    aId = 'alert_float_' + aId;
    el = $('<div id="' + aId + '" class="float-alert animated fadeInRight col-xs-11 col-sm-4 alert alert-' + type + '"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="fa fa-bell-o" data-notify="icon"></span><span class="alert-title">' + message + '</span></div>');
    $("body").append(el);
    setTimeout(function() {
        $('#' + aId).hide('fast', function() { $('#' + aId).remove(); });
    }, 3500);
}

// Initing relation tasks tables
function init_rel_tasks_table(rel_id, rel_type, selector) {
    if (typeof(selector) == 'undefined') { selector = '.table-rel-tasks'; }
    var $selector = $("body").find(selector);
    if ($selector.length == 0) { return; }

    var TasksServerParams = {},
        not_sortable_tasks, Tasks_Filters;

    Tasks_Filters = $('._hidden_inputs._filters._tasks_filters input');

    $.each(Tasks_Filters, function() {
        TasksServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    var url = admin_url + 'tasks/init_relation_tasks/' + rel_id + '/' + rel_type;

    not_sortable_tasks = [($selector.find('th').length - 1)];

    if ($selector.attr('data-new-rel-type') == 'project') {
        not_sortable_tasks.push(0);
        url += '?bulk_actions=true';
    }

    var Api = initDataTable($selector, url, not_sortable_tasks, not_sortable_tasks, TasksServerParams, [$selector.find('th.duedate').index(), 'ASC']);
    // Hide option on lead modal because the table reserved area is too small
    if (Api && rel_type == 'lead') {
        Api.column($('table .table-tasks-options').index()).visible(false, false).columns.adjust();
    }
}

// Returns datatbles export button array based on settings
function get_dt_export_buttons(table) {
    // pdfmake arabic fonts support
    if (app_language.toLowerCase() == 'persian' || app_language.toLowerCase() == 'arabic') {
        if ($('body').find('#amiri').length == 0) {
            var mainjs = document.createElement('script');
            mainjs.setAttribute('src', 'https://rawgit.com/xErik/pdfmake-fonts-google/master/build/script/ofl/amiri.js');
            mainjs.setAttribute('id', 'amiri');
            document.head.appendChild(mainjs);

            var mapjs = document.createElement('script');
            mapjs.setAttribute('src', 'https://rawgit.com/xErik/pdfmake-fonts-google/master/build/script/ofl/amiri.map.js');
            document.head.appendChild(mapjs);
        }
    }

    var formatExport = {
        body: function(data, row, column, node) {

            // Fix for notes inline datatables
            // Causing issues because of the hidden textarea for edit and the content is duplicating
            // This logic may be extended in future for other similar fixes
            var newTmpRow = $('<div></div>', data);
            newTmpRow.append(data);
            if (newTmpRow.find('[data-note-edit-textarea]').length > 0) {
                newTmpRow.find('[data-note-edit-textarea]').remove();
                data = newTmpRow.html().trim();
            }

            // Datatables use the same imlpementation to strip the html.
            var div = document.createElement("div");
            div.innerHTML = data;
            var text = div.textContent || div.innerText || "";

            return text.trim();
        }
    }

    var table_buttons_options = [{
        extend: 'collection',
        text: appLang.dt_button_export,
        className: 'btn btn-default-dt-options',
        buttons: [{
            extend: 'excel', // Causing issues with formats, the excel is still using csv so in this case will work good
            text: appLang.dt_button_excel,
            footer: true,
            exportOptions: {
                columns: [':not(.not-export)'],
                rows: function(index) {
                    return _dt_maybe_export_only_selected_rows(index, table);
                },
                format: formatExport,
            },
        }, {
            extend: 'csvHtml5',
            text: appLang.dt_button_csv,
            footer: true,
            exportOptions: {
                columns: [':not(.not-export)'],
                rows: function(index) {
                    return _dt_maybe_export_only_selected_rows(index, table);
                },
                format: formatExport,
            }
        }, {
            extend: 'pdfHtml5',
            text: appLang.dt_button_pdf,
            footer: true,
            exportOptions: {
                columns: [':not(.not-export)'],
                rows: function(index) {
                    return _dt_maybe_export_only_selected_rows(index, table);
                },
                format: formatExport,
            },
            orientation: 'landscape',
            customize: function(doc) {
                // Fix for column widths
                var table_api = $(table).DataTable();
                var columns = table_api.columns().visible();
                var columns_total = columns.length;
                var pdf_widths = [];
                var total_visible_columns = 0;
                for (i = 0; i < columns_total; i++) {
                    // Is only visible column
                    if (columns[i] == true) {
                        total_visible_columns++;
                    }
                }
                setTimeout(function() {
                    if (total_visible_columns <= 5) {
                        for (i = 0; i < total_visible_columns; i++) {
                            pdf_widths.push((735 / total_visible_columns));
                        }
                        doc.content[1].table.widths = pdf_widths;
                    }
                }, 10);

                if (app_language.toLowerCase() == 'persian' || app_language.toLowerCase() == 'arabic') {
                    doc.defaultStyle.font = Object.keys(pdfMake.fonts)[0];
                }
                //  doc.defaultStyle.font = 'test';
                doc.styles.tableHeader.alignment = 'left';
                doc.styles.tableHeader.margin = [5, 5, 5, 5];
                doc.pageMargins = [12, 12, 12, 12];
            }
        }, {
            extend: 'print',
            text: appLang.dt_button_print,
            footer: true,
            exportOptions: {
                columns: [':not(.not-export)'],
                rows: function(index) {
                    return _dt_maybe_export_only_selected_rows(index, table);
                },
                format: formatExport,
            }
        }],
    }, {
        extend: 'colvis',
        postfixButtons: ['colvisRestore'],
        className: 'btn btn-default-dt-options dt-column-visibility',
        text: appLang.dt_button_column_visibility
    }];

    if (app_show_table_columns_visibility == 0) {
        delete table_buttons_options[1];
    }

    if (table_export_button_is_hidden()) {
        delete table_buttons_options[0];
    }

    var tableButtons = $("body").find('.table-btn');
    $.each(tableButtons, function() {
        var b = $(this);
        if (b.length && b.attr('data-table')) {
            if ($(table).is(b.attr('data-table'))) {
                table_buttons_options.push({
                    text: b.text(),
                    className: 'btn btn-default-dt-options',
                    action: function(e, dt, node, config) {
                        b.click();
                    }
                });
            }
        }
    });

    table_buttons_options.push({
        text: '<i class="fa fa-refresh"></i>',
        className: 'btn btn-default-dt-options btn-dt-reload',
        action: function(e, dt, node, config) {
            dt.ajax.reload();
        }
    });

    return table_buttons_options;
}

// Check if table export button should be hidden based on settings
function table_export_button_is_hidden() {
    if (app_show_table_export_button != 'to_all') {
        if (app_show_table_export_button == 'hide' || app_show_table_export_button == 'only_admins' && is_admin == 0) {
            return true;
        }
    }
    return false;
}

// Datatables inline/offline lazy load images
function DataTablesOfflineLazyLoadImages(nRow, aData, iDisplayIndex) {
    var img = $('img.img-table-loading', nRow);
    img.attr('src', img.data('orig'));
    img.prev('div').addClass('hide');
    return nRow;
}

function _dt_maybe_export_only_selected_rows(index, table) {
    table = $(table);
    index = index.toString();
    var bulkActionsCheckbox = table.find('thead th input[type="checkbox"]').eq(0);
    if (bulkActionsCheckbox && bulkActionsCheckbox.length > 0) {
        var rows = table.find('tbody tr');
        var anyChecked = false;
        $.each(rows, function() {
            if ($(this).find('td:first input[type="checkbox"]:checked').length) {
                anyChecked = true;
            }
        });

        if (anyChecked) {
            if (table.find('tbody tr:eq(' + (index) + ') td:first input[type="checkbox"]:checked').length > 0) {
                return index;
            } else {
                return null;
            }
        } else {
            return index;
        }
    }
    return index;
}

// Datatables custom job to page function
function _table_jump_to_page(table, oSettings) {

    var paginationData = table.DataTable().page.info();
    var previousDtPageJump = $("body").find('#dt-page-jump-' + oSettings.sTableId);
    var currentPage = null;

    if (previousDtPageJump.length) {
        previousDtPageJump.remove();
    }

    if (paginationData.pages > 6) {
        var jumpToPageSelect = $("<select></select>", {
            "class": "dt-page-jump-select form-control",
            'id': 'dt-page-jump-' + oSettings.sTableId
        });
        for (i = 1; i <= paginationData.pages; i++) {
            var selectedCurrentPage = ((paginationData.page + 1) === i) ? 'selected' : '';
            jumpToPageSelect.append("<option value='" + i + "'" + selectedCurrentPage + ">" + i + "</option>");
        }
        $("#" + oSettings.sTableId + "_wrapper .dt-page-jump").append(jumpToPageSelect);
    }
}

// @deprecated
function initDatatableOffline(dt_table) {
    initDataTableInline(dt_table);
}

// Datatbles inline/offline - no serverside
function initDataTableInline(dt_table) {

    var selector = typeof(dt_table) !== 'undefined' ? dt_table : '.dt-table';
    var tables = $(selector);

    if (tables.length == 0) {
        return;
    }

    var length_options = [10, 25, 50, 100];
    var length_options_names = [10, 25, 50, 100];

    app_tables_pagination_limit = parseFloat(app_tables_pagination_limit);

    if ($.inArray(app_tables_pagination_limit, length_options) == -1) {
        length_options.push(app_tables_pagination_limit)
        length_options_names.push(app_tables_pagination_limit)
    }

    length_options.sort(function(a, b) {
        return a - b;
    });

    length_options_names.sort(function(a, b) {
        return a - b;
    });

    length_options.push(-1);
    length_options_names.push(appLang.dt_length_menu_all);


    if (tables.length) {
        var order_col, order_type, options, _buttons;
        var _options = {
            "language": appLang.datatables,
            "processing": true,
            'paginate': true,
            "responsive": true,
            "autoWidth": false,
            "order": [0, 'DESC'],
            "pageLength": app_tables_pagination_limit,
            "lengthMenu": [length_options, length_options_names],
            "fnRowCallback": DataTablesOfflineLazyLoadImages,
            "fnDrawCallback": function(oSettings) {
                _table_jump_to_page(this, oSettings);
                if (oSettings.aoData.length == 0 || oSettings.aiDisplay.length == 0) {
                    $(oSettings.nTableWrapper).addClass('app_dt_empty');
                } else {
                    $(oSettings.nTableWrapper).removeClass('app_dt_empty');
                }
            },
            "initComplete": function(settings, json) {
                var dtOfflineEmpty = this.find('.dataTables_empty');

                if (this.hasClass('scroll-responsive') || app_scroll_responsive_tables == 1) {
                    this.wrap('<div class="table-responsive"></div>');
                }

                if (dtOfflineEmpty.length) {
                    dtOfflineEmpty.attr('colspan', this.find('thead th').length);
                }
                this.parents('.table-loading').removeClass('table-loading');
                var t_export = $(selector);
                var th_last_child = t_export.find('thead th:last-child');
                var th_first_child = t_export.find('thead th:first-child');
                if (th_last_child.text().trim() == appLang.options) {
                    th_last_child.addClass('not-export');
                }
                if (th_first_child.find('input[type="checkbox"]').length > 0) {
                    th_first_child.addClass('not-export');
                }
            },
            dom: "<'row'><'row'<'col-md-6'lB><'col-md-6'f>r>t<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>",
        }

        var order_col = $($(this)).attr('data-order-col');
        var order_type = $($(this)).attr('data-order-type');
        $.each(tables, function() {
            $(this).addClass('dt-no-serverside');
            options = _options;
            if ($(this).hasClass('scroll-responsive') || app_scroll_responsive_tables == 1) {
                options.responsive = false;
            }
            order_col = $(this).attr('data-order-col');
            order_type = $(this).attr('data-order-type');
            if (order_col && order_type) {
                options.order = [
                    [order_col, order_type]
                ]
            }
            _buttons = get_dt_export_buttons(this);
            // Remove the reload button here because its not ajax request
            delete _buttons[2];
            options.buttons = _buttons;
            var tableApi = $(this).DataTable(options);
            $(document).on("change", ".dt-page-jump-select", function() {
                tableApi.page($(this).val() - 1).draw(false);
            });
        });
    }
}

// General function for all datatables serverside
function initDataTable(selector, url, notsearchable, notsortable, fnserverparams, defaultorder) {
    var table = typeof(selector) == 'string' ? $("body").find('table' + selector) : selector;

    if (table.length == 0) {
        return false;
    }

    fnserverparams = (fnserverparams == 'undefined' || typeof(fnserverparams) == 'undefined') ? [] : fnserverparams;

    // If not order is passed order by the first column
    if (typeof(defaultorder) == 'undefined') {
        defaultorder = [
            [0, 'ASC']
        ];
    } else {
        if (defaultorder.length == 1) {
            defaultorder = [defaultorder]
        }
    }

    var length_options = [10, 25, 50, 100];
    var length_options_names = [10, 25, 50, 100];

    app_tables_pagination_limit = parseFloat(app_tables_pagination_limit);

    if ($.inArray(app_tables_pagination_limit, length_options) == -1) {
        length_options.push(app_tables_pagination_limit)
        length_options_names.push(app_tables_pagination_limit)
    }

    length_options.sort(function(a, b) {
        return a - b;
    });
    length_options_names.sort(function(a, b) {
        return a - b;
    });

    length_options.push(-1);
    length_options_names.push(appLang.dt_length_menu_all);

    var dtSettings = {
        "language": appLang.datatables,
        "processing": true,
        "retrieve": true,
        "serverSide": true,
        'paginate': true,
        'searchDelay': 750,
        "bDeferRender": true,
        "responsive": true,
        "autoWidth": false,
        dom: "<'row'><'row'<'col-md-7'lB><'col-md-5'f>r>t<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>",
        "pageLength": app_tables_pagination_limit,
        "lengthMenu": [length_options, length_options_names],
        "columnDefs": [{
            "searchable": false,
            "targets": notsearchable,
        }, {
            "sortable": false,
            "targets": notsortable
        }],
        "fnDrawCallback": function(oSettings) {
            _table_jump_to_page(this, oSettings);
            if (oSettings.aoData.length == 0) {
                $(oSettings.nTableWrapper).addClass('app_dt_empty');
            } else {
                $(oSettings.nTableWrapper).removeClass('app_dt_empty');
            }
        },
        "fnCreatedRow": function(nRow, aData, iDataIndex) {
            // If tooltips found
            $(nRow).attr('data-title', aData.Data_Title)
            $(nRow).attr('data-toggle', aData.Data_Toggle)
        },
        "initComplete": function(settings, json) {
            var t = this;

            $('.btn-dt-reload').attr('data-toggle', 'tooltip');
            $('.btn-dt-reload').attr('title', appLang.dt_button_reload);

            if (t.hasClass('scroll-responsive') || app_scroll_responsive_tables == 1) {
                t.wrap('<div class="table-responsive"></div>');
            }

            var dtEmpty = t.find('.dataTables_empty');
            if (dtEmpty.length) {
                dtEmpty.attr('colspan', t.find('thead th').length);
            }

            // Hide mass selection because causing issue on small devices
            if (is_mobile() && $(window).width() < 400 && t.find('tbody td:first-child input[type="checkbox"]').length > 0) {
                t.DataTable().column(0).visible(false, false).columns.adjust();
                $("a[data-target*='bulk_actions']").addClass('hide');
            }

            t.parents('.table-loading').removeClass('table-loading');
            t.removeClass('dt-table-loading');
            var th_last_child = t.find('thead th:last-child');
            var th_first_child = t.find('thead th:first-child');
            if (th_last_child.text().trim() == appLang.options) {
                th_last_child.addClass('not-export');
            }
            if (th_first_child.find('input[type="checkbox"]').length > 0) {
                th_first_child.addClass('not-export');
            }
            mainWrapperHeightFix();
        },
        "order": defaultorder,
        "ajax": {
            "url": url,
            "type": "POST",
            "data": function(d) {
                if (typeof(csrfData) !== 'undefined') {
                    d[csrfData['token_name']] = csrfData['hash'];
                }
                for (var key in fnserverparams) {
                    d[key] = $(fnserverparams[key]).val();
                }
            },
            "error": function(error) {
                if (error.responseText != '') {
                    alert_float('danger', error.responseText);
                }
            },
        },
        buttons: get_dt_export_buttons(table),
    };

    if (table.hasClass('scroll-responsive') || app_scroll_responsive_tables == 1) {
        dtSettings.responsive = false;
    }

    table = table.dataTable(dtSettings);

    var tableApi = table.DataTable();

    var hiddenHeadings = table.find('th.not_visible');
    var hiddenIndexes = [];

    $.each(hiddenHeadings, function() {
        hiddenIndexes.push(this.cellIndex);
    });

    setTimeout(function() {
        for (var i in hiddenIndexes) {
            tableApi.columns(hiddenIndexes[i]).visible(false, false).columns.adjust();
        }
    }, 10);

    // Fix for hidden tables colspan not correct if the table is empty
    if (table.is(':hidden')) {
        table.find('.dataTables_empty').attr('colspan', table.find('thead th').length);
    }

    $(document).on("change", ".dt-page-jump-select", function() {
        tableApi.page($(this).val() - 1).draw(false);
    });

    return tableApi;
}

// Update tags in task single modal
function task_single_update_tags() {
    var taskTags = $("#taskTags");
    $.post(admin_url + 'tasks/update_tags', {
        tags: taskTags.tagit('assignedTags'),
        task_id: taskTags.attr('data-taskid')
    });
}

// By default only 2 attachments for tasks are shown the other are hidden and there is button show more, this will show all the attachments
function task_attachments_toggle() {
    $("body").find('.task-attachments-more').toggleClass('hide');
    $("body").find('.task-attachments-less').toggleClass('hide');
}

// Update todo items when drop happen
function update_todo_items() {
    var unfinished_items = $('.unfinished-todos li:not(.no-todos)');
    var finished = $('.finished-todos li:not(.no-todos)');
    var i = 1;
    // Refresh orders
    $.each(unfinished_items, function() {
        $(this).find('input[name="todo_order"]').val(i);
        $(this).find('input[name="finished"]').val(0);
        i++;
    });
    if (unfinished_items.length == 0) {
        $('.nav-total-todos').addClass('hide');
        $('.unfinished-todos li.no-todos').removeClass('hide');
    } else if (unfinished_items.length > 0) {
        if (!$('.unfinished-todos li.no-todos').hasClass('hide')) {
            $('.unfinished-todos li.no-todos').addClass('hide');
        }
        $('.nav-total-todos').removeClass('hide').html(unfinished_items.length)
    }
    x = 1;
    $.each(finished, function() {
        $(this).find('input[name="todo_order"]').val(x);
        $(this).find('input[name="finished"]').val(1);
        $(this).find('input[type="checkbox"]').prop('checked', true);
        i++;
        x++;
    });
    if (finished.length == 0) {
        $('.finished-todos li.no-todos').removeClass('hide');
    } else if (finished.length > 0) {
        if (!$('.finished-todos li.no-todos').hasClass('hide')) {
            $('.finished-todos li.no-todos').addClass('hide');
        }
    }
    var update = [];
    $.each(unfinished_items, function() {
        var description = $(this).find('.todo-description');
        if (description.hasClass('line-throught')) {
            description.removeClass('line-throught')
        }
        $(this).find('input[type="checkbox"]').prop('checked', false);
        update.push([
            $(this).find('input[name="todo_id"]').val(),
            $(this).find('input[name="todo_order"]').val(),
            $(this).find('input[name="finished"]').val(),
        ]);
    });
    $.each(finished, function() {
        var description = $(this).find('.todo-description');
        if (!description.hasClass('line-throught')) {
            description.addClass('line-throught')
        }
        update.push([
            $(this).find('input[name="todo_id"]').val(),
            $(this).find('input[name="todo_order"]').val(),
            $(this).find('input[name="finished"]').val(),
        ]);
    });
    data = {};
    data.data = update;
    $.post(admin_url + 'todo/update_todo_items_order', data);
}

// Delete single todo item
function delete_todo_item(list, id) {
    requestGetJSON('todo/delete_todo_item/' + id).done(function(response) {
        if (response.success == true) {
            $(list).parents('li').remove();
            update_todo_items();
        }
    });
}

// Edit todo item
function edit_todo_item(id) {
    requestGetJSON('todo/get_by_id/' + id).done(function(response) {
        var todo_modal = $('#__todo');
        todo_modal.find('input[name="todoid"]').val(response.todoid);
        todo_modal.find('textarea[name="description"]').val(response.description);
        todo_modal.modal('show');
    });
}

// Date picker init with selected timeformat from settings
function init_datepicker(element, element_time) {

    var datepickers = typeof(element) == 'undefined' ? $('.datepicker') : element;
    var datetimepickers = typeof(element_time) == 'undefined' ? $('.datetimepicker') : element_time;

    if (datetimepickers.length == 0 && datepickers.length == 0) { return; }
    var opt;
    // Datepicker without time
    $.each(datepickers, function() {
        var opt = {
            timepicker: false,
            scrollInput: false,
            lazyInit: true,
            format: app_date_format,
            dayOfWeekStart: app_calendar_first_day,
        };

        // Check in case the input have date-end-date or date-min-date
        var max_date = $(this).data('date-end-date');
        var min_date = $(this).data('date-min-date');
        if (max_date) {
            opt.maxDate = max_date;
        }
        if (min_date) {
            opt.minDate = min_date;
        }
        // Init the picker
        $(this).datetimepicker(opt);
    });
    var opt_time;
    // Datepicker with time
    $.each(datetimepickers, function() {
        opt_time = {
            lazyInit: true,
            scrollInput: false,
            validateOnBlur: false,
            dayOfWeekStart: app_calendar_first_day
        };
        if (app_time_format == 24) {
            opt_time.format = app_date_format + ' H:i';
        } else {
            opt_time.format = app_date_format + ' g:i A';
            opt_time.formatTime = 'g:i A';
        }
        // Check in case the input have date-end-date or date-min-date
        var max_date = $(this).data('date-end-date');
        var min_date = $(this).data('date-min-date');
        if (max_date) {
            opt_time.maxDate = max_date;
        }
        if (min_date) {
            opt_time.minDate = min_date;
        }
        // Init the picker
        $(this).datetimepicker(opt_time);
    });
}

// All inputs used for tags
function init_tags_inputs() {
    var tags_inputs = $("body").find('input.tagsinput');
    if (tags_inputs.length) {
        tags_inputs.tagit({
            availableTags: availableTags,
            allowSpaces: true,
            animate: false,
            placeholderText: appLang.tag,
            showAutocompleteOnFocus: true,
            caseSensitive: false,
            autocomplete: {
                appendTo: '#inputTagsWrapper',
            },
            afterTagAdded: function(event, ui) {
                var tagIndexAvailable = availableTags.indexOf($.trim($(ui.tag).find('.tagit-label').text()));
                if (tagIndexAvailable > -1) {
                    var _tagId = availableTagsIds[tagIndexAvailable];
                    $(ui.tag).addClass('tag-id-' + _tagId);
                }
                showHideTagsPlaceholder($(this));
            },
            afterTagRemoved: function(event, ui) {
                showHideTagsPlaceholder($(this));
            }
        });
    }
}

// Init color pickers
function init_color_pickers() {
    var color_pickers = $("body").find('div.colorpicker-input');
    if (color_pickers.length) {
        color_pickers.colorpicker({
            format: "hex"
        });
    }
}

// Init bootstrap select picker
function init_selectpicker() {
    var select_pickers = $("body").find('select.selectpicker');
    if (select_pickers.length) {
        select_pickers.selectpicker({
            showSubtext: true
        });
    }
}

// Datatables custom view will fill input with the value
function dt_custom_view(value, table, custom_input_name, clear_other_filters) {
    var name = typeof(custom_input_name) == 'undefined' ? 'custom_view' : custom_input_name;
    if (typeof(clear_other_filters) != 'undefined') {
        var filters = $('._filter_data li.active').not('.clear-all-prevent');
        filters.removeClass('active');
        $.each(filters, function() {
            var input_name = $(this).find('a').attr('data-cview');
            $('._filters input[name="' + input_name + '"]').val('');
        });
    }
    var _cinput = do_filter_active(name);
    if (_cinput != name) {
        value = "";
    }
    $('input[name="' + name + '"]').val(value);
    $(table).DataTable().ajax.reload();
}

// Sets table filters dropdown to active
function do_filter_active(value, parent_selector) {
    if (value != '' && typeof(value) != 'undefined') {
        $('[data-cview="all"]').parents('li').removeClass('active');
        var selector = $('[data-cview="' + value + '"]');
        if (typeof(parent_selector) != 'undefined') {
            selector = $(parent_selector + ' [data-cview="' + value + '"]');
        }
        var parent = selector.parents('li');
        if (parent.hasClass('filter-group')) {
            var group = parent.data('filter-group');
            $('[data-filter-group="' + group + '"]').not(parent).removeClass('active');
            $('input[name="' + value + '"]').val('');
        }
        if (!parent.not('.dropdown-submenu').hasClass('active')) {
            parent.addClass('active');

        } else {
            parent.not('.dropdown-submenu').removeClass('active');
            // Remove active class from the parent dropdown if nothing selected in the child dropdown
            var parents_sub = selector.parents('li.dropdown-submenu');
            if (parents_sub.length > 0) {
                if (parents_sub.find('li.active').length == 0) {
                    parents_sub.removeClass('active');
                }
            }
            value = "";
        }
        return value;
    } else {
        $('._filters input').val('');
        $('._filter_data li.active').removeClass('active');
        $('[data-cview="all"]').parents('li').addClass('active');
        return "";
    }
}

// Called when editing member profile
function init_roles_permissions(roleid, user_changed) {
    roleid = typeof(roleid) == 'undefined' ? $('select[name="role"]').val() : roleid;
    var isedit = $('.member > input[name="isedit"]');

    // Check if user is edit view and user has changed the dropdown permission if not only return
    if (isedit.length > 0 && typeof(roleid) !== 'undefined' && typeof(user_changed) == 'undefined') {
        return;
    }

    // Administrators does not have permissions
    if ($('input[name="administrator"]').prop('checked') === true) {
        return;
    }

    // Last if the roleid is blank return
    if (roleid == '') {
        return;
    }

    // Get all permissions
    var permissions = $('table.roles').find('tr');
    requestGetJSON('misc/get_role_permissions_ajax/' + roleid).done(function(response) {
        var can_view_st, can_view_own_st;

        $.each(permissions, function() {
            var permissionid = $(this).data('id');
            var row = $(this);

            // No permissions for this role
            if (response.length == 0) {
                row.find('input[type="checkbox"]').prop('checked', false);
            }

            $.each(response, function(i, obj) {
                if (permissionid == obj.permissionid) {
                    can_view_st = (obj.can_view == 1 ? true : false);
                    can_view_own_st = (obj.can_view_own == 1 ? true : false)
                    row.find('[data-can-view]').prop('checked', can_view_st);
                    if (can_view_st == true) {
                        row.find('[data-can-view]').change();
                    }
                    row.find('[data-can-view-own]').prop('checked', can_view_own_st);
                    if (can_view_own_st == true) {
                        row.find('[data-can-view-own]').change();
                    }
                    row.find('[data-can-edit]').prop('checked', (obj.can_edit == 1 ? true : false));
                    row.find('[data-can-create]').prop('checked', (obj.can_create == 1 ? true : false));
                    row.find('[data-can-delete]').prop('checked', (obj.can_delete == 1 ? true : false));
                }
            });
        });
    });
}
// Generate hidden input field
function hidden_input(name, val) {
    return '<input type="hidden" name="' + name + '" value="' + val + '">';
}

// Show/hide full table
function toggle_small_view(table, main_data) {

    $("body").toggleClass('small-table');
    var tablewrap = $('#small-table');
    if (tablewrap.length == 0) { return; }
    var _visible = false;
    if (tablewrap.hasClass('col-md-5')) {
        tablewrap.removeClass('col-md-5').addClass('col-md-12');
        _visible = true;
        $('.toggle-small-view').find('i').removeClass('fa fa-angle-double-right').addClass('fa fa-angle-double-left');
    } else {
        tablewrap.addClass('col-md-5').removeClass('col-md-12');
        $('.toggle-small-view').find('i').removeClass('fa fa-angle-double-left').addClass('fa fa-angle-double-right');
    }
    var _table = $(table).DataTable();
    // Show hide hidden columns
    _table.columns(hidden_columns).visible(_visible, false);
    _table.columns.adjust();
    $(main_data).toggleClass('hide')
}

// Strip html from string
function stripTags(html) {
    var tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
}

// Check if field is empty
function empty(data) {
    if (typeof(data) == 'number' || typeof(data) == 'boolean') {
        return false;
    }
    if (typeof(data) == 'undefined' || data === null) {
        return true;
    }
    if (typeof(data.length) != 'undefined') {
        return data.length == 0;
    }
    var count = 0;
    for (var i in data) {
        if (data.hasOwnProperty(i)) {
            count++;
        }
    }
    return count == 0;
}

// Is mobile checker javascript
function is_mobile() {
    return app_is_mobile;
}

// Main logout function check if timers found to show the warning
function logout() {
    var started_timers = $('.started-timers-top').find('li.timer').length;
    if (started_timers > 0) {
        var warning = $('#timers-logout-template-warning').html();
        var $p = system_popup({ message: ' ', content: warning });
        $p.find('.popup-message').addClass('hide');
        return false;
    } else {
        // No timer logout free
        window.location.href = site_url + 'authentication/logout';
    }
}

// Generate color rgb
function color(r, g, b) {
    return 'rgb(' + r + ',' + g + ',' + b + ')';
}

// Url builder function with parameteres
function buildUrl(url, parameters) {
    var qs = "";
    for (var key in parameters) {
        var value = parameters[key];
        qs += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
    }
    if (qs.length > 0) {
        qs = qs.substring(0, qs.length - 1); //chop off last "&"
        url = url + "?" + qs;
    }
    return url;
}

// Function that convert decimal logged time to HH:MM format
function decimalToHM(decimal) {
    var hrs = parseInt(Number(decimal));
    var min = Math.round((Number(decimal) - hrs) * 60);
    return (hrs < 10 ? "0" + hrs : hrs) + ':' + (min < 10 ? "0" + min : min);
}

// Init the media elfinder for tinymce browser
function elFinderBrowser(field_name, url, type, win) {
    tinymce.activeEditor.windowManager.open({
        file: admin_url + 'misc/tinymce_file_browser',
        title: appLang.media_files,
        width: 900,
        height: 450,
        resizable: 'yes'
    }, {
        setUrl: function(url) {
            win.document.getElementById(field_name).value = url;
        }
    });
    return false;
}

// Function to init the tinymce editor
function init_editor(selector, settings) {
    selector = typeof(selector) == 'undefined' ? '.tinymce' : selector;
    var _editor_selector_check = $(selector);
    if (_editor_selector_check.length == 0) { return; }

    $.each(_editor_selector_check, function() {
        if ($(this).hasClass('tinymce-manual')) {
            $(this).removeClass('tinymce');
        }
    });

    // Original settings
    var _settings = {
        branding: false,
        selector: selector,
        browser_spellcheck: true,
        height: 400,
        theme: 'modern',
        skin: 'perfex',
        language: tinymce_lang,
        relative_urls: false,
        inline_styles: true,
        verify_html: false,
        cleanup: false,
        valid_elements: '+*[*]',
        valid_children: "+body[style], +style[type]",
        apply_source_formatting: false,
        remove_script_host: false,
        removed_menuitems: 'newdocument restoredraft',
        forced_root_block: false,
        autosave_restore_when_empty: false,
        fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
        setup: function(ed) {
            // Default fontsize is 12
            ed.on('init', function() {
                this.getDoc().body.style.fontSize = '12pt';
            });
        },
        table_default_styles: {
            // Default all tables width 100%
            width: '100%',
        },
        plugins: [
            'advlist autoresize autosave lists link image print hr codesample',
            'visualblocks code fullscreen',
            'media save table contextmenu',
            'paste textcolor colorpicker'
        ],
        toolbar1: 'fontselect fontsizeselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | image link | bullist numlist | restoredraft',
        file_browser_callback: elFinderBrowser,
    };

    // Add the rtl to the settings if is true
    isRTL == 'true' ? _settings.directionality = 'rtl' : '';
    isRTL == 'true' ? _settings.plugins[0] += ' directionality' : '';

    // Possible settings passed to be overwrited or added
    if (typeof(settings) != 'undefined') {
        for (var key in settings) {
            _settings[key] = settings[key];
        }
    }

    // Init the editor
    var editor = tinymce.init(_settings);
    $(document).trigger('app.editor.initialized');

    return editor;
}

// Function used to add custom bootstrap menu for setup and main menu and to add fa on front like fa fa-question
function _formatMenuIconInput(e) {
    if (typeof(e) == 'undefined') { return; }
    var _input = $(e.target);
    if (!_input.val().match(/^fa /)) {
        _input.val('fa ' + _input.val());
    }
}

// Show password on hidden input field
function showPassword(name) {
    var target = $('input[name="' + name + '"]');
    if ($(target).attr('type') == 'password' && $(target).val() != '') {
        $(target)
            .queue(function() {
                $(target).attr('type', 'text').dequeue();
            });
    } else {
        $(target).queue(function() {
            $(target).attr('type', 'password').dequeue();
        });
    }
}

// This is used for mobile where tooltip on _buttons class wrapper is found
// Will show all buttons tooltips as regular button with text
function init_btn_with_tooltips() {
    if (is_mobile()) {
        var tooltips_href_btn = $('._buttons').find('.btn-with-tooltip');
        $.each(tooltips_href_btn, function() {
            var title = $(this).attr('title');
            if (typeof(title) == 'undefined') {
                title = $(this).attr('data-title');
            }
            if (typeof(title) != 'undefined') {
                $(this).append(' ' + title);
                $(this).removeClass('btn-with-tooltip');
            }
        });
        var tooltips_group = $('._buttons').find('.btn-with-tooltip-group');
        $.each(tooltips_group, function() {
            var title = $(this).attr('title');
            if (typeof(title) == 'undefined') {
                title = $(this).attr('data-title');
            }
            if (typeof(title) != 'undefined') {
                $(this).find('.btn').eq(0).append(' ' + title);
                $(this).removeClass('btn-with-tooltip-group');
            }
        });
    }
}

// Helper hash id for estimates,invoices,proposals,expenses, credit notes
function do_hash_helper(hash) {
    if (typeof(history.pushState) != "undefined") {
        var url = window.location.href;
        var obj = { Url: url };
        history.pushState(obj, '', obj.Url);
        window.location.hash = hash;
    }
}

// Validate the form reminder
function init_form_reminder() {
    var forms = $('[id^="form-reminder-"]');
    $.each(forms, function(i, form) {
        _validate_form($(form), {
            date: 'required',
            staff: 'required'
        }, reminderFormHandler);
    });
}

// Edit reminder function
function edit_reminder(id, e) {
    requestGetJSON('misc/get_reminder/' + id).done(function(response) {
        var $reminderModal = $('.reminder-modal-' + response.rel_type + '-' + response.rel_id);
        if ($reminderModal.length === 0 && $('body').hasClass('all_reminders')) {
            // maybe from view all reminders?
            $reminderModal = $('.reminder-modal--');
            $reminderModal.find('input[name="rel_type"]').val(response.rel_type);
            $reminderModal.find('input[name="rel_id"]').val(response.rel_id);
        }
        $reminderModal.find('form').attr('action', admin_url + 'misc/edit_reminder/' + id);
        $reminderModal.find('#description').val(response.description);
        $reminderModal.find('#date').val(response.date);
        $reminderModal.find('#staff').selectpicker('val', response.staff);
        $reminderModal.find('#notify_by_email').prop('checked', response.notify_by_email == 1 ? true : false);
        $reminderModal.modal('show');
    });
}

// Handles reminder modal form
function reminderFormHandler(form) {
    form = $(form);
    var data = form.serialize();
    var serializeArray = $(form).serializeArray();
    $.post(form.attr('action'), data).done(function(data) {

        data = JSON.parse(data);

        if (data.message != '') {
            alert_float(data.alert_type, data.message);
        }
        reload_reminders_tables();
    });

    if ($('body').hasClass('all_reminders')) {
        $('.reminder-modal--').modal('hide');
    } else {
        $('.reminder-modal-' + form.find('[name="rel_type"]').val() + '-' + form.find('[name="rel_id"]').val()).modal('hide');
    }

    return false;
}

// Reloads reminders table eq when reminder is deleted
function reload_reminders_tables() {
    var available_reminders_table = ['.table-reminders', '.table-reminders-leads', '.table-my-reminders'];

    $.each(available_reminders_table, function(i, table) {
        if ($.fn.DataTable.isDataTable(table)) {
            $("body").find(table).DataTable().ajax.reload();
        }
    });
}

// Function to close modal manually... needed in some modals where the data is flexible.
function close_modal_manually(modal) {
    modal = $(modal).length == 0 ? $("body").find(modal) : modal = $(modal);
    modal.fadeOut('fast', function() {
        modal.remove();
        if (!$("body").find('.modal').is(':visible')) {
            $('.modal-backdrop').remove();
            $("body").removeClass('modal-open');
        }
    });
}

/* Global function for editing notes */
function toggle_edit_note(id) {
    $("body").find('[data-note-edit-textarea="' + id + '"]').toggleClass('hide');
    $("body").find('[data-note-description="' + id + '"]').toggleClass('hide');
}

// Global function to edit note
function edit_note(id) {
    var description = $("body").find('[data-note-edit-textarea="' + id + '"] textarea').val();
    if (description != '') {
        $.post(admin_url + 'misc/edit_note/' + id, {
            description: description
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $("body").find('[data-note-description="' + id + '"]').html(nl2br(description));
            }
        });
        toggle_edit_note(id);
    }

}

// Toggles sales file visibility for customer eq for invoices, estimates, proposals
function toggle_file_visibility(attachment_id, rel_id, invoker) {
    requestGet('misc/toggle_file_visibility/' + attachment_id).done(function(response) {
        if (response == 1) {
            $(invoker).find('i').removeClass('fa fa-toggle-off').addClass('fa fa-toggle-on');
        } else {
            $(invoker).find('i').removeClass('fa fa-toggle-on').addClass('fa fa-toggle-off');
        }
    });
}

// Equivalent function like php nl2br
function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

// Fixes kanban height to be compatible with content and screen height
function fix_kanban_height(col_px, container_px) {
    // Set the width of the kanban container
    $("body").find('div.dt-loader').remove();
    var kanbanCol = $('.kan-ban-content-wrapper');
    kanbanCol.css('max-height', (window.innerHeight - col_px) + 'px');
    $('.kan-ban-content').css('min-height', (window.innerHeight - col_px) + 'px');
    var kanbanColCount = parseInt(kanbanCol.length);
    $('.container-fluid').css('min-width', (kanbanColCount * container_px) + 'px');
}

// Kanban load more
function kanban_load_more(status_id, e, url, column_px, container_px) {
    var LoadMoreParameters = [];
    var search = $('input[name="search"]').val();
    var _kanban_param_val;
    var page = $(e).attr('data-page');
    var total_pages = $('[data-col-status-id="' + status_id + '"]').data('total-pages');
    if (page <= total_pages) {

        var sort_type = $('input[name="sort_type"]');
        var sort = $('input[name="sort"]').val();
        if (sort_type.length != 0 && sort_type.val() != '') {
            LoadMoreParameters['sort_by'] = sort_type.val();
            LoadMoreParameters['sort'] = sort;
        }

        if (typeof(search) != 'undefined' && search != '') {
            LoadMoreParameters['search'] = search;
        }

        $.each($('#kanban-params input'), function() {
            if ($(this).attr('type') == 'checkbox') {
                _kanban_param_val = $(this).prop('checked') === true ? $(this).val() : '';
            } else {
                _kanban_param_val = $(this).val();
            }
            if (_kanban_param_val != '') {
                LoadMoreParameters[$(this).attr('name')] = _kanban_param_val;
            }
        });

        LoadMoreParameters['status'] = status_id;
        LoadMoreParameters['page'] = page;
        LoadMoreParameters['page']++;
        requestGet(buildUrl(admin_url + url, LoadMoreParameters)).done(function(response) {
            page++;
            $('[data-load-status="' + status_id + '"]').before(response);
            $(e).attr('data-page', page);
            fix_kanban_height(column_px, container_px);
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
        if (page >= total_pages - 1) {
            $(e).addClass("disabled");
        }
    }
}

// Check if kanban col is empty and perform necessary actions
function check_kanban_empty_col(selector) {
    var statuses = $('[data-col-status-id]');
    $.each(statuses, function(i, obj) {
        var total = $(obj).find(selector).length;
        if (total == 0) {
            $(obj).find('.kanban-empty').removeClass('hide');
            $(obj).find('.kanban-load-more').addClass('hide');
        } else {
            $(obj).find('.kanban-empty').addClass('hide');
        }
    });
}

// General function to init kan ban based on settings
function init_kanban(url, callbackUpdate, connect_with, column_px, container_px, callback_after_load) {

    if ($('#kan-ban').length == 0) { return; }
    var parameters = [];
    var _kanban_param_val;

    $.each($('#kanban-params input'), function() {
        if ($(this).attr('type') == 'checkbox') {
            _kanban_param_val = $(this).prop('checked') === true ? $(this).val() : '';
        } else {
            _kanban_param_val = $(this).val();
        }
        if (_kanban_param_val != '') {
            parameters[$(this).attr('name')] = _kanban_param_val;
        }
    });

    var search = $('input[name="search"]').val();
    if (typeof(search) != 'undefined' && search !== '') { parameters['search'] = search; }

    var sort_type = $('input[name="sort_type"]');
    var sort = $('input[name="sort"]').val();
    if (sort_type.length != 0 && sort_type.val() != '') {
        parameters['sort_by'] = sort_type.val();
        parameters['sort'] = sort;
    }

    parameters['kanban'] = true;
    var url = admin_url + url;
    url = buildUrl(url, parameters);
    delay(function() {
        $("body").append('<div class="dt-loader"></div>');
        $('#kan-ban').load(url, function() {

            fix_kanban_height(column_px, container_px);
            var scrollingSensitivity = 20,
                scrollingSpeed = 60;

            if (typeof(callback_after_load) != 'undefined') { callback_after_load(); }

            $(".status").sortable({
                connectWith: connect_with,
                helper: 'clone',
                appendTo: '#kan-ban',
                placeholder: "ui-state-highlight-card",
                revert: 'invalid',
                scrollingSensitivity: 50,
                scrollingSpeed: 70,
                sort: function(event, uiHash) {
                    var scrollContainer = uiHash.placeholder[0].parentNode;
                    // Get the scrolling parent container
                    scrollContainer = $(scrollContainer).parents('.kan-ban-content-wrapper')[0];
                    var overflowOffset = $(scrollContainer).offset();
                    if ((overflowOffset.top + scrollContainer.offsetHeight) - event.pageY < scrollingSensitivity) {
                        scrollContainer.scrollTop = scrollContainer.scrollTop + scrollingSpeed;
                    } else if (event.pageY - overflowOffset.top < scrollingSensitivity) {
                        scrollContainer.scrollTop = scrollContainer.scrollTop - scrollingSpeed;
                    }
                    if ((overflowOffset.left + scrollContainer.offsetWidth) - event.pageX < scrollingSensitivity) {
                        scrollContainer.scrollLeft = scrollContainer.scrollLeft + scrollingSpeed;
                    } else if (event.pageX - overflowOffset.left < scrollingSensitivity) {
                        scrollContainer.scrollLeft = scrollContainer.scrollLeft - scrollingSpeed;

                    }
                },
                change: function() {
                    var list = $(this).closest('ul');
                    var KanbanLoadMore = $(list).find('.kanban-load-more');
                    $(list).append($(KanbanLoadMore).detach());
                },
                start: function(event, ui) {
                    $('body').css('overflow', 'hidden');

                    $(ui.helper).addClass('tilt');
                    $(ui.helper).find('.panel-body').css('background', '#fbfbfb');
                    // Start monitoring tilt direction
                    tilt_direction($(ui.helper));
                },
                stop: function(event, ui) {
                    $('body').removeAttr('style');
                    $(ui.helper).removeClass("tilt");
                    // Unbind temporary handlers and excess data
                    $("html").off('mousemove', $(ui.helper).data("move_handler"));
                    $(ui.helper).removeData("move_handler");
                },
                update: function(event, ui) {
                    callbackUpdate(ui, this);
                }
            });

            $('.status').sortable({
                cancel: '.not-sortable'
            });

        });

    }, 200);
}

function kan_ban_sort(type, callback) {
    var sort_type = $('input[name="sort_type"]');
    sort_type.val(type);
    var sort = $('input[name="sort"]');
    var val = sort.val().toLowerCase();
    sort.val((val == 'asc' ? 'DESC' : 'ASC'));
    init_kan_ban_sort_icon(sort.val(), type);
    callback();
}

function init_kan_ban_sort_icon(sort, type) {
    $('body').find('.kanban-sort-icon').remove();
    $('body').find('.' + type).prepend(" <i class=\'kanban-sort-icon fa fa-sort-amount-" + sort.toLowerCase() + '\'></i>');
}

// Kanban til direction
function tilt_direction(item) {
    setTimeout(function() {
        var left_pos = item.position().left,
            move_handler = function(e) {
                if (e.pageX >= left_pos) {
                    item.addClass("right");
                    item.removeClass("left");
                } else {
                    item.addClass("left");
                    item.removeClass("right");
                }
                left_pos = e.pageX;
            };
        $("html").on("mousemove", move_handler);
        item.data("move_handler", move_handler);
    }, 1000);
}

// When window scroll to down load more posts
$('#newsfeed').scroll(function(e) {
    var elem = $(e.currentTarget);
    if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) {
        load_newsfeed();
    }
    $('#newsfeed .close_newsfeed').css('top', ($(this).scrollTop() + 20) + "px");
});

// Newsfeed form after newfeed href is clicked
function init_newsfeed_form() {
    // Configure dropzone
    if (typeof(newsFeedDropzone) == 'undefined') {
        // Init new post form
        $("body").on('submit', '#new-post-form', function() {
            $.post(this.action, $(this).serialize()).done(function(response) {
                response = JSON.parse(response);
                if (response.postid) {
                    if (newsFeedDropzone.getQueuedFiles().length > 0) {
                        newsFeedDropzone.options.url = admin_url + 'newsfeed/add_post_attachments/' + response.postid;
                        newsFeedDropzone.processQueue();
                        return;
                    }
                    newsfeed_new_post(response.postid);
                    clear_newsfeed_post_area();
                }
            });
            return false;
        });
    }

    newsFeedDropzone = new Dropzone("#new-post-form", $.extend({}, _dropzone_defaults(), {
        clickable: '.add-post-attachments',
        autoProcessQueue: false,
        addRemoveLinks: true,
        parallelUploads: app_newsfeed_maximum_files_upload,
        maxFiles: app_newsfeed_maximum_files_upload,
        dragover: function(file) {
            $('#new-post-form').addClass('dropzone-active');
        },
        complete: function(file) {},
        drop: function(file) {
            $('#new-post-form').removeClass('dropzone-active')
        },
        success: function(files, response) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                response = JSON.parse(response);
                newsfeed_new_post(response.postid);
                clear_newsfeed_post_area();
                this.removeAllFiles();
            }
        }
    }));
}

// Clear newsfeed new post area
function clear_newsfeed_post_area() {
    $('#new-post-form textarea').val('');
    $('#post-visibility').selectpicker('val', 'all');
}

// Load post likes modal
function load_post_likes(postid) {

    if (track_load_post_likes <= post_likes_total_pages) {
        $.post(admin_url + 'newsfeed/load_likes_modal', {
            page: track_load_post_likes,
            postid: postid
        }).done(function(response) {
            track_load_post_likes++
            $('#modal_post_likes_wrapper').append(response);
        });

        if (track_load_post_likes >= post_likes_total_pages - 1) {
            $('.likes_modal .modal-footer').addClass('hide');
        }
    }
}

// Load comment likes modal
function load_comment_likes(commentid) {

    if (track_load_comment_likes <= comment_likes_total_pages) {
        $.post(admin_url + 'newsfeed/load_comment_likes_model', {
            page: track_load_comment_likes,
            commentid: commentid
        }).done(function(response) {
            track_load_comment_likes++
            $('#modal_comment_likes_wrapper').append(response);
        });

        if (track_load_comment_likes >= comment_likes_total_pages - 1) {
            $('.likes_modal .modal-footer').addClass('hide');
        }
    }
}

// On click href load more comments from single post
function load_more_comments(link) {
    var postid = $(link).data('postid');
    var page = $(link).find('input[name="page"]').val();
    var total_pages = $(link).data('total-pages');

    if (page <= total_pages) {
        $.post(admin_url + 'newsfeed/init_post_comments/' + postid, {
            page: page
        }).done(function(response) {
            $(link).data('track-load-comments', page);
            $('[data-comments-postid="' + postid + '"] .load-more-comments').before(response);
        });
        page++;
        $(link).find('input[name="page"]').val(page);
        if (page >= total_pages - 1) {
            $(link).addClass('hide');
            $(link).removeClass('display-block');
        }
    }
}

// New post added append data
function newsfeed_new_post(postid) {
    var data = {};
    data.postid = postid;
    $.post(admin_url + 'newsfeed/load_newsfeed', data).done(function(response) {
        var pinned = $('#newsfeed_data').find('.pinned');
        var pinned_length = pinned.length
        if (pinned_length == 0) {
            $('#newsfeed_data').prepend(response);
        } else {
            var last_pinned = $('#newsfeed_data').find('.pinned').eq(pinned_length - 1);
            $(last_pinned).after(response);
        }
    });
}

// Init newsfeed data
function load_newsfeed(postid) {

    var data = {};
    data.page = newsfeed_posts_page;
    if (typeof(postid) != 'undefined' && postid != 0) {
        data.postid = postid;
    }
    var total_pages = $('input[name="total_pages_newsfeed"]').val();
    if (newsfeed_posts_page <= total_pages) {
        $.post(admin_url + 'newsfeed/load_newsfeed', data).done(function(response) {
            newsfeed_posts_page++
            $('#newsfeed_data').append(response);
        });
        if (newsfeed_posts_page >= total_pages - 1) { return; }
    }
}

// When user click heart button
function like_post(postid) {
    requestGetJSON('newsfeed/like_post/' + postid).done(function(response) {
        if (response.success == true) { refresh_post_likes(postid); }
    });
}

// Unlikes post
function unlike_post(postid) {
    requestGetJSON('newsfeed/unlike_post/' + postid).done(function(response) {
        if (response.success == true) { refresh_post_likes(postid); }
    });
}

// Like post comment
function like_comment(commentid, postid) {
    requestGetJSON('newsfeed/like_comment/' + commentid + '/' + postid).done(function(response) {
        if (response.success == true) {
            $('[data-commentid="' + commentid + '"]').replaceWith(response.comment);
        }
    });
}

// Unlike post comment
function unlike_comment(commentid, postid) {
    requestGetJSON('newsfeed/unlike_comment/' + commentid + '/' + postid).done(function(response) {
        if (response.success == true) {
            $('[data-commentid="' + commentid + '"]').replaceWith(response.comment);
        }
    });
}

// Add new comment to post
function add_comment(input) {
    var postid = $(input).data('postid');
    $.post(admin_url + 'newsfeed/add_comment', {
        content: $(input).val(),
        postid: postid
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success == true) {
            $(input).val('');
            if ($("body").find('[data-comments-postid="' + postid + '"] .post-comment').length > 0) {
                $("body").find('[data-comments-postid="' + postid + '"] .post-comment').prepend(response.comment);
            } else {
                refresh_post_comments(postid);
            }
        }
    });
}

// Removes post comment
function remove_post_comment(id, postid) {
    requestGetJSON('newsfeed/remove_post_comment/' + id + '/' + postid).done(function(response) {
        if (response.success == true) {
            $('.comment[data-commentid="' + id + '"]').remove();
        }
    });
}

// Refreshing only post likes
function refresh_post_likes(postid) {
    requestGet('newsfeed/init_post_likes/' + postid + '?refresh_post_likes=true').done(function(response) {
        $('[data-likes-postid="' + postid + '"]').html(response);
    });
}

// Refreshing only post comments
function refresh_post_comments(postid) {
    $.post(admin_url + 'newsfeed/init_post_comments/' + postid + '?refresh_post_comments=true').done(function(response) {
        $('[data-comments-postid="' + postid + '"]').html(response);
    });
}

// Delete post from database
function delete_post(postid) {
    if (confirm_delete()) {
        $.post(admin_url + 'newsfeed/delete_post/' + postid, function(response) {
            if (response.success == true) { $('[data-main-postid="' + postid + '"]').remove(); }
        }, 'json');
    }
}

// Pin post to top
function pin_post(id) {
    requestGetJSON('newsfeed/pin_newsfeed_post/' + id).done(function(response) {
        if (response.success == true) { window.location.reload(); }
    });
}

// Unpin post from top
function unpin_post(id) {
    requestGetJSON('newsfeed/unpin_newsfeed_post/' + id).done(function(response) {
        if (response.success == true) { window.location.reload(); }
    });
}

function _gen_lead_add_inline_on_select_field(type) {
    var html = '';
    if ($('body').hasClass('leads-email-integration') || $('body').hasClass('web-to-lead-form')) {
        type = 'lead_' + type;
    }
    html = "<div id=\"new_lead_" + type + "_inline\" class=\"form-group\"><label for=\"new_" + type + "_name\">" + $('label[for=\"' + type + '\"]').html().trim() + "</label><div class=\"input-group\"><input type=\"text\" id=\"new_" + type + "_name\" name=\"new_" + type + "_name\" class=\"form-control\"><div class=\"input-group-addon\"><a href=\"#\" onclick=\"lead_add_inline_select_submit('" + type + "'); return false;\" class=\"lead-add-inline-submit-" + type + "\"><i class=\"fa fa-check\"></i></a></div></div></div>";
    $('.form-group-select-input-' + type).after(html);
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop('disabled', true);
    $(".inline-field-new").addClass('disabled').css('opacity', 0.5);
    $('.form-group-select-input-' + type).addClass('hide');
}

function new_lead_status_inline() {
    _gen_lead_add_inline_on_select_field('status');
}

function new_lead_source_inline() {
    _gen_lead_add_inline_on_select_field('source');
}

function lead_add_inline_select_submit(type) {
    var val = $('#new_' + type + '_name').val().trim();
    if (val != '') {

        var requestURI = type;
        if (type.indexOf('lead_') > -1) {
            requestURI = requestURI.replace('lead_', '');
        }

        var data = {};
        data.name = val;
        data.inline = true;
        $.post(admin_url + 'leads/' + requestURI, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var select = $('body').find('select#' + type);
                select.append('<option value="' + response.id + '">' + val + '</option>');
                select.selectpicker('val', response.id);
                select.selectpicker('refresh');
                select.parents('.form-group').removeClass('has-error');
            }
        });
    }

    $('#new_lead_' + type + '_inline').remove();
    $('.form-group-select-input-' + type).removeClass('hide');
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop('disabled', false);
    $(".inline-field-new").removeClass('disabled').removeAttr('style');
}

// Init lead for add/edit/view or refresh data
function init_lead(id) {
    if ($('#task-modal').is(':visible')) {
        $('#task-modal').modal('hide');
    }
    // In case header error
    if (init_lead_modal_data(id)) {
        $('#lead-modal').modal('show');
    }
}

// Lead form validation
function validate_lead_form() {
    _validate_form($('#lead_form'), {
        name: 'required',
        status: {
            required: {
                depends: function(element) {
                    if ($('[lead-is-junk-or-lost]').length > 0) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        },
        source: 'required',
        email: {
            email: true,
            remote: {
                url: admin_url + "leads/email_exists",
                type: 'post',
                data: {
                    email: function() {
                        return $('input[name="email"]').val();
                    },
                    leadid: function() {
                        return $('input[name="leadid"]').val();
                    }
                }
            }
        }
    }, lead_profile_form_handler);
}

// Lead conver to customer form validation
function validate_lead_convert_to_client_form() {
    var rules_convert_lead = {
        firstname: 'required',
        lastname: 'required',
        password: {
            required: {
                depends: function(element) {
                    var sent_set_password = $('input[name="send_set_password_email"]');
                    if (sent_set_password.prop('checked') == false) {
                        return true;
                    }
                }
            }
        },
        email: {
            required: true,
            email: true,
            remote: {
                url: site_url + "admin/misc/contact_email_exists",
                type: 'post',
                data: {
                    email: function() {
                        return $('#lead_to_client_form input[name="email"]').val();
                    },
                    userid: ''
                }
            }
        }
    };
    if (app_company_is_required == 1) {
        rules_convert_lead.company = 'required';
    }
    _validate_form($('#lead_to_client_form'), rules_convert_lead);
}

// Lead profile data function form handler
function lead_profile_form_handler(form) {
    form = $(form);
    var data = form.serialize();
    var serializeArray = $(form).serializeArray();
    var leadid = $('#lead-modal').find('input[name="leadid"]').val();
    $('.lead-save-btn').addClass('disabled');
    $.post(form.attr('action'), data).done(function(response) {
        response = JSON.parse(response);
        if (response.message != '') {
            alert_float('success', response.message);
        }
        if (response.proposal_warning && response.proposal_warning != false) {
            $("body").find('#lead_proposal_warning').removeClass('hide');
            $("body").find('#lead-modal').animate({
                scrollTop: 0
            }, 800);
        } else {
            _lead_init_data(response, response.id);
        }
        // If is from kanban reload the list tables
        if ($.fn.DataTable.isDataTable('.table-leads')) {
            table_leads.DataTable().ajax.reload(null, false);
        }
    }).fail(function(data) {
        alert_float('danger', data.responseText);
        return false;
    });
    return false;
}

// Updates all proposals emails linked to lead, this wil be executed when eq lead email is changed
function update_all_proposal_emails_linked_to_lead(id) {
    $.post(admin_url + 'leads/update_all_proposal_emails_linked_to_lead/' + id, {
        update: true
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', response.message);
        }
        init_lead_modal_data(id);
    });
}

// Add lead data returned from server to the lead modal
function _lead_init_data(data, id) {

    var hash = window.location.hash;

    var $leadModal = $('#lead-modal');
    $('#lead_reminder_modal').html(data.leadView.reminder_data)

    $leadModal.find('.data').html(data.leadView.data);
    $leadModal.modal({ show: true, backdrop: 'static' });
    init_tags_inputs();
    init_selectpicker();
    init_form_reminder();
    init_datepicker();
    init_color_pickers();
    validate_lead_form();

    if (hash == '#tab_lead_profile' || hash == '#attachments' || hash == '#lead_notes') {
        window.location.hash = hash;
    }
    if (id != '' && typeof(id) != 'undefined') {

        if (typeof(Dropbox) != 'undefined') {
            document.getElementById("dropbox-chooser-lead").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    $.post(admin_url + 'leads/add_external_attachment', {
                        files: files,
                        lead_id: id,
                        external: 'dropbox'
                    }).done(function() {
                        init_lead_modal_data(id);
                    });
                },
                linkType: "preview",
                extensions: app_allowed_files.split(','),
            }));
        }

        if (typeof(leadAttachmentsDropzone) != 'undefined') {
            leadAttachmentsDropzone.destroy();
        }

        leadAttachmentsDropzone = new Dropzone("#lead-attachment-upload", $.extend({}, _dropzone_defaults(), {
            sending: function(file, xhr, formData) {
                formData.append("id", id);
                if (this.getQueuedFiles().length == 0) {
                    formData.append("last_file", true);
                }
            },
            success: function(file, response) {
                response = JSON.parse(response);
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    _lead_init_data(response, response.id);
                }
            }
        }));

        $leadModal.find('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
        var latest_lead_activity = $leadModal.find('#lead_activity .feed-item:last-child .text').html();
        if (typeof(latest_lead_activity) != 'undefined') {
            $leadModal.find('#lead-latest-activity').html(latest_lead_activity);
        } else {
            $leadModal.find('.lead-latest-activity > .lead-info-heading').addClass('hide');
        }

        // The status is not required when lead is lost or junk
        // Remove the * required mark
        if ($('[lead-is-junk-or-lost]').length > 0) {
            $('.form-group-select-input-status').find('.req').remove();
        }
    }
}

// Fetches lead modal data, can be edit/add/view
function init_lead_modal_data(id, url) {
    requestGetJSON((typeof(url) != 'undefined' ? url : 'leads/lead/') + (typeof(id) != 'undefined' ? id : '')).done(function(response) {
        _lead_init_data(response, id);
    }).fail(function(data) {
        alert_float('danger', data.responseText);
    });
}

// Kan ban leads sorting
function leads_kanban_sort(type) {
    kan_ban_sort(type, leads_kanban);
}

// Update lead when action is performed from leads kan ban eq order or status change
function leads_kanban_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {};
        data.status = $(ui.item.parent()[0]).data('lead-status-id');
        data.leadid = $(ui.item).data('lead-id');

        var order = [];
        var status = $(ui.item).parents('.leads-status').find('li')
        var i = 1;
        $.each(status, function() {
            order.push([$(this).data('lead-id'), i]);
            i++;
        });

        data.order = order;
        setTimeout(function() {
            $.post(admin_url + 'leads/update_kan_ban_lead_status', data).done(function(response) {
                check_kanban_empty_col('[data-lead-id]');
            });
        }, 200);
    }
}

// Leads statuses kanban sortable
function init_leads_status_sortable() {
    $("#kan-ban").sortable({
        helper: 'clone',
        item: '.kan-ban-col',
        update: function(event, ui) {
            var order = [];
            var status = $('.kan-ban-col');
            var i = 0;
            $.each(status, function() {
                order.push([$(this).data('col-status-id'), i]);
                i++;
            });
            var data = {}
            data.order = order;
            $.post(admin_url + 'leads/update_status_order', data);
        }
    });
}

// Init the leads kanban
function leads_kanban(search) {
    init_kanban('leads/kanban', leads_kanban_update, '.leads-status', 315, 360, init_leads_status_sortable);
}

// Deleting lead attachments
function delete_lead_attachment(wrapper, id) {
    if (confirm_delete()) {
        requestGetJSON('leads/delete_attachment/' + id).done(function(response) {
            if (response.success == true) { $(wrapper).parents('.lead-attachment-wrapper').remove(); }
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
    }
}

// Delete lead note
function delete_lead_note(wrapper, id) {
    if (confirm_delete()) {
        requestGetJSON('leads/delete_note/' + id).done(function(response) {
            if (response.success == true) { $(wrapper).parents('.lead-note').remove(); }
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
    }
}

// Mark lead as lost function
function lead_mark_as_lost(id) {
    requestGetJSON('leads/mark_as_lost/' + id).done(function(response) {
        if (response.success == true) {
            alert_float('success', response.message);
            $("body").find('tr#lead_' + id).remove();
            $("body").find('#kan-ban li[data-lead-id="' + id + '"]').remove();
        }
        _lead_init_data(response, response.id);
    }).fail(function(error) {
        alert_float('danger', error.responseText);
    });
}

// Unmark lead as lost function
function lead_unmark_as_lost(id) {
    requestGetJSON('leads/unmark_as_lost/' + id).done(function(response) {
        if (response.success == true) { alert_float('success', response.message); }
        _lead_init_data(response, response.id);
    }).fail(function(error) {
        alert_float('danger', error.responseText);
    });
}

// Mark lead as junk function
function lead_mark_as_junk(id) {
    requestGetJSON('leads/mark_as_junk/' + id).done(function(response) {
        if (response.success == true) {
            alert_float('success', response.message);
            $("body").find('tr#lead_' + id).remove();
            $("body").find('#kan-ban li[data-lead-id="' + id + '"]').remove();
        }
        _lead_init_data(response, response.id);
    }).fail(function(error) {
        alert_float('danger', error.responseText);
    });
}

// Unmark lead as junk function
function lead_unmark_as_junk(id) {
    requestGetJSON('leads/unmark_as_junk/' + id).done(function(response) {
        if (response.success == true) { alert_float('success', response.message); }
        _lead_init_data(response, response.id);
    }).fail(function(error) {
        alert_float('danger', error.responseText);
    });
}

// Convert lead to customer
function convert_lead_to_customer(id) {
    $('#lead-modal').modal('hide');
    requestGet('leads/get_convert_data/' + id).done(function(response) {
        $('#lead_convert_to_customer').html(response);
        $('#convert_lead_to_client_modal').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    }).fail(function(data) {
        alert_float('danger', data.responseText);
    });
}

// Leads bulk action
function leads_bulk_action(event) {
    if (confirm_delete()) {
        var mass_delete = $('#mass_delete').prop('checked');
        var ids = [];
        var data = {};
        if (mass_delete == false || typeof(mass_delete) == 'undefined') {
            data.status = $('#move_to_status_leads_bulk').val();
            data.assigned = $('#assign_to_leads_bulk').val();
            data.source = $('#move_to_source_leads_bulk').val();
            data.last_contact = $('#leads_bulk_last_contact').val();
            data.tags = $('#tags_bulk').tagit('assignedTags');
            data.visibility = $('input[name="leads_bulk_visibility"]:checked').val();

            data.assigned = typeof(data.assigned) == 'undefined' ? '' : data.assigned;
            data.visibility = typeof(data.visibility) == 'undefined' ? '' : data.visibility;

            if (data.status == '' && data.assigned == '' && data.source == '' && data.last_contact == '' && data.tags == '' && data.visibility == '') {
                return;
            }
        } else {
            data.mass_delete = true;
        }
        var rows = table_leads.find('tbody tr');
        $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') == true) {
                ids.push(checkbox.val());
            }
        });
        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function() {
            $.post(admin_url + 'leads/bulk_action', data).done(function() {
                window.location.reload();
            }).fail(function(data) {
                $('#lead-modal').modal('hide');
                alert_float('danger', data.responseText);
            });
        }, 200);
    }
}

// Proposal sync data in case eq mail is changed, shown for lead and customers.
function sync_proposals_data(rel_id, rel_type) {
    var data = {};
    var modal_sync = $('#sync_data_proposal_data');
    data.country = modal_sync.find('select[name="country"]').val();
    data.zip = modal_sync.find('input[name="zip"]').val();
    data.state = modal_sync.find('input[name="state"]').val();
    data.city = modal_sync.find('input[name="city"]').val();
    data.address = modal_sync.find('textarea[name="address"]').val();
    data.phone = modal_sync.find('input[name="phone"]').val();
    data.rel_id = rel_id;
    data.rel_type = rel_type;
    $.post(admin_url + 'proposals/sync_data', data).done(function(response) {
        response = JSON.parse(response);
        alert_float('success', response.message);
        modal_sync.modal('hide');
    });
}

// Table announcements
function init_table_announcements(manual) {
    if (typeof(manual) == 'undefined' && $("body").hasClass('dashboard')) { return false; }
    initDataTable('.table-announcements', admin_url + 'announcements', [2], [2], 'undefined', [1, 'DESC']);
}

// Table tickets
function init_table_tickets(manual) {

    // Single ticket is for other tickets from user
    if (typeof(manual) == 'undefined' &&
        ($("body").hasClass('dashboard') || $('body').hasClass('single-ticket'))) {
        return false;
    }

    if ($("body").find('.tickets-table').length == 0) { return; }

    var tickets_not_sortable = $('.tickets-table').find('th').length - 1;
    var TicketServerParams = {},
        Tickets_Filters = $('._hidden_inputs._filters.tickets_filters input');
    var tickets_date_created_index = $('table.tickets-table thead .ticket_created_column').index();
    $.each(Tickets_Filters, function() {
        TicketServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    TicketServerParams['project_id'] = '[name="project_id"]';

    var _tns = [tickets_not_sortable];
    var _tickets_table_url = admin_url + 'tickets';

    if ($("body").hasClass('tickets_page')) {
        _tns.push(0);
        _tickets_table_url += '?bulk_actions=true';
    }

    _table_api = initDataTable('.tickets-table', _tickets_table_url, _tns, _tns, TicketServerParams, [tickets_date_created_index, 'DESC']);

    if (_table_api && $("body").hasClass('dashboard')) {
        _table_api
            .column(tickets_not_sortable).visible(false, false)
            .column(3).visible(false, false)
            .column(tickets_date_created_index).visible(false, false)
            .column(4).visible(false, false)
            .column(5).visible(false, false)
            .columns.adjust();
    }
}

// Staff projects table in staff profile
function init_table_staff_projects(manual) {
    if (typeof(manual) == 'undefined' && $("body").hasClass('dashboard')) { return false; }
    if ($("body").find('.table-staff-projects').length == 0) { return; }

    var staffProjectsParams = {},
        Staff_Projects_Filters = $('._hidden_inputs._filters.staff_projects_filter input');

    $.each(Staff_Projects_Filters, function() {
        staffProjectsParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    initDataTable('.table-staff-projects', admin_url + 'projects/staff_projects', 'undefined', 'undefined', staffProjectsParams, [2, 'ASC']);
}

// Fix task checklist content textarea height
function do_task_checklist_items_height(task_checklist_items) {
    if (typeof(task_checklist_items) == 'undefined') {
        task_checklist_items = $("body").find("textarea[name='checklist-description']");
    }

    $.each(task_checklist_items, function() {
        var val = $(this).val();
        if ($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))) {
            $(this).height(0).height(this.scrollHeight);
            $(this).parents('.checklist').height(this.scrollHeight);
        }
        if (val == '') {
            $(this).removeAttr('style');
            $(this).parents('.checklist').removeAttr('style');
        }
    });
}

// Recalculate task checklist items progress, this progress is shown only if there is more then 2 checklist items
function recalculate_checklist_items_progress() {
    var total_finished = $('input[name="checklist-box"]:checked').length;
    var total_checklist_items = $('input[name="checklist-box"]').length;
    var percent = 0,
        task_progress_bar = $('.task-progress-bar');
    if (total_checklist_items == 0) {
        // remove the heading for checklist items
        $("body").find('.chk-heading').remove();
        $('#task-no-checklist-items').removeClass('hide');
    } else {
        $('#task-no-checklist-items').addClass('hide');
    }
    if (total_checklist_items > 2) {
        task_progress_bar.parents('.progress').removeClass('hide');
        percent = (total_finished * 100) / total_checklist_items;
    } else {
        task_progress_bar.parents('.progress').addClass('hide');
        return false;
    }
    task_progress_bar.css('width', percent.toFixed(2) + '%');
    task_progress_bar.text(percent.toFixed(2) + '%');
}

// Remove task checklist items template
function remove_checklist_item_template(id) {
    requestGetJSON('tasks/remove_checklist_item_template/' + id).done(function(response) {
        if (response.success == true) {
            var itemsTemplateSelect = $("body").find('select.checklist-items-template-select');
            var deletedItemDescription = itemsTemplateSelect.find('option[value="' + id + '"]').html().trim();
            var currentChecklists = $('#task-modal .checklist');
            $.each(currentChecklists, function(i, area) {
                var checkList = $(area);
                if (checkList.find('textarea[name="checklist-description"]').val().trim() == deletedItemDescription) {
                    checkList.find('.save-checklist-template').removeClass('hide');
                }
            });
            itemsTemplateSelect.find('option[value="' + id + '"]').remove();
            itemsTemplateSelect.selectpicker('refresh');
            if (itemsTemplateSelect.find('option').length == 1) {
                itemsTemplateSelect.selectpicker('destroy');
                $('.checklist-templates-wrapper').addClass('hide');
            }
        }
    });
}

// New task checklist items template
function save_checklist_item_template(id, field) {
    var description = $('.checklist[data-checklist-id="' + id + '"] textarea').val();
    $.post(admin_url + 'tasks/save_checklist_item_template', { description: description }).done(function(response) {
        response = JSON.parse(response);
        $(field).addClass('hide');
        var singleChecklistTemplate = $('.checklist-templates-wrapper');
        singleChecklistTemplate.find('select option[value=""]').after('<option value="' + response.id + '">' + description.trim() + '</option>');
        singleChecklistTemplate.removeClass('hide');
        singleChecklistTemplate.find('select').selectpicker('refresh');
    });
}

// Updates task checklist items order
function update_checklist_order() {
    var order = [];
    var items = $("body").find('.checklist');
    if (items.length == 0) { return; }
    var i = 1;
    $.each(items, function() {
        order.push([$(this).data('checklist-id'), i]);
        i++;
    });
    var data = {}
    data.order = order;
    $.post(admin_url + 'tasks/update_checklist_order', data);
}

// New task checklist item
function add_task_checklist_item(task_id, description) {
    description = typeof(description) == 'undefined' ? '' : description;
    $.post(admin_url + 'tasks/add_checklist_item', {
        taskid: task_id,
        description: description
    }).done(function() {
        init_tasks_checklist_items(true, task_id);
    });
}

// Remove task checklist item from the task
function delete_checklist_item(id, field) {
    requestGetJSON('tasks/delete_checklist_item/' + id).done(function(response) {
        if (response.success == true) {
            $(field).parents('.checklist').remove();
            recalculate_checklist_items_progress();
        }
    });
}

// Fetches task checklist items.
function init_tasks_checklist_items(is_new, task_id) {
    $.post(admin_url + 'tasks/init_checklist_items', {
        taskid: task_id
    }).done(function(data) {
        $('#checklist-items').html(data);
        if (typeof(is_new) != 'undefined') {
            var first = $('#checklist-items').find('.checklist textarea').eq(0);
            if (first.val() == '') { first.focus(); }
        }
        recalculate_checklist_items_progress();
        update_checklist_order();
    });
}

// Removes task single attachment
function remove_task_attachment(link, id) {
    if (confirm_delete()) {
        requestGetJSON('tasks/remove_task_attachment/' + id).done(function(response) {
            if (response.success == true) { $('[data-task-attachment-id="' + id + '"]').remove(); }
            var att_wrap = $("body").find('.task_attachments_wrapper');
            var attachments = att_wrap.find('.task-attachment-col');
            var taskAttachmentsMore = $("body").find('#show-more-less-task-attachments-col .task-attachments-more');
            if (attachments.length == 0) {
                att_wrap.remove();
            } else if (attachments.length == 2 && taskAttachmentsMore.hasClass('hide')) {
                $("body").find('#show-more-less-task-attachments-col').remove();
            } else if ($('.task-attachment-col:visible').length == 0 && !taskAttachmentsMore.hasClass('hide')) {
                taskAttachmentsMore.click();
            }
        });
    }
}

// Add new task comment from the modal
function add_task_comment(task_id) {
    var data = {};
    data.content = tinyMCE.activeEditor.getContent();

    data.taskid = task_id;
    $.post(admin_url + 'tasks/add_task_comment', data).done(function(response) {
        response = JSON.parse(response);
        _task_append_html(response.taskHtml);
        // Remove task comment editor instance
        // Causing error because of are you sure you want to leave this page, the plugin still sees as active and dirty.
        tinymce.remove('#task_comment');
    });
}

// Deletes task comment from database
function remove_task_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('tasks/remove_comment/' + commentid).done(function(response) {
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
            }
        });
    }
}

// Remove task assignee
function remove_assignee(id, task_id) {
    if (confirm_delete()) {
        requestGetJSON('tasks/remove_assignee/' + id + '/' + task_id).done(function(response) {
            if (response.success == true) {
                alert_float('success', response.message);
                _task_append_html(response.taskHtml);
            }
        });
    }
}

// Remove task follower
function remove_follower(id, task_id) {
    if (confirm_delete()) {
        requestGetJSON('tasks/remove_follower/' + id + '/' + task_id).done(function(response) {
            if (response.success == true) {
                alert_float('success', response.message);
                _task_append_html(response.taskHtml);
            }
        });
    }
}

// Marking task as complete
function mark_complete(task_id) {
    task_mark_as(5, task_id);
}

// Unmarking task as complete
function unmark_complete(task_id) {
    task_mark_as(4, task_id, 'tasks/unmark_complete/' + task_id);
}

// Mark task status
function task_mark_as(status, task_id, url) {
    url = typeof(url) == 'undefined' ? 'tasks/mark_as/' + status + '/' + task_id : url;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    $("body").append('<div class="dt-loader"></div>');
    requestGetJSON(url).done(function(response) {
        $("body").find('.dt-loader').remove();
        if (response.success == true) {
            reload_tasks_tables();
            if (taskModalVisible) { _task_append_html(response.taskHtml); }
            if (status == 5 && typeof(_maybe_remove_task_from_project_milestone) == 'function') {
                _maybe_remove_task_from_project_milestone(task_id);
            }
            if ($('.tasks-kanban').length == 0) { alert_float('success', response.message); }
        }
    });
}

// Change task priority from sigle modal
function task_change_priority(priority_id, task_id) {
    url = 'tasks/change_priority/' + priority_id + '/' + task_id;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    requestGetJSON(url).done(function(response) {
        if (response.success == true) {
            reload_tasks_tables();
            if (taskModalVisible) { _task_append_html(response.taskHtml); }
        }
    });
}

// Change task milestone from single modal
function task_change_milestone(milestone_id, task_id) {
    url = 'tasks/change_milestone/' + milestone_id + '/' + task_id;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    requestGetJSON(url).done(function(response) {
        if (response.success == true) {
            reload_tasks_tables();
            if (taskModalVisible) { _task_append_html(response.taskHtml); }
        }
    });
}

// Non finished timesheet delete, this is available for all staff
function delete_user_unfinished_timesheet(id) {
    if (confirm_delete()) {
        requestGetJSON('tasks/delete_user_unfinished_timesheet/' + id).done(function(response) {
            _init_timers_top_html(JSON.parse(response.timers));
            reload_tasks_tables();
        });
    }
}

// Reload all tasks possible table where the table data needs to be refreshed after an action is performed on task.
function reload_tasks_tables() {
    var av_tasks_tables = ['.table-tasks', '.table-rel-tasks', '.table-rel-tasks-leads', '.table-timesheets'];
    $.each(av_tasks_tables, function(i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}

// Makes task public with AJAX request
function make_task_public(task_id) {
    requestGetJSON('tasks/make_public/' + task_id).done(function(response) {
        if (response.success == true) {
            reload_tasks_tables();
            _task_append_html(response.taskHtml);
        }
    });
}

// New task function, various actions performed
function new_task(url) {
    url = typeof(url) != 'undefined' ? url : admin_url + 'tasks/task';

    var $leadModal = $('#lead-modal');
    if ($leadModal.is(':visible')) {
        url += '&opened_from_lead_id=' + $leadModal.find('input[name="leadid"]').val();
        if (url.indexOf('?') === -1) { url = url.replace('&', '?'); }
        $leadModal.modal('hide');
    }

    var $taskSingleModal = $('#task-modal');
    if ($taskSingleModal.is(':visible')) { $taskSingleModal.modal('hide'); }

    var $taskEditModal = $('#_task_modal');
    if ($taskEditModal.is(':visible')) { $taskEditModal.modal('hide'); }

    requestGet(url).done(function(response) {
        $('#_task').html(response);
        $("body").find('#_task_modal').modal({ show: true, backdrop: 'static' });
    });
}

// Show/hide tags placeholder
function showHideTagsPlaceholder($tagit) {
    var $input = $tagit.data("ui-tagit").tagInput,
        placeholderText = $tagit.data("ui-tagit").options.placeholderText;
    $tagit.tagit("assignedTags").length > 0 ? $input.removeAttr('placeholder') : $input.attr('placeholder', placeholderText);
}

// Create new task directly from relation, related options selected after modal is shown
function new_task_from_relation(table, rel_type, rel_id) {
    if (typeof(rel_type) == 'undefined' && typeof(rel_id) == 'undefined') {
        rel_id = $(table).data('new-rel-id');
        rel_type = $(table).data('new-rel-type');
    }
    var url = admin_url + 'tasks/task?rel_id=' + rel_id + '&rel_type=' + rel_type;
    new_task(url);
}

// Go to edit view
function edit_task(task_id) {
    requestGet('tasks/task/' + task_id).done(function(response) {
        $('#_task').html(response)
        $('#task-modal').modal('hide');
        $("body").find('#_task_modal').modal({ show: true, backdrop: 'static' });
    });
}

// Handles task add/edit form modal.
function task_form_handler(form) {
    tinymce.triggerSave();
    $('#_task_modal').find('input[name="startdate"]').prop('disabled', false);

    var formURL = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: $(form).attr('method'),
        data: formData,
        mimeType: $(form).attr('enctype'),
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success == true) { alert_float('success', response.message); }
        if (!$("body").hasClass('project')) {
            $('#_task_modal').attr('data-task-created', true);
            $('#_task_modal').modal('hide');
            init_task_modal(response.id);
            reload_tasks_tables();
        } else {
            // reload page on project area
            var location = window.location.href;
            var params = [];
            location = location.split('?');
            var group = get_url_param('group');
            var excludeCompletedTasks = get_url_param('exclude_completed');
            if (group) { params['group'] = group; }
            if (excludeCompletedTasks) { params['exclude_completed'] = excludeCompletedTasks; }
            params['taskid'] = response.id;
            window.location.href = buildUrl(location[0], params)
        }
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.responseText));
    });

    return false;
}

// Full screen white popup
function system_popup(data) {

    data.content = typeof(data.content) == 'undefined' ? '' : data.content;

    var $popupHTML = $('<div/>', {
        id: 'system-popup',
        class: 'system-popup',
    }).appendTo('body');

    var overlayHTML = '';
    overlayHTML += '<div class="popup-wrapper fadeIn animated">';
    overlayHTML += '<h2 class="popup-message">';
    overlayHTML += data.message;
    overlayHTML += '</h2>';
    overlayHTML += '<div class="popup-content">';
    overlayHTML += data.content;
    overlayHTML += '<button type="button" class="system-popup-close"> </button>';
    overlayHTML += '</div>';
    overlayHTML += '</div>';

    $popupHTML.html(overlayHTML).removeClass('hide');
    $('body').addClass('system-popup');
    $popupHTML.find('.system-popup-close').on('click', function() {
        var that = this;
        requestGet('misc/clear_system_popup').done(function(response) {
            setTimeout(function() {
                $('body').removeClass('system-popup');
                $popupHTML.fadeOut(400, function() {
                    $popupHTML.remove();
                });
                $(that).off('click');
            }, 50);
        });
    });

    return $popupHTML;

}

// Action for task timer start/stop
function timer_action(e, task_id, timer_id) {

    timer_id = typeof(timer_id) == 'undefined' ? '' : timer_id;

    var $timerSelectTask = $('#timer-select-task');
    if (task_id == '' && $timerSelectTask.is(':visible')) {
        return;
    }
    if (timer_id != '' && task_id == '0') {
        var popupData = {};
        popupData.content = '';
        popupData.content += '<div class="row">';
        popupData.content += '<div class="form-group"><select id="timer_add_task_id" data-empty-title="' + appLang.search_tasks + '" data-width="60%" class="ajax-search" data-live-search="true">';
        popupData.content += '</select></div>';
        popupData.content += '<div class="form-group">';
        popupData.content += '<textarea id="timesheet_note" placeholder="' + appLang.note + '" style="margin:0 auto;width:60%;" rows="4" class="form-control"></textarea>'
        popupData.content += '</div>';
        popupData.content += '<button type=\'button\' onclick=\'timer_action(this,document.getElementById("timer_add_task_id").value,' + timer_id + ');return false;\' class=\'btn btn-info\'>' + appLang.confirm + '</button>';

        popupData.message = appLang.task_stop_timer;
        var $popupHTML = system_popup(popupData);
        $popupHTML.attr('id', 'timer-select-task');
        init_ajax_search('tasks', '#timer_add_task_id', undefined, admin_url + 'tasks/ajax_search_assign_task_to_timer');
        return false;
    }

    $(e).addClass('disabled');

    var data = {};
    data.task_id = task_id;
    data.timer_id = timer_id;
    data.note = $("body").find('#timesheet_note').val();
    if (!data.note) { data.note = ''; }
    var taskModalVisible = $('#task-modal').is(':visible');
    $.post(admin_url + 'tasks/timer_tracking?single_task=' + taskModalVisible, data).done(function(response) {
        response = JSON.parse(response);
        if (taskModalVisible) { _task_append_html(response.taskHtml); }
        if ($timerSelectTask.is(':visible')) {
            $timerSelectTask.find('.system-popup-close').click();
        }
        _init_timers_top_html(JSON.parse(response.timers));
        $('.popover-top-timer-note').popover('hide')
        reload_tasks_tables();
    });
}

// Init task modal and get data from server
function init_task_modal(task_id) {

    var queryStr = '';
    var $leadModal = $('#lead-modal');
    var $taskAddEditModal = $('#_task_modal');
    if ($leadModal.is(':visible')) {
        queryStr += '?opened_from_lead_id=' + $leadModal.find('input[name="leadid"]').val();
        $leadModal.modal('hide');
    } else if ($taskAddEditModal.attr('data-lead-id') != undefined) {
        queryStr += '?opened_from_lead_id=' + $taskAddEditModal.attr('data-lead-id');
    }

    requestGet('tasks/get_task_data/' + task_id + queryStr).done(function(response) {
        _task_append_html(response);
    }).fail(function(data) {
        $('#task-modal').modal('hide');
        alert_float('danger', data.responseText);
    });
}

// General function to append task html returned from request
function _task_append_html(html) {
    $('#task-modal .data').html(html);
    //init_tasks_checklist_items(false, task_id);
    recalculate_checklist_items_progress();
    do_task_checklist_items_height();
    setTimeout(function() {
        $('#task-modal').modal('show');
        // Init_tags_input is trigged too when task modal is shown
        // This line prevents triggering twice.
        if ($('#task-modal').is(':visible')) { init_tags_inputs(); }
        fix_task_modal_left_col_height();
    }, 150);
}

// Tracking stats modal from task single
function task_tracking_stats(id) {
    requestGet('tasks/task_tracking_stats/' + id).done(function(response) {
        $('<div/>', { id: 'tracking-stats' }).appendTo('body').html(response);
        $('#task-tracking-stats-modal').modal('toggle');
    });
}

// Fetches all staff timers and append to DOM
function init_timers() {
    requestGetJSON('tasks/get_staff_started_timers').done(function(response) {
        _init_timers_top_html(response);
    });
}

// Top started timers dropdown init html data with class
function _init_timers_top_html(data) {
    var $tt = $('#top-timers');
    var $ttIcon = $('#top-timers').find('.icon-started-timers');
    data.total_timers > 0 ? $ttIcon.removeClass('hide').html(data.total_timers) : $ttIcon.addClass('hide');
    $('#started-timers-top').html(data.html);
}

// Init task edit comment
function edit_task_comment(id) {
    var edit_wrapper = $('[data-edit-comment="' + id + '"]');
    edit_wrapper.next().addClass('hide');
    edit_wrapper.removeClass('hide');
    tinymce.remove('#task_comment_' + id);
    init_editor('#task_comment_' + id, _simple_editor_config());
    tinymce.triggerSave();
}

function _simple_editor_config() {
    return {
        height: 150,
        auto_focus: true,
        menubar: false,
        plugins: [
            'table advlist codesample autosave autoresize lists link image textcolor media contextmenu paste',
        ],
        toolbar: 'insert formatselect bold forecolor backcolor' + (is_mobile() ? ' | ' : ' ') + 'alignleft aligncenter alignright bullist numlist | restoredraft',
        insert_button_items: 'image media link codesample',
        toolbar1: ''
    };
}

// Cancel editing commment after clicked on edit href
function cancel_edit_comment(id) {
    var edit_wrapper = $('[data-edit-comment="' + id + '"]');
    tinymce.remove('[data-edit-comment="' + id + '"] textarea');
    edit_wrapper.addClass('hide');
    edit_wrapper.next().removeClass('hide');
}

// Save task edited comment
function save_edited_comment(id, task_id) {
    tinymce.triggerSave();
    var data = {};
    data.id = id;
    data.task_id = task_id;
    data.content = $('[data-edit-comment="' + id + '"]').find('textarea').val();
    $.post(admin_url + 'tasks/edit_comment', data).done(function(response) {
        response = JSON.parse(response);
        if (response.success == true) {
            alert_float('success', response.message);
            _task_append_html(response.taskHtml);
        } else {
            cancel_edit_comment(id);
        }
        tinymce.remove('[data-edit-comment="' + id + '"] textarea');
    });
}

// Fix task single modal height to be on both sides the same
function fix_task_modal_left_col_height() {
    if (!is_mobile()) {
        $("body").find('.task-single-col-left').css('min-height', $("body").find('.task-single-col-right').outerHeight(true) + 'px');
    }
}

// Updates task when action performed form kan ban area eq status changed.
function tasks_kanban_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var status = $(ui.item.parent()[0]).data('task-status-id');
        var tasks = $(ui.item.parent()[0]).find('[data-task-id]');

        var data = {};
        data.order = [];
        var i = 0;
        $.each(tasks, function() {
            data.order.push([$(this).data('task-id'), i]);
            i++;
        });

        task_mark_as(status, $(ui.item).data('task-id'));
        check_kanban_empty_col('[data-task-id]');
        setTimeout(function() {
            $.post(admin_url + 'tasks/update_order', data);
        }, 200);
    }
}

// Init tasks kan ban
function tasks_kanban() {
    init_kanban('tasks/kanban', tasks_kanban_update, '.tasks-status', 265, 360);
}

// Task single edit description with inline editor, used from task single modal
function edit_task_inline_description(e, id) {

    tinyMCE.remove('#task_view_description');

    if ($(e).hasClass('editor-initiated')) {
        $(e).removeClass('editor-initiated');
        return;
    }

    $(e).addClass('editor-initiated');
    $.Shortcuts.stop();
    tinymce.init({
        selector: '#task_view_description',
        theme: 'inlite',
        skin: 'perfex',
        auto_focus: "task_view_description",
        plugins: 'table link paste contextmenu textpattern',
        insert_toolbar: 'quicktable',
        selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
        inline: true,
        table_default_styles: {
            width: '100%'
        },
        setup: function(editor) {
            editor.on('blur', function(e) {
                if (editor.isDirty()) {
                    $.post(admin_url + 'tasks/update_task_description/' + id, {
                        description: editor.getContent()
                    });
                }
                setTimeout(function() {
                    editor.remove();
                    $.Shortcuts.start();
                }, 500);
            });
        }
    });
}

// Tasks bulk actions action
function tasks_bulk_action(event) {
    if (confirm_delete()) {
        var ids = [],
            data = {},
            mass_delete = $('#mass_delete').prop('checked');
        if (mass_delete == false || typeof(mass_delete) == 'undefined') {
            data.status = $('#move_to_status_tasks_bulk_action').val();

            var assignees = $('#task_bulk_assignees');
            data.assignees = assignees.length ? assignees.selectpicker('val') : '';

            var tags_bulk = $('#tags_bulk');
            data.tags = tags_bulk.length ? tags_bulk.tagit('assignedTags') : '';

            var milestone = $('#task_bulk_milestone');
            data.milestone = milestone.length ? milestone.selectpicker('val') : '';

            data.priority = $('#task_bulk_priority').val();
            data.priority = typeof(data.priority) == 'undefined' ? '' : data.priority;

            if (data.status == '' && data.priority == '' && data.tags == '' && data.assignees == '' && data.milestone == '') {
                return;
            }
        } else {
            data.mass_delete = true;
        }
        var rows = $($('#tasks_bulk_actions').attr('data-table')).find('tbody tr');
        $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') == true) {
                ids.push(checkbox.val());
            }
        });
        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function() {
            $.post(admin_url + 'tasks/bulk_action', data).done(function() {
                window.location.reload();
            });
        }, 200);
    }
}

function load_small_table_item(id, selector, input_name, url, table) {
    var _tmpID = $('input[name="' + input_name + '"]').val();
    // Check if id passed from url, hash is prioritized becuase is last
    if (_tmpID != '' && !window.location.hash) {
        id = _tmpID;
        // Clear the current id value in case user click on the left sidebar credit_note_ids
        $('input[name="' + input_name + '"]').val('');
    } else {
        // check first if hash exists and not id is passed, becuase id is prioritized
        if (window.location.hash && !id) {
            id = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
        }
    }
    if (typeof(id) == 'undefined' || id == '') { return; }
    if (!$("body").hasClass('small-table')) { toggle_small_view(table, selector); }
    $('input[name="' + input_name + '"]').val(id);
    do_hash_helper(id);
    $(selector).load(admin_url + url + '/' + id);
    if (is_mobile()) {
        $('html, body').animate({
            scrollTop: $(selector).offset().top + 150
        }, 600);
    }
}

// Init single invoice
function init_invoice(id) {
    load_small_table_item(id, '#invoice', 'invoiceid', 'invoices/get_invoice_data_ajax', '.table-invoices');
}

// Init single credit note
function init_credit_note(id) {
    load_small_table_item(id, '#credit_note', 'credit_note_id', 'credit_notes/get_credit_note_data_ajax', '.table-credit-notes');
}

// Init single estimate
function init_estimate(id) {
    load_small_table_item(id, '#estimate', 'estimateid', 'estimates/get_estimate_data_ajax', '.table-estimates');
}

// Init single proposal
function init_proposal(id) {
    load_small_table_item(id, '#proposal', 'proposal_id', 'proposals/get_proposal_data_ajax', '.table-proposals');
}

function init_expense(id) {
    load_small_table_item(id, '#expense', 'expenseid', 'expenses/get_expense_data_ajax', '.table-expenses');
}

// Clear billing and shipping inputs for invoice,estimate etc...
function clear_billing_and_shipping_details() {
    for (var f in bs_fields) {
        if (bs_fields[f].indexOf('country') > -1) {
            $('select[name="' + bs_fields[f] + '"]').selectpicker('val', '');
        } else {
            $('input[name="' + bs_fields[f] + '"]').val('');
            $('textarea[name="' + bs_fields[f] + '"]').val('');
        }
        if (bs_fields[f] == 'billing_country') {
            $('input[name="include_shipping"]').prop("checked", false);
            $('input[name="include_shipping"]').change();
        }
    }

    init_billing_and_shipping_details();
}

// Init billing and shipping details for invoice, estimate etc...
function init_billing_and_shipping_details() {
    var _f;
    var include_shipping = $('input[name="include_shipping"]').prop('checked');
    for (var f in bs_fields) {
        _f = '';
        if (bs_fields[f].indexOf('country') > -1) {
            _f = $("#" + bs_fields[f] + " option:selected").data('subtext');
        } else if (bs_fields[f].indexOf('shipping_street') > -1 || bs_fields[f].indexOf('billing_street') > -1) {
            _f = $('textarea[name="' + bs_fields[f] + '"]').val().replace(/(?:\r\n|\r|\n)/g, "<br />");
        } else {
            _f = $('input[name="' + bs_fields[f] + '"]').val();
        }
        if (bs_fields[f].indexOf('shipping') > -1) {
            if (!include_shipping) {
                _f = '';
            }
        }
        if (typeof(_f) == 'undefined') {
            _f = '';
        }
        _f = (_f != '' ? _f : '--');
        $('.' + bs_fields[f]).html(_f);
    }
    $('#billing_and_shipping_details').modal('hide');
}

// Record payment function
function record_payment(id) {
    if (typeof(id) == 'undefined' || id == '') { return; }
    $('#invoice').load(admin_url + 'invoices/record_invoice_payment_ajax/' + id);
}

// Add item to preview
function add_item_to_preview(id) {
    requestGetJSON('invoice_items/get_item_by_id/' + id).done(function(response) {
        clear_item_preview_values();

        $('.main textarea[name="description"]').val(response.description);
        $('.main textarea[name="long_description"]').val(response.long_description.replace(/(<|&lt;)br\s*\/*(>|&gt;)/g, " "));

        _set_item_preview_custom_fields_array(response.custom_fields);

        $('.main input[name="quantity"]').val(1);

        var taxSelectedArray = [];
        if (response.taxname && response.taxrate) {
            taxSelectedArray.push(response.taxname + '|' + response.taxrate);
        }
        if (response.taxname_2 && response.taxrate_2) {
            taxSelectedArray.push(response.taxname_2 + '|' + response.taxrate_2);
        }

        $('.main select.tax').selectpicker('val', taxSelectedArray);
        $('.main input[name="unit"]').val(response.unit);

        var $currency = $("body").find('.accounting-template select[name="currency"]');
        var baseCurency = $currency.attr('data-base');
        var selectedCurrency = $currency.find('option:selected').val();
        var $rateInputPreview = $('.main input[name="rate"]');

        if (baseCurency == selectedCurrency) {
            $rateInputPreview.val(response.rate);
        } else {
            var itemCurrencyRate = response['rate_currency_' + selectedCurrency];
            if (!itemCurrencyRate || parseFloat(itemCurrencyRate) === 0) {
                $rateInputPreview.val(response.rate);
            } else {
                $rateInputPreview.val(itemCurrencyRate);
            }
        }

        $(document).trigger({
            type: "item-added-to-preview",
            item: response,
            item_type: 'item',
        });
    });
}

function _set_item_preview_custom_fields_array(custom_fields) {

    var cf_act_as_inputs = ['input', 'number', 'date_picker', 'date_picker_time', 'colorpicker'];

    for (var i = 0; i < custom_fields.length; i++) {
        var cf = custom_fields[i];
        if ($.inArray(cf.type, cf_act_as_inputs) > -1) {
            var f = $('tr.main td[data-id="' + cf.id + '"] input');
            // trigger change eq. for colorpicker
            f.val(cf.value).trigger('change');
        } else if (cf.type == 'textarea') {
            $('tr.main td[data-id="' + cf.id + '"] textarea').val(cf.value);
        } else if (cf.type == 'select' || cf.type == 'multiselect') {
            if (!empty(cf.value)) {
                var selected = cf.value.split(',');
                selected = selected.map(function(e) {
                    return e.trim();
                });
                $('tr.main td[data-id="' + cf.id + '"] select').selectpicker('val', selected);
            }
        } else if (cf.type == 'checkbox') {
            if (!empty(cf.value)) {
                var selected = cf.value.split(',');
                selected = selected.map(function(e) {
                    return e.trim();
                });
                $.each(selected, function(i, e) {
                    $('tr.main td[data-id="' + cf.id + '"] input[type="checkbox"][value="' + e + '"]').prop('checked', true);
                });
            }
        }
    }
}
// Add task to preview
function add_task_to_preview_as_item(id) {
    requestGetJSON('tasks/get_billable_task_data/' + id).done(function(response) {
        response.taxname = $('select.main-tax').selectpicker('val');
        var previewArea = $('.main');
        previewArea.find('textarea[name="description"]').val(response.name);
        previewArea.find('textarea[name="long_description"]').val(response.description);
        previewArea.find('input[name="quantity"]').val(response.total_hours);
        previewArea.find('input[name="rate"]').val(response.hourly_rate);
        previewArea.find('input[name="unit"]').val('');
        $('input[name="task_id"]').val(id);
        $(document).trigger({
            type: "item-added-to-preview",
            item: response,
            item_type: 'task',
        });
    });
}

// Clear the items added to preview
function clear_item_preview_values(default_taxes) {

    // Get the last taxes applied to be available for the next item
    var last_taxes_applied = $('table.items tbody').find('tr:last-child').find('select').selectpicker('val');
    var previewArea = $('.main');

    previewArea.find('textarea').val(''); // includes cf
    previewArea.find('td.custom_field input[type="checkbox"]').prop('checked', false); // cf
    previewArea.find('td.custom_field input:not(:checkbox):not(:hidden)').val(''); // cf // not hidden for chkbox hidden helpers
    previewArea.find('td.custom_field select').selectpicker('val', ''); // cf
    previewArea.find('input[name="quantity"]').val(1);
    previewArea.find('select.tax').selectpicker('val', last_taxes_applied);
    previewArea.find('input[name="rate"]').val('');
    previewArea.find('input[name="unit"]').val('');

    $('input[name="task_id"]').val('');
    $('input[name="expense_id"]').val('');
}

// Append the added items to the preview to the table as items
function add_item_to_table(data, itemid, merge_invoice, bill_expense) {

    // If not custom data passed get from the preview
    data = typeof(data) == 'undefined' || data == 'undefined' ? get_item_preview_values() : data;
    if (data.description === "" && data.long_description === "" && data.rate === "") { return; }
    var table_row = '';
    var unit_placeholder = '';
    var item_key = $("body").find('tbody .item').length + 1;

    table_row += '<tr class="sortable item" data-merge-invoice="' + merge_invoice + '" data-bill-expense="' + bill_expense + '">';

    table_row += '<td class="dragger">';

    // Check if quantity is number
    if (isNaN(data.qty)) {
        data.qty = 1;
    }

    // Check if rate is number
    if (data.rate == '' || isNaN(data.rate)) {
        data.rate = 0;
    }

    var amount = data.rate * data.qty;
    amount = accounting.formatNumber(amount);
    var tax_name = 'newitems[' + item_key + '][taxname][]';
    $("body").append('<div class="dt-loader"></div>');
    var regex = /<br[^>]*>/gi;
    get_taxes_dropdown_template(tax_name, data.taxname).done(function(tax_dropdown) {

        // order input
        table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

        table_row += '</td>';

        table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

        table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';

        var custom_fields = $('tr.main td.custom_field');
        var cf_has_required = false;

        if (custom_fields.length > 0) {

            $.each(custom_fields, function() {

                var cf = $(this).clone();
                var cf_html = '';
                var cf_field = $(this).find('[data-fieldid]');
                var cf_name = 'newitems[' + item_key + '][custom_fields][items][' + cf_field.attr('data-fieldid') + ']';

                if (cf_field.is(':checkbox')) {

                    var checked = $(this).find('input[type="checkbox"]:checked');
                    var checkboxes = cf.find('input[type="checkbox"]');

                    $.each(checkboxes, function(i, e) {
                        var random_key = Math.random().toString(20).slice(2);
                        $(this).attr('id', random_key)
                            .attr('name', cf_name)
                            .next('label').attr('for', random_key);
                        if ($(this).attr('data-custom-field-required') == '1') {
                            cf_has_required = true;
                        }
                    });

                    $.each(checked, function(i, e) {
                        cf.find('input[value="' + $(e).val() + '"]')
                            .attr('checked', true);
                    });

                    cf_html = cf.html();

                } else if (cf_field.is('input') || cf_field.is('textarea')) {
                    if (cf_field.is('input')) {
                        cf.find('[data-fieldid]').attr('value', cf_field.val());
                    } else {
                        cf.find('[data-fieldid]').html(cf_field.val());
                    }
                    cf.find('[data-fieldid]').attr('name', cf_name);
                    if (cf.find('[data-fieldid]').attr('data-custom-field-required') == '1') {
                        cf_has_required = true;
                    }
                    cf_html = cf.html();
                } else if (cf_field.is('select')) {

                    if ($(this).attr('data-custom-field-required') == '1') {
                        cf_has_required = true;
                    }

                    var selected = $(this).find('select[data-fieldid]').selectpicker('val');
                    selected = typeof(selected != 'array') ? new Array(selected) : selected;

                    // Check if is multidimensional by multi-select customfield
                    selected = selected[0].constructor === Array ? selected[0] : selected;

                    var selectNow = cf.find('select');
                    var $wrapper = $('<div/>');
                    selectNow.attr('name', cf_name);

                    var $select = selectNow.clone();
                    $wrapper.append($select);
                    $.each(selected, function(i, e) {
                        $wrapper.find('select option[value="' + e + '"]').attr('selected', true);
                    });

                    cf_html = $wrapper.html();
                }
                table_row += '<td class="custom_field">' + cf_html + '</td>';
            });
        }

        table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

        unit_placeholder = '';

        if (!data.unit || typeof(data.unit) == 'undefined') {
            unit_placeholder = appLang.unit;
            data.unit = '';
        }

        table_row += '<input type="text" placeholder="' + unit_placeholder + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';

        table_row += '</td>';

        table_row += '<td class="rate"><input type="number" data-toggle="tooltip" title="' + appLang.item_field_not_formatted + '" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + item_key + '][rate]" value="' + data.rate + '" class="form-control"></td>';

        table_row += '<td class="taxrate">' + tax_dropdown + '</td>';

        table_row += '<td class="amount" align="right">' + amount + '</td>';

        table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

        table_row += '</tr>';

        $('table.items tbody').append(table_row);

        $(document).trigger({
            type: "item-added-to-table",
            data: data,
            row: table_row
        });

        setTimeout(function() {
            calculate_total();
        }, 15);

        var billed_task = $('input[name="task_id"]').val();
        var billed_expense = $('input[name="expense_id"]').val();

        if (billed_task != '' && typeof(billed_task) != 'undefined') {
            billed_tasks = billed_task.split(',');
            $.each(billed_tasks, function(i, obj) {
                $('#billed-tasks').append(hidden_input('billed_tasks[' + item_key + '][]', obj));
            });
        }

        if (billed_expense != '' && typeof(billed_expense) != 'undefined') {
            billed_expenses = billed_expense.split(',');
            $.each(billed_expenses, function(i, obj) {
                $('#billed-expenses').append(hidden_input('billed_expenses[' + item_key + '][]', obj));
            });
        }

        init_selectpicker();
        init_datepicker();
        init_color_pickers();
        clear_item_preview_values();
        reorder_items();

        $('body').find('#items-warning').remove();
        $("body").find('.dt-loader').remove();
        $('#item_select').selectpicker('val', '');

        if (cf_has_required == true && $('.invoice-form').length) {
            validate_invoice_form();
        } else if (cf_has_required && $('.estimate-form').length) {
            validate_estimate_form();
        } else if (cf_has_required && $('.proposal-form').length) {
            validate_proposal_form();
        } else if (cf_has_required && $('.credit-note-form').length) {
            validate_credit_note_form();
        }

        return true;

    });

    return false;
}

// Get taxes dropdown selectpicker template / Causing problems with ajax becuase is fetching from server
function get_taxes_dropdown_template(name, taxname) {
    jQuery.ajaxSetup({ async: false });
    var d = $.post(admin_url + 'misc/get_taxes_dropdown_template/', { name: name, taxname: taxname });
    jQuery.ajaxSetup({ async: true });

    return d;
}

// Custom function for deselecting selected value from ajax dropdown
function deselect_ajax_search(e) {
    var $elm = $('select#' + $(e).attr('data-id'));
    $elm.data('AjaxBootstrapSelect').list.cache = {}
    var $elmWrapper = $elm.parents('.bootstrap-select');
    $elm.html('').append('<option value=""></option>').selectpicker('val', '')
    $elmWrapper.removeClass('ajax-remove-values-option').find('.ajax-clear-values').remove();
    setTimeout(function() {
        $elm.trigger('selected.cleared.ajax.bootstrap.select', e);
        $elm.trigger('change').data('AjaxBootstrapSelect').list.cache = {}
    }, 50);
}

// Ajax project search but only for specific customer
function init_ajax_project_search_by_customer_id(selector) {
    selector = typeof(selector) == 'undefined' ? '#project_id.ajax-search' : selector;
    init_ajax_search('project', selector, {
        customer_id: function() {
            return $('select[name="clientid"]').val();
        }
    });
}

// Ajax project search select
function init_ajax_projects_search(selector) {
    selector = typeof(selector) == 'undefined' ? '#project_id.ajax-search' : selector;
    init_ajax_search('project', selector);
}

// Fix for reordering the items the tables to show the full width
function fixHelperTableHelperSortable(e, ui) {
    ui.children().each(function() {
        $(this).width($(this).width());
    });
    return ui;
}

// Make items sortable with jquery sort plugin
function init_items_sortable(preview_table) {
    var _items_sortable = $("body").find('.items tbody');
    if (_items_sortable.length == 0) { return; }
    _items_sortable.sortable({
        helper: fixHelperTableHelperSortable,
        handle: '.dragger',
        placeholder: 'ui-placeholder',
        itemPath: '> tbody',
        itemSelector: 'tr.sortable',
        items: "tr.sortable",
        update: function() {
            if (typeof(preview_table) == 'undefined') {
                reorder_items();
            } else {
                // If passed from the admin preview there is other function for re-ordering
                save_ei_items_order();
            }
        },
        sort: function(event, ui) {
            // Firefox fixer when dragging
            var $target = $(event.target);
            if (!/html|body/i.test($target.offsetParent()[0].tagName)) {
                var top = event.pageY - $target.offsetParent().offset().top - (ui.helper.outerHeight(true) / 2);
                ui.helper.css({
                    'top': top + 'px'
                });
            }
        }
    });
}

// Save the items from order from the admin preview
function save_ei_items_order() {
    var rows = $('.table.invoice-items-preview.items tbody tr,.table.estimate-items-preview.items tbody tr,.table.credit-note-items-preview.items tbody tr');
    var i = 1,
        order = [],
        _order_id, type, item_id;
    if ($('.table.items').hasClass('invoice-items-preview')) {
        type = 'invoice';
    } else if ($('.table.items').hasClass('estimate-items-preview')) {
        type = 'estimate';
    } else if ($('.table.items').hasClass('credit-note-items-preview')) {
        type = 'credit_note';
    } else {
        return false;
    }

    $.each(rows, function() {
        order.push([$(this).data('item-id'), i]);
        // update item number when reordering
        $(this).find('td.item_no').html(i);
        i++;
    });

    setTimeout(function() {
        $.post(admin_url + 'misc/update_ei_items_order/' + type, {
            data: order
        });
    }, 200);
}

// Reoder the items in table edit for estimate and invoices
function reorder_items() {
    var rows = $('.table.table-main-invoice-edit tbody tr.item,.table.table-main-estimate-edit tbody tr.item, .table.table-main-credit-note-edit tbody tr.item');
    var i = 1;
    $.each(rows, function() {
        $(this).find('input.order').val(i);
        i++;
    });
}

// Get the preview main values
function get_item_preview_values() {
    var response = {};
    response.description = $('.main textarea[name="description"]').val();
    response.long_description = $('.main textarea[name="long_description"]').val();
    response.qty = $('.main input[name="quantity"]').val();
    response.taxname = $('.main select.tax').selectpicker('val');
    response.rate = $('.main input[name="rate"]').val();
    response.unit = $('.main input[name="unit"]').val();
    return response;
}

// Calculate invoice total - NOT RECOMENDING EDIT THIS FUNCTION BECUASE IS VERY SENSITIVE
function calculate_total() {

    var calculated_tax,
        taxrate,
        item_taxes,
        row,
        _amount,
        _tax_name,
        taxes = {},
        taxes_rows = [],
        subtotal = 0,
        total = 0,
        quantity = 1,
        total_discount_calculated = 0,
        rows = $('.table.table-main-invoice-edit tbody tr.item,.table.table-main-estimate-edit tbody tr.item,.table.table-main-credit-note-edit tbody tr.item'),
        discount_area = $('#discount_area'),
        adjustment = $('input[name="adjustment"]').val(),
        discount_percent = $('input[name="discount_percent"]').val(),
        discount_fixed = $('input[name="discount_total"]').val(),
        discount_total_type = $('.discount-total-type.selected'),
        discount_type = $('select[name="discount_type"]').val();

    $('.tax-area').remove();

    $.each(rows, function() {
        quantity = $(this).find('[data-quantity]').val();
        if (quantity == '') {
            quantity = 1;
            $(this).find('[data-quantity]').val(1);
        }

        _amount = accounting.toFixed($(this).find('td.rate input').val() * quantity, app_decimal_places);
        _amount = parseFloat(_amount);

        $(this).find('td.amount').html(accounting.formatNumber(_amount));
        subtotal += _amount;
        row = $(this);
        item_taxes = $(this).find('select.tax').selectpicker('val');

        if (item_taxes) {
            $.each(item_taxes, function(i, taxname) {
                taxrate = row.find('select.tax [value="' + taxname + '"]').data('taxrate');
                calculated_tax = (_amount / 100 * taxrate);
                if (!taxes.hasOwnProperty(taxname)) {
                    if (taxrate != 0) {
                        _tax_name = taxname.split('|');
                        tax_row = '<tr class="tax-area"><td>' + _tax_name[0] + '(' + taxrate + '%)</td><td id="tax_id_' + slugify(taxname) + '"></td></tr>';
                        $(discount_area).after(tax_row);
                        taxes[taxname] = calculated_tax;
                    }
                } else {
                    // Increment total from this tax
                    taxes[taxname] = taxes[taxname] += calculated_tax;
                }
            });
        }
    });

    // Discount by percent
    if ((discount_percent != '' && discount_percent != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-percent')) {
        total_discount_calculated = (subtotal * discount_percent) / 100;
    } else if ((discount_fixed != '' && discount_fixed != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-fixed')) {
        total_discount_calculated = discount_fixed;
    }

    $.each(taxes, function(taxname, total_tax) {
        if ((discount_percent != '' && discount_percent != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-percent')) {
            total_tax_calculated = (total_tax * discount_percent) / 100;
            total_tax = (total_tax - total_tax_calculated);
        } else if ((discount_fixed != '' && discount_fixed != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-fixed')) {
            var t = (discount_fixed / subtotal) * 100;
            total_tax = (total_tax - (total_tax * t) / 100);
        }

        total += total_tax;
        total_tax = accounting.formatNumber(total_tax)
        $('#tax_id_' + slugify(taxname)).html(total_tax);
    });

    total = (total + subtotal);

    // Discount by percent
    if ((discount_percent != '' && discount_percent != 0) && discount_type == 'after_tax' && discount_total_type.hasClass('discount-type-percent')) {
        total_discount_calculated = (total * discount_percent) / 100;
    } else if ((discount_fixed != '' && discount_fixed != 0) && discount_type == 'after_tax' && discount_total_type.hasClass('discount-type-fixed')) {
        total_discount_calculated = discount_fixed;
    }

    total = total - total_discount_calculated;
    adjustment = parseFloat(adjustment);

    // Check if adjustment not empty
    if (!isNaN(adjustment)) {
        total = total + adjustment;
    }

    var discount_html = '-' + accounting.formatNumber(total_discount_calculated);
    $('input[name="discount_total"]').val(accounting.toFixed(total_discount_calculated, app_decimal_places));

    // Append, format to html and display
    $('.discount-total').html(discount_html);
    $('.adjustment').html(accounting.formatNumber(adjustment));
    $('.subtotal').html(accounting.formatNumber(subtotal) + hidden_input('subtotal', accounting.toFixed(subtotal, app_decimal_places)));
    $('.total').html(format_money(total) + hidden_input('total', accounting.toFixed(total, app_decimal_places)));
    $(document).trigger('sales-total-calculated');
}

// Deletes invoice items
function delete_item(row, itemid) {
    $(row).parents('tr').addClass('animated fadeOut', function() {
        setTimeout(function() {
            $(row).parents('tr').remove();
            calculate_total();
        }, 50)
    });
    // If is edit we need to add to input removed_items to track activity
    if ($('input[name="isedit"]').length > 0) {
        $('#removed-items').append(hidden_input('removed_items[]', itemid));
    }
}

// Format money functions
function format_money(total) {
    if (app_currency_placement === 'after') {
        return accounting.formatMoney(total, {
            format: "%v %s"
        });
    } else {
        return accounting.formatMoney(total);
    }
}

// Set the currency symbol for accounting
function init_currency_symbol() {
    var $accountingTemplate = $("body").find('.accounting-template');
    if ($accountingTemplate.length) {
        accounting.settings.currency.symbol = $accountingTemplate.find('select[name="currency"] option:selected')
            .data('subtext');
        calculate_total();
    }
}

// Delete invoice attachment
function delete_invoice_attachment(id) {
    if (confirm_delete()) {
        requestGet('invoices/delete_attachment/' + id).done(function(success) {
            if (success == 1) {
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                init_invoice($("body").find('input[name="_attachment_sale_id"]').val());
            }
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Delete credit note attachment
function delete_credit_note_attachment(id) {
    if (confirm_delete()) {
        requestGet('credit_notes/delete_attachment/' + id).done(function(success) {
            if (success == 1) {
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                init_credit_note($("body").find('input[name="_attachment_sale_id"]').val());
            }
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Delete estimate attachment
function delete_estimate_attachment(id) {
    if (confirm_delete()) {
        requestGet('estimates/delete_attachment/' + id).done(function(success) {
            if (success == 1) {
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").hasClass('estimates-pipeline') ? estimate_pipeline_open(rel_id) : init_estimate(rel_id);
            }
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Delete proposal attachment
function delete_proposal_attachment(id) {
    if (confirm_delete()) {
        requestGet('proposals/delete_attachment/' + id).done(function(success) {
            if (success == 1) {
                var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                $("body").hasClass('proposals-pipeline') ? proposal_pipeline_open(rel_id) : init_proposal(rel_id);
            }
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Invoices quick total stats
function init_invoices_total(manual) {

    if ($('#invoices_total').length == 0) { return; }
    var _inv_total_inline = $('.invoices-total-inline');
    var _inv_total_href_manual = $('.invoices-total');

    if ($("body").hasClass('invoices_total_manual') && typeof(manual) == 'undefined' &&
        !_inv_total_href_manual.hasClass('initialized')) {
        return;
    }

    if (_inv_total_inline.length > 0 && _inv_total_href_manual.hasClass('initialized')) {
        // On the next request won't be inline in case of currency change
        // Used on dashboard
        _inv_total_inline.removeClass('invoices-total-inline');
        return;
    }

    _inv_total_href_manual.addClass('initialized');
    var _years = $("body").find('select[name="invoices_total_years"]').selectpicker('val');
    var years = [];
    $.each(_years, function(i, _y) {
        if (_y != '') { years.push(_y); }
    });

    var currency = $("body").find('select[name="total_currency"]').val();
    var data = {
        currency: currency,
        years: years,
        init_total: true,
    };

    var project_id = $('input[name="project_id"]').val();
    var customer_id = $('.customer_profile input[name="userid"]').val();
    if (typeof(project_id) != 'undefined') {
        data.project_id = project_id;
    } else if (typeof(customer_id) != 'undefined') {
        data.customer_id = customer_id;
    }
    $.post(admin_url + 'invoices/get_invoices_total', data).done(function(response) {
        $('#invoices_total').html(response);
    });
}

// Estimates quick total stats
function init_estimates_total(manual) {

    if ($('#estimates_total').length == 0) { return; }
    var _est_total_href_manual = $('.estimates-total');
    if ($("body").hasClass('estimates_total_manual') && typeof(manual) == 'undefined' &&
        !_est_total_href_manual.hasClass('initialized')) {
        return;
    }
    _est_total_href_manual.addClass('initialized');
    var currency = $("body").find('select[name="total_currency"]').val();
    var _years = $("body").find('select[name="estimates_total_years"]').selectpicker('val');
    var years = [];
    $.each(_years, function(i, _y) {
        if (_y != '') { years.push(_y); }
    });

    var customer_id = '';
    var project_id = '';

    var _customer_id = $('.customer_profile input[name="userid"]').val();
    var _project_id = $('input[name="project_id"]').val();
    if (typeof(_customer_id) != 'undefined') {
        customer_id = _customer_id;
    } else if (typeof(_project_id) != 'undefined') {
        project_id = _project_id;
    }

    $.post(admin_url + 'estimates/get_estimates_total', {
        currency: currency,
        init_total: true,
        years: years,
        customer_id: customer_id,
        project_id: project_id,
    }).done(function(response) {
        $('#estimates_total').html(response);
    });
}

// Expenses quick total stats
function init_expenses_total() {

    if ($('#expenses_total').length == 0) { return; }
    var currency = $("body").find('select[name="expenses_total_currency"]').val();
    var _years = $("body").find('select[name="expenses_total_years"]').selectpicker('val');
    var years = [];
    $.each(_years, function(i, _y) {
        if (_y != '') { years.push(_y); }
    });

    var customer_id = '';
    var _customer_id = $('.customer_profile input[name="userid"]').val();
    if (typeof(customer_id) != 'undefined') { customer_id = _customer_id; }

    var project_id = '';
    var _project_id = $('input[name="project_id"]').val();
    if (typeof(project_id) != 'undefined') { project_id = _project_id; }

    $.post(admin_url + 'expenses/get_expenses_total', {
        currency: currency,
        init_total: true,
        years: years,
        customer_id: customer_id,
        project_id: project_id,
    }).done(function(response) {
        $('#expenses_total').html(response);
    });
}

// Validate invoice add/edit form
function validate_invoice_form(selector) {
    selector = typeof(selector) == 'undefined' ? '#invoice-form' : selector;

    _validate_form($(selector), {
        clientid: 'required',
        date: 'required',
        currency: 'required',
        number: {
            required: true,
        }
    });
    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "invoices/validate_invoice_number",
            type: 'post',
            data: {
                number: function() {
                    return $('input[name="number"]').val();
                },
                isedit: function() {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function() {
                    return $('input[name="number"]').data('original-number');
                },
                date: function() {
                    return $('input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: appLang.invoice_number_exists,
        }
    });
}

function validate_credit_note_form(selector) {
    selector = typeof(selector) == 'undefined' ? '#credit-note-form' : selector;

    _validate_form($(selector), {
        clientid: 'required',
        date: 'required',
        currency: 'required',
        number: {
            required: true,
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "credit_notes/validate_number",
            type: 'post',
            data: {
                number: function() {
                    return $('input[name="number"]').val();
                },
                isedit: function() {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function() {
                    return $('input[name="number"]').data('original-number');
                }
            }
        },
        messages: {
            remote: appLang.credit_note_number_exists,
        }
    });
}

// Validates estimate add/edit form
function validate_estimate_form(selector) {

    selector = typeof(selector) == 'undefined' ? '#estimate-form' : selector;

    _validate_form($(selector), {
        clientid: 'required',
        date: 'required',
        currency: 'required',
        number: {
            required: true
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "estimates/validate_estimate_number",
            type: 'post',
            data: {
                number: function() {
                    return $('input[name="number"]').val();
                },
                isedit: function() {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function() {
                    return $('input[name="number"]').data('original-number');
                },
                date: function() {
                    return $('body').find('.estimate input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: appLang.estimate_number_exists,
        }
    });

}

// Sort estimates in the pipeline view / switching sort type by click
function estimates_pipeline_sort(type) {
    kan_ban_sort(type, estimate_pipeline);
}

// Sort proposals in the pipeline view / switching sort type by click
function proposal_pipeline_sort(type) {
    kan_ban_sort(type, proposals_pipeline);
}

// Init estimates pipeline
function estimate_pipeline() {
    init_kanban('estimates/get_pipeline', estimates_pipeline_update, '.pipeline-status', 347, 360);
}

// Used when estimate is updated from pipeline. eq changed order or moved to another status
function estimates_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {};
        data.estimateid = $(ui.item).data('estimate-id');
        data.status = $(ui.item.parent()[0]).data('status-id');
        var order = [];
        var status = $(ui.item).parents('.pipeline-status').find('li')
        var i = 1;
        $.each(status, function() {
            order.push([$(this).data('estimate-id'), i]);
            i++;
        });
        data.order = order;
        check_kanban_empty_col('[data-estimate-id]');
        $.post(admin_url + 'estimates/update_pipeline', data);
    }
}

// Used when proposal is updated from pipeline. eq changed order or moved to another status
function proposals_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {};
        data.order = [];
        data.proposalid = $(ui.item).data('proposal-id');
        data.status = $(ui.item.parent()[0]).data('status-id');
        var status = $(ui.item).parents('.pipeline-status').find('li')

        var i = 1;
        $.each(status, function() {
            data.order.push([$(this).data('proposal-id'), i]);
            i++;
        });

        check_kanban_empty_col('[data-proposal-id]');
        $.post(admin_url + 'proposals/update_pipeline', data);
    }
}

// Init proposals pipeline
function proposals_pipeline() {
    init_kanban('proposals/get_pipeline', proposals_pipeline_update, '.pipeline-status', 347, 360);
}

// Open single proposal in pipeline
function proposal_pipeline_open(id) {
    if (id == '') { return; }
    requestGet('proposals/pipeline_open/' + id).done(function(response) {
        $('#proposal').html(response);
        if ($('.proposal-pipeline-modal:visible').length == 0) {
            $('.proposal-pipeline-modal').modal({ show: true, backdrop: 'static', keyboard: false });
        } else {
            $('#proposal').find('.modal.proposal-pipeline-modal').addClass('in').css('display', 'block');
        }
    });
}

// Estimate single open in pipeline
function estimate_pipeline_open(id) {
    if (id == '') { return; }
    requestGet('estimates/pipeline_open/' + id).done(function(response) {
        $('#estimate').html(response);
        if ($('.estimate-pipeline:visible').length == 0) {
            $('.estimate-pipeline').modal({ show: true, backdrop: 'static', keyboard: false });
        } else {
            $('#estimate').find('.modal.estimate-pipeline').addClass('in').css('display', 'block');
        }
    });
}

// Delete estimate note
function delete_sales_note(wrapper, id) {
    if (confirm_delete()) {
        requestGetJSON('misc/delete_note/' + id).done(function(response) {
            if (response.success == true) {
                $(wrapper).parents('.sales-note-wrapper').remove();
                var salesNotesWrapper = $('#sales-notes-wrapper')
                var totalNotesNow = salesNotesWrapper.attr('data-total') - 1;
                var notesTotal = $('.notes-total');
                salesNotesWrapper.attr('data-total', totalNotesNow);
                if (totalNotesNow <= 0) {
                    notesTotal.addClass('hide');
                } else {
                    notesTotal.html('<span class="badge">' + totalNotesNow + '</span>');
                }
            }
        });
    }
}

// Get all estimate notes
function get_sales_notes(id, controller) {
    requestGet(controller + '/get_notes/' + id).done(function(response) {
        $('#sales_notes_area').html(response);
        var totalNotesNow = $('#sales-notes-wrapper').attr('data-total');
        if (totalNotesNow > 0) {
            $('.notes-total').html('<span class="badge">' + totalNotesNow + '</span>').removeClass('hide');
        }
    });
}

// Proposal merge field into the editor
function insert_proposal_merge_field(field) {
    tinymce.activeEditor.execCommand('mceInsertContent', false, $(field).text());
}

// Toggle full view for small tables like proposals
function small_table_full_view() {
    $('#small-table').toggleClass('hide');
    $('.small-table-right-col').toggleClass('col-md-12 col-md-7');
}

// Used to update manually the prefix after invoice/estimate is created.
function save_sales_number_settings(e) {
    var data = {};
    data.prefix = $("body").find('input[name="s_prefix"]').val();
    $.post($(e).data('url'), data).done(function(response) {
        response = JSON.parse(response);
        if (response.success && response.message) {
            alert_float('success', response.message);
            $('#prefix').html(data.prefix);
        }
    });
}

// Prefix for invoices/estimates in case there is year.
function do_prefix_year(date) {
    var date_array;
    if (date.indexOf('.') > -1) {
        date_array = date.split('.');
    } else if (date.indexOf('-') > -1) {
        date_array = date.split('-');
    } else if (date.indexOf('/') > -1) {
        date_array = date.split('/');
    }
    if (typeof(date_array) != 'undefined') {
        $.each(date_array, function(i, string) {
            if (string.length == 4) {
                var $pYear = $('#prefix_year');
                if ($pYear.hasClass('format-n-yy')) {
                    string = string.substr(-2);
                } else if ($pYear.hasClass('format-mm-yyyy')) {
                    var month_index;
                    if (app_date_format == 'd-m-Y' ||
                        app_date_format == 'd/m/Y' ||
                        app_date_format == 'Y-m-d' ||
                        app_date_format == 'd.m.Y') {
                        month_index = 1;
                    } else if (app_date_format == 'm-d-Y' ||
                        app_date_format == 'm.d.Y' ||
                        app_date_format == 'm/d/Y') {
                        month_index = 0;
                    }
                    $('#prefix_month').html(date_array[month_index]);
                }
                $pYear.html(string);
            }
        });
    }
}

// Set single notification as read INLINE
function set_notification_read_inline(id) {
    requestGet('misc/set_notification_read_inline/' + id).done(function() {
        var notification = $("body").find('.notification-wrapper[data-notification-id="' + id + '"]');
        notification.find('.notification-box,.notification-box-all').removeClass('unread');
        notification.find('.not-mark-as-read-inline').tooltip('destroy').remove();
    });
}

// Marks all notifications as read INLINE
function mark_all_notifications_as_read_inline() {
    requestGet('misc/mark_all_notifications_as_read_inline/').done(function() {
        var notification = $("body").find('.notification-wrapper');
        notification.find('.notification-box,.notification-box-all').removeClass('unread');
        notification.find('.not-mark-as-read-inline').tooltip('destroy').remove();
    });
}

// Deletes activity for sales eq. invoices, estimates.
function delete_sale_activity(id) {
    if (confirm_delete()) {
        requestGet('misc/delete_sale_activity/' + id).done(function() {
            $("body").find('[data-sale-activity-id="' + id + '"]').remove();
        });
    }
}

// View calendar custom single event
function view_event(id) {
    if (typeof(id) == 'undefined') { return; }
    $.post(admin_url + 'utilities/view_event/' + id).done(function(response) {
        $('#event').html(response);
        $('#viewEvent').modal('show');
        init_datepicker();
        init_selectpicker();
        validate_calendar_form();
    });
}

// Delete calendar event form
function delete_event(id) {
    if (confirm_delete()) {
        requestGetJSON('utilities/delete_event/' + id).done(function(response) {
            if (response.success == true) { window.location.reload(); }
        });
    }
}

// Validate calendar event form
function validate_calendar_form() {
    _validate_form($("body").find('._event form'), {
        title: 'required',
        start: 'required',
        reminder_before: 'required'
    }, calendar_form_handler);

    _validate_form($("body").find('#viewEvent form'), {
        title: 'required',
        start: 'required',
        reminder_before: 'required'
    }, calendar_form_handler);
}

// Handles calendar event saving
function calendar_form_handler(form) {
    $.post(form.action, $(form).serialize()).done(function(response) {
        response = JSON.parse(response);
        if (response.success == true) {
            alert_float('success', response.message);
            setTimeout(function() {
                var location = window.location.href;
                location = location.split('?');
                window.location.href = location[0];
            }, 500);
        }
    });

    return false;
}

// Will give alert to confirm delete
function confirm_delete() {
    var r = confirm(appLang.confirm_action_prompt);
    if (r == false) { return false; }
    return true;
}

// Attached new hotkey handler
function add_hotkey(key, func) {
    $.Shortcuts.add({
        type: 'down',
        mask: key,
        handler: func
    });
}

// Fetches notifications
function fetch_notifications(callback) {
    requestGetJSON('misc/notifications_check').done(function(response) {
        var nw = notifications_wrapper;
        nw.html(response.html);
        var total = nw.find('ul.notifications').attr('data-total-unread');
        document.title = total > 0 ? ('(' + total + ') ' + doc_initial_title) : doc_initial_title;
        var nIds = response.notificationsIds;
        if (app_user_browser == 'firefox' && nIds.length > 1) {
            var lastNotification = nIds[0];
            nIds = new Array();
            nIds.push(lastNotification);
        }
        setTimeout(function() {
            if (nIds.length > 0) {
                $.each(nIds, function(i, notId) {
                    var nSelector = 'li[data-notification-id="' + notId + '"]';
                    var $not = nw.find(nSelector);
                    $.notify("", {
                        'title': appLang.new_notification,
                        'body': $not.find('.notification-title').text(),
                        'requireInteraction': true,
                        'icon': $not.find('.notification-image').attr('src'),
                        'tag': notId,
                        'closeTime': app_dismiss_desktop_not_after != "0" ? app_dismiss_desktop_not_after * 1000 : null
                    }).close(function() {
                        requestGet('misc/set_desktop_notification_read/' + notId).done(function(response) {
                            var $totalIndicator = nw.find('.icon-total-indicator');
                            nw.find('li[data-notification-id="' + notId + '"] .notification-box').removeClass('unread');
                            var currentTotalNotifications = $totalIndicator.text();
                            currentTotalNotifications = currentTotalNotifications.trim();
                            currentTotalNotifications = (currentTotalNotifications - 1);
                            if (currentTotalNotifications > 0) {
                                document.title = '(' + currentTotalNotifications + ') ' + doc_initial_title;
                                $totalIndicator.html(currentTotalNotifications)
                            } else {
                                document.title = doc_initial_title;
                                $totalIndicator.addClass('hide');
                            }
                        });
                    }).click(function(e) {
                        parent.focus();
                        window.focus();
                        setTimeout(function() {
                            nw.find(nSelector + ' .notification-link').addClass('desktopClick').click();
                            e.target.close();
                        }, 70);
                    });
                });
            }
        }, 10);
    });
}

function init_ajax_search(type, selector, server_data, url) {
    var ajaxSelector = $('body').find(selector);
    if (ajaxSelector.length) {
        var options = {
            ajax: {
                url: (typeof(url) == 'undefined' ? admin_url + 'misc/get_relation_data' : url),
                data: function() {
                    var data = {};
                    data.type = type;
                    data.rel_id = '';
                    data.q = '{{{q}}}';
                    if (typeof(server_data) != 'undefined') {
                        jQuery.extend(data, server_data);
                    }
                    return data;
                }
            },
            locale: {
                emptyTitle: appLang.search_ajax_empty,
                statusInitialized: appLang.search_ajax_initialized,
                statusSearching: appLang.search_ajax_searching,
                statusNoResults: appLang.not_results_found,
                searchPlaceholder: appLang.search_ajax_placeholder,
                currentlySelected: appLang.currently_selected
            },
            requestDelay: 500,
            cache: false,
            preprocessData: function(processData) {
                var bs_data = [];
                var len = processData.length;
                for (var i = 0; i < len; i++) {
                    var tmp_data = {
                        'value': processData[i].id,
                        'text': processData[i].name,
                    };
                    if (processData[i].subtext) {
                        tmp_data.data = { subtext: processData[i].subtext }
                    }
                    bs_data.push(tmp_data);
                }
                return bs_data;
            },
            preserveSelectedPosition: 'after',
            preserveSelected: true
        }
        if (ajaxSelector.data('empty-title')) {
            options.locale.emptyTitle = ajaxSelector.data('empty-title');
        }
        ajaxSelector.selectpicker().ajaxSelectPicker(options);
    }
}

// Used for email template URL
function merge_field_format_url(url, node, on_save, name) {
    // Merge fields url
    if (url.indexOf("{") > -1 && url.indexOf("}") > -1) {
        url = '{' + url.split('{')[1];
    }
    // Return new URL
    return url;
}

// Predefined and default dropzone plugin options
function _dropzone_defaults() {
    return {
        createImageThumbnails: true,
        dictDefaultMessage: appLang.drop_files_here_to_upload,
        dictFallbackMessage: appLang.browser_not_support_drag_and_drop,
        dictFileTooBig: appLang.file_exceeds_maxfile_size_in_form,
        dictCancelUpload: appLang.cancel_upload,
        dictRemoveFile: appLang.remove_file,
        dictMaxFilesExceeded: appLang.you_can_not_upload_any_more_files,
        maxFilesize: (max_php_ini_upload_size_bytes / (1024 * 1024)).toFixed(0),
        acceptedFiles: app_allowed_files,
        error: function(file, response) {
            alert_float('danger', response);
        },
        complete: function(file) {
            this.files.length && this.removeFile(file);
        },
    };
}

function onChartClickRedirect(evt, chart, fetchUrl) {
    if (typeof(fetchUrl) == 'undefined') {
        fetchUrl = 'statusLink';
    }
    var item = chart.getElementAtEvent(evt)[0];
    if (item) {
        var link = chart.data.datasets[0][fetchUrl][item['_index']];
        if (link) {
            window.location.href = link;
        }
    }
}

// Function to slug string
function slugify(string) {
    return string
        .toString()
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "-")
        .replace(/[^\w\-]+/g, "")
        .replace(/\-\-+/g, "-")
        .replace(/^-+/, "")
        .replace(/-+$/, "");
}

// Delay function
var delay = (function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

// General helper function for $.get ajax requests
function requestGet(uri, params) {
    params = typeof(params) == 'undefined' ? {} : params;
    var options = {
        type: 'GET',
        url: uri.indexOf(admin_url) > -1 ? uri : admin_url + uri
    };
    return $.ajax($.extend({}, options, params));
}

// General helper function for $.get ajax requests with dataType JSON
function requestGetJSON(uri, params) {
    params = typeof(params) == 'undefined' ? {} : params;
    params.dataType = 'json';
    return requestGet(uri, params);
}
