<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Paroles atjaunošana</title>
</head>
<body style="font-family: Georgia, 'Times New Roman', serif; color: #1c1a17; background: #f7f1e3; margin: 0; padding: 24px;">
<div style="max-width: 600px; margin: 0 auto; background: #fdfaf2; border: 1px solid #d6cdb6; padding: 24px;">
    <h2 style="margin-top: 0; font-family: Georgia, serif;">Paroles atjaunošana</h2>

    <p style="margin: 0 0 8px; font-weight: bold;">
        Sveiki, <span>{{ $recipientName }}</span>!
    </p>

    <p style="margin: 0 0 16px;">
        Saņemts pieprasījums atjaunot paroli jūsu kontam.
    </p>

    <div style="margin: 16px 0;">
        <a href="{{ $resetUrl }}"
           style="display: inline-block; background: #7a1f1f; color: #ffffff; padding: 12px 18px; border-radius: 4px; text-decoration: none; font-weight: bold;">
            Atjaunot paroli
        </a>
    </div>

    <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 24px 0 12px;">

    <p style="margin: 0; font-size: 12px; color: #6b6356;">
        Šī saite ir derīga {{ $minutes }} minūtes.
    </p>

    <p style="margin: 8px 0 0; font-size: 12px; color: #6b6356;">
        Ja jūs nepieprasījāt paroles atjaunošanu, varat ignorēt šo e-pastu.
    </p>
</div>
</body>
</html>

