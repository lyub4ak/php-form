$(document).ready(function() {
    const jq_form = $('#contact-form');
    const a_inputs = jq_form.find('input, textarea');
    const jq_email = a_inputs.filter('input[name="email"]');
    const jq_submit = jq_form.find('button');
    const jq_error_container = $('#error-container');
    let is_email = false;

    // Checks email.
    jq_email.on('blur', function(e){
        is_email = isEmail($(this));
        if(is_email)
            $('#email-error').remove();
        else if($('#email-error').length == 0)
            $(this).after('<div id="email-error" class="error">Не корректный E-mail.</div>');
    });

    // Checks if form can be submit.
    a_inputs.on('keyup', function(e){
        if(canSubmit(a_inputs) && is_email)
            jq_submit.prop('disabled', false).css({'background-color': '#42A9FF'});
        else
            jq_submit.prop('disabled', true).removeAttr('style');
    });

    /**
     * Submits form.
     *
     * @link http://jquery.page2page.ru/index.php5/Ajax-%D0%B7%D0%B0%D0%BF%D1%80%D0%BE%D1%81 jQuery.ajax()
     */
    jq_form.on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: "./ajax_form.php",
            type: "POST",
            dataType: false, // data type of response from server, false - auto detect.
            data: jq_form.serialize(),
            success: function(response) {
                let a_result = $.parseJSON(response);
                if(a_result.a_errors.length > 0) {
                    let html_errors = '';
                    a_result.a_errors.each(function () {
                        html_errors = '<div>' + $(this) + '</div>';
                    });
                    jq_error_container.html(html_errors)
                } else {
                    $('#contact-container').html('Спасибо, ' + a_result.html_name + '!')
                }
            },
            error: function(response) {
                jq_error_container.html('<div>Ошибка. Данные не отправлены.</div>');
            }
        });
    });
});

/**
 * Checks that all inputs are not empty.
 *
 * @param {[jQuery]} a_inputs Array of inputs for check.
 * @returns {boolean} Whether form can be submitted.
 */
function canSubmit (a_inputs){
    let can_submit = true;
    a_inputs.each(function () {
        let text_value = $(this).val().trim();
        if(text_value.length == 0){
            can_submit = false;
            return false;
        }
    });

    return can_submit;
}

/**
 * Checks that input has valid email.
 *
 * @param {jQuery} jq_email Input for check.
 * @returns {boolean} <tt>true</tt> - whether value of input is valid email, <tt>false</tt> - otherwise.
 */
function isEmail(jq_email) {
    let o_mail_pattern = new RegExp('^[A-Za-z0-9]+([\._A-Za-z0-9-]+)*@[A-Za-z0-9]+(\.[A-Za-z0-9]+)*(\.[A-Za-z]{2,})$');
    return o_mail_pattern.test(jq_email.val());
}