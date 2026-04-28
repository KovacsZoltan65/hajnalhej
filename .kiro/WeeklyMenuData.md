# HAJNALHÉJ – WEEKLY MENU MODUL REFAKTOR (SPATIE/LARAVEL-DATA)

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
A Weekly Menu modult refaktoráld typed Data layer használatára, a Product modulban már bevezetett architektúra mintát követve.

---

# KONZISZTENCIA KÖTELEZŐ

A Product modulban már használt minta KÖTELEZŐ:

- FormRequest marad validation layer
- Data = typed input/output
- Service = business logic
- Repository = query/persistence
- Controller = vékony

NE térj el ettől.

---

# FELTÉTELEZETT DOMAIN

weekly_menus tábla:

- id
- name
- start_date (date)
- end_date (date)
- is_active (bool)
- created_at
- updated_at

weekly_menu_items tábla:

- id
- weekly_menu_id
- product_id
- price_override (?decimal)
- is_available (bool)
- sort_order (int)

---

# FELADATOK

## 1. Data osztályok létrehozása

app/Data/WeeklyMenu/

- WeeklyMenuData.php
- WeeklyMenuStoreData.php
- WeeklyMenuUpdateData.php
- WeeklyMenuIndexData.php
- WeeklyMenuListItemData.php

app/Data/WeeklyMenuItem/

- WeeklyMenuItemData.php
- WeeklyMenuItemStoreData.php
- WeeklyMenuItemUpdateData.php

---

## 2. WeeklyMenuStoreData

```php
public string $name;
public string $start_date;
public string $end_date;
public bool $is_active;
```

---

## 3. WeeklyMenuIndexData

Filterek:

- search:?string
- active:?bool
- date_from:?string
- date_to:?string
- page:int
- per_page:int
- sort_field:string
- sort_direction:string

---

## 4. WeeklyMenuListItemData

Admin lista:

- id
- name
- start_date
- end_date
- is_active
- items_count

---

## 5. WeeklyMenuItemStoreData

```php
public int $product_id;
public ?float $price_override;
public bool $is_available;
public int $sort_order;
```

---

# 6. Controller refaktor

## Előtte:

$request->validated()

## Utána:

WeeklyMenuStoreData::from($request)
WeeklyMenuUpdateData::from($request)
WeeklyMenuIndexData::from($request->all())

Nested items:

WeeklyMenuItemStoreData::from($request)

---

# 7. Service réteg

## WeeklyMenuService

```php
store(WeeklyMenuStoreData $data)
update(WeeklyMenu $menu, WeeklyMenuUpdateData $data)
paginate(WeeklyMenuIndexData $filters)
```

## WeeklyMenuItemService

```php
addItem(WeeklyMenu $menu, WeeklyMenuItemStoreData $data)
updateItem(...)
removeItem(...)
reorderItems(...)
```

---

# 8. Business szabályok (KÖTELEZŐ)

## 1. Aktív menü

- egyszerre csak 1 aktív weekly menu lehet
- ha új aktiválódik → régi deaktiválódik

## 2. Dátum validáció

- start_date <= end_date
- nem lehet átfedés két aktív menü között

## 3. Item szabályok

- product csak egyszer szerepelhet egy menüben
- price_override fallback → product.price
- sort_order default 0

---

# 9. Repository réteg

```php
paginate(WeeklyMenuIndexData $filters)
```

Támogatott:

- search
- active filter
- date range filter
- items_count eager load

---

# 10. Inertia output

```php
return Inertia::render(...[
    'menus' => WeeklyMenuListItemData::collect($menus),
]);
```

Edit:

```php
'menu' => WeeklyMenuData::from($menu)
```

---

# 11. Tesztek (Pest)

Írj/frissíts:

- WeeklyMenu create Data flow
- WeeklyMenu update Data flow
- active menu rule
- date overlap rule
- item add/update/remove
- item unique constraint
- index filter működés

---

# 12. Frontend kompatibilitás

- NE törd el a meglévő PrimeVue DataTable-t
- payload struktúra maradjon kompatibilis
- csak tisztítás / stabilizálás megengedett

---

# 13. Extra senior bónusz

Ha belefér:

- items_count eager loading
- lazy Data fields
- nested item Data mapping
- enum jellegű státusz kezelés (ha később kell)

---

# OUTPUT FORMÁTUM

## 1. Rövid összegzés

Mit refaktoráltál és miért jobb.

## 2. Fájl lista

## 3. Data osztályok teljes kód

## 4. Controller diff

## 5. Service diff

## 6. Repository diff

## 7. Pest tesztek

## 8. További javaslatok

---

# FONTOS

NE csinálj túltervezett rendszert.
Ez egy bakery app, nem NASA.

De legyen:

- tiszta
- typed
- bővíthető
- stabil
