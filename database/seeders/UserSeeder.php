<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminPhone = env('ADMIN_PHONE', '09120000000');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');
        $testPhone = env('TEST_USER_PHONE', '09123456789');
        $testPassword = env('TEST_USER_PASSWORD', 'password');

        // Create AdminMiddleware User
        $admin = User::create([
            'name' => 'مدیر فروشگاه',
            'phone' => $adminPhone,
            'password' => Hash::make($adminPassword),
            'first_name' => 'مدیر',
            'last_name' => 'فروشگاه',
            'email_verified_at' => now(),
        ]);
        // Privileged flags are not mass-assignable by design.
        $admin->forceFill(['is_admin' => true, 'is_active' => true])->save();

        // Create default address for admin
        Address::create([
            'user_id' => $admin->id,
            'title' => 'دفتر کار',
            'first_name' => 'مدیر',
            'last_name' => 'فروشگاه',
            'phone' => $adminPhone,
            'province' => 'تهران',
            'city' => 'تهران',
            'address_line' => 'خیابان ولیعصر، برج آسمان، طبقه سوم',
            'postal_code' => '1234567890',
            'is_default' => true,
        ]);

        // Create Regular Test User
        $user = User::create([
            'name' => 'علی احمدی',
            'phone' => $testPhone,
            'password' => Hash::make($testPassword),
            'first_name' => 'علی',
            'last_name' => 'احمدی',
            'email_verified_at' => now(),
        ]);
        $user->forceFill(['is_admin' => false, 'is_active' => true])->save();

        // Create addresses for regular user
        Address::create([
            'user_id' => $user->id,
            'title' => 'خانه',
            'first_name' => 'علی',
            'last_name' => 'احمدی',
            'phone' => '09123456789',
            'province' => 'تهران',
            'city' => 'تهران',
            'address_line' => 'خیابان شهید بهشتی، کوچه ۵، پلاک ۱۲، واحد ۳',
            'postal_code' => '1598765432',
            'is_default' => true,
        ]);

        Address::create([
            'user_id' => $user->id,
            'title' => 'محل کار',
            'first_name' => 'علی',
            'last_name' => 'احمدی',
            'phone' => '09123456789',
            'province' => 'تهران',
            'city' => 'تهران',
            'address_line' => 'میرداماد، خیابان ن сами، پلاک ۴۵، طبقه دوم',
            'postal_code' => '1987654321',
            'is_default' => false,
        ]);

        // Create additional test users
        $testUsers = [
            [
                'name' => 'مریم رضایی',
                'phone' => '09351234567',
                'first_name' => 'مریم',
                'last_name' => 'رضایی',
            ],
            [
                'name' => 'محمد کریمی',
                'phone' => '09198765432',
                'first_name' => 'محمد',
                'last_name' => 'کریمی',
            ],
            [
                'name' => 'فاطمه محمدی',
                'phone' => '09133334444',
                'first_name' => 'فاطمه',
                'last_name' => 'محمدی',
            ],
        ];

        foreach ($testUsers as $testUser) {
            $createdUser = User::create([
                'name' => $testUser['name'],
                'phone' => $testUser['phone'],
                'password' => Hash::make('password'),
                'first_name' => $testUser['first_name'],
                'last_name' => $testUser['last_name'],
                'email_verified_at' => now(),
            ]);
            $createdUser->forceFill(['is_admin' => false, 'is_active' => true])->save();

            // Create a default address for each test user
            Address::create([
                'user_id' => $createdUser->id,
                'title' => 'خانه',
                'first_name' => $testUser['first_name'],
                'last_name' => $testUser['last_name'],
                'phone' => $testUser['phone'],
                'province' => 'تهران',
                'city' => 'تهران',
                'address_line' => 'آدرس پیش‌فرض برای '.$testUser['name'],
                'postal_code' => '1111111111',
                'is_default' => true,
            ]);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info("AdminMiddleware: {$adminPhone} / {$adminPassword}");
        $this->command->info("Test User: {$testPhone} / {$testPassword}");
        $this->command->info('Debug OTP for all users: 123456');
    }
}
