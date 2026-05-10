# Hajnalhéj – spatie/laravel-data refaktor Codex promptok

Cél: a jelenlegi részleges `spatie/laravel-data` használatot következetes, modulonként bevezethető Data/DTO réteggé alakítani.

Kiinduló audit alapján a projektben már van jó minta a `Products` és `WeeklyMenu` moduloknál, de több admin modul még `validated()` tömböket ad át controllerből service-be. A refaktor célja nem a teljes alkalmazás egyszerre történő átírása, hanem kis, tesztelhető lépésekben haladni.

Forrásirány:

- Spatie Laravel Data v4 dokumentáció: Data objectek használhatók validációra, transzformációra, request adatból létrehozásra és kimeneti payload egységesítésre.
- A projektben maradjon a meglévő Controller → Service → Repository architektúra.
- A FormRequestek egyelőre maradjanak meg validációra és authorize-ra. A Data osztályok első körben típusos input/output szerződésként működjenek.

---

## 0. Általános futtatási szabály minden Codex prompt előtt

Minden prompt előtt:

```bash
git status
```

Ha nem tiszta a working tree, előbb commit vagy stash.

Minden prompt után:

```bash
composer exec pint
php artisan test
npm test -- --run
npm run build
```

Ha a teljes suite túl hosszú, legalább az érintett backend és frontend teszteket futtasd.

---

# 1. Data használati standard dokumentum létrehozása

```text
Feladat: Készíts belső fejlesztői dokumentációt a Hajnalhéj projekt `spatie/laravel-data` használati standardjához.

Projekt kontextus:
- Laravel 13 + Vue 3 + Inertia + PrimeVue + Tailwind
- Architektúra: Controller → Service → Repository
- A projektben már léteznek Data osztályok: Products, WeeklyMenu, WeeklyMenuItem, Settings, Branch.
- A cél nem a FormRequestek azonnali kiváltása, hanem a service réteg típusosítása és az Inertia payload egységesítése.

Hozz létre / módosítsd:
- `docs/dev/laravel-data-standard.md`

A dokumentum tartalma:
1. Mikor kell Data osztály?
   - StoreData
   - UpdateData
   - IndexData / FilterData
   - ListItemData
   - DetailData
   - Nested item Data
2. Mikor nem kell Data osztály?
   - egyszerű dashboard read-only aggregációk
   - tisztán internal service DTO nélküli query-k, ha nincs ismétlődő contract
3. Ajánlott controller minta:
   - `$data = SomeStoreData::from($request->validated())`
   - `$this->service->create($data)`
4. Ajánlott service minta:
   - service ne kapjon nyers validált tömböt új/refaktorált admin CRUD modulban
5. Ajánlott repository minta:
   - repository query/persistence maradjon, ne validáljon
6. Output minta Inertia felé:
   - `ListItemData::collect(...)`
   - `toFrontendFilters()` IndexData esetén
7. Tesztelési elvárások:
   - Data unit teszt legalább a normalizálásra
   - controller/service feature teszt ne törjön
8. Migration strategy:
   - modulonként, kis PR/commit
   - először IngredientSupplierTerm
   - utána Ingredients, Suppliers
   - utána Purchases / Receipts
   - utána ProductionPlans

Fontos:
- Ne módosíts üzleti logikát.
- Ne írj át működő FormRequest validációt Data rules alapú validációra ebben a lépésben.
- Magyar nyelvű dokumentáció készüljön.

Validáció:
- Nincs szükség teszt módosításra, de a teljes projekt maradjon buildelhető.
```

---

# 2. Üres / félkész Data osztályok auditja és javítása

```text
Feladat: Auditáld és javítsd a félkész vagy hibagyanús Data osztályokat.

Érintett fájlok indulásként:
- `app/Data/BranchData.php`
- `app/Data/BranchInventoryData.php`
- minden `app/Data/**/*.php`

Elvárások:
1. Keresd meg az üres, nem használt vagy félkész Data osztályokat.
2. `BranchInventoryData` esetén dönts:
   - ha nincs használatban és nincs egyértelmű szerződése, töröld,
   - vagy egészítsd ki valós mezőkkel, ha a projektben ténylegesen használni kell.
3. `BranchData` esetén javítsd a nullable mezők kezelését:
   - ne használj `optional($value, '')` mintát sima string fallbackre,
   - használj `?? ''` vagy nullable property-t a tényleges domain szerint.
4. Ellenőrizd, hogy minden Data osztály:
   - `Spatie\LaravelData\Data`-t extendel,
   - szigorúan típusos property-ket használ,
   - ne tartalmazzon halott metódust,
   - ne tartalmazzon üzleti logikát, ami service-be való.

Tesztelés:
- Futtasd az érintett backend teszteket.
- Ha törölsz Data osztályt, keress rá minden hivatkozásra.

Kimenet:
- Kis, célzott módosítás.
- Rövid commit üzenet javaslat:
  `refactor: clean up laravel data objects`
```

---

# 3. IngredientSupplierTerm modul Data-alapú refaktor

````text
Feladat: Refaktoráld az IngredientSupplierTerm admin modult `spatie/laravel-data` alapú input/output szerződésre.

Érintett modul:
- `IngredientSupplierTermController`
- `IngredientSupplierTermService`
- `IngredientSupplierTermRepository`
- `StoreIngredientSupplierTermRequest`
- `UpdateIngredientSupplierTermRequest`
- `IngredientSupplierTermIndexRequest`
- `InlineUpdateIngredientSupplierTermRequest`
- kapcsolódó Inertia payload / Vue oldal, ha szükséges
- kapcsolódó tesztek

Hozz létre:
- `app/Data/IngredientSupplierTerms/IngredientSupplierTermIndexData.php`
- `app/Data/IngredientSupplierTerms/IngredientSupplierTermStoreData.php`
- `app/Data/IngredientSupplierTerms/IngredientSupplierTermUpdateData.php`
- `app/Data/IngredientSupplierTerms/IngredientSupplierTermInlineUpdateData.php`
- `app/Data/IngredientSupplierTerms/IngredientSupplierTermListItemData.php`
- opcionálisan `IngredientSupplierTermDetailData.php`, ha van show/detail payload

Szabályok:
1. FormRequestek maradjanak validációra és authorize-ra.
2. Controller ne adjon tovább nyers `$request->validated()` tömböt service-be.
3. Controller példa:
   - `IngredientSupplierTermStoreData::from($request->validated())`
   - `IngredientSupplierTermUpdateData::from($request->validated())`
   - `IngredientSupplierTermIndexData::from($request->validated())`
4. Service metódusok típusos Data objektumokat kapjanak.
5. Repository csak ott kapjon Data objektumot, ahol ez tényleg tisztítja a kódot; különben a service alakítsa payloadra.
6. A preferred szabály maradjon változatlan:
   - egy ingredienthez csak egy aktív preferred term lehet.
   - inactive rekord nem lehet preferred.
7. A soft delete működés ne változzon.
8. Az Inertia payloadban a listaelemek `IngredientSupplierTermListItemData`-n keresztül legyenek transzformálva, ha jelenleg kézzel map-eltek.

Tesztelés:
- Frissítsd / egészítsd ki a meglévő feature teszteket.
- Adj Data unit tesztet legalább ezekre:
  - StoreData létrejön validált payloadból.
  - UpdateData kezeli az opcionális mezőket.
  - IndexData `toFrontendFilters()` stabil payloadot ad.
  - ListItemData helyesen mapeli a relation mezőket, például ingredient és supplier neveket.

Fontos:
- Ne módosíts Vue UX-t ebben a promptban, csak ha payload compatibility miatt muszáj.
- Ne nevezd át a route-okat.
- Ne módosíts adatbázis sémát.

Futtatás:
```bash
php artisan test --filter=IngredientSupplierTerm
npm test -- --run IngredientSupplierTerms
npm run build
````

Commit üzenet javaslat:
`refactor: add data objects to ingredient supplier terms`

````

---

# 4. Ingredients modul Data-alapú refaktor

```text
Feladat: Refaktoráld az Ingredients admin modult `spatie/laravel-data` alapú input/output szerződésre az IngredientSupplierTerm modulban kialakított minta alapján.

Érintett modul:
- `IngredientController`
- `IngredientService`
- `IngredientRepository`
- `StoreIngredientRequest`
- `UpdateIngredientRequest`
- `InlineUpdateIngredientRequest`
- kapcsolódó Vue oldal és tesztek, ha payload változik

Hozz létre:
- `app/Data/Ingredients/IngredientIndexData.php`
- `app/Data/Ingredients/IngredientStoreData.php`
- `app/Data/Ingredients/IngredientUpdateData.php`
- `app/Data/Ingredients/IngredientInlineUpdateData.php`
- `app/Data/Ingredients/IngredientListItemData.php`
- opcionálisan `IngredientDetailData.php`

Elvárások:
1. Controller ne adjon át nyers validált tömböt service-be új/refaktorált metódusokban.
2. Service metódusok Data objektumokat kapjanak.
3. Low-stock / stock mezők és unit mezők mapelése maradjon kompatibilis a frontenddel.
4. Ha van inline edit, annak külön InlineUpdateData készüljön, ne a teljes UpdateData legyen túlhúzva.
5. ListItemData tartalmazza a táblázatban ténylegesen használt mezőket.
6. Ne változzon a validációs viselkedés.

Tesztelés:
- Feature tesztek: create/update/index/inline update.
- Data unit tesztek:
  - StoreData típusosság
  - UpdateData opcionális mezők
  - ListItemData stock mezők
  - IndexData filter payload

Futtatás:
```bash
php artisan test --filter=Ingredient
npm test -- --run Ingredients
npm run build
````

Commit üzenet javaslat:
`refactor: add data objects to ingredients admin`

````

---

# 5. Suppliers modul Data-alapú refaktor

```text
Feladat: Refaktoráld a Suppliers admin modult `spatie/laravel-data` alapú input/output szerződésre.

Érintett modul:
- `SupplierController`
- `SupplierService`
- `SupplierRepository`
- `SupplierIndexRequest`
- `StoreSupplierRequest`
- `UpdateSupplierRequest`
- kapcsolódó Vue és tesztek, ha payload változik

Hozz létre:
- `app/Data/Suppliers/SupplierIndexData.php`
- `app/Data/Suppliers/SupplierStoreData.php`
- `app/Data/Suppliers/SupplierUpdateData.php`
- `app/Data/Suppliers/SupplierListItemData.php`
- opcionálisan `SupplierDetailData.php`

Elvárások:
1. FormRequestek maradnak validációra.
2. Controller Data objektumot hoz létre validált payloadból.
3. Service Data objektumot kap.
4. Supplier list payload legyen stabil és frontend-kompatibilis.
5. Kapcsolódó beszerzési modulok ne törjenek.

Külön figyelem:
- lead time mezők
- aktív/inaktív státusz
- kapcsolattartó mezők
- ingredient_supplier_terms kapcsolatok, ha listában használtak

Tesztelés:
```bash
php artisan test --filter=Supplier
npm test -- --run Suppliers
npm run build
````

Commit üzenet javaslat:
`refactor: add data objects to suppliers admin`

````

---

# 6. Categories modul Data-alapú refaktor

```text
Feladat: Refaktoráld a Categories modult Data-alapú input/output szerződésre.

Érintett modul:
- `CategoryController`
- `CategoryService`
- `CategoryRepository`
- `StoreCategoryRequest`
- `UpdateCategoryRequest`
- kapcsolódó Vue és tesztek

Hozz létre:
- `app/Data/Categories/CategoryIndexData.php`
- `app/Data/Categories/CategoryStoreData.php`
- `app/Data/Categories/CategoryUpdateData.php`
- `app/Data/Categories/CategoryListItemData.php`

Elvárások:
1. A slug generálási és unique szabályok ne változzanak.
2. A controller ne adjon nyers validált tömböt service-be.
3. A lista payload frontend-kompatibilis maradjon.
4. Ha a Product modul category adatot fogyaszt, ne törjön.

Tesztelés:
```bash
php artisan test --filter=Category
npm test -- --run Categories
npm run build
````

Commit üzenet javaslat:
`refactor: add data objects to categories admin`

````

---

# 7. Product modul Data használatának szigorítása

```text
Feladat: Auditáld és szigorítsd a már meglévő Product Data réteget.

Érintett fájlok:
- `app/Data/Products/*`
- `ProductController`
- `ProductService`
- `ProductRepository`
- `StoreProductRequest`
- `UpdateProductRequest`
- `InlineUpdateProductRequest`
- Product admin Vue tesztek

Elvárások:
1. Ne hozz létre párhuzamos, duplikált Product Data osztályokat.
2. Javítsd, ha controller `$request->all()`-ból hoz létre Data objektumot.
   - preferált: `$request->validated()`
3. Ha nincs `ProductIndexRequest`, vagy nem teljes, alakítsd úgy, hogy az IndexData validált forrásból épüljön.
4. Ellenőrizd, hogy StoreData és UpdateData között ne legyen felesleges duplikáció, de ne erőltesd absztrakt base classba, ha nem javítja az olvashatóságot.
5. ListItemData csak frontend payloadot formázzon, üzleti számítást ne végezzen.
6. A recipe/BOM linkek és category mezők ne törjenek.

Tesztelés:
```bash
php artisan test --filter=Product
npm test -- --run Products
npm run build
````

Commit üzenet javaslat:
`refactor: tighten product data object usage`

````

---

# 8. WeeklyMenu és WeeklyMenuItem Data réteg konzisztencia audit

```text
Feladat: Auditáld és konzisztenssé tedd a WeeklyMenu és WeeklyMenuItem Data réteget.

Érintett fájlok:
- `app/Data/WeeklyMenu/*`
- `app/Data/WeeklyMenuItem/*`
- `WeeklyMenuController`
- `WeeklyMenuService`
- `WeeklyMenuItemService`
- kapcsolódó Requestek, Vue oldalak és tesztek

Elvárások:
1. Ellenőrizd, hogy Store/Update/Index/ListItem Data szerepe tiszta-e.
2. Javítsd azokat a controller pontokat, ahol nyers `$request->validated()` tömb megy service-be.
3. Ha van duplikált `WeeklyMenuItemData` két namespace alatt, vizsgáld meg:
   - tényleg kell-e mindkettő,
   - vagy összevonható-e törés nélkül.
4. A nested weekly menu item kezelés ne törjön.
5. A frontend payload kompatibilis maradjon.

Tesztelés:
```bash
php artisan test --filter=WeeklyMenu
npm test -- --run WeeklyMenus
npm run build
````

Commit üzenet javaslat:
`refactor: normalize weekly menu data objects`

````

---

# 9. Purchases és Purchase Receipt Flow Data előkészítés

```text
Feladat: Készíts Data objektumokat a Purchases / Purchase Receipt Flow modulokhoz, de csak ott kösd be őket, ahol a meglévő tesztek alapján biztonságos.

Érintett modulok:
- `PurchaseController`
- `PurchaseService`
- `PurchaseRepository`
- `StorePurchaseRequest`
- `UpdatePurchaseRequest`
- `PurchaseIndexRequest`
- purchase receipt flow fájlok, ha vannak

Hozz létre:
- `app/Data/Purchases/PurchaseIndexData.php`
- `app/Data/Purchases/PurchaseStoreData.php`
- `app/Data/Purchases/PurchaseUpdateData.php`
- `app/Data/Purchases/PurchaseListItemData.php`
- `app/Data/Purchases/PurchaseItemData.php`
- ha receipt flow létezik:
  - `app/Data/PurchaseReceipts/PurchaseReceiptStoreData.php`
  - `app/Data/PurchaseReceipts/PurchaseReceiptItemData.php`
  - `app/Data/PurchaseReceipts/PurchaseReceiptListItemData.php`

Elvárások:
1. Nested item payloadhoz külön Data osztály legyen.
2. Money/decimal mezők kezelése legyen explicit:
   - string vagy float/int domain döntés szerint,
   - ne legyen véletlen PHP float kerekítési bug.
3. Ne változzon a készletfrissítési logika.
4. Ne változzon a könyvelési/posting logika.
5. Ne változzon a supplier term kiválasztási logika.
6. Ha túl nagy lenne a bekötés, csak Data osztályokat és unit teszteket készíts, majd jelöld TODO-ban a következő promptnak.

Tesztelés:
```bash
php artisan test --filter=Purchase
npm test -- --run Purchases
npm run build
````

Commit üzenet javaslat:
`refactor: prepare purchase data objects`

````

---

# 10. Purchases Data teljes bekötése

```text
Feladat: Kösd be teljesen a Purchases Data objektumokat a controller/service rétegbe.

Előfeltétel:
- A 9. prompt már létrehozta a Purchases Data osztályokat és legalább alap unit teszteket.

Elvárások:
1. Controller ne adjon át nyers validált tömböt service-be.
2. Service metódusok Data objektumot kapjanak.
3. Nested purchase itemeket `PurchaseItemData` reprezentálja.
4. Repository csak persistence/query logikát tartalmazzon.
5. Existing business rules maradjanak változatlanok.
6. Inertia payload legyen kompatibilis a jelenlegi Vue oldalakkal.

Tesztelés:
```bash
php artisan test --filter=Purchase
php artisan test --filter=Inventory
npm test -- --run Purchases
npm run build
````

Commit üzenet javaslat:
`refactor: wire purchase data objects into services`

````

---

# 11. ProductionPlan Data objektumok nested szerződéssel

```text
Feladat: Refaktoráld a ProductionPlan modult Data-alapú input/output szerződésre, külön figyelemmel a nested item és step adatokra.

Érintett modul:
- `ProductionPlanController`
- `ProductionPlanService`
- `ProductionPlanCreateFlowService`
- `ProductionPlanRepository`
- `StoreProductionPlanRequest`
- `UpdateProductionPlanRequest`
- `StoreProductionPlanCreateFlowRequest`
- kapcsolódó Vue oldalak és tesztek

Hozz létre:
- `app/Data/ProductionPlans/ProductionPlanIndexData.php`
- `app/Data/ProductionPlans/ProductionPlanStoreData.php`
- `app/Data/ProductionPlans/ProductionPlanUpdateData.php`
- `app/Data/ProductionPlans/ProductionPlanItemData.php`
- `app/Data/ProductionPlans/ProductionPlanStepData.php`
- `app/Data/ProductionPlans/ProductionPlanListItemData.php`
- `app/Data/ProductionPlans/ProductionPlanDetailData.php`
- opcionálisan `ProductionPlanPreviewData.php`

Elvárások:
1. Nested inputhoz külön Data objektumok legyenek.
2. Timeline generálási üzleti logika maradjon service-ben.
3. Data objektum ne számolja újra a timeline-t.
4. Snapshot mezők mapelése legyen explicit.
5. target_ready_at kezelése ne változzon.
6. Ingredient rollup / low stock jelzés ne törjön.
7. Ha a refaktor túl nagy, bontsd két commitra:
   - input Data
   - output Data

Tesztelés:
```bash
php artisan test --filter=ProductionPlan
npm test -- --run ProductionPlans
npm run build
````

Commit üzenet javaslat:
`refactor: add nested data objects to production plans`

````

---

# 12. Orders admin Data output refaktor

```text
Feladat: Vezess be Data objektumokat az Orders admin list/detail output egységesítésére.

Érintett modul:
- `OrderController`
- `OrderService`
- `OrderRepository`
- `OrderIndexRequest`
- `UpdateOrderStatusRequest`
- admin orders Vue oldalak és tesztek

Hozz létre:
- `app/Data/Orders/OrderIndexData.php`
- `app/Data/Orders/OrderListItemData.php`
- `app/Data/Orders/OrderDetailData.php`
- `app/Data/Orders/OrderItemData.php`
- `app/Data/Orders/OrderStatusUpdateData.php`

Elvárások:
1. Első körben főleg output Data legyen fókuszban.
2. Admin lista payload `OrderListItemData` alapján menjen.
3. Show/detail payload `OrderDetailData` alapján menjen.
4. Status update input `OrderStatusUpdateData` lehet, de ne változzon a status transition logika.
5. Order item snapshot mezők maradjanak érintetlenek.
6. Audit log események ne változzanak.

Tesztelés:
```bash
php artisan test --filter=Order
npm test -- --run Orders
npm run build
````

Commit üzenet javaslat:
`refactor: add order admin data payloads`

````

---

# 13. StockCount és Inventory Data audit

```text
Feladat: Auditáld és részlegesen refaktoráld a StockCount és Inventory modulokat Data objektumokkal.

Érintett modulok:
- `StockCountController`
- `StockCountService`
- `InventoryController`
- `InventoryService`
- `StoreStockCountRequest`
- `UpdateStockCountRequest`
- `StockCountIndexRequest`
- `InventoryLedgerIndexRequest`
- kapcsolódó Vue és tesztek

Hozz létre szükség szerint:
- `app/Data/StockCounts/StockCountIndexData.php`
- `app/Data/StockCounts/StockCountStoreData.php`
- `app/Data/StockCounts/StockCountUpdateData.php`
- `app/Data/StockCounts/StockCountListItemData.php`
- `app/Data/Inventory/InventoryLedgerIndexData.php`
- `app/Data/Inventory/InventoryMovementListItemData.php`
- `app/Data/Inventory/InventoryAdjustmentData.php`

Elvárások:
1. Készletmozgás logika ne változzon.
2. Decimal/quantity mezők kezelése legyen explicit.
3. Inventory ledger filterek IndexData-ba kerüljenek.
4. Output payload legyen kompatibilis.
5. Ha van audit log, ne változzon az eseménynév/payload szerkezete, kivéve ha teszttel igazoltan biztonságos.

Tesztelés:
```bash
php artisan test --filter=StockCount
php artisan test --filter=Inventory
npm test -- --run Inventory
npm run build
````

Commit üzenet javaslat:
`refactor: add data objects to stock and inventory flows`

````

---

# 14. Data objektumok TypeScript export lehetőségének felmérése

```text
Feladat: Mérd fel, hogy érdemes-e a projektben a `spatie/laravel-data` TypeScript definíció generálását bekapcsolni.

Fontos:
- Ez csak audit és proof-of-concept legyen.
- Ne alakítsd át a teljes frontend típusrendszert.

Teendők:
1. Nézd meg a projekt composer configját és a laravel-data configot.
2. Ellenőrizd, telepítve / publikálva van-e a TypeScript transformer támogatás.
3. Ha biztonságos, készíts dokumentált POC-t 1-2 Data osztályra:
   - ProductListItemData
   - IngredientSupplierTermListItemData
4. Dokumentáld:
   - milyen parancs kell a TS generáláshoz,
   - hová kerülne a generált fájl,
   - hogyan használná Vue komponens.
5. Ha nincs bekötve, csak dokumentációt és javaslatot készíts, ne erőltesd.

Hozz létre / módosítsd:
- `docs/dev/laravel-data-typescript-poc.md`

Ne módosíts üzleti logikát.
Ne törj buildet.

Futtatás:
```bash
php artisan test
npm run build
````

Commit üzenet javaslat:
`docs: evaluate laravel data typescript generation`

````

---

# 15. Záró audit: Data coverage és maradék array contractok

```text
Feladat: Készíts záró auditot a `spatie/laravel-data` lefedettségről és a még megmaradt nyers array contractokról.

Teendők:
1. Listázd az összes Data osztályt modul szerint.
2. Listázd azokat a controller/service pontokat, ahol még `$request->validated()` vagy array payload megy tovább service-be.
3. Minősítsd a maradékokat:
   - OK maradhat array
   - később Data ajánlott
   - refaktor szükséges
4. Ellenőrizd, van-e duplikált Data osztály vagy félrevezető név.
5. Ellenőrizd, hogy nincs-e Data objektumba került üzleti logika.
6. Készíts következő backlog listát.

Hozz létre:
- `docs/audits/laravel-data-coverage-audit.md`

Kimeneti struktúra:
- Executive summary
- Data coverage modulonként
- Jó minták
- Rossz minták
- Maradék kockázatok
- Következő backlog
- Ajánlott prioritás

Futtatás:
```bash
composer exec pint
php artisan test
npm test -- --run
npm run build
````

Commit üzenet javaslat:
`docs: add laravel data coverage audit`

````

---

## Ajánlott végső futtatási sorrend összefoglalva

1. Data standard dokumentum
2. Üres/félkész Data osztályok takarítása
3. IngredientSupplierTerm Data refaktor
4. Ingredients Data refaktor
5. Suppliers Data refaktor
6. Categories Data refaktor
7. Product Data szigorítás
8. WeeklyMenu Data konzisztencia
9. Purchases Data előkészítés
10. Purchases Data bekötés
11. ProductionPlan nested Data refaktor
12. Orders admin Data output
13. StockCount + Inventory Data audit/refaktor
14. TypeScript export POC
15. Záró Data coverage audit

## Ajánlott commit stratégia

Minden számozott prompt külön commit legyen.

Példa:

```bash
git add .
git commit -m "refactor: add data objects to ingredient supplier terms"
````

Nagyobb moduloknál, például ProductionPlan és Purchases, megengedett két commit:

```bash
git commit -m "refactor: add production plan input data objects"
git commit -m "refactor: add production plan output data payloads"
```

## Stop szabály

Állj meg és ne folytasd a következő prompttal, ha:

- backend teszt törik,
- frontend build törik,
- Inertia payload inkompatibilitás jelenik meg,
- üzleti logika változott tesztfrissítés nélkül,
- készlet / order / purchase pénzügyi adat számítása megváltozott.

Ilyenkor előbb külön bugfix commit, csak utána következő refaktor.
