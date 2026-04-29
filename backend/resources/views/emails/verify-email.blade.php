<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Apstipriniet savu e-pasta adresi</title>
</head>
<body style="font-family: Georgia, 'Times New Roman', serif; color: #1c1a17; background: #f7f1e3; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fdfaf2; border: 1px solid #d6cdb6; padding: 32px 24px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <p style="margin: 0; font-family: Arial, sans-serif; font-size: 11px; letter-spacing: 4px; color: #b08a3e; text-transform: uppercase;">
                Fencer's Guild Latvia
            </p>
            <h1 style="margin: 6px 0 0; font-family: Georgia, serif; font-size: 22px; color: #1c1a17;">
                Blossfechten Riga
            </h1>
            <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 18px auto 0; width: 33%;">
        </div>

        <h2 style="margin: 0 0 12px; font-family: Georgia, serif; font-size: 18px;">Apstipriniet savu e-pasta adresi</h2>

        <p style="margin: 0 0 8px; font-weight: bold;">Sveiki, {{ $recipientName }}!</p>

        <p style="margin: 0 0 16px; line-height: 1.5;">
            Paldies, ka pievienojāties Blossfechten Riga skolai. Lūdzu, apstipriniet savu e-pasta adresi, noklikšķinot uz pogas zemāk.
        </p>

        <div style="margin: 20px 0; text-align: center;">
            <a href="{{ $verifyUrl }}"
               style="display: inline-block; background: #7a1f1f; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; font-family: Georgia, serif;">
                Apstiprināt e-pastu
            </a>
        </div>

        <p style="margin: 0 0 8px; font-size: 13px; color: #6b6356; line-height: 1.5;">
            Ja poga neatveras, ielīmējiet šo saiti pārlūkā:
        </p>
        <p style="margin: 0 0 16px; font-size: 12px; word-break: break-all;">
            <a href="{{ $verifyUrl }}" style="color: #7a1f1f;">{{ $verifyUrl }}</a>
        </p>

        <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 24px 0 12px;">

        <p style="margin: 0; font-size: 12px; color: #6b6356;">
            Šī saite ir derīga {{ $minutes }} minūtes.
        </p>
        <p style="margin: 8px 0 0; font-size: 12px; color: #6b6356;">
            Ja jūs neveidojāt kontu, varat ignorēt šo e-pastu.
        </p>
    </div>
</body>
</html>
