<?php

declare(strict_types=1);

namespace App\Data\Branches;

use Spatie\LaravelData\Data;

class BranchFormOptionsData extends Data
{
    /**
     * @param  array<int, array{value:string,label:string}>  $types
     * @param  array<int, array{value:string,label:string}>  $activeOptions
     */
    public function __construct(
        public array $types,
        public array $activeOptions,
    ) {}

    public static function make(): self
    {
        return new self(
            types: array_map(
                static fn (string $type): array => [
                    'value' => $type,
                    'label' => __("admin_branches.types.{$type}"),
                ],
                BranchType::values(),
            ),
            activeOptions: [
                ['value' => '', 'label' => __('common.all')],
                ['value' => '1', 'label' => __('common.active')],
                ['value' => '0', 'label' => __('common.inactive')],
            ],
        );
    }
}
