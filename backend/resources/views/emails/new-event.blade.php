<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ trans('messages.emails.new_event.heading', [], $locale) }}</title>
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

        <h2 style="margin: 0 0 12px; font-family: Georgia, serif; font-size: 18px;">
            {{ trans('messages.emails.new_event.heading', [], $locale) }}
        </h2>

        <p style="margin: 0 0 8px; font-weight: bold;">
            {{ trans('messages.emails.new_event.greeting', ['name' => $recipientName], $locale) }}
        </p>

        <p style="margin: 0 0 16px; line-height: 1.5;">
            {{ trans('messages.emails.new_event.intro', [], $locale) }}
        </p>

        <div style="border: 1px solid #d6cdb6; background: #f7f1e3; padding: 18px 20px; margin: 0 0 20px;">
            <h3 style="margin: 0 0 12px; font-family: Georgia, serif; font-size: 17px; color: #7a1f1f;">
                {{ $eventTitle }}
            </h3>

            <p style="margin: 0 0 6px; font-size: 14px;">
                <strong>{{ trans('messages.emails.new_event.when', [], $locale) }}:</strong> {{ $eventWhen }}
            </p>

            @if ($eventLocation)
                <p style="margin: 0 0 6px; font-size: 14px;">
                    <strong>{{ trans('messages.emails.new_event.where', [], $locale) }}:</strong> {{ $eventLocation }}
                </p>
            @endif

            @if ($eventDescription)
                <p style="margin: 12px 0 0; font-size: 14px; line-height: 1.5; color: #3a352d;">
                    {{ $eventDescription }}
                </p>
            @endif
        </div>

        <div style="margin: 20px 0; text-align: center;">
            <a href="{{ $eventUrl }}"
               style="display: inline-block; background: #7a1f1f; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; font-family: Georgia, serif;">
                {{ trans('messages.emails.new_event.cta', [], $locale) }}
            </a>
        </div>

        <p style="margin: 0 0 8px; font-size: 13px; color: #6b6356; line-height: 1.5;">
            {{ trans('messages.emails.new_event.link_hint', [], $locale) }}
        </p>
        <p style="margin: 0 0 16px; font-size: 12px; word-break: break-all;">
            <a href="{{ $eventUrl }}" style="color: #7a1f1f;">{{ $eventUrl }}</a>
        </p>

        <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 24px 0 12px;">

        <p style="margin: 0; font-size: 12px; color: #6b6356; line-height: 1.5;">
            {{ trans('messages.emails.new_event.unsubscribe_hint', [], $locale) }}
            <a href="{{ $unsubscribeUrl }}" style="color: #7a1f1f;">{{ trans('messages.emails.new_event.unsubscribe', [], $locale) }}</a>
        </p>
    </div>
</body>
</html>
