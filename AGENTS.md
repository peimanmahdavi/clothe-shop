# AGENTS.md — Asemanpoosh (آسمان‌پوش) Cloth Shop

Persian (fa, RTL) Laravel e-commerce app + admin panel. When in doubt, follow
sibling files; this is a real, working app—match its patterns.

## Stack (verified)

- PHP 8.5 (`composer.json` requires ^8.3), Laravel framework ^13.17.
- Tailwind CSS v4 via `@tailwindcss/vite`; Vite ^8; vanilla JS in `resources/js/app.js` (NO Alpine/Livewire).
- DB: `.env` uses `pgsql`; tests use `sqlite` `:memory:` (`phpunit.xml`). Don't assume SQLite-specific SQL.
- No Jalali package: Persian dates come from `App\Support\Jalali` + the `@jalali` Blade directive.
- Tests: PHPUnit ^12. Only `tests/Feature/ExampleTest.php` exists—coverage is thin, add tests as you go.

## Commands

```bash
composer install && npm install        # setup (see composer "setup" script)
php artisan dev                         # runs serve + queue:listen + pail + vite concurrently
php artisan serve                       # server only
npm run dev                             # vite only
npm run build                           # REQUIRED after JS/CSS changes, else Vite manifest errors
php artisan migrate --seed --force      # apply schema + seed
php artisan migrate:fresh --seed --force # rebuild DB (drops all tables)
php artisan test --compact <path> --filter=name   # tests (sqlite :memory:)
vendor/bin/pint --dirty --format agent  # format modified PHP before finishing
```

If a frontend change doesn't appear or you see "Unable to locate file in Vite manifest", run `npm run build`.

## Conventions (match existing code)

- Models declare `#[Fillable([...])]` / `#[Hidden([...])]` PHP attributes, NOT `$fillable`/`$hidden` properties.
- Every model with timestamps casts explicitly; use typed relation methods with PHPDoc (`/** @return HasMany<Transaction, $this> */`).
- Persian display strings (status labels, gateways) live as `const` maps on the model (e.g. `Order::STATUSES`, `Transaction::STATUSES/PAYMENT_METHODS`) and are rendered via helper methods (`statusLabel()`, `paymentMethodLabel()`). Add new statuses to ALL maps that iterate them (`dashboard.blade.php` `$statusStyles`/`$statusBars`, admin & user views) or you'll hit undefined-key errors.
- Use named routes + `route()` everywhere.
- Money: `number_format()` then Persian-digit conversion (the per-view `$toPersian` / `$toman` closures).
- Views: `@extends('layouts.app')` (storefront) or `@extends('layouts.admin')` (admin); inline `@php` helper closures at top; Blade components in `resources/views/components/` (e.g. `<x-status-badge>`).
- Tailwind theme tokens are CSS vars in `resources/css/app.css` (`brand`, `accent`, `ink`, `muted`, `line`, `canvas`). Use these, not raw hex.
- JS: add an `initX()` function in `resources/js/app.js` and call it in the init block at the bottom. Interactivity uses `data-*` hooks.

## Architecture & boundaries

- Controllers: `app/Http/Controllers/{Admin,Auth,Api}`, thin; dashboard metrics computed in `Admin\DashboardController`.
- Services: `app/Services/OtpService.php`, `SmsService.php` (phone+OTP auth; password login optional).
- Policies: `app/Policies/{OrderPolicy,AddressPolicy}.php` — use `$this->authorize(...)`.
- Payments: `Transaction` belongs to `Order`+`User`; two methods `gateway`/`card_to_card`. Card-to-card flow: order → `pending_transaction` → upload receipt (`profile.orders.receipt`) → `pending_verification` → admin approve (`processing`) / reject (back to `pending_transaction`). Wrap order+transaction writes in `DB::transaction()`.
- Receipt images: `storage/app/public/receipts`, served via `public/storage` symlink (`php artisan storage:link`). Card-to-card destination/account/bank read from `config('payment.*')` (env `CARD_TO_CARD_*`).
- Seeded credentials come from env: `ADMIN_PHONE`/`ADMIN_PASSWORD`, `TEST_USER_PHONE`/`TEST_USER_PASSWORD` (see `.env.example`). Debug OTP is `123456` when `APP_DEBUG=true`.
- `layouts.admin` shares `$pendingOrdersCount` and `$activeCampaign` via a view composer in `AppServiceProvider`; it only counts `status = 'pending'`.

## Known issues / gotchas (verified)

- `routes/web.php` has a DUPLICATED profile security block (`/security` routes registered twice). De-duplicate if touching that file; Laravel tolerates it but it's redundant.
- Receipt generator (`CardToCardOrderSeeder`) uses GD with Latin-only text (GD lacks Persian shaping).
- Responsive tables: ALL order tables (admin, `profile/orders.blade.php`, `profile/dashboard.blade.php`) must have ZERO horizontal scroll on mobile (< md). Use one unified `<table>` with the `hidden md:table-cell` pattern — never `overflow-x-auto` + `min-w-[...]` (that was the old dashboard bug, now fixed). Mobile shows 3 compact columns: سفارش (order # + Jalali date + compact status badge), مبلغ (Toman, `whitespace-nowrap`), عملیات (stacked/side-by-side compact buttons). Desktop (`md+`) shows all full columns. Actions are pinned to the left edge (RTL) so they stay visible without scrolling.
- RTL everywhere (`dir="rtl"`); Vazirmatn font. Fonts resolve at runtime (build warns about `/fonts/...` — expected, harmless).

## Tooling

- Laravel Boost MCP is configured (`.mcp.json`); prefer `search-docs`, `database-query`/`database-schema` over raw SQL. `.ai/rules` does NOT exist here; skip that step.
- Skills live in `.agents/skills` (infer-conventions, laravel-best-practices, tailwindcss-development, testing-best-practices, deploying-to-cloud).
- Do not add dependencies or create documentation files without explicit user approval.
