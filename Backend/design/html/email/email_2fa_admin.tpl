{$subject = {$btr->rs__tfa__email_2fa_subject} scope=global}

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{$btr->rs__tfa__email_2fa_subject}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
        /*<![CDATA[*/
        div, p, a, li, td, span { -webkit-text-size-adjust: none; }
        /*]]>*/
    </style>
</head>
<body style="margin:0; padding:0;">
<div style="padding:15px 5px; background-color:#efefef; height:100%;">
    <table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0 auto;">
        <tbody>
        <tr><td style="border:0; height:5px;" border="0"></td></tr>
        <tr>
            <td border="0" valign="top" align="left" style="border:0;">
                <div style="border-radius:4px; overflow:hidden;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0"
                           style="padding:15px 20px; background-color:#38c0f3;">
                        <tbody>
                        <tr>
                            <td align="left" style="border:0;">
                                <span style="font-family:trebuchet ms,helvetica,sans-serif; font-size:20px; font-weight:bold; color:#ffffff;">
                                    {$btr->rs__tfa__email_2fa_title}
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0"
                           style="padding:25px 20px; background-color:#ffffff;">
                        <tbody>
                        <tr>
                            <td align="center" style="border:0; padding-bottom:15px;">
                                <div style="font-family:trebuchet ms,helvetica,sans-serif; font-size:14px; color:#333333; line-height:1.6;">
                                    <p style="margin:0 0 15px;">{$btr->rs__tfa__email_2fa_attempt}</p>
                                    <p style="margin:0 0 20px;">{$btr->rs__tfa__email_2fa_code_desc} <strong>{$tfa_code_ttl_min} {$btr->rs__tfa__email_minutes}</strong>.</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="border:0; padding-bottom:20px;">
                                <div style="display:inline-block; background-color:#f4f4f4; border-radius:8px;
                                            padding:18px 40px; font-family:monospace; font-size:36px;
                                            font-weight:bold; letter-spacing:8px; color:#333333;">
                                    {$tfa_code}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="border:0;">
                                <p style="font-family:trebuchet ms,helvetica,sans-serif; font-size:12px;
                                          color:#999999; margin:0;">
                                    {$btr->rs__tfa__email_2fa_ignore}
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
