# Blossfechten Riga

Full-stack website for the Blossfechten Riga historical fencing club — **Laravel 13 API** + **Vue 3 SPA**, MySQL, Sanctum auth, 5-language i18n (LV / EN / RU / CS / DE).

```
BlossfechtenRiga/
├── backend/          # Laravel 13 API
└── frontend/         # Vue 3 + Vite SPA
```

---

## Prerequisites

- PHP 8.2+ with `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip`
- Composer 2.x
- Node 20+ / npm 10+
- MySQL 8 (you already have MySQL Workbench)

---

## 1. Database

Create a database called `blossfechten_riga` in MySQL Workbench (utf8mb4 / utf8mb4_unicode_ci) using the built-in root user. Update `backend/.env` if your MySQL password is not empty:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blossfechten_riga
DB_USERNAME=root
DB_PASSWORD=<your-password>
```

## 2. Backend (Laravel)

```bash
cd backend
composer install                 # already run once, safe to re-run
php artisan migrate --seed       # creates schema and seeds members, pages, trainings, users
php artisan serve                # http://localhost:8000
```

Seeded accounts:

| Role   | Email                       | Password       |
|--------|-----------------------------|----------------|
| admin  | admin@blossfechten.lv       | `ChangeMe!2026`|
| member | member@blossfechten.lv      | `member123`    |

Both are seeded **email-verified**. That matters: notification fan-out skips
unverified addresses, so an unverified admin silently receives nothing. Accounts
made with `php artisan user:create` are verified for the same reason — there is
no verification mail on that path to click.

If you have an account from before this was fixed, verify it once:

```bash
php artisan user:verify admin@blossfechten.lv
```

### API surface

**Public**
- `GET  /api/trainings` — upcoming sessions (also reports `is_registered` and `registration_status` for the current user)
- `GET  /api/trainings/{id}` — single session, with `confirmed_count`. Deliberately carries no registration rows: this route is public and those rows hold member names, ranks and their free-text notes. Admins read the attendee list from `/api/admin/trainings/{id}/registrations`.
- `GET  /api/content/pages/{slug}` — `about`, `meyer`
- `GET  /api/content/members` — core members

- `GET|POST /api/notifications/unsubscribe/{token}` — one-click opt-out of event announcements (public by design)

**Auth (session cookie, Sanctum SPA)**
- `POST /api/auth/register` · `POST /api/auth/login` · `POST /api/auth/logout` · `GET /api/auth/me`
- `POST /api/auth/email/verify/resend` — public; re-sends verification to an address that registered but never confirmed
- `POST /api/trainings/{id}/register` — confirmed or auto-waitlist
- `DELETE /api/trainings/{id}/register` — releases the seat and promotes the next member waiting
- `GET  /api/me/registrations`

Registration refuses any address already on file, verified or not, and never
logs the caller into an existing account. Someone who never received their
verification mail uses the resend endpoint above, which authenticates nobody and
answers identically whether or not the address exists.

Login, registration and password reset are rate limited to 5 requests a minute
per IP; the endpoints that send mail to an arbitrary address are held to 3. The
limiters are named (`auth`, `auth-mail`) and defined in `AppServiceProvider` —
they key on the client address rather than the session, because Laravel's stock
`throttle:n,m` keys on the authenticated user id, which a successful
registration changes on every request.

**Admin** (role = admin)
- `GET|POST|PUT|DELETE /api/admin/trainings[/{id}]`
- `GET /api/admin/trainings/{id}/registrations`
- `GET /api/admin/users` · `PUT /api/admin/users/{id}`

CSRF is handled via `/sanctum/csrf-cookie` (the Vue axios client calls it automatically on mutating requests).

Locale selection is driven by the `X-Locale` header (the SPA sets it from the current UI locale). Laravel translation files live in `backend/lang/{lv,en,ru,cs,de}/messages.php`. Per-entity translations (training titles/descriptions, page bodies, member bios) are stored as `json` columns and keyed by locale.

---

## 3. Frontend (Vue)

```bash
cd frontend
npm install           # already run once
npm run dev           # http://localhost:5173
```

Stack:

- Vue 3 + Vite + Vue Router + Pinia
- `vue-i18n@9` — 5 locales (`src/i18n/locales/{lv,en,ru,cs,de}.js`)
- Tailwind CSS 3 with a parchment/ink/oxblood palette and Cormorant Garamond + UnifrakturMaguntia typography
- Axios with `withCredentials` + Sanctum CSRF interceptor (`src/lib/api.js`)

### Pages

| Route        | Component           | Notes                                      |
|--------------|---------------------|--------------------------------------------|
| `/`          | Home                | Hero, three pillars, Meyer quote           |
| `/about`     | About               | Pulls `pages/about` from API               |
| `/meyer`     | Meyer               | Five-level curriculum from i18n            |
| `/schedule`  | Schedule            | Regular schedule + upcoming sessions + register/unregister |
| `/members`   | Members             | Core members with locale-aware bios        |
| `/contact`   | Contact             | Phone, email, socials                      |
| `/login`     | Login               | Session auth                               |
| `/register`  | Register            | Includes preferred language                |
| `/dashboard` | Dashboard           | Logged-in user's registrations             |
| `/admin`     | Admin               | CRUD trainings, assign user roles          |

### Auth flow (SPA with Sanctum cookies)

1. Axios calls `GET /sanctum/csrf-cookie` on first mutation → sets `XSRF-TOKEN` cookie.
2. `POST /api/auth/login` establishes the session cookie.
3. `GET /api/auth/me` restores the session on app start (called from `App.vue` on mount).
4. All protected routes are gated in `src/router/index.js` via route meta `auth` / `admin`.

### i18n flow

- `src/i18n/index.js` picks `localStorage.locale` → browser language → `en`.
- `LangSwitcher.vue` calls `setLocale(code)` which updates vue-i18n, persists to `localStorage`, and sets `<html lang>`.
- Axios sends `X-Locale` on every request; Laravel's `SetLocale` middleware picks it up so server-side messages are translated too.

---

## 4. Example: Training registration end-to-end

**Backend** — `TrainingController@register` (backend/app/Http/Controllers/Api/TrainingController.php):

```php
$locked = TrainingSession::whereKey($training->id)->lockForUpdate()->firstOrFail();

$taken = $locked->registrations()
    ->where('status', 'confirmed')
    ->where('user_id', '!=', $user->id)
    ->count();

Registration::updateOrCreate(
    ['user_id' => $user->id, 'training_session_id' => $locked->id],
    [
        'status' => $taken >= $locked->capacity ? 'waitlist' : 'confirmed',
        'note' => $request->input('note'),
    ]
);
```

The read and the write sit inside a transaction with the session row locked, so
two people clicking Register at the same moment cannot both be given the last
seat. A member re-posting a registration to change their note is excluded from
the seat count, so it cannot demote them to the waitlist of a session they are
already confirmed for.

**Frontend** — `useTrainingsStore.register` (frontend/src/stores/trainings.js):

```js
const { data } = await api.post(`/api/trainings/${id}/register`, { note })
await this.fetch()
return data          // carries the localised "seat" vs "waitlist" message
```

**UI** — `TrainingCard.vue` shows the "Register" button when authenticated and the seat is open, the "Waitlist" button when full, or a "Log in to register" link otherwise. Once registered it also states which of the two the member actually holds — a confirmed seat, or a place in the queue.

### The waiting list

`GET /api/trainings` reports `registration_status` (`confirmed` / `waitlist` /
`null`) alongside `is_registered`, because the two are not the same thing and a
member who only holds a place must not turn up expecting to train.

Seats are handed on automatically. `TrainingSession::promoteFromWaitlist()`
fills every free seat from the waiting list oldest-first, and is called whenever
seats open:

- when a member gives up a seat (`DELETE /api/trainings/{id}/register`)
- when an admin raises the capacity of a session

Both callers hold a row lock while they do it, so concurrent releases cannot
promote past the capacity. Cancelled sessions promote nobody.

Anyone who moves up is emailed — see **A place has come free** below. A seat
nobody was told about is a seat nobody turns up for.

---

## 5. Event announcement emails

When an admin saves a training session with **Notify members by email** ticked, every
member who opted in gets a one-off announcement in their own language.

- `AnnounceTrainingSession` (queued) fans out to `User::eventSubscribers()` — opted in
  **and** email-verified — in chunks of 100.
- The job claims the session with a conditional `whereNull('notified_at')` update, so a
  retry, a double dispatch, or a later edit can never mail the club twice about the same
  event. The admin UI disables the checkbox once `notified_at` is set.
- `NewEventNotification` renders `emails.new-event` (+ plain-text twin) using the
  recipient's `locale`, falling back to English then Latvian when an admin leaves a
  language blank.
- Every message carries `List-Unsubscribe` / `List-Unsubscribe-Post` headers plus a
  visible unsubscribe link pointing at `/unsubscribe?token=…`. Members can also toggle
  the preference on `/profile`.

Announcements are sent from the global `MAIL_FROM_ADDRESS`, exactly like the contact and
verification mail. Keep it on the club domain — DMARC for `blossfechtenriga.com` uses
strict alignment (`adkim=s; aspf=s`), so a From address on any other domain fails the
check:

```env
MAIL_FROM_ADDRESS="hello@blossfechtenriga.com"
```

### Reminders, change notices and waitlist promotions

Three further mails hang off the same machinery. The first two have their own toggle in
the admin form; the third fires on its own, because it reports something that already
happened rather than something the admin chose to send:

**Day-before reminder.** `send_reminder` is a column on the session, ticked by default.
`events:send-reminders` runs every fifteen minutes from the scheduler and queues a
reminder once a session comes within 24 hours of starting. Only members holding a
*confirmed* seat are mailed — a waitlisted member has nothing to turn up for. `reminded_at`
records the send, so it goes out exactly once.

Two wrinkles worth knowing:

- A session announced within the last hour is skipped, so an event created the day before
  it runs does not fire an announcement and a reminder minutes apart.
- Moving an already-reminded session back beyond the 24-hour window clears `reminded_at`,
  so members get a fresh reminder for the new date rather than silence.

**Change notice.** `notify_changes` is a per-request flag, not a column — it is unticked
every time the form opens, so it only ever fires when the admin deliberately asks. It mails
everyone holding a seat *or* a waitlist place, and only when something worth reading
changed: time, location, title, description, or cancellation. Editing capacity alone sends
nothing. Cancelling gets its own subject line, a banner, and no call to action; putting a
cancelled session back on gets the opposite banner.

**A place has come free.** When a seat is released — a member cancels, or an admin raises
the capacity — `TrainingSession::promoteFromWaitlist()` moves people up and
`NotifyPromotedFromWaitlist` mails the ones who moved. It is dispatched *after* the
promoting transaction commits, never inside it: a rolled back promotion that had already
queued mail would tell a member they hold a seat that does not exist.

The job carries registration ids rather than user ids, because the same member may be
waiting on several sessions and only the row that moved is worth mailing about. It
re-checks before sending, exactly as the reminder does — between the promotion and the
queue draining, the session may have been cancelled or the seat handed straight back, and
"you have a seat" arriving after either is worse than silence.

There is no admin toggle for this one. It is the only mail a waitlisted member genuinely
cannot do without, and a promotion nobody is told about produces an empty seat.

All three are transactional — they follow from the member's own registration rather than the
announcement subscription — so they carry no unsubscribe link, ignore the `notify_new_events`
opt-out, and omit the `List-Unsubscribe` headers. The way out of a reminder is to give up
the seat.

### Trying it without mailing the club

```bash
php artisan events:test-announcement aleksis.vejs@gmail.com
php artisan events:test-announcement aleksis.vejs@gmail.com --type=reminder
php artisan events:test-announcement aleksis.vejs@gmail.com --type=promoted
php artisan events:test-announcement aleksis.vejs@gmail.com --type=changed
php artisan events:test-announcement aleksis.vejs@gmail.com --type=cancelled
php artisan events:test-announcement aleksis.vejs@gmail.com --locale=de   # preview another language
```

Sends one sample mail, immediately, to that address only. The sample session is never
persisted, so nothing appears on the calendar and no real event is marked as announced or
reminded. On the announcement type, if the address belongs to a member the mail carries
their real unsubscribe token — clicking it genuinely opts them out. `--locale` only changes
this one preview; it does not alter the member's stored language.

To check what the scheduler would pick up without queueing anything:

```bash
php artisan events:send-reminders --pretend
```

### Queue worker (required)

The announcements are queued, and shared cPanel hosting has no long-running worker, so
the scheduler drains the queue. Add **one** cron entry in cPanel, matching the interval
and PHP path already used by the other apps on this account:

```
*/10 * * * * cd /home2/riginspe/blossfechten/backend && /usr/local/bin/php artisan schedule:run >> ~/blossfechten-schedule.log 2>&1
```

`routes/console.php` schedules two things: `queue:work --queue=mail,default
--stop-when-empty --max-time=300` on every run, guarded by a 10-minute overlap lock, and
`events:send-reminders` every fifteen minutes. Announcements therefore go out
within ten minutes of the admin saving the event. Without this cron, queued mail sits in
the `jobs` table and is never delivered — the admin UI will still report success, so
verify after deploying:

```bash
php artisan queue:work --queue=mail,default --stop-when-empty
```

---

## 6. Analytics and cookie consent

Google Analytics is **not** loaded until the visitor accepts it. `src/analytics.js`
keeps the choice in `localStorage` under `cookie-consent` and only injects the
gtag script on `granted`; `CookieConsent.vue` is the banner. Declining is exactly
as easy as accepting and is first in DOM order.

Two consequences worth knowing:

- With no `VITE_GA_MEASUREMENT_ID` set — which is every `npm run dev` — there is
  nothing to consent to, so the banner does not appear at all. To see it locally,
  put a placeholder id in `frontend/.env.local`.
- Page views that happen before a choice is made are never replayed. Accepting
  counts the page the visitor is on and nothing before it.

## 7. Uploaded media

Admin uploads land in `backend/public/img/` and are served from the API origin.
The stored filename takes its extension from the **sniffed MIME type**, never
from the uploaded filename — `image` validation passes on content, so a genuine
GIF called `x.php` would otherwise be written into a web-served directory as
`<uuid>.php`. `backend/public/img/.htaccess` is the second lock on that door:
it disables the PHP engine, drops the script handlers, and refuses to serve
executable extensions at all. SVG is rejected because it is an executable
document in a browser and this origin shares the session cookie domain.

## 8. Production notes

- Build the frontend with `npm run build` and serve `frontend/dist` behind your reverse proxy.
- Point `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN` to your production host in `backend/.env`.
- Run `php artisan migrate --force` on deploy. `php artisan config:cache && php artisan route:cache` for production.
- The admin account password should be rotated on first use.

---

© Blossfechten Riga. Ars fechtens.
