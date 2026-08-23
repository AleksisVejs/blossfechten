Blossfechten Riga — Fencer's Guild Latvia

{{ trans('messages.emails.new_event.heading', [], $locale) }}

{{ trans('messages.emails.new_event.greeting', ['name' => $recipientName], $locale) }}

{{ trans('messages.emails.new_event.intro', [], $locale) }}

{{ $eventTitle }}
{{ trans('messages.emails.new_event.when', [], $locale) }}: {{ $eventWhen }}
@if ($eventLocation)
{{ trans('messages.emails.new_event.where', [], $locale) }}: {{ $eventLocation }}
@endif
@if ($eventDescription)

{{ $eventDescription }}
@endif

{{ trans('messages.emails.new_event.cta', [], $locale) }}:
{{ $eventUrl }}

---
{{ trans('messages.emails.new_event.unsubscribe_hint', [], $locale) }}
{{ $unsubscribeUrl }}
