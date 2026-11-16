<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'پیراهن و تی‌شرت', 'description' => 'پیراهن، تی‌شرت و پولوشرت نخی برای استفاده روزمره', 'sort_order' => 1],
            ['name' => 'پوشاک زنانه', 'description' => 'پیراهن، تاپ و ست‌های زنانه با برش مینیمال', 'sort_order' => 2],
            ['name' => 'کفش', 'description' => 'کتانی و دمپایی راحت برای پیاده‌روی و روزهای کاری', 'sort_order' => 3],
            ['name' => 'کیف و اکسسوری', 'description' => 'کیف دستی و اکسسوری تکمیل‌کننده استایل', 'sort_order' => 4],
        ])->mapWithKeys(fn (array $category): array => [
            $category['name'] => Category::create($category),
        ]);

        $brands = collect([
            ['name' => 'آسمان‌پوش', 'description' => 'برند اختصاصی فروشگاه'],
            ['name' => 'کلاسیک‌ویر', 'description' => 'پیراهن و پوشاک کلاسیک'],
            ['name' => 'فشن‌ترندز', 'description' => 'پوشاک زنانه و اکسسوری'],
            ['name' => 'نایک', 'description' => 'کفش ورزشی و رانینگ'],
        ])->mapWithKeys(fn (array $brand): array => [
            $brand['name'] => Brand::create($brand),
        ]);

        $products = [
            [
                'name' => 'پیراهن چهارخانه آبی و مشکی',
                'category' => 'پیراهن و تی‌شرت',
                'brand' => 'کلاسیک‌ویر',
                'price' => 890000,
                'sale_price' => 490000,
                'stock' => 18,
                'rating' => 4.9,
                'reviews_count' => 862,
                'is_featured' => true,
                'images' => ['p83-1.webp', 'p83-2.webp', 'p83-3.webp'],
            ],
            [
                'name' => 'تی‌شرت نخی آستین کوتاه',
                'category' => 'پیراهن و تی‌شرت',
                'brand' => 'آسمان‌پوش',
                'price' => 420000,
                'sale_price' => 295000,
                'stock' => 34,
                'rating' => 4.8,
                'reviews_count' => 534,
                'is_featured' => true,
                'images' => ['p84-1.webp', 'p84-2.webp', 'p84-3.webp'],
            ],
            [
                'name' => 'پیراهن آبی دخترانه',
                'category' => 'پوشاک زنانه',
                'brand' => 'فشن‌ترندز',
                'price' => 1150000,
                'sale_price' => 805000,
                'stock' => 12,
                'rating' => 4.7,
                'reviews_count' => 318,
                'is_featured' => true,
                'images' => ['p162-1.webp', 'p162-2.webp', 'p162-3.webp'],
            ],
            [
                'name' => 'پیراهن تابستانی گلدار',
                'category' => 'پوشاک زنانه',
                'brand' => 'فشن‌ترندز',
                'price' => 980000,
                'sale_price' => 686000,
                'stock' => 9,
                'rating' => 4.6,
                'reviews_count' => 245,
                'is_featured' => false,
                'images' => ['p163-1.webp', 'p163-2.webp', 'p163-3.webp'],
            ],
            [
                'name' => 'پیراهن مجلسی مشکی',
                'category' => 'پوشاک زنانه',
                'brand' => 'آسمان‌پوش',
                'price' => 1850000,
                'sale_price' => 1295000,
                'stock' => 4,
                'rating' => 4.9,
                'reviews_count' => 726,
                'is_featured' => true,
                'images' => ['p177-1.webp', 'p177-2.webp', 'p177-3.webp'],
            ],
            [
                'name' => 'کورست چرمی با دامن',
                'category' => 'پوشاک زنانه',
                'brand' => 'آسمان‌پوش',
                'price' => 2180000,
                'sale_price' => 1690000,
                'stock' => 6,
                'rating' => 4.8,
                'reviews_count' => 412,
                'is_featured' => false,
                'images' => ['p178-1.webp', 'p178-2.webp', 'p178-3.webp'],
            ],
            [
                'name' => 'کتانی ایر جردن قرمز و مشکی',
                'category' => 'کفش',
                'brand' => 'نایک',
                'price' => 3260000,
                'sale_price' => 2690000,
                'stock' => 3,
                'rating' => 5.0,
                'reviews_count' => 903,
                'is_featured' => true,
                'images' => ['p88-1.webp', 'p88-2.webp', 'p88-3.webp'],
            ],
            [
                'name' => 'دمپایی راحتی قهوه‌ای',
                'category' => 'کفش',
                'brand' => 'آسمان‌پوش',
                'price' => 690000,
                'sale_price' => 483000,
                'stock' => 27,
                'rating' => 4.5,
                'reviews_count' => 198,
                'is_featured' => false,
                'images' => ['p185-1.webp', 'p185-2.webp', 'p185-3.webp'],
            ],
            [
                'name' => 'کیف دستی آبی زنانه',
                'category' => 'کیف و اکسسوری',
                'brand' => 'فشن‌ترندز',
                'price' => 1180000,
                'sale_price' => 826000,
                'stock' => 15,
                'rating' => 4.7,
                'reviews_count' => 366,
                'is_featured' => false,
                'images' => ['p172-1.webp', 'p172-2.webp', 'p172-3.webp'],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::create([
                'name' => $data['name'],
                'category_id' => $categories[$data['category']]->id,
                'brand_id' => $brands[$data['brand']]->id,
                'description' => 'پارچه باکیفیت، دوخت تمیز و برش راحت؛ مناسب استفاده روزمره و استایل مینیمال.',
                'price' => $data['price'],
                'sale_price' => $data['sale_price'],
                'stock' => $data['stock'],
                'sizes' => $data['sizes'] ?? (in_array($data['category'], ['کفش'], true) ? ['40', '41', '42', '43'] : ['S', 'M', 'L', 'XL']),
                'colors' => $data['colors'] ?? ['مشکی', 'سفید', 'آبی'],
                'rating' => $data['rating'],
                'reviews_count' => $data['reviews_count'],
                'is_featured' => $data['is_featured'],
                'is_active' => true,
            ]);

            foreach ($data['images'] as $index => $image) {
                $product->images()->create([
                    'path' => $image,
                    'sort_order' => $index,
                ]);
            }
        }

        Discount::create([
            'title' => 'جشنواره پایان فصل پاییز',
            'code' => null,
            'percentage' => 45,
            'description' => 'تخفیف روی منتخبی از پیراهن‌ها، کفش‌ها و کیف‌های کالکشن پاییز.',
            'starts_at' => now()->subDays(12),
            'ends_at' => now()->addDays(3),
            'is_active' => true,
        ]);

        Discount::create([
            'title' => 'پیشنهاد دانشجویی',
            'code' => 'SKY25',
            'percentage' => 25,
            'description' => 'با کارت دانشجویی روی همه محصولات فروشگاه.',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
            'is_active' => true,
        ]);

        Discount::create([
            'title' => 'حراج آخر هفته',
            'code' => 'WEEKEND',
            'percentage' => 15,
            'description' => 'جشنواره هفته گذشته که به پایان رسیده است.',
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->subDays(2),
            'is_active' => false,
        ]);

        $customers = [
            'مریم رضایی', 'سینا احمدی', 'نگار موسوی', 'علی کریمی', 'پریسا شریفی',
            'محمد حسینی', 'الهام نادری', 'رضا تهرانی', 'سارا کاظمی', 'امیر صادقی',
        ];

        $productModels = Product::orderBy('id')->get();

        $statuses = ['pending', 'processing', 'shipped', 'completed', 'completed', 'completed', 'processing', 'shipped'];

        for ($i = 0; $i < 48; $i++) {
            $daysAgo = intdiv($i, 2);

            $product = $productModels[$i % $productModels->count()];
            $unitPrice = $product->sale_price ?? $product->price;
            $quantity = 1 + ($i % 3);

            Order::create([
                'number' => (string) (1024 + $i),
                'product_id' => $product->id,
                'customer_name' => $customers[$i % count($customers)],
                'phone' => '0912'.str_pad((string) (1000000 + $i * 37), 7, '0', STR_PAD_LEFT),
                'total' => $unitPrice * $quantity,
                'status' => $statuses[$i % count($statuses)],
                'size' => is_array($product->sizes) ? ($product->sizes[0] ?? null) : null,
                'color' => is_array($product->colors) ? ($product->colors[0] ?? null) : null,
                'placed_at' => now()->subDays($daysAgo)->setTime(9 + ($i % 10), ($i * 7) % 60),
            ]);
        }
    }
}
