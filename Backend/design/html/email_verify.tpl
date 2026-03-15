
    {if $user->rs_tfa_email_verified || !isset($user->rs_tfa_email_verified)}
        <span style="color:#27ae60;">&#10003; {$btr->rs__tfa__user_verified_yes|escape}</span>
    {else}
        <span style="color:#e67e22;">&#9888; {$btr->rs__tfa__user_verified_no|escape}</span>
        <label class="switch switch-default ml-1">
            <input class="switch-input" name="rs_tfa_verify_email" value="1" type="checkbox"/>
            <span class="switch-label"></span>
            <span class="switch-handle"></span>
        </label>
        <span class="text_grey font_12">{$btr->rs__tfa__user_activate_email|escape}</span>
        
    {/if}
