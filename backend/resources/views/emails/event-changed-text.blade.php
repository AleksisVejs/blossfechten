Blossfechten Riga — Fencer's Guild Latvia

{{ trans($isCancelled ? 'messages.emails.changed.heading_cancelled' : 'messages.emails.changed.heading', [], $locale) }}

{{ trans('messages.emails.changed.greeting', ['name' => $recipientName], $locale) }}

{{ trans($isCancelled ? 'messages.emails.changed.intro_cancelled' : 'messages.emails.changed.intro', ['title' => $eventTitle], $locale) }}
@if ($isCancelled)

*** {{ trans('messages.emails.changed.cancelled_banner', [], $locale) }} ***
@elseif ($isReinstated)

*** {{ trans('messages.emails.changed.reinstated_banner', [], $locale) }} ***
@endif

{{ $eventTitle }}
@forelse ($changes as $change)
{{ $change['label'] }}: @if ($change['from']){{ $change['from'] }} -> @endif{{ $change['to'] }}

@empty
{{ trans('messages.emails.changed.fields.when', [], $locale) }}: {{ $eventWhen }}
@if ($eventLocation)
{{ trans('messages.emails.changed.fields.location', [], $locale) }}: {{ $eventLocation }}
@endif
@endforelse
@unless ($isCancelled)

{{ trans('messages.emails.changed.cta', [], $locale) }}:
{{ $eventUrl }}
@endunless

---
{{ trans('messages.emails.changed.why', [], $locale) }}
