{if $rs_tfa_global_off}
    <div style="margin-top:12px;">
        <span style="font-size:13px;color:#999;">{$lang->rs__tfa__user_tfa_global_off}</span>
    </div>
{else}
    <div style="margin-top:12px;">
        <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;">
            <input type="checkbox" name="rs_tfa_disabled" value="1" {if $rs_tfa_user_disabled}checked{/if} style="margin-top:3px;flex-shrink:0;" />
            <span>
                <strong>{$lang->rs__tfa__user_tfa_disable_label}</strong>
                <br>
                <span style="font-size:13px;color:#666;">
                    {$lang->rs__tfa__user_tfa_disable_desc}
                </span>
            </span>
        </label>
    </div>
{/if}
