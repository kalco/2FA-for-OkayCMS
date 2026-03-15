{$subject = {$btr->rs__tfa__email_magic_subject} scope=global}

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{$btr->rs__tfa__email_magic_subject}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
        div, p, a, li, td, span { -webkit-text-size-adjust: none; }
    </style>
</head>
<body style="margin:0; padding:0;">
<div style="padding:15px 5px; background-color:#efefef; height:100%;">
    <table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0 auto;">
        <tbody>
        <tr><td style="border:0; height:5px;"></td></tr>
        <tr>
            <td valign="top" align="left" style="border:0;">
                <div style="border-radius:4px; overflow:hidden;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0"
                           style="padding:15px 20px; background-color:#38c0f3;">
                        <tbody>
                        <tr>
                            <td align="left" style="border:0;">
                                <span style="font-family:trebuchet ms,helvetica,sans-serif; font-size:20px; font-weight:bold; color:#ffffff;">
                                    {$btr->rs__tfa__email_magic_title}
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0"
                           style="padding:25px 20px; background-color:#ffffff;">
                        <tbody>
                        <tr>
                            <td align="center" style="border:0; padding-bottom:20px;">
                                <p style="font-family:trebuchet ms,helvetica,sans-serif; font-size:14px; color:#333333; line-height:1.6; margin:0 0 20px;">
                                    {$btr->rs__tfa__email_magic_desc} <strong>{$tfa_code_ttl_min} {$btr->rs__tfa__email_minutes}</strong>.
                                </p>
                                <a href="{$magic_login_url|escape}"
                                   style="display:inline-block; background-color:#38c0f3; color:#ffffff;
                                          text-decoration:none; padding:14px 32px; border-radius:4px;
                                          font-family:trebuchet ms,helvetica,sans-serif; font-size:15px; font-weight:bold;">
                                    {$btr->rs__tfa__email_magic_btn}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="border:0; padding-top:10px;">
                                <p style="font-family:trebuchet ms,helvetica,sans-serif; font-size:11px; color:#aaaaaa; margin:0;">
                                    {$btr->rs__tfa__email_magic_copy_link}<br>
                                    <span style="word-break:break-all;">{$magic_login_url|escape}</span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="border:0; padding-top:16px;">
                                <p style="font-family:trebuchet ms,helvetica,sans-serif; font-size:12px; color:#999999; margin:0;">
                                    {$btr->rs__tfa__email_magic_ignore}
                                </p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
