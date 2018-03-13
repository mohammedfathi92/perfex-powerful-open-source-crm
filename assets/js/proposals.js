$(function() {
    get_proposal_comments();
    $('body').on('click', '.dismiss-proposal-convert-modal', function(e) {
        e.preventDefault();
        $('body').find('.proposal-convert-modal').modal('hide');
    });
});

function init_proposal_editor() {

    tinymce.remove('div.editable');
    var _templates = [];
    $.each(proposal_templates, function(i, template) {
        _templates.push({
            url: admin_url + 'proposals/get_template?name=' + template,
            title: template
        });
    });

    var settings = {
        selector: 'div.editable',
        inline: true,
        theme: 'modern',
        skin: 'perfex',
        relative_urls: false,
        remove_script_host: false,
        inline_styles: true,
        verify_html: false,
        cleanup: false,
        valid_elements: '+*[*]',
        valid_children: "+body[style], +style[type]",
        apply_source_formatting: false,
        file_browser_callback: elFinderBrowser,
        table_class_list: [{
            title: 'Flat',
            value: 'table'
        }, {
            title: 'Table Bordered',
            value: 'table table-bordered'
        }, {
            title: 'Items Table',
            value: 'proposal-items table'
        }, ],
        table_default_styles: {
            width: '100%'
        },
        removed_menuitems: 'newdocument',
        fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
        plugins: [
            'advlist pagebreak autolink autoresize lists link image charmap hr anchor',
            'searchreplace wordcount visualblocks visualchars code',
            'media nonbreaking save table contextmenu directionality',
            'paste textcolor colorpicker'
        ],
        autoresize_bottom_margin: 50,
        pagebreak_separator: '<p pagebreak="true"></p>',
        toolbar1: 'save_button fontselect fontsizeselect insertfile | styleselect',
        toolbar2: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
        toolbar3: 'media image | forecolor backcolor link ',
        setup: function(editor) {

            editor.on('blur', function() {
                $.Shortcuts.start();
            });

            editor.on('focus', function() {
               $.Shortcuts.stop();
            });

            editor.addButton('save_button', {
                text: appLang.proposal_save,
                icon: false,
                id: 'inline-editor-save-btn',
                onclick: function() {
                    var data = {};
                    data.proposal_id = proposal_id;
                    data.content = editor.getContent();
                    $.post(admin_url + 'proposals/save_proposal_data', data).done(function(response) {
                        response = JSON.parse(response);
                        if (response.success == true) {
                            alert_float('success', response.message);
                        }
                    }).fail(function(error) {
                        var response = JSON.parse(error.responseText);
                        alert_float('danger', response.message);
                    });
                }
            });
        },
    };
    if (_templates.length > 0) {
        settings.templates = _templates;
        settings.plugins[3] = 'template ' + settings.plugins[3];
    }
    tinymce.init(settings);
}

function add_proposal_comment() {
    var comment = $('#comment').val();
    if (comment == '') { return;}
    var data = {};
    data.content = comment;
    data.proposalid = proposal_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'proposals/add_proposal_comment', data).done(function(response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#comment').val('');
            get_proposal_comments();
        }
    });
}

function get_proposal_comments() {
    if (typeof(proposal_id) == 'undefined') { return; }
    requestGet('proposals/get_proposal_comments/' + proposal_id).done(function(response){
        $('body').find('#proposal-comments').html(response);
    });
}

function remove_proposal_comment(commentid) {
    if(confirm_delete()){
        requestGetJSON('proposals/remove_comment/' + commentid).done(function(response){
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
            }
        });
    }
}

function edit_proposal_comment(id) {
    var content = $('body').find('[data-proposal-comment-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'proposals/edit_comment/' + id, { content: content }).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-proposal-comment="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_proposal_comment_edit(id);
    }
}

function toggle_proposal_comment_edit(id) {
    $('body').find('[data-proposal-comment="' + id + '"]').toggleClass('hide');
    $('body').find('[data-proposal-comment-edit-textarea="' + id + '"]').toggleClass('hide');
}

function convert_template(invoker) {
    var template = $(invoker).data('template');
    var html_helper_selector;
    if (template == 'estimate') {
        html_helper_selector = 'estimate';
    } else if (template == 'invoice') {
        html_helper_selector = 'invoice';
    } else {
        return false;
    }
    requestGet('proposals/get_' + html_helper_selector + '_convert_data/' + proposal_id).done(function(data){
        if($('.proposal-pipeline-modal').is(':visible')) {
            $('.proposal-pipeline-modal').modal('hide');
        }
        $('#convert_helper').html(data);
        $('#convert_to_' + html_helper_selector).modal({ show: true, backdrop: 'static' });
        reorder_items();
    });
}
