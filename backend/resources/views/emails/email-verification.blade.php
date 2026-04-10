<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - CoursePro1</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="padding: 32px 40px 24px 40px; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #2563eb;">CoursePro1</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 600; color: #111827;">Verify Your Email</h2>

                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.5; color: #374151;">
                                Welcome to CoursePro1. Use the following verification code to confirm your email address:
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 24px; background-color: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                                        <span style="font-size: 36px; font-weight: 700; font-family: 'Courier New', Courier, monospace; color: #2563eb; letter-spacing: 8px;">{{ $code }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.5; color: #6b7280; text-align: center;">
                                This code expires at {{ $expiresAt->format('g:i A') }}
                            </p>

                            <p style="margin: 24px 0 0 0; font-size: 14px; line-height: 1.5; color: #6b7280;">
                                If you did not create an account, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 24px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px;">
                            <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                CoursePro1 - Online Learning Platform
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
