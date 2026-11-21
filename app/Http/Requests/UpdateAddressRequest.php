<?php

namespace App\Http\Requests;

use App\Models\Address;

class UpdateAddressRequest extends AddressRequest
{
    public function authorize(): bool
    {
        $address = $this->route('address');

        return $address instanceof Address && $this->user()?->can('update', $address) === true;
    }
}
