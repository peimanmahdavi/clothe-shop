<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
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

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if (! empty($data['phone'])) {
            $user->phone = $data['phone'];
        }

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->name = trim("{$data['first_name']} {$data['last_name']}");
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت بهروزرسانی شد.',
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
