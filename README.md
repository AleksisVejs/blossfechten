# Blossfechten Riga

Full-stack website for the Blossfechten Riga HEMA club — **Laravel 11 API** + **Vue 3 SPA**, MySQL, Sanctum auth, 5-language i18n (LV / EN / RU / CS / DE).

```
BlossfechtenRiga/
├── backend/          # Laravel 11 API
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

### API surface

**Public**
- `GET  /api/trainings` — upcoming sessions (also reports `is_registered` for the current user)
- `GET  /api/trainings/{id}` — single session
- `GET  /api/content/pages/{slug}` — `about`, `meyer`
- `GET  /api/content/members` — core members

**Auth (session cookie, Sanctum SPA)**
- `POST /api/auth/register` · `POST /api/auth/login` · `POST /api/auth/logout` · `GET /api/auth/me`
- `POST /api/trainings/{id}/register` — confirmed or auto-waitlist
- `DELETE /api/trainings/{id}/register`
- `GET  /api/me/registrations`

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
$status = $training->confirmedCount() >= $training->capacity
    ? 'waitlist' : 'confirmed';

Registration::updateOrCreate(
    ['user_id' => $user->id, 'training_session_id' => $training->id],
    ['status' => $status, 'note' => $request->input('note')]
);
```

**Frontend** — `useTrainingsStore.register` (frontend/src/stores/trainings.js):

```js
await api.post(`/api/trainings/${id}/register`, { note })
await this.fetch()
```

**UI** — `TrainingCard.vue` shows the "Register" button when authenticated and the seat is open, the "Waitlist" button when full, or a "Log in to register" link otherwise.

---

## 5. Production notes

- Build the frontend with `npm run build` and serve `frontend/dist` behind your reverse proxy.
- Point `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN` to your production host in `backend/.env`.
- Run `php artisan migrate --force` on deploy. `php artisan config:cache && php artisan route:cache` for production.
- The admin account password should be rotated on first use.

---

© Blossfechten Riga. Ars fechtens.
