<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadCount(['orders', 'addresses']);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'is_admin' => $user->is_admin,
                'orders_count' => $user->orders_count,
                'addresses_count' => $user->addresses_count,
                'default_address' => $user->getDefaultAddress(),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'regex:/^09\d{9}$/'],
        ]);

        $user = $request->user();

        if (!empty($data['phone']) && $data['phone'] !== $user->phone) {
            $exists = User::where('phone', $data['phone'])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'این شماره تلفن قبلاً ثبت شده است.',
                ], 422);
            }

            $user->phone = $data['phone'];
        }

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->name = trim("{$user->first_name} {$user->last_name}");
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت به‌روزرسانی شد.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }
}