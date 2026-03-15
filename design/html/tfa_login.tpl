{literal}
<style>
.tfa-hidden { display:none!important; }

.tfa-alert{
    border-radius:4px;
    padding:10px 14px;
    margin-bottom:14px;
    font-size:13px;
}

.tfa-alert--error{background:#fff0f0;border:1px solid #f5c6cb;color:#721c24;}
.tfa-alert--success{background:#f0fff4;border:1px solid #c3e6cb;color:#155724;}

#tfa-code-input{
    font-size:24px;
    letter-spacing:8px;
    text-align:center;
    font-family:monospace;
}
</style>
{/literal}

<script>
window.TFA_FRONT = {
    passwordless: {if $settings->rs__tfa__passwordless_users}true{else}false{/if},
    pending: {if isset($smarty.session.tfa_user_pending_id)}true{else}false{/if},
    tfaError: '{$smarty.get.tfa_error|default:""|escape:"javascript"}',
    magicSent: {if $smarty.get.magic_sent}true{else}false{/if},

    text: {
        magic_title:   '{$lang->rs__tfa__magic_title|escape:"javascript"}',
        magic_desc:    '{$lang->rs__tfa__magic_desc|escape:"javascript"}',
        magic_btn:     '{$lang->rs__tfa__magic_btn|escape:"javascript"}',
        magic_sent:    '{$lang->rs__tfa__magic_sent|escape:"javascript"}',

        code_title:    '{$lang->rs__tfa__page_title|escape:"javascript"}',
        code_desc:     '{$lang->rs__tfa__code_desc|escape:"javascript"}',
        code_label:    '{$lang->rs__tfa__code_label|escape:"javascript"}',
        btn_verify:    '{$lang->rs__tfa__btn_verify|escape:"javascript"}',

        error_invalid: '{$lang->rs__tfa__error_invalid|escape:"javascript"}',
        error_expired: '{$lang->rs__tfa__error_expired|escape:"javascript"}',
        back_login:    '{$lang->rs__tfa__back_login|escape:"javascript"}'
    }
};
</script>

{literal}
<script>
$(function(){

    var tfa = window.TFA_FRONT;

    var $form        = $('form.fn_validate_login');
    if (!$form.length) return;

    var $emailGroup    = $form.find('input[name="email"]').closest('.form__group');
    var $passwordGroup = $form.find('input[name="password"]').closest('.form__group');
    var $formBody      = $form.find('.form__body');
    var $formTitle     = $form.find('.form__title span');
    var $submitBtn     = $form.find('button[name="login"]');
    var $remindLink    = $form.find('.password_remind');



    function showMessage() {
        if (tfa.magicSent) {
            $formBody.prepend('<div class="tfa-alert tfa-alert--success">' + tfa.text.magic_sent + '</div>');
        }
        if (tfa.tfaError === 'invalid_code') {
            $formBody.prepend('<div class="tfa-alert tfa-alert--error">' + tfa.text.error_invalid + '</div>');
        }
        if (tfa.tfaError === 'code_expired') {
            $formBody.prepend('<div class="tfa-alert tfa-alert--error">' + tfa.text.error_expired + '</div>');
        }
    }



    function enablePasswordless() {
        $passwordGroup.addClass('tfa-hidden');
        $passwordGroup.find('input').removeAttr('required');
        $remindLink.addClass('tfa-hidden');

        $form.append('<input type="hidden" name="magic_user_request" value="1">');

        $formTitle.text(tfa.text.magic_title);
        $formBody.prepend(
            '<p style="color:#666;font-size:14px;margin-bottom:12px;">' +
            tfa.text.magic_desc + '</p>'
        );
        $submitBtn.find('span').text(tfa.text.magic_btn);
        $submitBtn.removeAttr('name').removeAttr('value');
    }



    function showCodeInput() {
        $emailGroup.addClass('tfa-hidden');
        $passwordGroup.addClass('tfa-hidden');
        $passwordGroup.find('input').removeAttr('required');
        $remindLink.addClass('tfa-hidden');

        // Remove existing error messages from login form
        $formBody.find('.message_error').remove();

        var codeHtml =
            '<div class="form__group" id="tfa-code-group">' +
                '<input class="form__input form__placeholder--focus" ' +
                    'id="tfa-code-input" type="text" name="tfa_code" ' +
                    'inputmode="numeric" maxlength="6" placeholder="000000" ' +
                    'autocomplete="one-time-code" autofocus required />' +
                '<span class="form__placeholder">' + tfa.text.code_label + '</span>' +
            '</div>';

        $emailGroup.before(codeHtml);

        $formTitle.text(tfa.text.code_title);
        $formBody.prepend(
            '<p style="color:#666;font-size:14px;margin-bottom:12px;">' +
            tfa.text.code_desc + '</p>'
        );
        $submitBtn.find('span').text(tfa.text.btn_verify);
        $submitBtn.removeAttr('name').removeAttr('value');

        // Back to login link
        $form.after(
            '<p style="margin:12px 0 0;font-size:13px;color:#999;text-align:center;">' +
                '<a href="' + location.pathname + '">' + tfa.text.back_login + '</a>' +
            '</p>'
        );

        // Focus code input
        setTimeout(function(){ $('#tfa-code-input').focus(); }, 100);
    }



    showMessage();

    if (tfa.passwordless && !tfa.pending) {
        enablePasswordless();
    }

    if (tfa.pending) {
        showCodeInput();
    }

});
</script>
{/literal}
