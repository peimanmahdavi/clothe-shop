@extends('layouts.admin')

@section('title', 'مدیریت کاربران | پنل مدیریت')
@section('breadcrumb', 'مدیریت کاربران')
@section('page-title', 'مدیریت کاربران')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        $kpis = [
            ['label' => 'کل کاربران', 'value' => $toPersian($kpi['total']), 'unit' => 'نفر', 'hint' => 'همه حساب‌های ثبت‌شده', 'icon' => 'users', 'tone' => 'brand'],
            ['label' => 'کاربران فعال', 'value' => $toPersian($kpi['active']), 'unit' => 'نفر', 'hint' => 'قابلیت استفاده از فروشگاه', 'icon' => 'check', 'tone' => 'brand'],
            ['label' => 'مدیران سیستم', 'value' => $toPersian($kpi['admins']), 'unit' => 'نفر', 'hint' => 'دسترسی به پنل مدیریت', 'icon' => 'shield', 'tone' => 'accent'],
            ['label' => 'ثبت‌نام‌های این ماه', 'value' => $toPersian($kpi['new_this_month']), 'unit' => 'نفر', 'hint' => 'از ابتدای ماه جاری', 'icon' => 'plus', 'tone' => 'muted'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpis as $card)
            <div class="stat-card">
                <span class="pointer-events-none absolute -end-8 -top-8 size-24 rounded-full {{ $card['tone'] === 'accent' ? 'bg-accent-soft' : 'bg-brand-soft/70' }}" aria-hidden="true"></span>
                <div class="relative flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-muted">{{ $card['label'] }}</p>
                        <p class="mt-2 truncate text-lg font-extrabold text-ink sm:text-xl">
                            {{ $card['value'] }}
                            <span class="text-[11px] font-medium text-muted">{{ $card['unit'] }}</span>
                        </p>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $card['tone'] === 'accent' ? 'bg-accent-soft text-accent' : 'bg-brand-soft text-brand-deep' }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            @switch($card['icon'])
                                @case('users')
                                    <circle cx="9" cy="8" r="3.2" />
                                    <path d="M3.5 19.5a5.5 5.5 0 0 1 11 0" />
                                    <path d="M16 5.4a3.2 3.2 0 0 1 0 6.2M17.5 14.4a5.5 5.5 0 0 1 3 5.1" />
                                    @break
                                @case('check')
                                    <circle cx="12" cy="12" r="8.5" />
                                    <path d="m8.5 12 2.5 2.5 4.5-5" />
                                    @break
                                @case('shield')
                                    <path d="M12 3 5 6v5.5c0 4.4 2.9 7.9 7 9.5 4.1-1.6 7-5.1 7-9.5V6l-7-3Z" />
                                    <path d="m9.2 12 2 2 3.6-4" />
                                    @break
                                @default
                                    <circle cx="12" cy="12" r="8.5" />
                                    <path d="M12 8v8M8 12h8" />
                            @endswitch
                        </svg>
                    </span>
                </div>
                <p class="relative mt-3 text-[11px] font-medium text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="admin-card mt-4 p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="sm:col-span-2 xl:col-span-2">
                <label class="mb-1.5 block text-[11px] font-bold text-ink">جست‌وجو</label>
                <input type="search" name="search" maxlength="255" value="{{ $filters['search'] }}"
                       class="field" placeholder="نام، ایمیل یا شماره موبایل">
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-ink">نقش</label>
                <select name="role" class="field">
                    <option value="all" @selected($filters['role'] === 'all')>همه نقش‌ها</option>
                    <option value="admin" @selected($filters['role'] === 'admin')>مدیر (Admin)</option>
                    <option value="user" @selected($filters['role'] === 'user')>کاربر عادی (User)</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-ink">وضعیت حساب</label>
                <select name="status" class="field">
                    <option value="all" @selected($filters['status'] === 'all')>همه وضعیت‌ها</option>
                    <option value="active" @selected($filters['status'] === 'active')>فعال</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>مسدود / غیرفعال</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:col-span-2 sm:flex sm:items-end xl:col-span-4">
                <button type="submit" class="btn btn-primary btn-sm w-full sm:w-auto">اعمال فیلتر</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm w-full sm:w-auto">حذف فیلترها</a>
            </div>
        </form>
    </div>

    {{-- Dedicated table header: title on the right, primary action pinned to the left. --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3 px-1">
        <div class="flex items-center gap-2">
            <h2 class="text-sm font-extrabold text-ink">لیست کاربران</h2>
            <span class="inline-flex items-center rounded-full bg-brand-soft px-2.5 py-0.5 text-[11px] font-bold text-brand-deep">
                {{ $toPersian($kpi['total']) }}
            </span>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="mr-auto mt-3 inline-flex shrink-0 items-center gap-2 rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-sky-500/20 transition hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2">
            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M15.5 20.5v-1.8a4 4 0 0 0-4-4H6.5a4 4 0 0 0-4 4v1.8" />
                <circle cx="9" cy="7.5" r="3.5" />
                <path d="M19 8v6M22 11h-6" />
            </svg>
            افزودن کاربر
        </a>
    </div>

    <div class="admin-card mt-4 w-full max-w-full overflow-hidden">
        @if ($users->isEmpty())
            <div class="p-10 text-center text-sm text-muted">کاربری با این مشخصات یافت نشد.</div>
        @else
            {{-- One unified table: 3 compact columns on mobile, full set on md+. --}}
            <table class="w-full max-w-full table-fixed text-sm md:table-auto">
                <thead class="bg-canvas/70 text-[11px] text-muted">
                    <tr>
                        <th class="w-[38%] px-3 py-3 text-start font-bold md:hidden">کاربر</th>
                        <th class="hidden px-4 py-3 text-start font-bold md:table-cell">نام و آواتار</th>
                        <th class="w-[34%] px-2 py-3 text-start font-bold md:w-auto md:px-4">
                            <span class="md:hidden">تماس / سفارش</span>
                            <span class="hidden md:inline">ایمیل / موبایل</span>
                        </th>
                        <th class="hidden px-3 py-3 text-start font-bold md:table-cell">نقش</th>
                        <th class="hidden px-3 py-3 text-start font-bold md:table-cell">تعداد سفارش‌ها</th>
                        <th class="hidden px-3 py-3 text-start font-bold md:table-cell">وضعیت</th>
                        <th class="hidden px-3 py-3 text-start font-bold md:table-cell">تاریخ عضویت</th>
                        <th class="w-[28%] py-3 pl-3 pr-2 text-left font-bold md:w-auto md:px-4 md:text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($users as $user)
                        @php
                            $isSelf = $user->id === auth()->id();
                            $isLastAdmin = $user->isLastActiveAdmin();
                            $rolePill = $user->is_admin
                                ? 'bg-brand-soft text-brand-deep'
                                : 'bg-canvas text-muted';
                            $statusPill = $user->is_active
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700';
                        @endphp
                        <tr class="transition-colors hover:bg-canvas/60">
                            {{-- Mobile: user + compact role/status pills --}}
                            <td class="px-3 py-3 md:hidden">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-xs font-extrabold text-brand-deep sm:h-10 sm:w-10">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-ink sm:text-sm">{{ $user->full_name }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-1">
                                            <span class="status-pill px-2 py-0.5 text-[10px] sm:px-2.5 sm:py-1 sm:text-[11px] {{ $rolePill }}">{{ $user->roleLabel() }}</span>
                                            <span class="status-pill px-2 py-0.5 text-[10px] sm:px-2.5 sm:py-1 sm:text-[11px] {{ $statusPill }}">{{ $user->statusLabel() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Desktop: name + avatar --}}
                            <td class="hidden px-4 py-3 md:table-cell">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-xs font-extrabold text-brand-deep">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-ink">{{ $user->full_name }}</p>
                                        <p class="text-[11px] text-muted">#{{ $toPersian($user->id) }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact (always visible); orders count shown here on mobile --}}
                            <td class="min-w-0 px-2 py-3 md:px-4">
                                <p class="hidden truncate text-[11px] text-muted md:block" dir="ltr">{{ $user->email ?: '—' }}</p>
                                <p class="truncate text-right font-mono text-xs text-ink md:mt-0.5" dir="ltr">{{ $user->phone }}</p>
                                <p class="mt-1 whitespace-nowrap text-[11px] text-muted md:hidden">
                                    {{ $toPersian($user->orders_count) }} سفارش
                                </p>
                            </td>

                            <td class="hidden px-3 py-3 md:table-cell">
                                <span class="status-pill {{ $rolePill }}">{{ $user->roleLabel() }}</span>
                            </td>

                            <td class="hidden px-3 py-3 font-bold text-ink md:table-cell">{{ $toPersian($user->orders_count) }}</td>

                            <td class="hidden px-3 py-3 md:table-cell">
                                <span class="status-pill {{ $statusPill }}">{{ $user->statusLabel() }}</span>
                            </td>

                            <td class="hidden px-3 py-3 text-xs text-muted md:table-cell">
                                {{ $toPersian(\App\Support\Jalali::format($user->created_at, 'Y/m/d')) }}
                            </td>

                            {{-- Actions pinned to the left edge in RTL --}}
                            <td class="px-3 py-3 md:px-4">
                                <div class="flex flex-col items-stretch gap-1.5 md:flex-row md:items-center md:justify-end">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center justify-center gap-1 rounded-lg border border-line bg-white w-full px-2.5 py-1.5 text-xs font-bold md:w-auto text-ink transition hover:border-brand hover:text-brand-deep">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        ویرایش
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                        @csrf
                                        <button type="submit"
                                                @disabled($isSelf || $isLastAdmin && $user->is_active)
                                                title="{{ $isSelf ? 'نمی‌توانید وضعیت حساب خود را تغییر دهید' : ($isLastAdmin && $user->is_active ? 'آخرین مدیر را نمی‌توان مسدود کرد' : '') }}"
                                                class="inline-flex items-center justify-center rounded-lg border border-line bg-white w-full px-2.5 py-1.5 text-xs font-bold md:w-auto {{ $user->is_active ? 'text-accent' : 'text-green-700' }} transition hover:border-brand disabled:cursor-not-allowed disabled:opacity-40">
                                            {{ $user->is_active ? 'مسدود' : 'فعال' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('آیا از حذف این کاربر اطمینان دارید؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                @disabled($isSelf || $isLastAdmin)
                                                title="{{ $isSelf ? 'نمی‌توانید حساب خود را حذف کنید' : ($isLastAdmin ? 'آخرین مدیر را نمی‌توان حذف کرد' : '') }}"
                                                class="inline-flex items-center justify-center rounded-lg border border-line bg-white w-full px-2.5 py-1.5 text-xs font-bold md:w-auto text-muted transition hover:border-accent hover:text-accent disabled:cursor-not-allowed disabled:opacity-40">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-line p-4">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
