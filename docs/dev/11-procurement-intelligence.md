# Beszerzési intelligencia

## Cél

A beszerzési intelligencia modul döntéstámogatást ad az admin felhasználóknak. Nem könyvel készletet önmagában, hanem posted beszerzésekből, készletmozgásokból, alapanyag törzsadatokból és beszállítói feltételekből számol:

- ártrendeket,
- alapanyag költségtrendeket,
- fogyási előrejelzést,
- minimum készlet alapú utánrendelési javaslatokat,
- beszerzési figyelmeztetéseket,
- beszállítónként csoportosított draft beszerzéseket.

## Fő flow

```text
GET /admin/procurement-intelligence
  -> ProcurementIntelligenceController@index
  -> ProcurementIntelligenceIndexRequest
  -> ProcurementIntelligenceService::buildDashboard()
  -> ProcurementIntelligenceRepository
  -> Inertia::render('Admin/ProcurementIntelligence/Index')
```

Draft generálás:

```text
POST /admin/procurement-intelligence/purchase-drafts
  -> ProcurementIntelligenceController@generatePurchaseDrafts()
  -> GeneratePurchaseDraftRequest
  -> PurchaseDraftGenerationService::generateFromRecommendations()
  -> ProcurementIntelligenceService::minimumStockRecommendationsForFilters()
  -> PurchaseService::create()
  -> redirect admin.purchases.index?status=draft
```

## Fontos osztályok

- `App\Http\Controllers\Admin\ProcurementIntelligenceController`
- `App\Http\Requests\Admin\ProcurementIntelligenceIndexRequest`
- `App\Http\Requests\Admin\GeneratePurchaseDraftRequest`
- `App\Services\ProcurementIntelligenceService`
- `App\Services\PurchaseDraftGenerationService`
- `App\Repositories\ProcurementIntelligenceRepository`
- `resources/js/Pages/Admin/ProcurementIntelligence/Index.vue`
- `resources/js/Components/Admin/ProcurementIntelligence/*`

Controllerben csak authorizáció, FormRequest, service hívás és válasz legyen. Lekérdezés repositoryba, üzleti döntés service-be tartozik.

## Jogosultság

Az oldal route middleware-rel védi a hozzáférést:

```text
permission:procurement-intelligence.view
```

A controller ezen felül policy/gate ellenőrzést hív:

```php
$this->authorize('viewProcurementIntelligence');
```

Frontend jogosultsági jelzés csak navigációs és megjelenítési segítség. Biztonsági határ mindig backend oldalon legyen.

## Adatforrások

### Posted beszerzési tételek

Forrás:

- `purchases`
- `purchase_items`
- `suppliers`
- `ingredients`

Csak `purchases.status = posted` sorok számítanak ártrendbe, legutóbbi árba és beszállító kiválasztásba. Draft beszerzés nem számít valós ár- vagy készletadatnak.

### Készlet és BOM használat

Forrás:

- `ingredients.current_stock`
- `ingredients.minimum_stock`
- `ingredients.estimated_unit_cost`
- `ingredients.average_unit_cost`
- `product_ingredients`

A BOM használat figyelmeztetéshez kell: ha egy alapanyag receptben szerepel, de nincs készleten, kritikus jelzést kap.

### Fogyás

Forrás:

- `inventory_movements`

Csak `production_out` mozgások számítanak fogyásnak. A szolgáltatás külön használ 28 napos és 7 napos fogyási ablakot.

### Beszállítói feltételek

Forrás:

- `ingredient_supplier_terms`
- `suppliers`

Mezők:

- `lead_time_days`
- `minimum_order_quantity`
- `pack_size`
- `preferred`
- `unit_cost_override`

Ha egy alapanyag-beszállító kapcsolatnál nincs lead time, fallback lehet a beszállító `lead_time_days` értéke.

## Dashboard payload

`ProcurementIntelligenceService::buildDashboard()` visszatérési elemei:

- `defaults`: számítási konstansok a frontend magyarázó blokkhoz.
- `summary`: gyors összesítő kártyák.
- `supplier_price_trends`: beszállító-alapanyag párok ármozgása.
- `ingredient_cost_trends`: napi, alapanyag és beszállító szerinti aggregált költségsorok.
- `recent_purchases`: utolsó beszerzési tételek.
- `minimum_stock_recommendations`: utánrendelési javaslatok.
- `weekly_consumption_forecast`: heti fogyási becslés.
- `alerts`: figyelmeztetések.

Új frontend mező hozzáadásakor a service payload szerződését és a kapcsolódó Vitest teszteket is frissíteni kell.

## Számítási szabályok

### Ártrend

Beszállító-alapanyag páronként a legutóbbi posted tételt hasonlítja az előző posted tételhez.

```text
change_amount = last_unit_cost - previous_unit_cost
change_percent = change_amount / previous_unit_cost * 100
```

Trend címkék:

- `emelkedik`: legalább 1% növekedés,
- `csökken`: legalább 1% csökkenés,
- `stabil`: a kettő között.

### Fogyási előrejelzés

```text
four_week_average = 28_day_production_out / 4
daily_average = four_week_average / 7
coverage_days = current_stock / daily_average
next_week_forecast = four_week_average
```

Ha nincs fogyási adat, a fedezeti nap értéke `null`.

### Utánrendelési javaslat

```text
weekly_average = 28_day_production_out / 28 * 7
daily_average = weekly_average / 7
days_on_hand = current_stock / daily_average
lead_time_demand = daily_average * lead_time_days
safety_stock = daily_average * 3
target_stock = max(minimum_stock, lead_time_demand + safety_stock)
raw_suggested_quantity = max(0, target_stock - current_stock)
suggested_quantity = raw_suggested_quantity constrained by minimum_order_quantity and pack_size
```

Kerekítés:

1. Ha van `minimum_order_quantity`, a javaslat legalább akkora lesz.
2. Ha van `pack_size`, a mennyiség felfelé kerekül teljes csomagra.

### Sürgősség

- `critical`: nincs készlet, vagy legfeljebb 2 napra elég.
- `high`: minimum készleten vagy alatta van, vagy legfeljebb 7 napra elég.
- `medium`: legfeljebb 14 napra elég.
- `low`: minden más eset.

## Beszállító választás

Az utánrendelési javaslat egy beszállítói kontextust kap. A választási sorrend:

1. Preferált `ingredient_supplier_terms` kapcsolat.
2. Legutóbbi posted beszerzés beszállítója.
3. Legolcsóbb friss beszállító a kiválasztott időablakban.
4. Nincs beszállító.

Egységár forrás:

1. `unit_cost_override`,
2. az adott beszállítóhoz tartozó legutóbbi posted egységár,
3. legutóbbi vagy legolcsóbb friss posted egységár,
4. `ingredients.estimated_unit_cost` draft generáláskor.

## Figyelmeztetések

Aktív alert típusok:

- `low_stock`: készlet minimumon vagy alatta.
- `stockout_risk`: várható fogyás alapján 7 napon belül elfogyhat.
- `price_increase`: legalább 10% áremelkedés az előző beszerzéshez képest.
- `stale_purchase_data`: nincs friss posted beszerzési adat 90 napból.
- `missing_estimated_cost`: hiányzik a becsült egységköltség.
- `missing_minimum_stock`: nincs minimum készlet.
- `bom_no_stock`: receptben használt alapanyag, de nincs készleten.

Új alert típus hozzáadásakor frissítendő:

- `ProcurementIntelligenceService::filterOptions()`,
- alert számítás,
- frontend panel/table megjelenítés,
- feature és frontend tesztek,
- felhasználói dokumentáció.

## Query megjegyzések

A repository window functiont használ a legutóbbi vagy legolcsóbb sor kiválasztására:

```sql
ROW_NUMBER() OVER (...) as purchase_rank
```

Ne használj `row_number` aliast, mert MySQL alatt ütközhet a függvénynévvel. A külső query a `purchase_rank = 1` feltétellel választja ki a nyertes sort.

Az `ingredientIds` tömb üres állapotát minden ilyen repository metódusnak korán kell kezelnie, hogy ne épüljön felesleges vagy hibás `where in ()` lekérdezés.

## Draft generálás

`PurchaseDraftGenerationService` csak olyan javaslatból készít draftot, ahol `suggested_order_quantity > 0`.

Viselkedés:

- Ha a felhasználó kijelöl alapanyagokat, csak azokból generál.
- Ha nincs kijelölés, az aktuális szűrés szerinti összes generálható javaslatot használja.
- Beszállítónként külön draft beszerzés készül.
- Beszállító nélküli tételek egy `supplier_id = null` draftba kerülnek.
- A draft megjegyzése: `Automatikusan generált tervezet utánrendelési javaslatból.`

A draft generálás nem posted művelet, ezért nem hoz létre inventory movementet. Készletet csak a későbbi könyvelés módosíthat.

## Tesztelés

Releváns tesztek:

```text
tests/Feature/ProcurementIntelligenceFeatureTest.php
resources/js/Pages/Admin/ProcurementIntelligence/Index.spec.js
```

Backend minimum:

- jogosultság,
- ártrend,
- fogyási előrejelzés,
- alert típusok,
- preferált beszállító,
- pack size és minimum rendelés,
- draft generálás beszállítónként,
- beszállító nélküli legutóbbi beszerzés fallback.

Futtatás:

```text
vendor\bin\pest tests\Feature\ProcurementIntelligenceFeatureTest.php
npm test -- ProcurementIntelligence
```

## Fejlesztési szabályok

- Új üzleti szabály service-be kerüljön, ne Vue komponensbe.
- Új lekérdezés repositoryba kerüljön.
- Új bemeneti szűrő FormRequest validációval induljon.
- Új jogosultság `PermissionRegistry`-n keresztül legyen kezelve.
- Posted adatból számolj, ha valós beszerzési tény kell.
- Draft adatot csak munkatervezetként kezeld.
- Pénzhez decimal adatbázismezőt és explicit kerekítést használj.
- Nagyobb listáknál figyelj az indexekre, eager loadingra és aggregált querykre.
