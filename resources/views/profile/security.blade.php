@extends('layouts.app')

@section('title', 'امنیت حساب | آسمان‌پوش')

@section('content')
    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-xl">
                <div class="mb-6">
                    <a href="{{ route('profile.dashboard') }}" class="text-sm font-medium text-muted hover:text-ink">→ بازگشت به داشبورد</a>
                    <h1 class="mt-3 text-2xl font-extrabold text-ink">امنیت حساب</h1>
                    <p class="mt-1 text-sm text-muted">رمز عبور حساب خود را تنظیم، تغییر یا حذف کنید.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-brand/30 bg-brand-soft px-4 py-3 text-sm font-bold text-brand-deep">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="admin-card mb-4 p-5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-bold text-ink">وضعیت رمز عبور</span>
                        @if (filled($user->password))
                            <span class="status-pill bg-green-100 text-green-700">فعال</span>
                        @else
                            <span class="status-pill bg-amber-100 text-amber-700">تنظیم نشده</span>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-muted">شماره موبایل: <span dir="ltr">{{ $user->phone }}</span> — ورود با رمز یک‌بارمصرف همیشه فعال است.</p>
                </div>

                <form method="POST" action="{{ route('profile.security.password') }}" class="admin-card space-y-4 p-5">
                    @csrf
                    @method('PUT')
                    <h2 class="text-sm font-extrabold text-ink">{{ filled($user->password) ? 'تغییر رمز عبور' : 'تنظیم رمز عبور' }}</h2>

                    @if (filled($user->password))
                        <div>
                            <label for="current_password" class="mb-2 block text-sm font-semibold text-ink">رمز عبور فعلی <span class="text-accent">*</span></label>
                            <input type="password" id="current_password" name="current_password" class="field" autocomplete="current-password" required>
                        </div>
                    @endif

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-ink">رمز عبور جدید <span class="text-accent">*</span></label>
                        <input type="password" id="password" name="password" class="field" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-ink">تکرار رمز عبور جدید <span class="text-accent">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="field" autocomplete="new-password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ filled($user->password) ? 'ذخیره رمز جدید' : 'تنظیم رمز عبور' }}</button>
                </form>

                @if (filled($user->password))
                    <form method="POST" action="{{ route('profile.security.password.remove') }}" class="admin-card mt-4 space-y-3 p-5" onsubmit="return confirm('آیا از حذف رمز عبور مطمئن هستید؟')">
                        @csrf
                        @method('DELETE')
                        <h2 class="text-sm font-extrabold text-ink">حذف رمز عبور</h2>
                        <p class="text-xs text-muted">با حذف رمز، ورود فقط از طریق رمز یک‌بارمصرف انجام می‌شود.</p>
                        <div>
                            <label for="current_password_remove" class="mb-2 block text-sm font-semibold text-ink">رمز عبور فعلی <span class="text-accent">*</span></label>
                            <input type="password" id="current_password_remove" name="current_password" class="field" autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="btn btn-accent w-full">حذف رمز عبور</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
