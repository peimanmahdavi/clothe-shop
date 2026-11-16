@php
    $editing = $product->exists;
    $toPersian = static fn (string|int $value): string => strtr((string) $value, [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);
@endphp

<form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="grid items-start gap-4 lg:grid-cols-3">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="space-y-4 lg:col-span-2">
        <div class="admin-card space-y-4 p-5">
            <h2 class="text-sm font-extrabold text-ink">اطلاعات محصول</h2>

            <div>
                <label for="name" class="mb-1.5 block text-xs font-bold text-ink">نام محصول</label>
                <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" class="field" placeholder="مثلاً پیراهن چهارخانه آبی و مشکی" required>
                @error('name')
                    <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category_id" class="mb-1.5 block text-xs font-bold text-ink">دسته‌بندی</label>
                    <select id="category_id" name="category_id" class="field" required>
                        <option value="">انتخاب دسته‌بندی</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand_id" class="mb-1.5 block text-xs font-bold text-ink">برند</label>
                    <select id="brand_id" name="brand_id" class="field">
                        <option value="">بدون برند</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="price" class="mb-1.5 block text-xs font-bold text-ink">قیمت (تومان)</label>
                    <input id="price" name="price" type="number" min="1000" step="1000" value="{{ old('price', $product->price) }}" class="field" dir="ltr" required>
                    @error('price')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sale_price" class="mb-1.5 block text-xs font-bold text-ink">قیمت با تخفیف (اختیاری)</label>
                    <input id="sale_price" name="sale_price" type="number" min="0" step="1000" value="{{ old('sale_price', $product->sale_price) }}" class="field" dir="ltr" placeholder="خالی بگذارید اگر تخفیف ندارد">
                    @error('sale_price')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="stock" class="mb-1.5 block text-xs font-bold text-ink">موجودی انبار</label>
                    <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" class="field" dir="ltr" required>
                    @error('stock')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rating" class="mb-1.5 block text-xs font-bold text-ink">امتیاز (۰ تا ۵)</label>
                    <input id="rating" name="rating" type="number" min="0" max="5" step="0.1" value="{{ old('rating', $product->rating ?? 4.5) }}" class="field" dir="ltr">
                    @error('rating')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reviews_count" class="mb-1.5 block text-xs font-bold text-ink">تعداد نظرات</label>
                    <input id="reviews_count" name="reviews_count" type="number" min="0" value="{{ old('reviews_count', $product->reviews_count ?? 0) }}" class="field" dir="ltr">
                    @error('reviews_count')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیحات</label>
                <textarea id="description" name="description" rows="4" class="field" placeholder="جنس پارچه، برش و نکات نگهداری">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="admin-card p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-extrabold text-ink">تصاویر محصول</h2>
                <span class="rounded-full bg-brand-soft px-2 py-0.5 text-[10px] font-extrabold text-brand-deep">حداکثر ۳ تصویر</span>
            </div>
            <p class="mt-1 text-[11px] leading-5 text-muted">تصویر وسط در کارت محصول پیش‌فرض نمایش داده می‌شود.</p>

            <label for="images" class="mt-3 flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-brand/40 bg-brand-soft/30 px-3 py-6 text-center transition hover:border-brand hover:bg-brand-soft/60">
                <svg class="size-6 text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 16V4M7 9l5-5 5 5" />
                    <path d="M4 16v2.5A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5V16" />
                </svg>
                <span class="text-[11px] font-bold text-brand-deep">انتخاب تصویر (jpg، png، webp)</span>
                <span class="text-[10px] text-muted">هر تصویر حداکثر ۴ مگابایت</span>
                <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" multiple>
            </label>

            @error('images')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
            @error('images.*')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror

            @if ($editing && $product->images->isNotEmpty())
                <ul class="mt-3 grid grid-cols-3 gap-2">
                    @foreach ($product->images as $index => $image)
                        <li class="relative overflow-hidden rounded-lg border border-line bg-canvas">
                            <img src="{{ $image->url() }}" alt="تصویر {{ $toPersian($index + 1) }}" class="h-20 w-full object-cover" width="120" height="80" loading="lazy">
                            <button type="submit" form="delete-image-{{ $image->id }}" class="absolute end-1 top-1 flex size-6 items-center justify-center rounded-full bg-accent text-white shadow-sm transition hover:bg-accent-deep" aria-label="حذف تصویر {{ $toPersian($index + 1) }}">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6 6 18" />
                                </svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="admin-card space-y-3 p-5">
            <h2 class="text-sm font-extrabold text-ink">انتشار</h2>

            <label class="flex items-center gap-2 text-xs font-semibold text-ink">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', $product->is_active ?? true))>
                نمایش در فروشگاه
            </label>

            <label class="flex items-center gap-2 text-xs font-semibold text-ink">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_featured', $product->is_featured ?? false))>
                نمایش در پرفروش‌ترین‌ها
            </label>

            <div class="flex flex-col gap-2 pt-2">
                <button type="submit" class="btn btn-primary w-full">{{ $editing ? 'ذخیره تغییرات' : 'افزودن محصول' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline w-full">انصراف</a>
            </div>
        </div>
    </div>
</form>

@if ($editing)
    @foreach ($product->images as $image)
        <form id="delete-image-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
