{* Email not verified – notice page with resend option *}
{$meta_title = $lang->rs__tfa__not_verified_title scope=global}

<div class="block">
    <div class="block__header block__header--boxed block__header--border">
        <h1 class="block__heading">{$lang->rs__tfa__not_verified_title}</h1>
    </div>

    <div class="block block--boxed block--border">
        <div class="f_row justify-content-center">
            <div class="form_wrap f_col-lg-5 f_col-md-8">

                {if $resent}
                    <div class="message_ok" style="margin-bottom:20px;">
                        {$lang->rs__tfa__not_verified_resent}
                    </div>
                {else}
                    <p style="margin:0 0 20px;color:#666;">
                        {$lang->rs__tfa__not_verified_desc}
                    </p>

                    <form method="post" action="">
                        <div class="form form--boxed">
                            <div class="form__footer">
                                <button type="submit" class="form__button button--blick">
                                    {$lang->rs__tfa__not_verified_resend}
                                </button>
                            </div>
                        </div>
                    </form>
                {/if}

                <p style="margin:16px 0 0;font-size:13px;color:#999;text-align:center;">
                    <a href="{url_generator route='login'}">{$lang->rs__tfa__not_verified_login}</a>
                </p>

            </div>
        </div>
    </div>
</div>
