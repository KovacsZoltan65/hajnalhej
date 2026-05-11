# Laravel Data TypeScript export POC

## Státusz

Ez a dokumentum audit és proof-of-concept. Nem kapcsolja be a TypeScript generálást, nem módosít Data osztályokat, és nem alakítja át a Vue oldalak típusrendszerét.

Jelenlegi döntési javaslat: **egyelőre ne vezessük be automatikusan**, csak egy külön, kis PR-ben, amikor a frontend oldalon már van tudatos TypeScript belépési pont.

## Audit eredmény

Composer:

- Telepítve van: `spatie/laravel-data` `4.22.1`.
- Nincs telepítve: `spatie/laravel-typescript-transformer`.
- Nincs telepítve: `spatie/typescript-transformer`.
- A `spatie/laravel-data` saját `composer.json` fájlja dev dependency-ként jelzi a TypeScript transformer támogatást, de ez nem kerül be automatikusan az alkalmazásba.

Laravel config:

- Van publikált `config/data.php`.
- Nincs `config/typescript-transformer.php`.
- Nincs `config/typescript.php`.
- A `config/data.php` jelenleg csak data object, normalizer, validation és structure cache beállításokat tartalmaz.

Artisan parancsok:

- Elérhető: `php artisan data:cache-structures`.
- Elérhető: `php artisan make:data`.
- Nem elérhető: `php artisan typescript:transform`.

Frontend:

- A projekt jelenleg Vue SFC + JavaScript alapú.
- Nincs `tsconfig.json`.
- A Vite build nem TypeScript-alapú alkalmazásként van szervezve.

## Mit kellene telepíteni

A telepített `spatie/laravel-data` dokumentáció szerint TypeScript exporthoz külön csomag kell.

```bash
composer require spatie/laravel-typescript-transformer
php artisan vendor:publish --tag=typescript-transformer-config
```

Ezután a publikált `config/typescript-transformer.php` fájlban be kellene kötni a Laravel Data transformerét:

```php
Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer::class
```

Ha a projekt a collector alapú működést választja, akkor a Laravel Data collector is felvehető:

```php
Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptCollector::class
```

A generáló parancs várhatóan:

```bash
php artisan typescript:transform
```

## Javasolt generált fájl helye

Javasolt célfájl:

```text
resources/js/types/generated/laravel-data.d.ts
```

Indok:

- nem keveredik kézzel írt frontend kóddal,
- importálható Vue komponensekből,
- később `.gitignore` vagy commitolt generated artifact döntés külön meghozható.

Ha a frontend TypeScriptre vált, akkor a kézzel írt domain típusok helye lehet:

```text
resources/js/types/domain.ts
```

Ebben az esetben a generált `laravel-data.d.ts` csak backend contract export lenne.

## POC: kijelölt Data osztályok

### `ProductListItemData`

Forrás:

```text
app/Data/Products/ProductListItemData.php
```

Várható TypeScript forma:

```ts
export type ProductListItemData = {
    id: number;
    category_id: number | null;
    category_name: string | null;
    name: string;
    slug: string;
    short_description: string | null;
    description: string | null;
    price: number;
    is_active: boolean;
    is_featured: boolean;
    stock_status: string;
    image_path: string | null;
    sort_order: number;
    product_ingredients: Array<{
        id: number;
        product_id: number;
        ingredient_id: number;
        ingredient_name: string | null;
        ingredient_unit: string | null;
        ingredient_active: boolean;
        quantity: number;
        sort_order: number;
        notes: string | null;
    }>;
    updated_at: string | null;
};
```

Megjegyzés: a `product_ingredients` jelenleg `array<int, array<string, mixed>>` PHPDoc-pal szerepel. Automatikus generálásnál ez könnyen túl laza `Array<any>` vagy object-szerű típus lehet. Ha pontos TS output kell, ehhez külön nested Data objektum vagy részletesebb PHPDoc stratégia javasolt.

### `IngredientSupplierTermListItemData`

Forrás:

```text
app/Data/IngredientSupplierTerms/IngredientSupplierTermListItemData.php
```

Várható TypeScript forma:

```ts
export type IngredientSupplierTermListItemData = {
    id: number;
    ingredient_id: number;
    supplier_id: number;
    ingredient_name: string | null;
    ingredient_unit: string | null;
    supplier_name: string | null;
    lead_time_days: number | null;
    minimum_order_quantity: number | string | null;
    pack_size: number | string | null;
    unit_cost_override: number | string | null;
    preferred: boolean;
    active: boolean;
    meta: Record<string, unknown> | null;
    created_at: string | null;
    updated_at: string | null;
};
```

Megjegyzés: a `minimum_order_quantity`, `pack_size` és `unit_cost_override` PHP oldalon `int|float|string|null`. Ez domain szempontból indokolt lehet, mert decimal castból string is érkezhet. Frontenden ezt nem érdemes vakon `number`-ré szűkíteni, amíg a backend decimal output szerződés nincs egységesítve.

## Vue használati minta

Ha a projekt később TypeScriptre vált, egy komponensben így nézne ki:

```vue
<script setup lang="ts">
import type { ProductListItemData } from "@/types/generated/laravel-data";

const props = defineProps<{
    products: {
        data: ProductListItemData[];
        current_page: number;
        per_page: number;
        total: number;
    };
}>();
</script>
```

Jelenlegi JavaScript komponenseknél a generált `.d.ts` közvetlenül nem ad sok értéket, legfeljebb IDE/autocomplete szinten, fokozatos `lang="ts"` átállással.

## Kockázatok

- A projekt most JavaScript alapú; TypeScript generálás önmagában nem javítja a runtime biztonságot.
- A Data osztályok egy részében vannak `array<string, mixed>` és union decimal mezők. Ezek automatikus TS outputja túl laza vagy túl zajos lehet.
- A generált típusfájl helyéről és commitolásáról külön csapatdöntés kell.
- A transformer csomag telepítése új configot és új artisan parancsot hoz be; ezt külön PR-ben érdemes kezelni.

## Javasolt következő lépés

1. Ne kapcsoljuk be ebben a körben.
2. Később egy külön POC PR-ben:
    - telepítés: `spatie/laravel-typescript-transformer`,
    - config publikálás,
    - csak két Data osztály annotálása,
    - generált fájl célja: `resources/js/types/generated/laravel-data.d.ts`,
    - egyetlen Vue oldal opcionális `lang="ts"` próbája.
3. Ha a generált output tiszta és hasznos, akkor lehet modulonként bővíteni.
