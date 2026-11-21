# AGENTS.md — Asemanpoosh (آسمانپوش) Cloth Shop

Persian (fa, RTL) Laravel storefront + admin panel. Follow sibling files; this is a working app — match its patterns.

## Stack & environment (verified)

- PHP 8.5 installed; `composer.json` requires `php:^8.3`, `laravel/framework:^13.17`, PHPUnit ^12, Pint ^1.27.
- Tailwind v4 via `@tailwindcss/vite`, Vite 8, vanilla JS in `resources/js/app.js`. No Alpine/Livewire/Inertia.
- `.env` uses `pgsql`; `phpunit.xml` forces `sqlite` `:memory:` with array cache/session/queue. Don't assume SQLite-specific SQL in dev.
- **This container has no `pdo_sqlite`**: `php artisan test` fails with "could not find driver (Connection: sqlite)". Tests exist but can't run here — smoke-check with the pgsql DB (tinker / render views) instead.
- No Jalali package: `App\Support\Jalali` + the `@jalali` Blade directive (registered in `AppServiceProvider`).
- Fonts resolve at runtime; Vite build warnings about `/fonts/...` are expected and harmless.

## Commands

- `composer run setup` = install, copy `.env`, `key:generate`, `migrate --force`, `npm install --ignore-scripts`, `npm run build`. Read the script before improvising setup.
- `php artisan dev` runs serve + queue:listen + pail + vite concurrently (framework multiplex); `npm run dev` is vite only.
- `npm run build` is required after JS/CSS changes **and after adding new Tailwind classes in Blade** — production CSS is generated at build time, so new classes are otherwise missing (or you get "Unable to locate file in Vite manifest").
- `php artisan migrate:fresh --seed --force` rebuilds the dev DB (drops tables). Seeded logins come from `ADMIN_PHONE`/`ADMIN_PASSWORD` and `TEST_USER_PHONE`/`TEST_USER_PASSWORD` (`.env.example`); debug OTP is `123456` when `APP_DEBUG=true`.
- Focused test: `php artisan test --compact tests/Feature/AdminUserManagementTest.php --filter=<name>`.
- Finish with `vendor/bin/pint --dirty --format agent`, then `php artisan view:cache`, then the narrowest tests that run.
- No CI workflows and no custom git hooks — verification is manual.

## Architecture facts that are easy to miss

- Auth aliases live in `bootstrap/app.php`: `auth.api` (Bearer, hashed `api_token`), `auth.active` (logs out banned users even with a live session), `admin`. All `/admin/*` routes require `auth` + `auth.active` + `admin`; profile and API routes require `auth.active` too. Guests redirect to `route('auth.login')`. Gate `manage-users` = active admin.
- ~30 FormRequests in `app/Http/Requests` (`Admin/`, `Api/` subnamespaces). Controllers use `$request->validated()` with strict allowlists — no `$request->all()` or inline `->validate()`. Uploads use UUID filenames with MIME/dimension/size validation.
- `User::$fillable` deliberately excludes `is_admin`, `is_active`, `otp_*`, `api_token`; privileged writes use `forceFill()`. `UserFactory::newModel()` overrides to forceFill these flags, so tests can use `->admin()`, `->inactive()` or `create(['is_admin' => true])`.
- Role is the `is_admin` boolean; there is no roles table. User management is at `/admin/users` (`Admin\UserController`); block self-delete/self-demote/self-ban and last-active-admin changes with `User::isLastActiveAdmin()`.
- Named rate limiters in `AppServiceProvider`: `global`, `login`, `otp`, `password-reset`, `orders`, `receipts`. Apply them to new auth/order/receipt routes.
- Payments: `Transaction` belongs to `Order` + `User`; methods `gateway` / `card_to_card`. Totals are always recomputed from the server-side cart — never trust a client-supplied `total`. Card flow: order → `pending_transaction` → receipt upload → `pending_verification` → admin approve (`processing`) / reject (back to `pending_transaction`), wrapped in `DB::transaction()`. The payment callback is POST, throttled and replay-guarded.
- `OrderPolicy` / `AddressPolicy` enforce ownership (registered in `AuthServiceProvider`); call `$this->authorize(...)` and scope lookups to the current user rather than raw IDs.
- View composers in `AppServiceProvider`: `layouts.admin` gets `$pendingOrdersCount` (only `status = 'pending'`) and `$activeCampaign`; `layouts.app` gets `$cartCount`. Dashboard metrics are computed in `Admin\DashboardController`.
- Receipts: `storage/app/public/receipts` through the `public/storage` symlink; settings in `config('payment.receipts.*')` (`RECEIPT_DISK`, `RECEIPT_MAX_KB`). Card destination/account/bank come from `config('payment.card_to_card')`. Product images use the separate `products` disk (`public/images/products`).
- `CardToCardOrderSeeder` generates sample receipts with GD `imagestring()` — Latin text only, since GD lacks Persian shaping.

## UI conventions

- RTL everywhere (`dir="rtl"`), Vazirmatn font. Storefront views extend `layouts.app`; admin views extend `layouts.admin`. Sticky admin header is `z-30 bg-white/95 backdrop-blur-md`.
- Tailwind theme tokens are CSS vars in `resources/css/app.css` (`brand*`, `surface`, `canvas`, `ink`, `muted`, `line`, `accent*`); stock Tailwind colors (e.g. `sky-500`) are also available.
- Money: `number_format()` then the per-view `$toPersian` / `$toman` digit conversion. Persian status/gateway labels are `const` maps on models (`Order::STATUSES`, `Transaction::STATUSES/PAYMENT_METHODS`) rendered via helper methods; add new statuses to every map that iterates them or views break.
- Models use `#[Fillable(...)]` / `#[Hidden(...)]` attributes (not `$fillable` properties), explicit `casts()`, and typed relation methods with PHPDoc returns.
- JS: each feature is an `initX()` function in `resources/js/app.js`, called in the bottom init block; interactivity hooks are `data-*`.
- Zero horizontal scroll below `md` is a hard rule for all data tables (admin orders/carts/users, `profile/orders`, `profile/dashboard`): one unified `<table>` using `hidden md:table-cell`, never `overflow-x-auto` + `min-w-[...]`. Mobile shows 3 compact columns with actions pinned to the RTL left edge. `admin/users` uses `table-fixed md:table-auto` with `w-[38%]/[34%]/[28%]` columns.

## Tooling / meta

- Laravel Boost MCP is configured (`.mcp.json`, `opencode.json`); prefer `database-schema` / `database-query` / `search-docs` over raw SQL or guessed APIs. `.ai/rules` does NOT exist (ignore the reference in `CLAUDE.md`). Skills are in both `.agents/skills` and `.claude/skills`. `CLAUDE.md` holds Boost's generic PHP/PHPUnit rules; this file keeps only repo-specific facts.
- Do not add dependencies or create documentation files without explicit user approval.
