@extends('layouts.app')

@section('title', 'آدرس‌های من | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-3xl">
                {{-- Header --}}
                <div class="mb-8">
                    <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-extrabold text-ink">آدرس‌های من</h1>
                            <p class="mt-1 text-sm text-muted">آدرس‌های ذخیره شده برای تسریع در خرید</p>
                        </div>
                        <button type="button" id="add-address-btn" class="btn btn-primary">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            افزودن آدرس جدید
                        </button>
                    </div>
                </div>

                {{-- Addresses List --}}
                <div id="addresses-list" class="space-y-4">
                    @forelse ($addresses as $address)
                        <article class="address-card admin-card relative overflow-hidden" data-address-id="{{ $address->id }}" data-address='@json($address)'>
                            @if ($address->is_default)
                                <div class="absolute top-4 end-4 z-10">
                                    <span class="status-pill bg-brand-soft text-brand-deep">پیش‌فرض</span>
                                </div>
                            @endif

                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-deep">
                                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-bold text-ink truncate">{{ $address->title }}</h3>
                                                @if ($address->is_default)
                                                    <span class="text-[10px] font-bold text-brand-deep bg-brand-soft px-2 py-0.5 rounded-full">پیش‌فرض</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-muted">{{ $address->full_name }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" 
                                            class="edit-address-btn icon-button" 
                                            data-address-id="{{ $address->id }}"
                                            aria-label="ویرایش آدرس {{ $address->title }}">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z" />
                                            </svg>
                                        </button>
                                        
                                        @if (!$address->is_default)
                                            <button type="button" 
                                                class="set-default-btn icon-button text-amber-600 hover:bg-amber-50" 
                                                data-address-id="{{ $address->id }}"
                                                aria-label="تنظیم به عنوان پیش‌فرض">
                                                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z" />
                                                </svg>
                                            </button>
                                        @endif

                                        <button type="button" 
                                            class="delete-address-btn icon-button text-red-600 hover:bg-red-50" 
                                            data-address-id="{{ $address->id }}"
                                            aria-label="حذف آدرس {{ $address->title }}">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-line text-sm text-muted">
                                    <p class="flex items-center gap-2">
                                        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z" />
                                        </svg>
                                        <span>{{ $address->phone }}</span>
                                    </p>
                                    <p class="mt-1 flex items-center gap-2">
                                        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        <span>{{ $address->full_address }}</span>
                                    </p>
                                    @if ($address->postal_code)
                                        <p class="mt-1 flex items-center gap-2">
                                            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="2" y="3" width="20" height="14" rx="2" />
                                                <path d="M8 10h8M8 14h8" />
                                            </svg>
                                            <span>کد پستی: {{ $toPersian($address->postal_code) }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="admin-card p-10 text-center">
                            <div class="flex size-16 mx-auto items-center justify-center rounded-xl bg-canvas text-muted mb-3">
                                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink">هنوز آدرسی ثبت نکرده‌اید</h3>
                            <p class="mt-1 text-sm text-muted">اولین آدرس خود را برای تسریع در خریدهای آینده اضافه کنید</p>
                            <button type="button" id="add-first-address-btn" class="btn btn-primary mt-4 inline-flex">افزودن آدرس جدید</button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Add/Edit Address Modal --}}
    <div id="address-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-ink/40 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl animate-tick max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-line sticky top-0 bg-white z-10">
                <h3 id="modal-title" class="text-lg font-extrabold text-ink">افزودن آدرس جدید</h3>
                <button type="button" class="icon-button text-muted hover:text-ink" onclick="closeAddressModal()">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <form id="address-form" class="p-5 space-y-4" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="address_id" id="address_id">
                <input type="hidden" name="_method" id="form_method" value="POST">

                <div>
                    <label for="title" class="block text-sm font-semibold text-ink mb-2">عنوان آدرس <span class="text-accent">*</span></label>
                    <input type="text" id="title" name="title" class="field" placeholder="مثال: خانه، محل کار" required maxlength="50" autocomplete="off">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-ink mb-2">نام <span class="text-accent">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="field" placeholder="علی" required maxlength="50" autocomplete="given-name">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-ink mb-2">نام خانوادگی <span class="text-accent">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="field" placeholder="احمدی" required maxlength="50" autocomplete="family-name">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-ink mb-2">شماره تماس <span class="text-accent">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 start-3.5 flex items-center text-muted" dir="ltr">+98</span>
                        <input type="tel" id="phone" name="phone" class="field ps-16" placeholder="۹۱۲۳۴۵۶۷۸۹" dir="ltr" maxlength="10" pattern="9\d{9}" required autocomplete="tel" inputmode="numeric">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="province" class="block text-sm font-semibold text-ink mb-2">استان <span class="text-accent">*</span></label>
                        <input type="text" id="province" name="province" class="field" placeholder="تهران" required maxlength="100">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-ink mb-2">شهر <span class="text-accent">*</span></label>
                        <input type="text" id="city" name="city" class="field" placeholder="تهران" required maxlength="100">
                    </div>
                </div>

                <div>
                    <label for="address_line" class="block text-sm font-semibold text-ink mb-2">آدرس کامل <span class="text-accent">*</span></label>
                    <textarea id="address_line" name="address_line" class="field min-h-[100px] resize-none" placeholder="آدرس کامل با پلاک، واحد، کوچه و خیابان" required maxlength="500"></textarea>
                </div>

                <div>
                    <label for="postal_code" class="block text-sm font-semibold text-ink mb-2">کد پستی</label>
                    <input type="text" id="postal_code" name="postal_code" class="field" placeholder="۱۲۳۴۵۶۷۸۹۰" maxlength="10" pattern="\d{10}" dir="ltr" inputmode="numeric" autocomplete="postal-code">
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_default" id="is_default" value="1" class="rounded border-line text-brand focus:ring-brand focus:ring-2">
                        <span class="text-sm font-medium text-ink">تنظیم به عنوان آدرس پیش‌فرض</span>
                    </label>
                </div>

                <div id="form-error" class="hidden rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeAddressModal()" class="btn btn-outline flex-1">انصراف</button>
                    <button type="submit" class="btn btn-primary flex-1">
                        <span class="btn-text">ذخیره آدرس</span>
                        <span class="btn-loading hidden flex items-center gap-2">
                            <svg class="animate-spin size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                                <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1" />
                            </svg>
                            در حال ذخیره...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-ink/40 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl animate-tick">
            <div class="p-5">
                <h3 id="delete-modal-title" class="text-lg font-extrabold text-ink">حذف آدرس</h3>
                <p class="mt-2 text-sm text-muted">آیا از حذف این آدرس مطمئن هستید؟ این عملیات غیرقابل بازگشت است.</p>
                <div id="delete-address-name" class="mt-3 p-3 rounded-xl bg-canvas font-medium text-ink"></div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-outline flex-1">انصراف</button>
                    <button type="button" id="confirm-delete-btn" class="btn btn-accent flex-1">حذف</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addAddressBtn = document.getElementById('add-address-btn');
    const addFirstAddressBtn = document.getElementById('add-first-address-btn');
    const addressModal = document.getElementById('address-modal');
    const addressForm = document.getElementById('address-form');
    const deleteModal = document.getElementById('delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const formError = document.getElementById('form-error');
    const deleteAddressName = document.getElementById('delete-address-name');
    
    let deleteAddressId = null;
    let isEditing = false;

    const faDigits = '۰۱۲۳۴۵۶۷۸۹';
    const enDigits = '0123456789';
    const toEn = (s) => String(s).replace(/[۰-۹]/g, d => enDigits[faDigits.indexOf(d)]);
    const toFa = (s) => String(s).replace(/[0-9]/g, d => faDigits[Number(d)]);

    // Open modal for new address
    function openAddressModal(addressData = null) {
        isEditing = !!addressData;
        const modalTitle = addressModal.querySelector('#modal-title');
        const submitBtn = addressModal.querySelector('button[type="submit"] .btn-text');
        const formMethod = document.getElementById('form_method');
        const addressIdInput = document.getElementById('address_id');

        // Reset form
        addressForm.reset();
        formError.classList.add('hidden');
        
        if (addressData) {
            modalTitle.textContent = 'ویرایش آدرس';
            submitBtn.textContent = 'به‌روزرسانی آدرس';
            formMethod.value = 'PUT';
            addressIdInput.value = addressData.id;
            
            // Fill form
            document.getElementById('title').value = addressData.title;
            document.getElementById('first_name').value = addressData.first_name;
            document.getElementById('last_name').value = addressData.last_name;
            document.getElementById('phone').value = toFa(toEn(addressData.phone).replace(/^0/, ''));
            document.getElementById('province').value = addressData.province;
            document.getElementById('city').value = addressData.city;
            document.getElementById('address_line').value = addressData.address_line;
            document.getElementById('postal_code').value = addressData.postal_code || '';
            document.getElementById('is_default').checked = addressData.is_default;
        } else {
            modalTitle.textContent = 'افزودن آدرس جدید';
            submitBtn.textContent = 'ذخیره آدرس';
            formMethod.value = 'POST';
            addressIdInput.value = '';
            
            // Pre-fill with user's name if available
            const userFirstName = '{{ $user->first_name ?? '' }}';
            const userLastName = '{{ $user->last_name ?? '' }}';
            const userPhone = '{{ $user->phone ?? '' }}';
            
            if (userFirstName) document.getElementById('first_name').value = userFirstName;
            if (userLastName) document.getElementById('last_name').value = userLastName;
            if (userPhone) document.getElementById('phone').value = toFa(toEn(userPhone).replace(/^0/, ''));
        }

        addressModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => document.getElementById('title').focus(), 100);
    }

    function closeAddressModal() {
        addressModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openDeleteModal(addressId, addressTitle) {
        deleteAddressId = addressId;
        deleteAddressName.textContent = addressTitle;
        deleteModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        deleteAddressId = null;
        deleteModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Event listeners
    if (addAddressBtn) {
        addAddressBtn.addEventListener('click', () => openAddressModal());
    }
    
    if (addFirstAddressBtn) {
        addFirstAddressBtn.addEventListener('click', () => openAddressModal());
    }

    // Edit buttons
    document.querySelectorAll('.edit-address-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.address-card');
            const addressData = JSON.parse(card.dataset.address);
            openAddressModal(addressData);
        });
    });

    // Set default buttons
    document.querySelectorAll('.set-default-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const addressId = this.dataset.addressId;
            const btnEl = this;
            
            btnEl.disabled = true;
            btnEl.innerHTML = '<svg class="animate-spin size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25" /><path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1" /></svg>';

            try {
                const response = await fetch(`{{ route('profile.addresses.default', ':id') }}`.replace(':id', addressId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<svg class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z" /></svg>';
                }
            } catch (error) {
                alert('خطای شبکه. لطفاً مجدداً تلاش کنید.');
                btnEl.disabled = false;
                btnEl.innerHTML = '<svg class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z" /></svg>';
            }
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-address-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.dataset.addressId;
            const card = this.closest('.address-card');
            const title = card.querySelector('h3').textContent.trim();
            openDeleteModal(addressId, title);
        });
    });

    // Confirm delete
    confirmDeleteBtn.addEventListener('click', async function() {
        if (!deleteAddressId) return;

        this.disabled = true;
        this.textContent = 'در حال حذف...';

        try {
            const response = await fetch(`{{ route('profile.addresses.destroy', ':id') }}`.replace(':id', deleteAddressId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
                this.disabled = false;
                this.textContent = 'حذف';
            }
        } catch (error) {
            alert('خطای شبکه. لطفاً مجدداً تلاش کنید.');
            this.disabled = false;
            this.textContent = 'حذف';
        }
    });

    // Form submission
    addressForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        const isEdit = data._method === 'PUT';
        const addressId = data.address_id;
        
        const url = isEdit 
            ? `{{ route('profile.addresses.update', ':id') }}`.replace(':id', addressId)
            : '{{ route('profile.addresses.store') }}';
        
        const method = isEdit ? 'PUT' : 'POST';

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('hidden');
        submitBtn.querySelector('.btn-loading').classList.remove('hidden');
        formError.classList.add('hidden');

        // Normalize phone number to 09xxxxxxxxx
        if (data.phone) {
            const digits = toEn(data.phone).replace(/\D/g, '');
            data.phone = digits.startsWith('0') ? digits : '0' + digits;
        }
        // Normalize postal code
        if (data.postal_code) {
            data.postal_code = toEn(data.postal_code).replace(/\D/g, '');
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                closeAddressModal();
                window.location.reload();
            } else {
                formError.textContent = result.message;
                formError.classList.remove('hidden');
            }
        } catch (error) {
            formError.textContent = 'خطای شبکه. لطفاً مجدداً تلاش کنید.';
            formError.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').classList.remove('hidden');
            submitBtn.querySelector('.btn-loading').classList.add('hidden');
        }
    });

    // Phone input formatting
    const phoneInput = document.getElementById('phone');
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/[۰-۹]/g, d => englishDigits[persianDigits.indexOf(d)]);
        value = value.replace(/\D/g, '').slice(0, 10);
        this.value = value.replace(/[0-9]/g, d => persianDigits[Number(d)]);
    });

    // Postal code formatting
    const postalCodeInput = document.getElementById('postal_code');
    postalCodeInput.addEventListener('input', function() {
        let value = this.value.replace(/[۰-۹]/g, d => englishDigits[persianDigits.indexOf(d)]);
        value = value.replace(/\D/g, '').slice(0, 10);
        this.value = value.replace(/[0-9]/g, d => persianDigits[Number(d)]);
    });

    // Close modals on backdrop click
    addressModal.addEventListener('click', function(e) {
        if (e.target === this) closeAddressModal();
    });
    deleteModal.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddressModal();
            closeDeleteModal();
        }
    });
});
</script>
@endpush