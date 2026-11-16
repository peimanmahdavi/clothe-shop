@php
    $appliesTo = old('applies_to', $discount->applies_to ?? 'all');
    $selectedIds = static fn (string $field): array => collect(old($field, $discount->{$field} ?? []))
        ->map(fn ($v) => (int) $v)
        ->all();
@endphp

<div class="space-y-3" data-scope-root>
    <div>
        <label for="applies_to" class="mb-1.5 block text-xs font-bold text-ink">این جشنواره روی چه چیزی اعمال شود؟</label>
        <select id="applies_to" name="applies_to" class="field" data-scope-select>
            <option value="all" @selected($appliesTo === 'all')>همه محصولات</option>
            <option value="products" @selected($appliesTo === 'products')>محصولات انتخابی</option>
            <option value="brands" @selected($appliesTo === 'brands')>برندهای انتخابی</option>
            <option value="categories" @selected($appliesTo === 'categories')>دسته‌بندی‌های انتخابی</option>
        </select>
        @error('applies_to')<p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>@enderror
    </div>

    <div data-scope-panel="products" @class(['hidden' => $appliesTo !== 'products'])>
        <label for="product_ids" class="mb-1.5 block text-xs font-bold text-ink">محصولات (با Ctrl چند مورد انتخاب کنید)</label>
        <select id="product_ids" name="product_ids[]" multiple class="field h-32">
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedIds('product_ids'), true))>{{ $product->name }}</option>
            @endforeach
        </select>
        @error('product_ids')<p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>@enderror
    </div>

    <div data-scope-panel="brands" @class(['hidden' => $appliesTo !== 'brands'])>
        <label for="brand_ids" class="mb-1.5 block text-xs font-bold text-ink">برندها</label>
        <select id="brand_ids" name="brand_ids[]" multiple class="field h-32">
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected(in_array($brand->id, $selectedIds('brand_ids'), true))>{{ $brand->name }}</option>
            @endforeach
        </select>
        @error('brand_ids')<p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>@enderror
    </div>

    <div data-scope-panel="categories" @class(['hidden' => $appliesTo !== 'categories'])>
        <label for="category_ids" class="mb-1.5 block text-xs font-bold text-ink">دسته‌بندی‌ها</label>
        <select id="category_ids" name="category_ids[]" multiple class="field h-32">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedIds('category_ids'), true))>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_ids')<p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>@enderror
    </div>
</div>

<script>
    (function () {
        const root = document.querySelector('[data-scope-root]');
        if (!root) return;
        const select = root.querySelector('[data-scope-select]');
        const panels = root.querySelectorAll('[data-scope-panel]');
        const sync = () => {
            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.scopePanel !== select.value);
            });
        };
        select.addEventListener('change', sync);
        sync();
    })();
</script>