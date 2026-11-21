@php
    $isEdit = $user->exists;
    $action = $isEdit ? route('admin.users.update', $user) : route('admin.users.store');
@endphp

<form method="POST" action="{{ $action }}" class="admin-card space-y-5 p-5">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-xs font-bold text-accent">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="name" class="mb-1.5 block text-[11px] font-bold text-ink">نام کامل <span class="text-accent">*</span></label>
            <input id="name" name="name" type="text" maxlength="255" autocomplete="name"
                   value="{{ old('name', $user->name) }}" class="field" required>
            @error('name') <p class="mt-1 text-[11px] text-accent">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="phone" class="mb-1.5 block text-[11px] font-bold text-ink">شماره موبایل <span class="text-accent">*</span></label>
            <input id="phone" name="phone" type="tel" inputmode="numeric" dir="ltr" maxlength="11"
                   placeholder="09123456789" value="{{ old('phone', $user->phone) }}" class="field text-start" required>
            @error('phone') <p class="mt-1 text-[11px] text-accent">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="mb-1.5 block text-[11px] font-bold text-ink">ایمیل (اختیاری)</label>
            <input id="email" name="email" type="email" dir="ltr" maxlength="255"
                   placeholder="user@example.com" value="{{ old('email', $user->email) }}" class="field text-start">
            @error('email') <p class="mt-1 text-[11px] text-accent">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="first_name" class="mb-1.5 block text-[11px] font-bold text-ink">نام</label>
                <input id="first_name" name="first_name" type="text" maxlength="50"
                       value="{{ old('first_name', $user->first_name) }}" class="field">
                @error('first_name') <p class="mt-1 text-[11px] text-accent">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="last_name" class="mb-1.5 block text-[11px] font-bold text-ink">نام خانوادگی</label>
                <input id="last_name" name="last_name" type="text" maxlength="50"
                       value="{{ old('last_name', $user->last_name) }}" class="field">
                @error('last_name') <p class="mt-1 text-[11px] text-accent">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label for="password" class="mb-1.5 block text-[11px] font-bold text-ink">
                @if ($isEdit)
                    رمز عبور جدید (اختیاری)
                @else
                    رمز عبور <span class="text-accent">*</span>
                @endif
            </label>
            <input id="password" name="password" type="password" autocomplete="new-password"
                   class="field" @if (! $isEdit) required @endif>
            <p class="mt-1 text-[10px] text-muted">حداقل ۸ کاراکتر، شامل حرف و عدد.</p>
            @error('password') <p class="mt-1 text-[11px] text-accent">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1.5 block text-[11px] font-bold text-ink">تأیید رمز عبور</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   autocomplete="new-password" class="field" @if (! $isEdit) required @endif>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex items-center gap-2 rounded-xl border border-line bg-canvas px-3 py-2.5">
            <input type="hidden" name="is_admin" value="0">
            <input type="checkbox" name="is_admin" value="1" class="size-4 accent-brand"
                   @checked(old('is_admin', $user->is_admin))>
            <span class="text-xs font-bold text-ink">دسترسی مدیر سیستم</span>
        </label>
        <label class="flex items-center gap-2 rounded-xl border border-line bg-canvas px-3 py-2.5">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-4 accent-brand"
                   @checked(old('is_active', $user->is_active ?? true))>
            <span class="text-xs font-bold text-ink">حساب فعال</span>
        </label>
    </div>

    <div class="flex items-center gap-2 border-t border-line pt-4">
        <button type="submit" class="btn btn-primary btn-sm">{{ $isEdit ? 'ذخیره تغییرات' : 'ایجاد کاربر' }}</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">انصراف</a>
    </div>
</form>
