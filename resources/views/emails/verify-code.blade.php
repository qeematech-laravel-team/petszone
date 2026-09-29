<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:40px 0;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.05);">

                <!-- Header -->
                <tr>
                    <td style="background:#0d6efd; padding:20px; text-align:center; color:#ffffff;">
                        <h2 style="margin:0;">{{ setting('app_name') }}</h2>
                        <p style="margin:5px 0 0; font-size:14px;">Email Verification</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333333;">

                        <h3 style="margin-top:0;">Hi {{ $user->name ?? 'there' }},</h3>

                        <p>
                            To complete your registration, please use the verification code below:
                        </p>

                        <div style="
                            background:#f1f5ff;
                            border:1px dashed #0d6efd;
                            padding:20px;
                            text-align:center;
                            font-size:32px;
                            font-weight:bold;
                            letter-spacing:6px;
                            color:#0d6efd;
                            border-radius:8px;
                            margin:30px 0;
                        ">
                            {{ $code }}
                        </div>

                        <p>
                            This code will expire in <strong>10 minutes</strong>.
                        </p>

                        <p style="font-size:14px; color:#777;">
                            If you did not request this, please ignore this email.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f8f9fa; padding:15px; text-align:center; font-size:12px; color:#999;">
                        © {{ date('Y') }} {{ setting('app_name') }}. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
