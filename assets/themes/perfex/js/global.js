 $(window).on('load resize', function() {
     fix_proposal_right_height();
 });

 $.validator.setDefaults({
     highlight: function(element) {
         $(element).closest('.form-group').addClass('has-error');
     },
     unhighlight: function(element) {
         $(element).closest('.form-group').removeClass('has-error');
     },
     errorElement: 'span',
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

 $.validator.addMethod('filesize', function(value, element, param) {
     return this.optional(element) || (element.files[0].size <= param);
 }, file_exceeds_max_filesize);

 $.validator.addMethod("extension", function(value, element, param) {
     param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
     return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
 }, $.validator.format(validation_extension_not_allowed));

 $(function() {
     $('body').find('select').selectpicker({
         showSubtext: true,
     });

     init_progress_bars();
     init_color_pickers();
     jQuery.datetimepicker.setLocale(locale);
     init_datepicker();

     $('body').tooltip({
         selector: '[data-toggle="tooltip"]'
     });
     // Init popovers
     $('body').popover({
         selector: '[data-toggle="popover"]'
     });


     // Close all popovers if user click on body and the click is not inside the popover content area
     $('body').on('click', function(e) {
         $('[data-toggle="popover"]').each(function() {
             //the 'is' for buttons that trigger popups
             //the 'has' for icons within a button that triggers a popup
             if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                 $(this).popover('hide');
             }
         });
     });

    if (typeof(lightbox) != 'undefined') {
        var _lightBoxOptions = {
            'showImageNumberLabel': false,
            resizeDuration: 200,
        };
        lightbox.option(_lightBoxOptions);
    }

     // Add are you sure on all delete links (onclick is not included here)
     $('body').on('click', '._delete', function(e) {
         var r = confirm(confirm_action_prompt);
         if (r == true) {
             return true;
         } else {
             return false;
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

     $('.article_useful_buttons button').on('click', function(e) {
         e.preventDefault();
         var data = {};
         data.answer = $(this).data('answer');
         data.articleid = $('input[name="articleid"]').val();
         $.post(site_url + 'clients/add_kb_answer', data).done(function(response) {
             response = JSON.parse(response);
             if (response.success == true) {
                 $(this).focusout();
             }
             $('.answer_response').html(response.message);
         });
     });

     $(".proposal-left table").wrap("<div class='table-responsive'></div>");

     $('#identityConfirmationForm').validate({
         ignore: [], // allow hidden form/fields validation
         rules: {
             acceptance_firstname: 'required',
             acceptance_lastname: 'required',
             acceptance_email: {
                 email: true,
                 required: true
             }
         }
     });

     $('body.identity-confirmation #accept_action').on('click', function() {
         var $submitForm = $('#identityConfirmationForm');
         if ($submitForm.length && !$submitForm.validate().checkForm()) {
             $('#identityConfirmationModal').modal({ show: true, backdrop: 'static', keyboard: false });
         } else {
             $(this).prop('disabled', true);
             $submitForm.submit();
         }
         return false;
     });

     $('body').on('click', '[data-loading-text]', function() {
         var form = $(this).data('form');
         if (form != null) {
             if ($(form).valid()) {
                 $(this).button('loading');
             }
         } else {
             $(this).button('loading');
         }
     });

     $('#survey_form').validate();
     var survey_fields_required = $('#survey_form').find('[data-required="1"]');
     $.each(survey_fields_required, function() {
         $(this).rules("add", {
             required: true
         });
         var name = $(this).data('for');
         var label = $(this).parents('.form-group').find('[for="' + name + '"]');
         if (label.length > 0) {
             if (label.find('.req').length == 0) {
                 label.prepend(' <small class="req text-danger">* </small>');
             }
         }
     });

 });

 function init_progress_bars() {
     setTimeout(function() {
         $('.progress .progress-bar').each(function() {
             var bar = $(this);
             var perc = bar.attr("data-percent");
             var current_perc = 0;
             var progress = setInterval(function() {
                 if (current_perc >= perc) {
                     clearInterval(progress);
                     if (perc == 0) {
                         bar.css('width', 0 + '%');
                     }
                 } else {
                     current_perc += 1;
                     bar.css('width', (current_perc) + '%');
                 }
                 if (!bar.hasClass('no-percent-text')) {
                     bar.text((current_perc) + '%');
                 }
             }, 10);
         });
     }, 300);
 }

 function init_color_pickers() {
     $('body').find('.colorpicker-input').colorpicker({
         format: "hex"
     });
 }

 function init_datepicker() {
     var datepickers = $('.datepicker');
     var datetimepickers = $('.datetimepicker');
     if (datetimepickers.length == 0 && datepickers.length == 0) {
         return;
     }
     var opt;
     // Datepicker without time
     $.each(datepickers, function() {
         var opt = {
             timepicker: false,
             scrollInput: false,
             lazyInit: true,
             format: date_format,
             dayOfWeekStart: calendar_first_day,
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
             dayOfWeekStart: calendar_first_day
         };
         if (time_format == 24) {
             opt_time.format = date_format + ' H:i';
         } else {
             opt_time.format = date_format + ' g:i A';
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

 function is_mobile() {
     if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
         return true;
     }
     return false;
 }

 // Generate float alert
 function alert_float(type, message) {
     var aId, el;
     aId = $('body').find('float-alert').length;
     aId++;
     aId = 'alert_float_' + aId;
     el = $('<div id="' + aId + '" class="float-alert animated fadeInRight col-xs-11 col-sm-4 alert alert-' + type + '"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="fa fa-bell-o" data-notify="icon"></span><span class="alert-title">' + message + '</span></div>');
     $('body').append(el);

     setTimeout(function() {
         $('#' + aId).hide('fast', function() { $('#' + aId).remove(); });
     }, 4000);
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

 function fix_proposal_right_height() {
     var height = $(document).outerHeight(true);
     $('.proposal-right').height(height + 'px');
 }
