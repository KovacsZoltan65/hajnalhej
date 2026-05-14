<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Export\ExportRequestData;
use App\Enums\Export\ExportStatus;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExportJobRepository
{
    public function create(ExportRequestData $data, User $user): ExportJob
    {
        return ExportJob::query()->create([
            'type' => $data->type,
            'format' => $data->format,
            'status' => ExportStatus::Pending,
            'filters' => $data->filters,
            'created_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = ExportJob::query()
            ->with('creator:id,name,email')
            ->latest();

        if (! $user->hasRole('admin')) {
            $query->where('created_by', $user->id);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
