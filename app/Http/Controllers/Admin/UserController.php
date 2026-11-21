<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexUserRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(IndexUserRequest $request): View
    {
        $filters = $request->filters();
        $term = '%'.$filters['search'].'%';

        $users = User::query()
            ->withCount('orders')
            ->when($filters['search'] !== '', function ($query) use ($term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($filters['role'] === 'admin', fn ($query) => $query->admins())
            ->when($filters['role'] === 'user', fn ($query) => $query->where('is_admin', false))
            ->when($filters['status'] === 'active', fn ($query) => $query->active())
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $kpi = [
            'total' => User::query()->count(),
            'active' => User::query()->active()->count(),
            'admins' => User::query()->admins()->count(),
            'new_this_month' => User::query()->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'kpi' => $kpi,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['is_active' => true]),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = new User;
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'password' => $data['password'],
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
        ]);
        // Privileged flags require an explicit, trusted assignment.
        $user->forceFill([
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => $request->boolean('is_active', true),
        ]);
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'کاربر «'.$user->name.'» با موفقیت ایجاد شد.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $demote = array_key_exists('is_admin', $data) && $user->is_admin && ! $request->boolean('is_admin');
        $deactivate = array_key_exists('is_active', $data) && $user->is_active && ! $request->boolean('is_active');

        $this->guardPrivilegedChange($user, $demote, $deactivate);

        DB::transaction(function () use ($request, $user, $data): void {
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->forceFill([
                'is_admin' => $request->boolean('is_admin', $user->is_admin),
                'is_active' => $request->boolean('is_active', $user->is_active),
            ]);

            $user->save();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'اطلاعات کاربر «'.$user->name.'» به‌روزرسانی شد.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Never allow an admin to delete themselves or the last active admin.
        abort_if($user->id === auth()->id(), 403, 'نمی‌توانید حساب خودتان را حذف کنید.');
        abort_if($user->isLastActiveAdmin(), 403, 'آخرین مدیر سیستم را نمی‌توان حذف کرد.');

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'کاربر «'.$name.'» حذف شد.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        // Deactivating yourself, or the last active admin, is forbidden.
        if ($user->is_active) {
            $this->guardPrivilegedChange($user, false, true);
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('status', $user->is_active
            ? 'حساب کاربر فعال شد.'
            : 'حساب کاربر مسدود شد.');
    }

    /**
     * Block self-lockout and removal of the final active administrator.
     */
    private function guardPrivilegedChange(User $user, bool $demote, bool $deactivate): void
    {
        if (! $demote && ! $deactivate) {
            return;
        }

        abort_if($user->id === auth()->id(), 403, 'نمی‌توانید نقش یا وضعیت حساب خودتان را تغییر دهید.');
        abort_if($user->isLastActiveAdmin(), 403, 'آخرین مدیر سیستم را نمی‌توان تنزل داد یا مسدود کرد.');
    }
}
