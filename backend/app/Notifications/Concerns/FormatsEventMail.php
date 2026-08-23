<?php

namespace App\Notifications\Concerns;

use DateTimeInterface;

/**
 * Shared rendering rules for the three event mails: which language a member
 * reads, how to survive an admin leaving a translation blank, and how dates
 * and links are written.
 */
trait FormatsEventMail
{
    /** Locales the site ships translations for. */
    private const LOCALES = ['lv', 'en', 'ru', 'cs', 'de'];

    /**
     * Order to fall back through when an admin leaves a language blank.
     * English leads: a member reading in German is likelier to follow an
     * English title than a Latvian one.
     */
    private const CONTENT_FALLBACKS = ['en', 'lv', 'ru', 'cs', 'de'];

    private function localeFor(object $notifiable): string
    {
        $locale = $notifiable->locale ?? null;

        return in_array($locale, self::LOCALES, true) ? $locale : 'lv';
    }

    /**
     * Per-entity translations are json columns keyed by locale, and admins do
     * not always fill every language — fall back rather than mail a blank line.
     */
    private function localized(?array $values, string $locale): ?string
    {
        if (empty($values)) {
            return null;
        }

        foreach ([$locale, ...self::CONTENT_FALLBACKS] as $candidate) {
            $value = trim((string) ($values[$candidate] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function formatWhen(?DateTimeInterface $start, ?DateTimeInterface $end): string
    {
        if ($start === null) {
            return '';
        }

        if ($end === null) {
            return $start->format('d.m.Y H:i');
        }

        return $start->format('Y-m-d') === $end->format('Y-m-d')
            ? $start->format('d.m.Y H:i') . '-' . $end->format('H:i')
            : $start->format('d.m.Y H:i') . ' - ' . $end->format('d.m.Y H:i');
    }

    private function frontendUrl(): string
    {
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');

        return $frontend !== '' ? $frontend : rtrim((string) config('app.url', ''), '/');
    }
}
