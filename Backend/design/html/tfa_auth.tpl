{literal}
<style>
.tfa-hidden { display:none!important; }

.tfa-alert{
    border-radius:3px;
    padding:10px 14px;
    margin-bottom:14px;
    font-size:13px;
}

.tfa-alert--error{background:#fff0f0;border:1px solid #f5c6cb;color:#721c24;}
.tfa-alert--success{background:#f0fff4;border:1px solid #c3e6cb;color:#155724;}
.tfa-alert--info{background:#e8f4fd;border:1px solid #bee5eb;color:#0c5460;}

#tfa-code-input{
    font-size:26px;
    letter-spacing:10px;
    text-align:center;
    font-family:monospace;
}
</style>
{/literal}

{* Hidden 2FA code section template *}
<div id="tfa-code-section" class="tfa-hidden">
    <div class="input-group mb-1">
        <span class="input-group-addon">
            {include file='svg_icon.tpl' svgId='pass_icon'}
        </span>
        <input
            id="tfa-code-input"
            type="text"
            name="tfa_code"
            class="form-control"
            inputmode="numeric"
            maxlength="6"
            placeholder="000000"
            autocomplete="one-time-code"
        />
    </div>
</div>


<script>
window.TFA_CONFIG = {
    passwordless: {if $tfa_passwordless_enabled}true{else}false{/if},
    pending: {if $tfa_pending}true{else}false{/if},
    message: '{$tfa_magic_message|default:""|escape:"javascript"}',

    text:{
        magic_sent:'{$btr->rs__tfa__magic_sent|escape:"javascript"}',
        magic_token_invalid:'{$btr->rs__tfa__magic_token_invalid|escape:"javascript"}',
        magic_disabled:'{$btr->rs__tfa__magic_disabled|escape:"javascript"}',
        magic_empty:'{$btr->rs__tfa__magic_empty|escape:"javascript"}',

        login_placeholder:'{$btr->rs__tfa__auth_login_placeholder|escape:"javascript"}',
        magic_title:'{$btr->rs__tfa__magic_title|escape:"javascript"}',
        magic_desc:'{$btr->rs__tfa__magic_desc|escape:"javascript"}',
        magic_btn:'{$btr->rs__tfa__magic_btn|escape:"javascript"}',

        verify_btn:'{$btr->rs__tfa__modal_btn_verify|escape:"javascript"}',
        verify_title:'{$btr->rs__tfa__modal_title|escape:"javascript"}',
        verify_desc:'{$btr->rs__tfa__modal_desc|escape:"javascript"}',
    }
};
</script>


{literal}
<script>

$(function(){

    const tfa = window.TFA_CONFIG;

    const $form = $('form[method="post"]').first();
    const $loginInput = $('input[name="login"]');
    const $passwordInput = $('input[name="password"]');
    const $recoveryWrap = $('.fn_recovery_wrap');



    function showMessage(){

        if(!tfa.message) return;

        const map={
            magic_sent:{class:'tfa-alert--success',text:tfa.text.magic_sent},
            magic_token_invalid:{class:'tfa-alert--error',text:tfa.text.magic_token_invalid},
            magic_disabled:{class:'tfa-alert--info',text:tfa.text.magic_disabled},
            magic_empty:{class:'tfa-alert--error',text:tfa.text.magic_empty}
        };

        const msg=map[tfa.message];
        if(!msg) return;

        $('.auth_heading').before(
            `<div class="tfa-alert ${msg.class}">${msg.text}</div>`
        );
    }



    function enablePasswordless(){

        $passwordInput.closest('.input-group').hide();

        $('.fn_recovery').hide();
        $recoveryWrap.remove();

        $form.append('<input type="hidden" name="magic_admin_request" value="1">');

        $loginInput
            .attr('name','magic_admin_login')
            .attr('placeholder',tfa.text.login_placeholder);

        $('.auth_heading').text(tfa.text.magic_title);

        $('.auth_heading_promo').after(
            `<p style="color:#666;font-size:13px;margin-bottom:12px;">
                ${tfa.text.magic_desc}
            </p>`
        );

        $('.auth_buttons__login').text(tfa.text.magic_btn);
    }



    function showCodeInput(){

        $loginInput.closest('.input-group').hide();
        $passwordInput.closest('.input-group').hide();

        $('.fn_recovery').hide();
        $recoveryWrap.hide();

        const codeField = $('#tfa-code-section').html();

        $('.auth_buttons').before(codeField);

        $('input[name="tfa_code"]').focus();

        $('.auth_heading').text(tfa.text.verify_title);

         $('.auth_heading_promo').after(
            `<p style="color:#666;font-size:13px;margin-bottom:12px;">
                ${tfa.text.verify_desc}
            </p>`
        );

        $('.auth_buttons__login').text(tfa.text.verify_btn);
    }



    showMessage();

    if(tfa.passwordless && !tfa.pending){
        enablePasswordless();
    }

    if(tfa.pending){
        showCodeInput();
    }

});

</script>
{/literal}