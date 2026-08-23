<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ trans($isCancelled ? 'messages.emails.changed.heading_cancelled' : 'messages.emails.changed.heading', [], $locale) }}</title>
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
            {{ trans($isCancelled ? 'messages.emails.changed.heading_cancelled' : 'messages.emails.changed.heading', [], $locale) }}
        </h2>

        <p style="margin: 0 0 8px; font-weight: bold;">
            {{ trans('messages.emails.changed.greeting', ['name' => $recipientName], $locale) }}
        </p>

        <p style="margin: 0 0 16px; line-height: 1.5;">
            {{ trans($isCancelled ? 'messages.emails.changed.intro_cancelled' : 'messages.emails.changed.intro', ['title' => $eventTitle], $locale) }}
        </p>

        @if ($isCancelled)
            <div style="border: 1px solid #7a1f1f; background: #f6e7e7; padding: 14px 18px; margin: 0 0 20px; text-align: center;">
                <p style="margin: 0; font-family: Georgia, serif; font-size: 16px; color: #7a1f1f; font-weight: bold;">
                    {{ trans('messages.emails.changed.cancelled_banner', [], $locale) }}
                </p>
            </div>
        @elseif ($isReinstated)
            <div style="border: 1px solid #4b6b3a; background: #eef2e6; padding: 14px 18px; margin: 0 0 20px; text-align: center;">
                <p style="margin: 0; font-family: Georgia, serif; font-size: 16px; color: #3f5a30; font-weight: bold;">
                    {{ trans('messages.emails.changed.reinstated_banner', [], $locale) }}
                </p>
            </div>
        @endif

        <div style="border: 1px solid #d6cdb6; background: #f7f1e3; padding: 18px 20px; margin: 0 0 20px;">
            <h3 style="margin: 0 0 12px; font-family: Georgia, serif; font-size: 17px; color: #7a1f1f;">
                {{ $eventTitle }}
            </h3>

            @forelse ($changes as $change)
                <p style="margin: 0 0 10px; font-size: 14px; line-height: 1.5;">
                    <strong>{{ $change['label'] }}:</strong><br>
                    @if ($change['from'])
                        <span style="text-decoration: line-through; color: #8a8175;">{{ $change['from'] }}</span>
                        <span style="color: #6b6356;">&nbsp;&rarr;&nbsp;</span>
                    @endif
                    <span style="color: #1c1a17;">{{ $change['to'] }}</span>
                </p>
            @empty
                <p style="margin: 0 0 6px; font-size: 14px;">
                    <strong>{{ trans('messages.emails.changed.fields.when', [], $locale) }}:</strong> {{ $eventWhen }}
                </p>
                @if ($eventLocation)
                    <p style="margin: 0; font-size: 14px;">
                        <strong>{{ trans('messages.emails.changed.fields.location', [], $locale) }}:</strong> {{ $eventLocation }}
                    </p>
                @endif
            @endforelse
        </div>

        @unless ($isCancelled)
            <div style="margin: 20px 0; text-align: center;">
                <a href="{{ $eventUrl }}"
                   style="display: inline-block; background: #7a1f1f; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; font-family: Georgia, serif;">
                    {{ trans('messages.emails.changed.cta', [], $locale) }}
                </a>
            </div>
        @endunless

        <hr style="border: none; border-top: 1px solid #d6cdb6; margin: 24px 0 12px;">

        <p style="margin: 0; font-size: 12px; color: #6b6356; line-height: 1.5;">
            {{ trans('messages.emails.changed.why', [], $locale) }}
        </p>
    </div>
</body>
</html>
