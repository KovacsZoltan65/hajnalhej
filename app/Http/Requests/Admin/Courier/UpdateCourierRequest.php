<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Courier;

use App\Enums\Delivery\VehicleType;
use App\Models\Courier;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(PermissionRegistry::COURIERS_UPDATE) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(Courier::statuses())],
            'vehicle_type' => ['nullable', Rule::in(VehicleType::values())],
            'notes' => ['nullable', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
