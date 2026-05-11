<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Order\Delivery;

use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;

class AssignCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::ORDERS_UPDATE) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'courier_id' => ['required', 'integer', 'exists:couriers,id'],
            'delivery_scheduled_at' => ['nullable', 'date'],
        ];
    }
}
