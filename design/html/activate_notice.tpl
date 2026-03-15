{* Registration activation result page *}
{$meta_title = $lang->rs__tfa__activate_title scope=global}

<div class="block">
    <div class="block__header block__header--boxed block__header--border">
        <h1 class="block__heading">{$lang->rs__tfa__activate_title}</h1>
    </div>

    <div class="block block--boxed block--border">
        <div class="f_row justify-content-center">
            <div class="form_wrap f_col-lg-5 f_col-md-8" style="text-align:center;">

                {if $activation_result == 'success'}
                    <div class="message_ok" style="margin-bottom:24px;">
                        {$lang->rs__tfa__activate_success}
                    </div>
                {else}
                    <div class="message_error" style="margin-bottom:24px;">
                        {$lang->rs__tfa__activate_invalid}
                    </div>
                {/if}

                <a href="{url_generator route='login'}" class="form__button button--blick" style="display:inline-block;">
                    {$lang->rs__tfa__activate_go_login}
                </a>

            </div>
        </div>
    </div>
</div>
