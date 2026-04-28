# HAJNALHÉJ – PRODUCT MODUL REFACTOR SPATIE/LARAVEL-DATA HASZNÁLATÁVAL

Te egy senior Laravel 2026 architect vagy.

Projekt:
Hajnalhéj Bakery

Stack:

- Laravel 13
- PHP 8.4+
- Vue 3
- Inertia.js
- PrimeVue
- Tailwind
- MySQL
- Pest
- Spatie Laravel Data

Cél:
A meglévő Product modult refaktoráld úgy, hogy modern typed Data layer-t használjon.

## Kötelező szabályok

- Ne bontsd meg a jelenlegi működést.
- Route-ok maradjanak kompatibilisek.
- Frontend működjön változtatás nélkül, ha lehet.
- Kód legyen senior szintű, tiszta, olvasható.
- Laravel Pint kompatibilis.
- Larastan max szint kompatibilis.
- Pest tesztek frissüljenek.

---

# Jelenlegi Product modul tipikus mezők

products tábla:

- id
- category_id
- name
- slug
- short_description
- description
- price
- image_path
- sort_order
- is_active
- created_at
- updated_at

---

# Feladatok

## 1. Telepítés ellenőrzése

Ha nincs:

spatie/laravel-data

telepítsd és konfiguráld.

---

## 2. Hozd létre ezeket az osztályokat

app/Data/Products/ProductData.php
app/Data/Products/ProductStoreData.php
app/Data/Products/ProductUpdateData.php
app/Data/Products/ProductIndexData.php
app/Data/Products/ProductListItemData.php

---

# Elvárt tartalom

## ProductStoreData

HTTP create input:

- category_id:int
- name:string
- slug:?string
- short_description:?string
- description:?string
- price:int|float
- image_path:?string
- sort_order:int
- is_active:bool

## ProductUpdateData

Ugyanez update-re.

## ProductIndexData

Listázó szűrők:

- search:?string
- category_id:?int
- active:?bool
- page:int
- per_page:int
- sort_field:string
- sort_direction:string

## ProductListItemData

Admin listához optimalizált output:

- id
- category_name
- name
- slug
- price
- is_active
- sort_order

---

# 3. Refaktoráld a Controller-t

## Előtte:

$request->validated()

## Utána:

ProductStoreData::from($request)
ProductUpdateData::from($request)
ProductIndexData::from($request->all())

---

# 4. Refaktoráld a Service réteget

Használjon typed Data inputot:

public function store(ProductStoreData $data): Product
public function update(Product $product, ProductUpdateData $data): Product

---

# 5. Refaktoráld a Repository réteget

Listázás:

public function paginate(ProductIndexData $filters)

Kimenet ProductListItemData collection legyen, ha indokolt.

---

# 6. Slug kezelés

Ha slug üres:

- generálódjon name alapján
- unique legyen

---

# 7. Ár kezelés

Biztonságos cast:
decimal(10,2)

Data layer is kezelje megfelelően.

---

# 8. Inertia output

Ha Product edit oldal van:

return Inertia::render(...[
'product' => ProductData::from($product)
]);

---

# 9. Tesztek

Írj/frissíts Pest teszteket:

- Product create Data flow
- Product update Data flow
- Product index filters
- Slug auto generate
- Validation compatibility

---

# 10. Extra senior bónusz

Ha jó ötlet:

- TypeScript type export frontendhez
- Lazy properties
- Optional values
- Map input/output külön Data classokra

---

# Elvárt output formátum

## 1. Rövid összegzés

Mit refaktoráltál és miért jobb.

## 2. Módosított fájlok

Teljes lista.

## 3. Új Data osztályok kódja

## 4. Controller diff

## 5. Service diff

## 6. Repository diff

## 7. Pest tesztek

## 8. További ajánlások

Product Variants / Weekly Menu / Cart moduloknál hol használd még.

---

# Fontos tiltás

NE gyárts túltervezett enterprise szörnyet.
Maradjon Laraveles, gyors, elegáns.

# Minőségelvárás

Ha valami egyszerűbb Data nélkül, jelezd őszintén.
Ha valahol Data sokat ad, ott vezesd be teljesen.
