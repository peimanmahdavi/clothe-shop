@extends('layouts.app')

@section('title', 'ورود / ثبت‌نام | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp

    <section class="py-12 sm:py-20">
        <div class="container-page">
            <div class="mx-auto max-w-md">
                {{-- Logo --}}
                <div class="flex items-center justify-center gap-3 mb-8">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-linear-to-br from-brand to-brand-strong text-white shadow-lg shadow-brand-strong/25">
                        <svg class="size-6.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6.5 16.5A4 4 0 0 1 7 8.6a5.4 5.4 0 0 1 10.3 1 3.7 3.7 0 0 1 .2 6.9" />
                            <path d="M4 16.5h15.5" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-2xl font-extrabold text-ink">آسمان‌پوش</span>
                        <span class="block text-sm text-muted">ورود به حساب کاربری</span>
                    </span>
                </div>

                {{-- Step 1: Phone Input --}}
                <div id="step-phone" class="card p-6 sm:p-8" data-step="1">
                    <div class="text-center mb-8">
                        <h1 class="text-xl font-extrabold text-ink sm:text-2xl">شماره موبایل خود را وارد کنید</h1>
                        <p class="mt-2 text-sm text-muted">کد احراز هویت برای شما پیامک خواهد شد</p>
                    </div>

                    <form id="phone-form" class="space-y-4" novalidate>
                        @csrf

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-ink mb-2">شماره موبایل</label>
                            <div class="relative" dir="ltr">
                                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm font-medium text-muted" dir="ltr">+98</span>
                                <input type="tel"
                                    id="phone"
                                    name="phone"
                                    class="field"
                                    style="padding-left: 3.5rem; text-align: left;"
                                    placeholder="۹۱۲۳۴۵۶۷۸۹"
                                    dir="ltr"
                                    maxlength="10"
                                    pattern="9\d{9}"
                                    required
                                    autocomplete="tel"
                                    inputmode="numeric"
                                    aria-describedby="phone-hint">
                            </div>
                            <p id="phone-hint" class="mt-1.5 text-[11px] text-muted">مثال: ۹۱۲۳۴۵۶۷۸۹ (بدون صفر اول)</p>
                        </div>

                        <div id="phone-error" class="hidden rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert"></div>

                        <button type="submit" 
                            id="send-otp-btn" 
                            class="btn btn-primary w-full"
                            disabled>
                            <span class="btn-text">ارسال کد احراز هویت</span>
                            <span class="btn-loading hidden flex items-center gap-2">
                                <svg class="animate-spin size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                                    <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1" />
                                </svg>
                                در حال ارسال...
                            </span>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-xs text-muted">
                        با ادامه، شما <a href="#" class="font-semibold text-brand hover:underline">قوانین و مقررات</a> و 
                        <a href="#" class="font-semibold text-brand hover:underline">حریم خصوصی</a> را پذیرفته‌ید.
                    </p>
                </div>

                {{-- Step 2: OTP Input --}}
                <div id="step-otp" class="hidden card p-6 sm:p-8" data-step="2">
                    <div class="text-center mb-8">
                        <h1 class="text-xl font-extrabold text-ink sm:text-2xl">کد احراز هویت را وارد کنید</h1>
                        <p class="mt-2 text-sm text-muted">
                            کد ۶ رقمی به شماره 
                            <span id="otp-phone-display" class="font-bold text-ink"></span> 
                            ارسال شد
                        </p>
                    </div>

                    <form id="otp-form" class="space-y-4" novalidate>
                        @csrf
                        <input type="hidden" name="phone" id="otp-phone-hidden">

                        <div class="flex items-center justify-center gap-3" dir="ltr" role="group" aria-label="کد احراز هویت ۶ رقمی">
                            @for ($i = 1; $i <= 6; $i++)
                                <input type="text" 
                                    name="otp_digit_{{ $i }}" 
                                    class="otp-input field w-12 h-12 text-center text-2xl font-bold text-ink"
                                    maxlength="1" 
                                    pattern="\d"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    aria-label=" رقم {{ $toPersian($i) }} کد"
                                    @if ($i === 1) autofocus @endif>
                            @endfor
                        </div>

                        <input type="hidden" name="otp" id="otp-combined" required>

                        <div id="otp-error" class="hidden rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert"></div>

                        <button type="submit" 
                            id="verify-otp-btn" 
                            class="btn btn-primary w-full"
                            disabled>
                            <span class="btn-text">تأیید و ورود</span>
                            <span class="btn-loading hidden flex items-center gap-2">
                                <svg class="animate-spin size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                                    <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1" />
                                </svg>
                                در حال تأیید...
                            </span>
                        </button>
                    </form>

                    <div class="mt-6 text-center space-y-3">
                        <button type="button" 
                            id="resend-otp-btn" 
                            class="text-sm font-semibold text-brand hover:underline transition-colors"
                            disabled>
                            ارسال مجدد کد (<span id="resend-timer">۰۰:۰۰</span>)
                        </button>
                        <button type="button" 
                            id="back-to-phone-btn" 
                            class="text-sm font-medium text-muted hover:text-ink transition-colors">
                            تغییر شماره موبایل
                        </button>
                    </div>
                </div>

                {{-- Step 3: Complete Profile (for new users) --}}
                <div id="step-profile" class="hidden card p-6 sm:p-8" data-step="3">
                    <div class="text-center mb-8">
                        <h1 class="text-xl font-extrabold text-ink sm:text-2xl">پروفایل خود را تکمیل کنید</h1>
                        <p class="mt-2 text-sm text-muted">نام و نام خانوادگی خود را برای سفارشات آینده وارد کنید</p>
                    </div>

                    <form id="profile-form" class="space-y-4" novalidate>
                        @csrf

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-ink mb-2">نام</label>
                                <input type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    class="field" 
                                    placeholder="علی"
                                    required
                                    maxlength="50"
                                    autocomplete="given-name">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-ink mb-2">نام خانوادگی</label>
                                <input type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    class="field" 
                                    placeholder="احمدی"
                                    required
                                    maxlength="50"
                                    autocomplete="family-name">
                            </div>
                        </div>

                        <div id="profile-error" class="hidden rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert"></div>

                        <button type="submit" 
                            id="complete-profile-btn" 
                            class="btn btn-primary w-full">
                            <span class="btn-text">تکمیل و ورود</span>
                            <span class="btn-loading hidden flex items-center gap-2">
                                <svg class="animate-spin size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                                    <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1" />
                                </svg>
                                در حال پردازش...
                            </span>
                        </button>
                    </form>

                    <p class="mt-4 text-center text-sm text-muted">
                        <button type="button" id="skip-profile-btn" class="font-medium text-brand hover:underline">فعلاً نه، بعداً تکمیل می‌کنم</button>
                    </p>
                </div>

                {{-- Debug OTP Display (when APP_DEBUG=true) --}}
                @if (config('app.debug'))
                    <div id="debug-otp" class="hidden mt-6 p-4 rounded-xl bg-brand-soft border border-brand/30 text-sm text-brand-deep" dir="ltr">
                        <strong>Debug OTP:</strong> <span id="debug-otp-value" class="font-mono ml-2"></span>
                        <button type="button" onclick="document.getElementById('debug-otp').classList.add('hidden')" class="ml-4 text-brand hover:text-brand-deep">×</button>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneForm = document.getElementById('phone-form');
    const otpForm = document.getElementById('otp-form');
    const profileForm = document.getElementById('profile-form');
    const stepPhone = document.getElementById('step-phone');
    const stepOtp = document.getElementById('step-otp');
    const stepProfile = document.getElementById('step-profile');
    const sendOtpBtn = document.getElementById('send-otp-btn');
    const verifyOtpBtn = document.getElementById('verify-otp-btn');
    const completeProfileBtn = document.getElementById('complete-profile-btn');
    const resendOtpBtn = document.getElementById('resend-otp-btn');
    const backToPhoneBtn = document.getElementById('back-to-phone-btn');
    const skipProfileBtn = document.getElementById('skip-profile-btn');
    const phoneInput = document.getElementById('phone');
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpCombined = document.getElementById('otp-combined');
    const otpPhoneDisplay = document.getElementById('otp-phone-display');
    const otpPhoneHidden = document.getElementById('otp-phone-hidden');
    const phoneError = document.getElementById('phone-error');
    const otpError = document.getElementById('otp-error');
    const profileError = document.getElementById('profile-error');
    const resendTimer = document.getElementById('resend-timer');
    const debugOtp = document.getElementById('debug-otp');
    const debugOtpValue = document.getElementById('debug-otp-value');

    let resendCountdown = 0;
    let resendInterval = null;

    // Phone input formatting (Persian to English digits)
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    function toEnglishDigits(str) {
        return str.replace(/[۰-۹]/g, d => englishDigits[persianDigits.indexOf(d)]);
    }

    function toPersianDigits(str) {
        return String(str).replace(/[0-9]/g, d => persianDigits[Number(d)]);
    }

    function formatPhoneInput(value) {
        // Remove non-digits
        value = toEnglishDigits(value).replace(/\D/g, '');
        // Limit to 10 digits
        value = value.slice(0, 10);
        // Show in Persian digits
        phoneInput.value = toPersianDigits(value);
        return value;
    }

    // Phone input handler
    phoneInput.addEventListener('input', function(e) {
        const englishValue = formatPhoneInput(this.value);
        const isValid = englishValue.length === 10 && /^9\d{9}$/.test(englishValue);
        sendOtpBtn.disabled = !isValid;
        phoneError.classList.add('hidden');
    });

    // OTP inputs handling
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            this.value = toPersianDigits(this.value.replace(/\D/g, ''));
            
            // Combine all OTP inputs
            const otp = Array.from(otpInputs).map(i => toEnglishDigits(i.value)).join('');
            otpCombined.value = otp;
            
            // Auto-focus next input
            if (this.value && index < 5) {
                otpInputs[index + 1].focus();
            }
            
            verifyOtpBtn.disabled = otp.length !== 6;
            otpError.classList.add('hidden');
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        // Paste handling
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = toEnglishDigits(e.clipboardData.getData('text')).replace(/\D/g, '').slice(0, 6);
            pasteData.split('').forEach((digit, i) => {
                if (otpInputs[i]) {
                    otpInputs[i].value = toPersianDigits(digit);
                }
            });
            otpCombined.value = pasteData;
            verifyOtpBtn.disabled = pasteData.length !== 6;
            
            // Focus last filled or next empty
            const lastFilled = pasteData.length - 1;
            if (lastFilled < 5) {
                otpInputs[lastFilled + 1].focus();
            } else {
                otpInputs[5].focus();
            }
        });
    });

    // Step switching
    function showStep(step) {
        [stepPhone, stepOtp, stepProfile].forEach(s => s.classList.add('hidden'));
        document.querySelector(`[data-step="${step}"]`)?.classList.remove('hidden');
    }

    function startResendTimer() {
        resendCountdown = 60;
        resendOtpBtn.disabled = true;
        
        resendInterval = setInterval(() => {
            resendCountdown--;
            const mins = Math.floor(resendCountdown / 60).toString().padStart(2, '0');
            const secs = (resendCountdown % 60).toString().padStart(2, '0');
            resendTimer.textContent = toPersianDigits(`${mins}:${secs}`);
            
            if (resendCountdown <= 0) {
                clearInterval(resendInterval);
                resendOtpBtn.disabled = false;
                resendTimer.textContent = toPersianDigits('۰۰:۰۰');
            }
        }, 1000);
    }

    // Send OTP
    phoneForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const phone = '0' + toEnglishDigits(phoneInput.value);
        
        sendOtpBtn.disabled = true;
        sendOtpBtn.querySelector('.btn-text').classList.add('hidden');
        sendOtpBtn.querySelector('.btn-loading').classList.remove('hidden');
        phoneError.classList.add('hidden');

        try {
            const response = await fetch('{{ route('auth.send-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone }),
            });

            const data = await response.json();

            if (data.success) {
                // Show debug OTP in local
                if (data.debug_otp && debugOtp) {
                    debugOtpValue.textContent = data.debug_otp;
                    debugOtp.classList.remove('hidden');
                    // Auto-fill OTP inputs for debugging
                    data.debug_otp.split('').forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = toPersianDigits(digit);
                        }
                    });
                    otpCombined.value = data.debug_otp;
                    verifyOtpBtn.disabled = false;
                }

                otpPhoneDisplay.textContent = toPersianDigits(phone);
                otpPhoneHidden.value = phone;
                showStep(2);
                startResendTimer();
                otpInputs[0].focus();
            } else {
                phoneError.textContent = data.message;
                phoneError.classList.remove('hidden');
            }
        } catch (error) {
            phoneError.textContent = 'خطای شبکه. لطفاً مجدداً تلاش کنید.';
            phoneError.classList.remove('hidden');
        } finally {
            sendOtpBtn.disabled = false;
            sendOtpBtn.querySelector('.btn-text').classList.remove('hidden');
            sendOtpBtn.querySelector('.btn-loading').classList.add('hidden');
        }
    });

    // Verify OTP
    otpForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const phone = otpPhoneHidden.value;
        const otp = toEnglishDigits(otpCombined.value);

        verifyOtpBtn.disabled = true;
        verifyOtpBtn.querySelector('.btn-text').classList.add('hidden');
        verifyOtpBtn.querySelector('.btn-loading').classList.remove('hidden');
        otpError.classList.add('hidden');

        try {
            const response = await fetch('{{ route('auth.verify-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone, otp }),
            });

            const data = await response.json();

            if (data.success) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Check if profile needs completion
                    if (!data.user.first_name || !data.user.last_name) {
                        showStep(3);
                        document.getElementById('first_name').focus();
                    } else {
                        window.location.href = '{{ route('profile.dashboard') }}';
                    }
                }
            } else {
                otpError.textContent = data.message;
                otpError.classList.remove('hidden');
                
                // Clear OTP inputs
                otpInputs.forEach(input => input.value = '');
                otpCombined.value = '';
                verifyOtpBtn.disabled = true;
                otpInputs[0].focus();
            }
        } catch (error) {
            otpError.textContent = 'خطای شبکه. لطفاً مجدداً تلاش کنید.';
            otpError.classList.remove('hidden');
        } finally {
            verifyOtpBtn.disabled = false;
            verifyOtpBtn.querySelector('.btn-text').classList.remove('hidden');
            verifyOtpBtn.querySelector('.btn-loading').classList.add('hidden');
        }
    });

    // Resend OTP
    resendOtpBtn.addEventListener('click', async function() {
        const phone = otpPhoneHidden.value;
        
        this.disabled = true;
        this.textContent = 'در حال ارسال...';

        try {
            const response = await fetch('{{ route('auth.send-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone }),
            });

            const data = await response.json();

            if (data.success) {
                if (data.debug_otp && debugOtp) {
                    debugOtpValue.textContent = data.debug_otp;
                    debugOtp.classList.remove('hidden');
                    data.debug_otp.split('').forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = toPersianDigits(digit);
                        }
                    });
                    otpCombined.value = data.debug_otp;
                    verifyOtpBtn.disabled = false;
                }
                startResendTimer();
            } else {
                otpError.textContent = data.message;
                otpError.classList.remove('hidden');
                this.disabled = false;
                this.innerHTML = 'ارسال مجدد کد (<span id="resend-timer">۰۰:۰۰</span>)';
            }
        } catch (error) {
            otpError.textContent = 'خطای شبکه. لطفاً مجدداً تلاش کنید.';
            otpError.classList.remove('hidden');
            this.disabled = false;
            this.innerHTML = 'ارسال مجدد کد (<span id="resend-timer">۰۰:۰۰</span>)';
        }
    });

    // Back to phone
    backToPhoneBtn.addEventListener('click', function() {
        showStep(1);
        phoneInput.focus();
    });

    // Complete profile
    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();

        completeProfileBtn.disabled = true;
        completeProfileBtn.querySelector('.btn-text').classList.add('hidden');
        completeProfileBtn.querySelector('.btn-loading').classList.remove('hidden');
        profileError.classList.add('hidden');

        try {
            const response = await fetch('{{ route('auth.complete-profile') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ first_name: firstName, last_name: lastName }),
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = '{{ route('profile.dashboard') }}';
            } else {
                profileError.textContent = data.message;
                profileError.classList.remove('hidden');
            }
        } catch (error) {
            profileError.textContent = 'خطای شبکه. لطفاً مجدداً تلاش کنید.';
            profileError.classList.remove('hidden');
        } finally {
            completeProfileBtn.disabled = false;
            completeProfileBtn.querySelector('.btn-text').classList.remove('hidden');
            completeProfileBtn.querySelector('.btn-loading').classList.add('hidden');
        }
    });

    // Skip profile
    skipProfileBtn.addEventListener('click', function() {
        window.location.href = '{{ route('profile.dashboard') }}';
    });
});
</script>
@endpush