<?php

namespace App\Support;

class ConversionEventRegistry
{
    public const CTA_CLICK = 'cta.click';
    public const HERO_ASSIGNED = 'hero.assigned';
    public const HERO_VIEWED = 'hero.viewed';

    public const CART_VIEWED = 'cart.viewed';
    public const CART_ITEM_ADDED = 'cart.item_added';
    public const CART_ITEM_UPDATED = 'cart.item_updated';
    public const CART_ITEM_REMOVED = 'cart.item_removed';
    public const CART_CLEARED = 'cart.cleared';

    public const CHECKOUT_VIEWED = 'checkout.viewed';
    public const CHECKOUT_SUBMITTED = 'checkout.submitted';
    public const CHECKOUT_COMPLETED = 'checkout.completed';

    public const REGISTRATION_VIEWED = 'registration.viewed';
    public const REGISTRATION_SUBMITTED = 'registration.submitted';
    public const REGISTRATION_COMPLETED = 'registration.completed';

    /**
     * @return array<int, string>
     */
    public static function eventKeys(): array
    {
        return [
            self::CTA_CLICK,
            self::HERO_ASSIGNED,
            self::HERO_VIEWED,
            self::CART_VIEWED,
            self::CART_ITEM_ADDED,
            self::CART_ITEM_UPDATED,
            self::CART_ITEM_REMOVED,
            self::CART_CLEARED,
            self::CHECKOUT_VIEWED,
            self::CHECKOUT_SUBMITTED,
            self::CHECKOUT_COMPLETED,
            self::REGISTRATION_VIEWED,
            self::REGISTRATION_SUBMITTED,
            self::REGISTRATION_COMPLETED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function funnels(): array
    {
        return [
            'landing',
            'cart',
            'checkout',
            'registration',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::CTA_CLICK => 'CTA kattintás',
            self::HERO_ASSIGNED => 'Hero variáns kiosztva',
            self::HERO_VIEWED => 'Hero blokk megtekintve',
            self::CART_VIEWED => 'Kosár oldal megtekintve',
            self::CART_ITEM_ADDED => 'Termék kosárba téve',
            self::CART_ITEM_UPDATED => 'Kosár mennyiség frissítve',
            self::CART_ITEM_REMOVED => 'Termék törölve a kosárból',
            self::CART_CLEARED => 'Kosár ürítve',
            self::CHECKOUT_VIEWED => 'Checkout megtekintve',
            self::CHECKOUT_SUBMITTED => 'Checkout elküldve',
            self::CHECKOUT_COMPLETED => 'Checkout sikeresen lezárva',
            self::REGISTRATION_VIEWED => 'Regisztrációs oldal megtekintve',
            self::REGISTRATION_SUBMITTED => 'Regisztráció elküldve',
            self::REGISTRATION_COMPLETED => 'Regisztráció sikeres',
        ];
    }
}

