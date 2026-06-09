<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectString }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f19; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b0f19;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #111827; border: 1px solid #1f2937; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-image: linear-gradient(to right, #4f46e5, #7c3aed); padding: 40px 40px 30px 40px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">HRMS Enterprise</h1>
                            <p style="margin: 5px 0 0 0; color: #c084fc; font-size: 11px; font-weight: 600; letter-spacing: 1.5px; uppercase;">COMMUNICATION PORTAL</p>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <h2 style="margin: 0 0 20px 0; color: #ffffff; font-size: 18px; font-weight: 600;">{{ $subjectString }}</h2>
                            <div style="color: #94a3b8; font-size: 14px; line-height: 1.6; margin-bottom: 25px;">
                                {!! nl2br(e($contentString)) !!}
                            </div>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-top: 1px solid #1f2937; padding-top: 25px;">
                                <tr>
                                    <td style="color: #64748b; font-size: 12px;">
                                        This is an automated message sent from the HRMS Portal. Please do not reply directly to this email.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #090d16; padding: 20px; color: #475569; font-size: 11px; font-weight: 500;">
                            &copy; 2026 HRMS Enterprise. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
