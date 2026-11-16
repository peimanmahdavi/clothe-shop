<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    protected const RULES = [
        'title' => ['required', 'string', 'max:50'],
        'first_name' => ['required', 'string', 'max:50'],
        'last_name' => ['required', 'string', 'max:50'],
        'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
        'province' => ['required', 'string', 'max:100'],
        'city' => ['required', 'string', 'max:100'],
        'address_line' => ['required', 'string', 'max:500'],
        'postal_code' => ['nullable', 'string', 'regex:/^\d{10}$/'],
        'is_default' => ['boolean'],
    ];

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(self::RULES);
        $user = $request->user();

        $isFirstAddress = $user->addresses()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirstAddress;

        if ($isDefault) {
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'province' => $data['province'],
            'city' => $data['city'],
            'address_line' => $data['address_line'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت اضافه شد.',
            'data' => $address,
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $data = $request->validate(self::RULES);

        if ($request->boolean('is_default') && !$address->is_default) {
            $address->user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address->update([
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'province' => $data['province'],
            'city' => $data['city'],
            'address_line' => $data['address_line'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => $request->boolean('is_default', $address->is_default),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت به‌روزرسانی شد.',
            'data' => $address->fresh(),
        ]);
    }

    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $wasDefault = $address->is_default;
        $user = $address->user;
        $address->delete();

        if ($wasDefault) {
            $newDefault = $user->addresses()->latest()->first();
            $newDefault?->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت حذف شد.',
        ]);
    }

    public function setDefault(Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address->user->addresses()->where('is_default', true)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس پیش‌فرض تنظیم شد.',
            'data' => $address->fresh(),
        ]);
    }
}