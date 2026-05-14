<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Export\ExportType;
use App\Models\ExportJob;
use App\Models\User;

class ExportJobPolicy
{
    public function viewAny(User $user): bool
    {
        foreach (ExportType::cases() as $type) {
            if ($user->can($type->permission())) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user, ExportType|string $type): bool
    {
        $type = is_string($type) ? ExportType::tryFrom($type) : $type;

        return $type instanceof ExportType && $user->can($type->permission());
    }

    public function download(User $user, ExportJob $exportJob): bool
    {
        return $user->can($exportJob->type->permission())
            && ($exportJob->created_by === $user->id || $user->hasRole('admin'));
    }
}
