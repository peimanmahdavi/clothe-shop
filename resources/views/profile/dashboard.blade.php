@extends('layouts.app')

@section('title', 'داشبورد کاربر | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp

    <section class="py-8">
        <div class="container-page">
            {{-- Welcome Header --}}
            <div class="mb-8">
                <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-extrabold text-ink">سلام، {{ $user->first_name ?? $user->name }}!</h1>
                        <p class="mt-1 text-sm text-muted">به پنل کاربری آسمان‌پوش خوش آمدید</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('profile.checkout') }}" class="btn btn-primary btn-sm">ثبت سفارش جدید</a>
                        <a href="{{ route('profile.security') }}" class="btn btn-outline btn-sm">امنیت حساب</a>
                        @if (!$user->first_name || !$user->last_name)
                            <button type="button"
                                id="complete-profile-btn"
                                class="btn btn-outline btn-sm"
                                data-first-name="{{ $user->first_name ?? '' }}"
                                data-last-name="{{ $user->last_name ?? '' }}">
                                تکمیل پروفایل
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-8">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-muted">کل سفارش‌ها</p>
                            <p class="mt-1 text-2xl font-extrabold text-ink">{{ $toPersian($stats['total_orders']) }}</p>
                        </div>
                        <div class="icon-tile">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8h12l-1 12H7L6 8Z" />
                                <path d="M9 8a3 3 0 0 1 6 0" />
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('profile.orders') }}" class="mt-3 block text-xs font-bold text-brand hover:underline">مشاهده همه</a>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-muted">در انتظار پرداخت</p>
                            <p class="mt-1 text-2xl font-extrabold text-accent">{{ $toPersian($stats['pending_orders']) }}</p>
                        </div>
                        <div class="icon-tile bg-accent-soft text-accent">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 6v6l4 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-muted">تکمیل شده</p>
                            <p class="mt-1 text-2xl font-extrabold text-brand-deep">{{ $toPersian($stats['completed_orders']) }}</p>
                        </div>
                        <div class="icon-tile bg-green-100 text-green-600">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-medium text-muted">آدرس‌های ذخیره شده</p>
                            <p class="mt-1 text-2xl font-extrabold text-ink">{{ $toPersian($stats['total_addresses']) }}</p>
                        </div>
                        <div class="icon-tile bg-amber-100 text-amber-600">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('profile.addresses') }}" class="mt-3 block text-xs font-bold text-brand hover:underline">مدیریت آدرس‌ها</a>
                </div>
            </div>

            {{-- Default Address --}}
            @if ($stats['default_address'])
                <div class="admin-card overflow-hidden mb-8">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-deep">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-ink">آدرس پیش‌فرض</p>
                                    <p class="text-sm text-muted">{{ $stats['default_address']->title }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="status-pill bg-brand-soft text-brand-deep">پیش‌فرض</span>
                                <a href="{{ route('profile.addresses') }}" class="btn btn-outline btn-sm">ویرایش</a>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-line text-sm text-muted">
                            <p>{{ $stats['default_address']->full_name }}</p>
                            <p class="mt-0.5">{{ $stats['default_address']->phone }}</p>
                            <p class="mt-0.5">{{ $stats['default_address']->full_address }}</p>
                            @if ($stats['default_address']->postal_code)
                                <p class="mt-0.5">کد پستی: {{ $toPersian($stats['default_address']->postal_code) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="admin-card p-5 text-center mb-8">
                    <div class="flex size-14 mx-auto items-center justify-center rounded-xl bg-brand-soft text-brand-deep mb-3">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-ink">هنوز آدرسی ثبت نکرده‌اید</h3>
                    <p class="mt-1 text-sm text-muted">برای تسریع در خریدهای آینده، آدرس خود را اضافه کنید</p>
                    <a href="{{ route('profile.addresses') }}" class="btn btn-primary mt-4 inline-flex">افزودن آدرس جدید</a>
                </div>
            @endif

            {{-- Recent Orders --}}
            <div class="admin-card overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-line">
                    <h2 class="text-lg font-extrabold text-ink">سفارش‌های اخیر</h2>
                    <a href="{{ route('profile.orders') }}" class="text-sm font-bold text-brand hover:underline">مشاهده همه</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <div class="p-10 text-center">
                        <div class="flex size-16 mx-auto items-center justify-center rounded-xl bg-canvas text-muted mb-3">
                            <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8h12l-1 12H7L6 8Z" />
                                <path d="M9 8a3 3 0 0 1 6 0" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-ink">هنوز سفارشی ثبت نکرده‌اید</h3>
                        <p class="mt-1 text-sm text-muted">محصولات مورد علاقه خود را پیدا کنید و اولین سفارش خود را ثبت کنید</p>
                        <a href="{{ route('home') }}" class="btn btn-primary mt-4 inline-flex">خرید محصول</a>
                    </div>
                @else
                    <div>
                        <table class="w-full text-sm">
                            <thead class="bg-canvas/70 text-[11px] text-muted">
                                <tr>
                                    <th class="px-4 py-3 text-start font-bold md:hidden">سفارش</th>
                                    <th class="hidden px-5 py-3 text-start font-bold md:table-cell">شماره سفارش</th>
                                    <th class="hidden px-3 py-3 text-start font-bold md:table-cell">تاریخ</th>
                                    <th class="px-3 py-3 text-start font-bold">مبلغ</th>
                                    <th class="hidden px-3 py-3 text-start font-bold md:table-cell">وضعیت</th>
                                    <th class="px-4 py-3 text-end font-bold md:px-5">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($recentOrders as $order)
                                    <tr class="transition-colors hover:bg-canvas/60">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'processing' => 'bg-brand-soft text-brand-deep',
                                                'shipped' => 'bg-blue-100 text-blue-700',
                                                'completed' => 'bg-green-100 text-green-700',
                                                'cancelled' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        {{-- Mobile: Order & Date + compact status --}}
                                        <td class="px-4 py-3 md:hidden">
                                            <p class="font-mono text-[13px] font-bold text-ink" dir="ltr">#{{ $order->number }}</p>
                                            <p class="mt-0.5 text-[11px] text-muted">{{ $toPersian($order->placed_at->format('Y/m/d')) }}</p>
                                            <span class="status-pill mt-1.5 {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">{{ $order->statusLabel() }}</span>
                                        </td>
                                        {{-- Desktop: Order number --}}
                                        <td class="hidden px-5 py-3 md:table-cell">
                                            <p class="font-bold text-ink font-mono" dir="ltr">{{ $order->number }}</p>
                                        </td>
                                        {{-- Desktop: Date --}}
                                        <td class="hidden px-3 py-3 text-xs text-muted md:table-cell">{{ $toPersian($order->placed_at->format('Y/m/d')) }}</td>
                                        {{-- Amount (always visible) --}}
                                        <td class="px-3 py-3">
                                            <p class="whitespace-nowrap font-bold text-ink">{{ $toPersian(number_format($order->total)) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                        </td>
                                        {{-- Desktop: Status --}}
                                        <td class="hidden px-3 py-3 md:table-cell">
                                            <span class="status-pill {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">
                                                {{ $order->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('profile.orders.show', $order) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">جزئیات</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const completeProfileBtn = document.getElementById('complete-profile-btn');
    
    if (completeProfileBtn) {
        completeProfileBtn.addEventListener('click', function() {
            // Show modal to complete profile
            showCompleteProfileModal();
        });
    }

    function showCompleteProfileModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-ink/40 backdrop-blur-sm p-4';
        modal.innerHTML = `
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl animate-tick">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-extrabold text-ink">تکمیل پروفایل</h3>
                    <button type="button" class="icon-button text-muted hover:text-ink" onclick="this.closest('.fixed').remove()">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-muted mb-6">برای استفاده بهتر از خدمات، نام و نام خانوادگی خود را وارد کنید.</p>
                <form id="profile-complete-form" class="space-y-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-ink mb-2">نام</label>
                            <input type="text" name="first_name" class="field" required maxlength="50" value="{{ $user->first_name ?? '' }}" autocomplete="given-name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-ink mb-2">نام خانوادگی</label>
                            <input type="text" name="last_name" class="field" required maxlength="50" value="{{ $user->last_name ?? '' }}" autocomplete="family-name">
                        </div>
                    </div>
                    <div id="modal-error" class="hidden rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent"></div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="btn btn-outline flex-1">انصراف</button>
                        <button type="submit" class="btn btn-primary flex-1">ذخیره</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);

        const form = modal.querySelector('#profile-complete-form');
        const errorDiv = modal.querySelector('#modal-error');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'در حال ذخیره...';
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('{{ route('auth.complete-profile') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        first_name: form.first_name.value.trim(),
                        last_name: form.last_name.value.trim(),
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    modal.remove();
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'خطای شبکه. لطفاً مجدداً تلاش کنید.';
                errorDiv.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'ذخیره';
            }
        });
    }
});
</script>
@endpush