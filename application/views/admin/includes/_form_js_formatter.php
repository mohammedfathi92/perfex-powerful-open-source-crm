<script>

var fbOptions = {
    dataType: 'json'
};

if (formData && formData.length) {
    fbOptions.formData = formData;
}

fbOptions.disableFields = ['autocomplete', 'button', 'checkbox', 'checkbox-group', 'date', 'hidden', 'number', 'radio-group', 'select', 'text', 'textarea'];

fbOptions.controlPosition = 'left';
fbOptions.controlOrder = [
    'header',
    'paragraph',
    'file',
];

fbOptions.inputSets = [];

var db_fields = <?php echo json_encode($db_fields); ?>;
var cfields = <?php echo json_encode($cfields); ?>;
$.each(db_fields, function(i, f) {
    fbOptions.inputSets.push(f);
});
if (cfields && cfields.length) {
    $.each(cfields, function(i, f) {
        fbOptions.inputSets.push(f);
    });
}
fbOptions.typeUserEvents = {
    'text': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'input');
        },
    },
    'email': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'input');
        },
    },
    'color': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'input');
        },
    },
    'date': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'input');
        },
    },
    'datetime': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'datetime');
        },
    },
    'select': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'select');
        },
    },
    'file': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'file');
            // set file upload field name to be always file-input
            $(fId).find('.name-wrap .input-wrap input').val('file-input')
        },
    },
    'textarea': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'textarea');
        },
    },
    'checkbox-group': {
        onadd: function(fId) {
            do_form_field_restrictions(fId, 'checkbox-group');
        },
    },
}
$(function() {

    $('body').on('click', '.del-button', function() {
        var _field = $(this).parents('li.form-field');
        var _preview_name;
        var s = $('.cb-wrap .ui-sortable');
        if (_field.find('.prev-holder input').length > 0) {
            _preview_name = _field.find('.prev-holder input').attr('name');
        } else if (_field.find('.prev-holder datetime').length > 0) {
            _preview_name = _field.find('.prev-holder datetime').attr('name');
        } else if (_field.find('.prev-holder textarea').length > 0) {
            _preview_name = _field.find('.prev-holder textarea').attr('name');
        } else if (_field.find('.prev-holder select').length > 0) {
            _preview_name = _field.find('.prev-holder select').attr('name');
        }

        var pos = _preview_name.lastIndexOf('-');
        _preview_name = _preview_name.substr(0, pos);
        if (_preview_name != 'file-input') {
            $('li[type="' + _preview_name + '"]').removeClass('disabled')
        } else {
            setTimeout(function() {
                s.find('li').eq(2).removeClass('disabled');
            }, 50);
        }
        setTimeout(function() {
            s.sortable({ cancel: '.disabled' });
            s.sortable('refresh');
        }, 80);
    });

    $('body').on('blur', '.form-field:not([type="header"],[type="paragraph"],[type="checkbox-group"]) input[name="className"]',
        function() {
        var className = $(this).val();
        if (className.indexOf('form-control') == -1) {
            className = className.trim();
            className += ' form-control';
            className = className.trim();
            $(this).val(className);
        }
    });

    $('body').on('focus', '.name-wrap input', function() {
        $(this).blur();
    });

})

function do_form_field_restrictions(fId, type) {
    var _field = $(fId);

    var _preview_name;
    var s = $('.cb-wrap .ui-sortable');
    if (type == 'checkbox-group') {
        _preview_name = _field.find('input[type="checkbox"]').eq(0).attr('name');
    } else if (type == 'file') {
        setTimeout(function() {
            s.find('li').eq(2).addClass('disabled');
        }, 50);
    } else if(type == 'datetime'){
         _preview_name = _field.find('.name-wrap input').val();
         setTimeout(function(){
            _field.find('.prev-holder datetime').html('');
        },50);
         _field.find('.prev-hold input[type="'+type+'"]').html('');
    } else {
        _preview_name = _field.find(type).attr('name');
    }

    if(type == 'datetime'){
        $('[type="' + _preview_name + '"]:not(.form-field)').addClass('disabled');
    } else if(type != 'file') {
        var pos = _preview_name.lastIndexOf('-');
        _preview_name = _preview_name.substr(0, pos);
        $('[type="' + _preview_name + '"]:not(.form-field)').addClass('disabled');
    }

    $('.frmb-control li[type="'+_preview_name+'"]').removeClass('text-danger');

    if(typeof(mustRequiredFields) != 'undefined' && $.inArray(_preview_name,mustRequiredFields) != -1){
        _field.find('.required-wrap input[type="checkbox"]').prop('disabled',true);
    }

    setTimeout(function() {
        s.sortable({ cancel: '.disabled' });
        s.sortable('refresh');
    }, 80);
}

</script>
