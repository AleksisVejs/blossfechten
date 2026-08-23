<?php

return [
    'training' => [
        'cancelled' => 'Dieses Training wurde abgesagt.',
        'already_started' => 'Anmeldung geschlossen — das Training hat bereits begonnen.',
        'registered' => 'Sie sind angemeldet.',
        'waitlisted' => 'Das Training ist voll; Sie stehen auf der Warteliste.',
        'unregistered' => 'Ihre Anmeldung wurde storniert.',
    ],
    'emails' => [
        'new_event' => [
            'subject' => 'Neue Veranstaltung: :title',
            'fallback_title' => 'Neue Veranstaltung',
            'heading' => 'Eine neue Veranstaltung bei Blossfechten Riga',
            'greeting' => 'Hallo, :name!',
            'intro' => 'Dem Kalender wurde eine neue Veranstaltung hinzugefügt. Hier sind die Details.',
            'when' => 'Wann',
            'where' => 'Wo',
            'cta' => 'Ansehen und anmelden',
            'link_hint' => 'Falls die Schaltfläche nicht funktioniert, fügen Sie diesen Link in Ihren Browser ein:',
            'unsubscribe_hint' => 'Wenn Sie keine Benachrichtigungen über neue Veranstaltungen mehr erhalten möchten,',
            'unsubscribe' => 'melden Sie sich hier ab.',
        ],
    ],
    'notifications' => [
        'unsubscribed' => 'Sie erhalten keine E-Mails mehr über neue Veranstaltungen.',
        'invalid_unsubscribe_token' => 'Dieser Abmeldelink ist ungültig.',
    ],
];
