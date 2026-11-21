<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class PaymentCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order && $this->user()?->can('view', $order) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:success,failed'],
            'ref_id' => ['nullable', 'string', 'max:191'],
            'card_number' => ['nullable', 'string', 'max:32'],
            'gateway' => ['nullable', 'string', 'max:50'],
        ];
    }
}
