{* Two-Factor Authentication – code entry page (frontend) *}
{$meta_title = $lang->rs__tfa__page_title scope=global}

<div class="block">
    <div class="block__header block__header--boxed block__header--border">
        <h1 class="block__heading">{$lang->rs__tfa__page_title}</h1>
    </div>

    <div class="block block--boxed block--border">
        <div class="f_row justify-content-center">
            <div class="form_wrap f_col-lg-5 f_col-md-8">

                <p style="margin:0 0 20px;color:#666;">
                    {$lang->rs__tfa__code_desc}
                </p>

                {if $tfa_error}
                <div class="message_error" style="margin-bottom:16px;">
                    {if $tfa_error == 'invalid_code'}
                        {$lang->rs__tfa__error_invalid}
                    {elseif $tfa_error == 'code_expired'}
                        {$lang->rs__tfa__error_expired}
                    {else}
                        {$lang->rs__tfa__error_generic}
                    {/if}
                </div>
                {/if}

                <form method="post" action="" autocomplete="off">

                    <div class="form form--boxed">
                        <div class="form__body">
                            <div class="form__group">
                                <input
                                    class="form__input"
                                    type="text"
                                    id="tfa_code"
                                    name="tfa_code"
                                    inputmode="numeric"
                                    pattern="[0-9]{ldelim}6{rdelim}"
                                    maxlength="6"
                                    placeholder="000000"
                                    autofocus
                                    required
                                    autocomplete="one-time-code"
                                    style="font-size:24px;letter-spacing:8px;text-align:center;"
                                />
                                <span class="form__placeholder">{$lang->rs__tfa__code_label}</span>
                            </div>
                        </div>
                        <div class="form__footer">
                            <button type="submit" class="form__button button--blick">
                                {$lang->rs__tfa__btn_verify}
                            </button>
                        </div>
                    </div>

                </form>

                <p style="margin:16px 0 0;font-size:13px;color:#999;text-align:center;">
                    {$lang->rs__tfa__no_code}
                    <a href="{url_generator route='login'}">{$lang->rs__tfa__back_login}</a>
                </p>

            </div>
        </div>
    </div>
</div>
