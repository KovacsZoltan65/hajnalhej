<?php

namespace App\Services;

use Illuminate\Http\Request;

class HeroExperimentService
{
    public const SESSION_KEY = 'experiments.hero_variant';
    public const ASSIGNED_FLAG_SESSION_KEY = 'experiments.hero_variant_assigned';

    public const VARIANT_ARTISAN_STORY = 'artisan_story';
    public const VARIANT_SPEED_CHECKOUT = 'speed_checkout';

    /**
     * @return array{variant:string,is_new_assignment:bool}
     */
    public function resolveVariant(Request $request): array
    {
        $existing = (string) $request->session()->get(self::SESSION_KEY, '');

        if (\in_array($existing, $this->variants(), true)) {
            return [
                'variant' => $existing,
                'is_new_assignment' => false,
            ];
        }

        $variant = random_int(0, 1) === 0
            ? self::VARIANT_ARTISAN_STORY
            : self::VARIANT_SPEED_CHECKOUT;

        $request->session()->put(self::SESSION_KEY, $variant);
        $request->session()->put(self::ASSIGNED_FLAG_SESSION_KEY, true);

        return [
            'variant' => $variant,
            'is_new_assignment' => true,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function variants(): array
    {
        return [
            self::VARIANT_ARTISAN_STORY,
            self::VARIANT_SPEED_CHECKOUT,
        ];
    }
}

