<?php

use App\Support\InertiaPage;

it('inertia page enum values point to existing vue pages', function (): void {
    $missing = [];

    foreach (InertiaPage::cases() as $page) {
        $path = resource_path("js/Pages/{$page->value}.vue");

        if (! is_file($path)) {
            $missing[] = "{$page->name}: {$page->value}";
        }
    }

    expect($missing)->toBe([]);
});

it('inertia page enum values are unique', function (): void {
    $values = array_map(
        static fn (InertiaPage $page): string => $page->value,
        InertiaPage::cases(),
    );

    expect($values)->toHaveCount(count(array_unique($values)));
});
