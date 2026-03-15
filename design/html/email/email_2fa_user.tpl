{$subject = {$lang->rs__tfa__email_2fa_subject} scope=global}

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{$lang->rs__tfa__email_2fa_subject}</title>
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">

    {include "email/email_head.tpl"}
</head>

<body>
<div class="es-wrapper-color">
    <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td class="es-p0t es-p0b" valign="center">

                {include "email/email_header.tpl"}

                <table class="es-content" cellspacing="0" cellpadding="0" align="center">
                    <tbody>
                    <tr>
                        <td align="center">
                            <table class="es-content-body" width="600" cellspacing="0" cellpadding="0" bgcolor="#ffffff" align="center">
                                <tbody>
                                <tr>
                                    <td class="es-p10t es-p10b es-p20r es-p20l" align="center">
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr>
                                                <td valign="top" align="center">
                                                    <table width="100%" cellspacing="0" cellpadding="0">
                                                        <tbody>
                                                        <tr>
                                                            <td class="es-p10t es-p15b" align="center">
                                                                <h1>{$lang->rs__tfa__email_2fa_title}</h1>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="es-p5t es-p5b es-p40r es-p40l" align="center">
                                                                <p>{$lang->rs__tfa__email_2fa_signing_in} <strong>{$settings->site_name}</strong>.</p>
                                                                <p>{$lang->rs__tfa__email_2fa_code_desc} <strong>{$tfa_code_ttl_min} {$lang->rs__tfa__email_minutes}</strong>.</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="es-p10t es-p10b" align="center">
                                                                <table cellspacing="0" cellpadding="0" align="center">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td align="center" style="background-color:#f4f4f4; border-radius:8px; padding:20px 40px;">
                                                                            <span style="font-size:36px; font-weight:bold; letter-spacing:8px; color:#333333; font-family:monospace;">{$tfa_code}</span>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="es-p10t es-p5b es-p40r es-p40l" align="center">
                                                                <p style="color:#999999; font-size:12px;">{$lang->rs__tfa__email_2fa_ignore}</p>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>

                {include "email/email_footer.tpl"}

            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
