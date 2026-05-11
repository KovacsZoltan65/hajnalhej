<?php

namespace App\Http\Requests\Checkout;

use App\Data\Branches\BranchType;
use App\Enums\Orders\FulfillmentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $requiresShippingAddress = $this->input('fulfillment_method') === FulfillmentMethod::DELIVERY->value
            && ! $this->boolean('same_as_billing');

        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'pickup_time_slot' => ['nullable', 'string', 'max:60'],
            'fulfillment_method' => ['required', Rule::in(FulfillmentMethod::values())],
            'pickup_branch_id' => [
                Rule::requiredIf($this->input('fulfillment_method') === FulfillmentMethod::PICKUP->value),
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(static fn ($query) => $query
                    ->where('active', true)
                    ->whereIn('type', [
                        BranchType::BAKERY,
                        BranchType::SHOP,
                        BranchType::PICKUP_POINT,
                    ])),
            ],
            'billing_address' => ['required', 'array'],
            'billing_address.name' => ['required', 'string', 'max:255'],
            'billing_address.country' => ['required', 'string', 'max:100'],
            'billing_address.postal_code' => ['required', 'string', 'max:20'],
            'billing_address.city' => ['required', 'string', 'max:100'],
            'billing_address.street' => ['required', 'string', 'max:255'],
            'billing_address.house_number' => ['required', 'string', 'max:50'],
            'billing_address.floor' => ['nullable', 'string', 'max:50'],
            'billing_address.door' => ['nullable', 'string', 'max:50'],
            'billing_address.company_name' => ['nullable', 'string', 'max:255'],
            'billing_address.tax_number' => ['nullable', 'string', 'max:50'],
            'billing_address.phone' => ['nullable', 'string', 'max:50'],
            'billing_address.notes' => ['nullable', 'string', 'max:1000'],
            'shipping_address' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'array'],
            'shipping_address.name' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'string', 'max:255'],
            'shipping_address.country' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'string', 'max:100'],
            'shipping_address.postal_code' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'string', 'max:20'],
            'shipping_address.city' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'string', 'max:100'],
            'shipping_address.street' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'string', 'max:255'],
            'shipping_address.house_number' => [Rule::requiredIf($requiresShippingAddress), 'nullable', 'string', 'max:50'],
            'shipping_address.floor' => ['nullable', 'string', 'max:50'],
            'shipping_address.door' => ['nullable', 'string', 'max:50'],
            'shipping_address.company_name' => ['nullable', 'string', 'max:255'],
            'shipping_address.tax_number' => ['nullable', 'string', 'max:50'],
            'shipping_address.phone' => ['nullable', 'string', 'max:50'],
            'shipping_address.notes' => ['nullable', 'string', 'max:1000'],
            'same_as_billing' => ['required', 'boolean'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
            'accept_privacy' => ['required', 'accepted'],
            'accept_terms' => ['required', 'accepted'],
        ];
    }
}
