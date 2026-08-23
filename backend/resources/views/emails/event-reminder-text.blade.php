Blossfechten Riga — Fencer's Guild Latvia

{{ trans('messages.emails.reminder.heading', [], $locale) }}

{{ trans('messages.emails.reminder.greeting', ['name' => $recipientName], $locale) }}

{{ trans('messages.emails.reminder.intro', [], $locale) }}

{{ $eventTitle }}
{{ trans('messages.emails.reminder.when', [], $locale) }}: {{ $eventWhen }}
@if ($eventLocation)
{{ trans('messages.emails.reminder.where', [], $locale) }}: {{ $eventLocation }}
@endif
@if ($eventDescription)

{{ $eventDescription }}
@endif

{{ trans('messages.emails.reminder.cta', [], $locale) }}:
{{ $eventUrl }}

---
{{ trans('messages.emails.reminder.why', [], $locale) }}
