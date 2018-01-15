$(function(){

    $('input[name="send_survey_to[leads]"]').on('change',function(){
        $('.leads-statuses').slideToggle();
    });

    $('input[name="send_survey_to[clients]"]').on('change',function(){
        $('.customer-groups').slideToggle();
    });

    $('.survey-customer-groups input').on('change',function(){
        if($('.survey-customer-groups input:checked').length > 0){
            $('#ml_customers_all').prop('checked',false);
        }
    });

    $('#ml_customers_all').on('change',function(){
        if($(this).prop('checked') !== false){
            $('.survey-customer-groups input').prop('checked',false);
        }
    });

    // Validate survey form
    _validate_form($('#survey_form'), {
        subject: 'required'
    });

    // Init questions sortable
    var questions_sortable = $("#survey_questions").sortable({
        placeholder: "ui-state-highlight-survey",
        update: function() {
            // Update question order
            update_questions_order();
        }
    });

    // Add merge field
    $('.add_email_list_custom_field_to_survey').on('click', function(e) {
        e.preventDefault();
        tinymce.get('description').execCommand('mceInsertContent', false, $(this).data('slug'));
    });

});
function survey_toggle_full_view(){
    $('#survey-add-edit-wrapper').toggleClass('hide');
    $('#survey_questions_wrapper').toggleClass('col-md-12');
    $('#survey_questions_wrapper').toggleClass('col-md-7');
}
// New survey question
function add_survey_question(type, surveyid) {
    $.post(admin_url + 'surveys/add_survey_question', {
        type: type,
        surveyid: surveyid
    }).done(function(response) {
        response = JSON.parse(response);
        question_area = '<li>';
        question_area += '<div class="form-group question">';
        question_area += '<div class="checkbox checkbox-primary required">';
        question_area += '<input type="checkbox" data-question_required="' + response.data.questionid + '" name="required[]" onchange="update_question(this,\'' + type + '\',' + response.data.questionid + ')">';
        question_area += '<label>' + response.survey_question_required + '</label>';
        question_area += '</div>';
        question_area += hidden_input('order[]', '');
        // used only to identify input key no saved in database
        question_area += '<label for="' + response.data.questionid + '" class="control-label display-block">'+response.survey_question_string+' <a href="#" onclick="update_question(this,\'' + type + '\',' + response.data.questionid + '); return false;" class="pull-right update-question-button"><i class="fa fa-refresh text-success question_update"></i></a><a href="#" class="pull-right"><i class="fa fa-remove text-danger" onclick="remove_question_from_database(this,' + response.data.questionid + '); return false;"></i></a></label>';
        question_area += '<input type="text" onblur="update_question(this,\'' + type + '\',' + response.data.questionid + ');" data-questionid="' + response.data.questionid + '" class="form-control questionid">';
        if (type == 'textarea') {
            question_area += '<textarea class="form-control mtop20" disabled="disabled" rows="6">' + response.survey_question_only_for_preview + '</textarea>';
        } else if (type == 'checkbox' || type == 'radio') {
            question_area += '<div class="row">';
            box_description_icon_class = 'fa-plus';
            box_description_function = 'add_box_description_to_database(this,' + response.data.questionid + ',' + response.data.boxid + '); return false;';
            question_area += '<div class="box_area">';
            question_area += '<div class="col-md-12">';
            question_area += '<a href="#" class="add_remove_action survey_add_more_box" onclick="' + box_description_function + '"><i class="fa ' + box_description_icon_class + '"></i></a>';
            question_area += '<div class="' + type + ' ' + type + '-primary">';
            question_area += '<input type="' + type + '" disabled="disabled"/>';
            question_area += '<label><input onblur="update_question(this,\'' + type + '\',' + response.data.questionid + ');" type="text" data-box-descriptionid="' + response.data[0].questionboxdescriptionid + '" class="survey_input_box_description"></label>';
            question_area += '</div>';
            question_area += '</div>';
            question_area += '</div>';
            // end box row
            question_area += '</div>';
        } else {
            question_area += '<input type="text" onchange="update_question(this,\'' + type + '\',' + response.data.questionid + ');" class="form-control mtop20" disabled="disabled" value="' + response.survey_question_only_for_preview + '">';
        }
        question_area += '</div>';
        question_area += '</li>';
        $('#survey_questions').append(question_area);
        $("#survey_questions").sortable('refresh');
        $('html,body').animate({
            scrollTop: $("#survey_questions li:last-child").offset().top},
            'slow');
        update_questions_order();
    });
}
// Update question when user click on reload button
function update_question(question, type, questionid) {
    $(question).parents('li').find('i.question_update').addClass('spinning');
    var data = {};
    var _question = $(question).parents('.question').find('input[data-questionid="' + questionid + '"]').val();
    var _required = $(question).parents('.question').find('input[data-question_required="' + questionid + '"]').prop('checked')

    data.question = {
        value: _question,
        required: _required
    };

    data.questionid = questionid;
    if (type == 'checkbox' || type == 'radio') {
        var tempData = [];
        var boxes_area = $(question).parents('.question').find('.box_area');

        $.each(boxes_area, function() {
            var boxdescriptionid = $(this).find('input.survey_input_box_description').data('box-descriptionid');
            var boxdescription = $(this).find('input.survey_input_box_description').val();
            var _temp_data = [boxdescriptionid, boxdescription];
            tempData.push(_temp_data);
        });

        data.boxes_description = tempData;
    }

    setTimeout(function() {
        $.post(admin_url + 'surveys/update_question', data).done(function(response) {
            $(question).parents('li').find('i.question_update').removeClass('spinning');
        });
    }, 10);
}

// Add more boxes to already added question // checkbox // radio box
function add_more_boxes(question, boxdescriptionid) {
    var box = $(question).parents('.box_area').clone();
    $(question).parents('.question').find('.box_area').last().after(box);
    $(box).find('i').removeClass('fa-plus').addClass('fa-minus').addClass('text-danger');
    $(box).find('input.survey_input_box_description').val('');
    $(box).find('input.survey_input_box_description').attr('data-box-descriptionid', boxdescriptionid);
    $(box).find('input.survey_input_box_description').focus();
    $(box).find('.add_remove_action').attr('onclick', 'remove_box_description_from_database(this,' + boxdescriptionid + '); return false;')
    update_questions_order();

}
// Remove question from database
function remove_question_from_database(question, questionid) {
    $.get(admin_url + 'surveys/remove_question/' + questionid, function(response) {
        if (response.success == false) {
            alert_float('danger', response.message);
        } else {
            $(question).parents('.question').remove();
            update_questions_order();
        }
    }, 'json');
}
// Remove question box description  // checkbox // radio box
function remove_box_description_from_database(question, questionboxdescriptionid) {
    $.get(admin_url + 'surveys/remove_box_description/' + questionboxdescriptionid, function(response) {
        if (response.success == true) {
            $(question).parents('.box_area').remove();
        } else {
            alert_float('danger', response.message);
        }
    }, 'json');
}
// Add question box description  // checkbox // radio box
function add_box_description_to_database(question, questionid, boxid) {
    $.get(admin_url + 'surveys/add_box_description/' + questionid + '/' + boxid, function(response) {
        if (response.boxdescriptionid !== false) {
            add_more_boxes(question, response.boxdescriptionid);
        } else {
            alert_float('danger', response.message);
        }
    }, 'json');
}
// Updating survey question order // called when drop event called
function update_questions_order() {
    var questions = $('#survey_questions').find('.question');
    var i = 1;
    $.each(questions, function() {
        $(this).find('input[name="order[]"]').val(i);
        i++;
    });
    var update = [];
    $.each(questions, function() {
        var questionid = $(this).find('input.questionid').data('questionid');
        var order = $(this).find('input[name="order[]"]').val();
        update.push([questionid, order])
    });
    data = {};
    data.data = update;
    $.post(admin_url + 'surveys/update_survey_questions_orders', data);
}
