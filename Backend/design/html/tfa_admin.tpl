{$meta_title=$btr->rs__tfa__title scope=global}

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->rs__tfa__title|escape}
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-sm-12 float-xs-right"></div>
</div>
{if $saved}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="alert alert--success alert--icon alert--center" style="margin-bottom:16px;">
            <div class="alert__content">
                <div class="alert__title">{$btr->rs__tfa__saved|escape}</div>
            </div>
        </div>
    </div>
</div>
{/if}

<div class="alert alert--center alert--icon {if $smtp_configured}alert--success{else}alert--warning{/if}">
    <div class="alert__content">
        <div class="alert__title">
            {if $smtp_configured}{$btr->rs__tfa__smtp_ok|escape}{else}{$btr->rs__tfa__smtp_warn|escape}{/if}
        </div>
    </div>
</div>

<form method="post" action="">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">
    <div class="row">
        <div class="col-xs-4">
            <div class="boxed">
                <div class="heading_box font_16 mb-2">{$btr->rs__tfa__settings_title|escape}</div>
                {* Enable 2FA for users *}
                <div class="activity_of_switch_item">
                    <div class="okay_switch clearfix">
                        <label class="switch_label">{$btr->rs__tfa__enable_users_label|escape}</label>
                        <label class="switch switch-default">
                            <input class="switch-input" name="rs__tfa__enable_users" value="1" type="checkbox" {if
                                $settings->rs__tfa__enable_users} checked=""{/if}/>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                    <p class="text_grey font_12">{$btr->rs__tfa__enable_users_desc|escape}</p>
                </div>
                {* Enable 2FA for admins *}
                <div class="activity_of_switch_item">
                    <div class="okay_switch clearfix">
                        <label class="switch_label">{$btr->rs__tfa__enable_admins_label|escape}</label>
                        <label class="switch switch-default">
                            <input class="switch-input" name="rs__tfa__enable_admins" value="1" type="checkbox" {if
                                $settings->rs__tfa__enable_admins} checked=""{/if}/>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                    <p class="text_grey font_12">{$btr->rs__tfa__enable_admins_desc|escape}</p>
                </div>
                {* Code / link TTL *}

                <div class="form-group">
                    <div class="heading_label">
                        <span>{$btr->rs__tfa__code_ttl_label|escape}</span>
                    </div>
                    <input class="form-control" type="number" min="1" max="60" name="rs__tfa__code_ttl"
                        placeholder="{$btr->rs__tfa__code_ttl_value|escape}" value="{$settings->rs__tfa__code_ttl}">
                    <p class="text_grey font_12 mt-1">{$btr->rs__tfa__code_ttl_desc|escape}</p>
                </div>
            </div>
        </div>

        <div class="col-xs-4">
            <div class="boxed">
                <div class="heading_box font_16 mb-2">{$btr->rs__tfa__reg_verify_title|escape}</div>

                <div class="activity_of_switch_item">
                    <div class="okay_switch clearfix">
                        <label class="switch_label">{$btr->rs__tfa__reg_verify_label|escape}</label>
                        <label class="switch switch-default">
                            <input class="switch-input" name="rs__tfa__verify_registration" value="1" type="checkbox"
                                {if $settings->rs__tfa__verify_registration} checked=""{/if}/>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                    <p class="text_grey font_12">{$btr->rs__tfa__reg_verify_desc|escape}</p>
                </div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="boxed">
                <div class="heading_box font_16 mb-2">{$btr->rs__tfa__passwordless_title|escape}</div>

                <div class="activity_of_switch_item">
                    <div class="okay_switch clearfix">
                        <label class="switch_label">{$btr->rs__tfa__passwordless_users|escape}</label>
                        <label class="switch switch-default">
                            <input class="switch-input" name="rs__tfa__passwordless_users" value="1" type="checkbox" {if
                                $settings->rs__tfa__passwordless_users} checked=""{/if}/>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                    <p class="text_grey font_12">{$btr->rs__tfa__passwordless_users_desc|escape}</p>
                </div>

                <div class="activity_of_switch_item">
                    <div class="okay_switch clearfix">
                        <label class="switch_label">{$btr->rs__tfa__passwordless_admins|escape}</label>
                        <label class="switch switch-default">
                            <input class="switch-input" name="rs__tfa__passwordless_admins" value="1" type="checkbox"
                                {if $settings->rs__tfa__passwordless_admins} checked=""{/if}/>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                    <p class="text_grey font_12">{$btr->rs__tfa__passwordless_admins_desc|escape}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 mt-1">
            <button type="submit" class="fn_step-15 btn btn_small btn_blue float-md-right">
                {include file='svg_icon.tpl' svgId='checked'}
                <span>{$btr->general_apply|escape}</span>
            </button>
        </div>
    </div>

</form>