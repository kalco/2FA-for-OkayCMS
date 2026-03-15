{* Passwordless / magic login – request form and state messages *}
{$meta_title = $lang->rs__tfa__magic_title scope=global}

<div class="block">
    <div class="block__header block__header--boxed block__header--border">
        <h1 class="block__heading">{$lang->rs__tfa__magic_title}</h1>
    </div>

    <div class="block block--boxed block--border">
        <div class="f_row justify-content-center">
            <div class="form_wrap f_col-lg-5 f_col-md-8">

                {if $magic_disabled}

                    <div class="message_error" style="margin-bottom:20px;">
                        {$lang->rs__tfa__magic_disabled}
                    </div>

                {elseif $magic_invalid}

                    <div class="message_error" style="margin-bottom:20px;">
                        {$lang->rs__tfa__magic_invalid}
                    </div>
                    <form method="post" action="">
                        <div class="form form--boxed">
                            <div class="form__body">
                                <div class="form__group">
                                    <input
                                        class="form__input"
                                        type="email"
                                        name="magic_email"
                                        placeholder="{$lang->rs__tfa__magic_email_placeholder}"
                                        required
                                        autofocus
                                    />
                                    <span class="form__placeholder">{$lang->rs__tfa__magic_email_label}</span>
                                </div>
                            </div>
                            <div class="form__footer">
                                <button type="submit" class="form__button button--blick">
                                    {$lang->rs__tfa__magic_btn}
                                </button>
                            </div>
                        </div>
                    </form>

                {elseif $magic_sent}

                    <div class="message_ok" style="margin-bottom:20px;">
                        {$lang->rs__tfa__magic_sent}
                    </div>

                {else}

                    <p style="margin:0 0 20px;color:#666;">
                        {$lang->rs__tfa__magic_desc}
                    </p>

                    <form method="post" action="">
                        <div class="form form--boxed">
                            <div class="form__body">
                                <div class="form__group">
                                    <input
                                        class="form__input"
                                        type="email"
                                        name="magic_email"
                                        placeholder="{$lang->rs__tfa__magic_email_placeholder}"
                                        required
                                        autofocus
                                    />
                                    <span class="form__placeholder">{$lang->rs__tfa__magic_email_label}</span>
                                </div>
                            </div>
                            <div class="form__footer">
                                <button type="submit" class="form__button button--blick">
                                    {$lang->rs__tfa__magic_btn}
                                </button>
                            </div>
                        </div>
                    </form>

                {/if}

                <p style="margin:16px 0 0;font-size:13px;color:#999;text-align:center;">
                    <a href="{url_generator route='login'}">{$lang->rs__tfa__magic_use_password}</a>
                    &nbsp;·&nbsp;
                    <a href="{url_generator route='login'}">{$lang->rs__tfa__magic_back_login}</a>
                </p>

            </div>
        </div>
    </div>
</div>
