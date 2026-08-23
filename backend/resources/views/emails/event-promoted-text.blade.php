Blossfechten Riga — Fencer's Guild Latvia

{{ trans('messages.emails.promoted.heading', [], $locale) }}

{{ trans('messages.emails.promoted.greeting', ['name' => $recipientName], $locale) }}

{{ trans('messages.emails.promoted.intro', [], $locale) }}

{{ $eventTitle }}
{{ trans('messages.emails.promoted.when', [], $locale) }}: {{ $eventWhen }}
@if ($eventLocation)
{{ trans('messages.emails.promoted.where', [], $locale) }}: {{ $eventLocation }}
@endif
@if ($eventDescription)

{{ $eventDescription }}
@endif

{{ trans('messages.emails.promoted.cta', [], $locale) }}:
{{ $eventUrl }}

---
{{ trans('messages.emails.promoted.why', [], $locale) }}
