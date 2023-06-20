<?php

namespace App\Shop\Customers\Requests;

use App\Shop\Base\BaseFormRequest;

class CreateCustomerRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'blockchain_wallet_seed' => ['required'],
            'email' => ['required', 'email', 'unique:customers'],
            'password' => ['required', 'min:8']
        ];
    }
}
