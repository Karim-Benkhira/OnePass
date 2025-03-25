<!DOCTYPE html>
<html>
<head>
    <title>New Device Login Attempt</title>
</head>
<body>
    <h2>Hello {{ $user->name }},</h2>
    <p>We detected a login attempt from a new device.</p>
    <p>If this was you, please verify your device by clicking the link below:</p>
    <p><a href="{{ $verificationUrl }}" style="padding:10px 20px; background-color:#28a745; color:white; text-decoration:none;">Verify Device</a></p>
    <p>If you did not attempt this login, please ignore this email and secure your account.</p>
    <p>Thank you,</p>
    <p><strong>OnePass Security Team</strong></p>
</body>
</html>
