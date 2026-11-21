<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create(['phone' => '09120000020']);
    }

    public function test_admin_can_open_user_management_with_kpis(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('مدیریت کاربران')
            ->assertSee('کل کاربران')
            ->assertSee('مدیران سیستم');
    }

    public function test_regular_user_cannot_open_admin_user_management(): void
    {
        $this->actingAs(User::factory()->create(['phone' => '09120000021']));

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_banned_user_is_blocked_even_with_a_valid_session(): void
    {
        $user = User::factory()->inactive()->create(['phone' => '09120000022']);
        $this->actingAs($user);

        $this->get(route('profile.dashboard'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->delete(route('admin.users.destroy', $admin))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_ban_or_demote_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'phone' => $admin->phone,
            'is_admin' => 0,
            'is_active' => 1,
        ])->assertForbidden();

        $this->post(route('admin.users.toggle-status', $admin))->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_last_active_admin_is_detected(): void
    {
        $admin = $this->admin();

        $this->assertTrue($admin->isLastActiveAdmin());
    }

    public function test_user_creation_rejects_weak_password_and_assigns_no_privileges_by_default(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.users.store'), [
            'name' => 'کاربر تست',
            'phone' => '09120000023',
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ])->assertSessionHasErrors('password');

        $this->post(route('admin.users.store'), [
            'name' => 'کاربر تست',
            'phone' => '09120000023',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'phone' => '09120000023',
            'is_admin' => false,
            'is_active' => true,
        ]);
    }

    public function test_customer_cannot_view_another_customers_order_or_upload_its_receipt(): void
    {
        $owner = User::factory()->create(['phone' => '09120000024']);
        $attacker = User::factory()->create(['phone' => '09120000025']);

        $order = Order::create([
            'number' => 'ORD-TEST-0001',
            'user_id' => $owner->id,
            'customer_name' => $owner->name,
            'phone' => $owner->phone,
            'total' => 100000,
            'status' => 'pending_transaction',
            'placed_at' => now(),
        ]);

        $this->actingAs($attacker);

        $this->get(route('profile.orders.show', $order))->assertForbidden();
        $this->get(route('profile.orders.receipt', $order))->assertForbidden();

        Storage::fake('public');
        $this->post(route('profile.orders.receipt.store', $order), [
            'tracking_code' => '123456',
            'receipt_image' => UploadedFile::fake()->image('receipt.png'),
        ])->assertForbidden();
    }
}
