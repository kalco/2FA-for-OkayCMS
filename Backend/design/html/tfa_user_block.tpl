<div class="mb-1">
    <div class="heading_label boxes_inline">{$btr->rs__tfa__user_disable_label}
        <i class="fn_tooltips" title="{$btr->rs__tfa__user_disable_desc|escape}">
            {include file='svg_icon.tpl' svgId='icon_tooltips'}
        </i>
    </div>
    <label class="switch switch-default">
        <input class="switch-input" name="rs_tfa_disabled" value='1' type="checkbox" {if
            $user->rs_tfa_disabled}checked=""{/if}/>
        <span class="switch-label"></span>
        <span class="switch-handle"></span>
    </label>
</div>