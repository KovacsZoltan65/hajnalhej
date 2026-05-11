# Laravel Data szabvány

## Cél

A Hajnalhéj projektben a `spatie/laravel-data` elsődleges feladata a típusos input és output szerződések egységesítése. A meglévő Controller -> Service -> Repository architektúra marad, a FormRequestek továbbra is validálnak és authorize-olnak.

Ebben a refaktor fázisban a Data osztályok nem váltják ki a FormRequest validációt. A controller a validált request adatból Data objektumot hoz létre, a service pedig nyers tömb helyett ezt kapja meg.

## Mikor kell Data osztály?

### StoreData

Új admin CRUD vagy refaktorált modul létrehozó műveletéhez kell.

```php
$data = StoreProductData::from($request->validated());
$this->service->create($data);
```

Használat:

- új rekord létrehozása,
- normalizált service input,
- ismétlődő mezőszerződés kiváltása.

### UpdateData

Meglévő rekord módosításához kell, ha a service több mezőt, opcionális értékeket vagy domain szintű állapotot kap.

Használat:

- admin edit form,
- státusz vagy beállítás módosítása,
- részleges frissítés, ha a szerződés ismétlődik.

### IndexData / FilterData

Listaoldalak query paramétereihez kell, ha a szűrés, keresés, rendezés vagy lapozás több helyen megjelenik.

Használat:

- admin index oldalak,
- repository filter objektum,
- frontend filter state visszatöltése.

Ajánlott publikus metódus:

```php
public function toFrontendFilters(): array
{
    return [
        'search' => $this->search,
        'status' => $this->status,
    ];
}
```

### ListItemData

Inertia lista payloadhoz kell, ha Eloquent modellekből vagy paginált collectionből stabil frontend szerződést adunk.

Használat:

- DataTable sorok,
- Select opciók,
- admin lista nézetek.

```php
'products' => ProductListItemData::collect($products),
```

### DetailData

Részletező vagy szerkesztő oldal payloadjához kell, ha a frontend több mezőt, kapcsolt adatot vagy form kezdőállapotot kap.

Használat:

- edit oldal,
- show oldal,
- összetett admin workflow.

### Nested item Data

Beágyazott elemekhez kell, ha a fő Data objektum listát vagy kapcsolt rekordokat ad át.

Használat:

- heti menü tételei,
- rendelési tételek,
- beszállítói feltételek,
- készlet- vagy beszerzési sorok.

## Mikor nem kell Data osztály?

Nem kell Data osztály minden egyszerű belső változóhoz vagy egyszer használatos lekérdezéshez.

Elfogadható Data nélkül:

- egyszerű dashboard read-only aggregációk,
- tisztán internal service query-k, ha nincs ismétlődő contract,
- privát helper jellegű értékek service metóduson belül,
- olyan repository paraméter, amely egyetlen skalár és nem része admin CRUD szerződésnek.

Ha ugyanaz az input vagy output több controllerben, service-ben vagy frontend oldalon megjelenik, Data osztályt kell bevezetni.

## Controller minta

Controller feladata marad:

- authorize,
- FormRequest fogadása,
- Data objektum létrehozása validált requestből,
- service hívás,
- redirect vagy Inertia válasz.

```php
public function store(StoreProductRequest $request): RedirectResponse
{
    $this->authorize('create', Product::class);

    $data = StoreProductData::from($request->validated());

    $this->products->create($data);

    return redirect()
        ->route('admin.products.index')
        ->with('success', 'Termék létrehozva.');
}
```

Controller ne adjon át nyers `validated()` tömböt új vagy refaktorált admin CRUD modulban.

## Service minta

Service réteg kapjon Data objektumot, és azon keresztül olvassa az inputot.

```php
public function create(StoreProductData $data): Product
{
    return DB::transaction(function () use ($data): Product {
        return $this->products->create($data);
    });
}
```

Service feladata marad:

- üzleti szabályok,
- tranzakciók,
- workflow-k,
- stock és order szabályok,
- audit mellékhatások.

Új vagy refaktorált admin CRUD modulban a service ne kapjon nyers validált tömböt. Ha repository számára tömb kell, a Data osztály adjon explicit metódust, például `toPersistenceArray()`.

## Repository minta

Repository feladata marad:

- query,
- filter,
- sorting,
- pagination,
- persistence,
- eager loading,
- aggregate lekérdezés.

Repository ne validáljon request adatot, és ne tartalmazzon controllerből származó workflow logikát.

```php
public function create(StoreProductData $data): Product
{
    return Product::query()->create($data->toPersistenceArray());
}
```

Index vagy filter esetén a repository kaphat `IndexData` vagy `FilterData` objektumot:

```php
public function paginate(ProductIndexData $filters): LengthAwarePaginator
{
    return Product::query()
        ->when($filters->search, fn ($query) => $query->where('name', 'like', "%{$filters->search}%"))
        ->paginate($filters->perPage);
}
```

## Inertia output minta

Inertia felé ne Eloquent modelleket vagy ad hoc tömböket adjunk, ha a payload admin UI contractként működik.

Lista:

```php
return Inertia::render('Admin/Products/Index', [
    'products' => ProductListItemData::collect($products),
    'filters' => $filters->toFrontendFilters(),
]);
```

Részletező oldal:

```php
return Inertia::render('Admin/Products/Edit', [
    'product' => ProductDetailData::from($product),
]);
```

Beágyazott elemek:

```php
class WeeklyMenuDetailData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        /** @var array<int, WeeklyMenuItemData> */
        public array $items,
    ) {
    }
}
```

## Tesztelési elvárások

Minden meaningful Data bevezetéshez legyen célzott teszt, ha normalizálás, default érték, típuskonverzió vagy nested mapping történik.

Minimum:

- Data unit teszt legalább normalizálásra vagy mappingre,
- meglévő controller/service feature tesztek ne törjenek,
- validáció továbbra is FormRequest tesztekben maradjon,
- Inertia payload mezőnév regressziót feature vagy frontend teszt fogjon meg, ha user-facing admin oldal érintett.

Nem elfogadható:

- nulla assertion,
- csak osztálypéldányosítás ellenőrzése üzleti érték nélkül,
- FormRequest validáció átmásolása Data rules-ba ebben a refaktor lépésben.

## Migration strategy

A Data réteget modulonként, kis PR vagy commit méretben kell bevezetni. Egy lépés egy jól körülhatárolt admin modul input és output contractját rendezze.

Sorrend:

1. `IngredientSupplierTerm`
2. `Ingredients`
3. `Suppliers`
4. `Purchases` / `Receipts`
5. `ProductionPlans`

Minden lépésben:

- először a meglévő controller, service, repository mintát kell megérteni,
- FormRequest validáció maradjon érintetlen, kivéve ha külön feladat kéri,
- service input legyen Data objektum,
- Inertia payload legyen Data alapú, ha lista vagy részletező contractról van szó,
- tesztek igazolják, hogy a korábbi viselkedés megmaradt.

## Döntési szabály

Ha bizonytalan, hogy kell-e Data osztály, ezt a kérdést kell feltenni:

```text
Ez az input vagy output contract több rétegen átmegy, vagy később frontend/service regressziót okozhat, ha ad hoc tömb marad?
```

Ha igen, vezessünk be Data osztályt. Ha nem, maradhat egyszerű belső érték.
