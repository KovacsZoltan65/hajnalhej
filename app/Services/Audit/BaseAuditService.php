<?php

namespace App\Services\Audit;

use App\Models\User;
use Illuminate\Support\Arr;

abstract class BaseAuditService
{
    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @param array<string, mixed> $context
     * @param array<string, mixed> $extraProperties
     */
    protected function log(
        string $logName,
        string $eventKey,
        string $description,
        ?User $actor,
        object $subject,
        array $before,
        array $after,
        array $context = [],
        array $extraProperties = [],
    ): void {
        $properties = array_merge([
            'event_key' => $eventKey,
            'before' => $before,
            'after' => $after,
            'context' => $context,
            'actor_snapshot' => $actor === null ? null : $this->userSnapshot($actor),
        ], $extraProperties);

        $builder = activity()->useLog($logName);

        if ($actor !== null) {
            $builder->causedBy($actor);
        }

        $builder
            ->performedOn($subject)
            ->event($eventKey)
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * @param array<int, string> $values
     * @return array<int, string>
     */
    protected function normalizeList(array $values): array
    {
        $items = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim((string) $value),
            $values,
        ), static fn (string $value): bool => $value !== ''));

        $items = array_values(array_unique($items));
        sort($items);

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    protected function userSnapshot(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => Arr::sort($user->getRoleNames()->values()->all()),
        ];
    }
}
