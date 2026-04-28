<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact form submission</title>
</head>
<body style="font-family: Georgia, 'Times New Roman', serif; color: #1c1a17; background: #f7f1e3; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fdfaf2; border: 1px solid #d6cdb6; padding: 24px;">
        <h2 style="margin-top: 0; font-family: Georgia, serif;">New contact form submission</h2>

        <p style="margin: 0 0 4px;"><strong>Name:</strong> {{ $senderName }}</p>
        <p style="margin: 0 0 4px;"><strong>Email:</strong>
            <a href="mailto:{{ $senderEmail }}" style="color: #7a1f1f;">{{ $senderEmail }}</a>
        </p>
        @if ($ip)
            <p style="margin: 0 0 16px; color: #6b6356; font-size: 13px;"><strong>IP:</strong> {{ $ip }}</p>
        @endif

        <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 16px 0;">

        <p style="margin: 0 0 8px; font-weight: bold;">Message:</p>
        <div style="white-space: pre-wrap; line-height: 1.5;">{{ $messageBody }}</div>

        <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 24px 0 12px;">
        <p style="margin: 0; font-size: 12px; color: #6b6356;">
            Reply directly to this email to respond to {{ $senderName }}.
        </p>
    </div>
</body>
</html>
