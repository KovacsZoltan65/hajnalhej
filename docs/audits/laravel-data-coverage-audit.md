# Laravel Data coverage audit

## Executive summary

A Hajnalhéj admin CRUD magterületein a `spatie/laravel-data` használat már következetes: a Products, Categories, Ingredients, IngredientSupplierTerms, Suppliers, WeeklyMenu, Purchases, ProductionPlans, Orders, StockCounts és Inventory moduloknál az új/refaktorált controller -> service határok Data objektumokra épülnek.

Aktuális állapot:

- Data osztályok száma: 63.
- Nincs azonos nevű Data osztály több namespace alatt.
- A Data osztályok `Spatie\LaravelData\Data`-t extendelnek.
- Nem találtam adatbázis-írást, tranzakciót, policy/authorize hívást vagy query építést Data osztályban.
- A maradék raw array contractok többsége nem az új admin CRUD refaktor célterülete, hanem auth/cart/checkout, dashboard/reporting, permission/role/user management vagy kisebb nested resource.

Összkép: a Data réteg most elég erős ahhoz, hogy az admin inventory/procurement/product/menu/order folyamatokban egységes typed szerződésként működjön. A következő érdemi lépés nem újabb tömeges refaktor, hanem a megmaradt array contractok priorizált, modulonkénti kezelése.

## Data coverage modulonként

### Root

- `BranchData`

Megjegyzés: önálló, egyszerű output DTO. Nincs párja vagy duplikált `BranchInventoryData`.

### Categories

- `CategoryIndexData`
- `CategoryListItemData`
- `CategoryStoreData`
- `CategoryUpdateData`

Állapot: lefedett admin CRUD.

### Ingredients

- `IngredientIndexData`
- `IngredientInlineUpdateData`
- `IngredientListItemData`
- `IngredientStoreData`
- `IngredientUpdateData`

Állapot: lefedett admin CRUD és inline update.

### IngredientSupplierTerms

- `IngredientSupplierTermIndexData`
- `IngredientSupplierTermInlineUpdateData`
- `IngredientSupplierTermListItemData`
- `IngredientSupplierTermStoreData`
- `IngredientSupplierTermUpdateData`

Állapot: lefedett admin CRUD és inline update.

### Inventory

- `InventoryAdjustmentData`
- `InventoryLedgerIndexData`
- `InventoryMovementListItemData`

Állapot: részben lefedett. Ledger filter és output Data alapú; adjustment input Data alapú. Waste input még raw array.

### Orders

- `OrderDetailData`
- `OrderIndexData`
- `OrderItemData`
- `OrderListItemData`
- `OrderStatusUpdateData`

Állapot: admin list/detail/status update lefedett.

### ProductionPlans

- `ProductionPlanDetailData`
- `ProductionPlanIndexData`
- `ProductionPlanItemData`
- `ProductionPlanListItemData`
- `ProductionPlanPreviewData`
- `ProductionPlanStepData`
- `ProductionPlanStoreData`
- `ProductionPlanUpdateData`

Állapot: input és output Data lefedett, nested item/step szerződéssel. A timeline számítás továbbra is service-ben van.

### Products

- `ProductIndexData`
- `ProductInlineUpdateData`
- `ProductListItemData`
- `ProductStoreData`
- `ProductUpdateData`

Állapot: admin CRUD és inline update lefedett. A `ProductCreateFlowService` még raw array contractot használ.

### PurchaseReceipts

- `PurchaseReceiptItemData`
- `PurchaseReceiptListItemData`
- `PurchaseReceiptStoreData`

Állapot: Data objektumok előkészítve. Teljes flow bekötés külön auditot igényel.

### Purchases

- `PurchaseIndexData`
- `PurchaseItemData`
- `PurchaseListItemData`
- `PurchaseStoreData`
- `PurchaseUpdateData`

Állapot: admin Purchases input/output Data bekötve, nested item contracttal.

### Settings

- `SettingSaveValueData`

Állapot: célzott beállítás DTO.

### StockCounts

- `StockCountDetailData`
- `StockCountIndexData`
- `StockCountItemData`
- `StockCountListItemData`
- `StockCountStoreData`
- `StockCountUpdateData`

Állapot: admin index/store/update/list/detail lefedett. A zárási/készletmozgási logika service-ben maradt.

### Suppliers

- `SupplierIndexData`
- `SupplierListItemData`
- `SupplierStoreData`
- `SupplierUpdateData`

Állapot: admin CRUD lefedett.

### WeeklyMenu

- `WeeklyMenuIndexData`
- `WeeklyMenuInlineUpdateData`
- `WeeklyMenuItemData`
- `WeeklyMenuListItemData`
- `WeeklyMenuStoreData`
- `WeeklyMenuUpdateData`

Állapot: heti menü admin flow lefedett, nested item outputtal.

### WeeklyMenuItem

- `WeeklyMenuItemStoreData`
- `WeeklyMenuItemUpdateData`

Állapot: heti menü item input lefedett. Nem duplikált a `WeeklyMenu\WeeklyMenuItemData` osztállyal, mert az output/nested item szerepet tölt be.

## Jó minták

- Controllerben validált payloadból Data készül: `SomeData::from($request->validated())`.
- Service új/refaktorált admin CRUD metódusok Data objektumot kapnak.
- Repository a legtöbb refaktorált admin modulban query/persistence réteg maradt.
- IndexData objektumok stabil `toFrontendFilters()` payloadot adnak.
- Nested input külön Data objektumot kapott: `PurchaseItemData`, `ProductionPlanItemData`, `StockCountItemData`.
- Output list/detail Data külön van választva: például `OrderListItemData` és `OrderDetailData`.
- Decimal/money mezők több beszerzési és készlet modulban explicit string/float döntéssel szerepelnek.

## Rossz minták

### Refaktor szükséges

- `InventoryController::storeWaste()` -> `InventoryService::recordWaste(array $payload, ...)`
    - Indok: admin inventory mutation, készletmozgást hoz létre.
    - Javaslat: `InventoryWasteData`, külön ingredient/product waste ág szerződéssel.

- `ProductController::storeCreateFlow()` -> `ProductCreateFlowService::store(array $payload)`
    - Indok: admin product creation flow, üzleti jelentőségű.
    - Javaslat: `ProductCreateFlowData`, vagy a meglévő `ProductStoreData` bővítése, ha a flow contractja azonos.

- `ProductIngredientController` -> `ProductIngredientService::{create, update}(array $payload)`
    - Indok: recipe/BOM nested resource, inventory és production plan számításokat érinthet.
    - Javaslat: `ProductIngredientStoreData`, `ProductIngredientUpdateData`, `ProductIngredientListItemData`.

- `RecipeStepController` -> `RecipeStepService::{create, update}(array $payload)`
    - Indok: production timeline input, gyártási terv számítások alapja.
    - Javaslat: `RecipeStepStoreData`, `RecipeStepUpdateData`, opcionálisan `RecipeStepListItemData`.

- `PurchaseDraftGenerationService::generateFromRecommendations(array $payload, ...)`
    - Indok: procurement intelligencia -> purchase draft átmenet, pénzügyi és supplier term adatokkal.
    - Javaslat: `PurchaseDraftGenerationData`, nested recommendation item contracttal.

### Később Data ajánlott

- `UserController` -> `UserAdminService::{create, update, createTemporaryPermission, createDiscount, updateDiscount}`
    - Indok: admin user management összetett, több payload típus.
    - Javaslat: külön `Users` Data namespace, de nem inventory/order prioritás.

- `RoleController` és `UserRoleController` -> role/permission sync array contractok.
    - Indok: security/permission domain; typed DTO javítaná az auditálhatóságot.
    - Javaslat: `RoleStoreData`, `RoleUpdateData`, `RolePermissionSyncData`, `UserRoleSyncData`.

- `ProcurementIntelligenceController` filterek és draft generation input.
    - Indok: dashboard + action keveredik; filter Data különösen hasznos lenne.
    - Javaslat: `ProcurementIntelligenceIndexData`, `PurchaseRecommendationDraftData`.

- `AuthorizationAuditController`, `PermissionController`, `SecurityDashboardController`
    - Indok: filter payloadok még array alapúak.
    - Javaslat: Index/FilterData objektumok, ha a dashboard/filter contract stabilizálódik.

- `RecipeService::paginateForAdmin(array $filters)`
    - Indok: admin recipe index filter.
    - Javaslat: `RecipeIndexData`; utána recipe steps külön.

### OK maradhat array

- Repository persistence metódusok: `create(array $data)`, `update(array $data)`.
    - Indok: repository persistence payload belső adapterként működik, nem külső contract.

- `CheckoutController` -> `CheckoutService::placeOrder(array $payload, ...)`
    - Indok: public order workflow külön domain; nagyobb, önálló refaktor kellene.
    - Megjegyzés: később érdemes `CheckoutData`-t bevezetni, de nem admin Data coverage zárás része.

- `CartController` lokális `$payload = $request->validated()`.
    - Indok: egyszerű request action, nem moduláris admin CRUD.

- `ConversionTrackingController` -> `ConversionTrackingService::trackFromRequest(array $payload, Request $request)`
    - Indok: tracking eventek rugalmas metadata contracttal dolgoznak.

- Dashboard/reporting service-ek aggregált array outputjai.
    - Indok: read-only aggregációk; Data csak akkor kell, ha több frontend fogyasztó vagy stabil publikus contract jelenik meg.

- Audit service context/before/after/extraProperties array payloadok.
    - Indok: activitylog property bag jellegű, szándékosan rugalmas.

- Belső service helper metódusok: például `ProductionPlanService::calculateMinimumReadyAt(array $items)`, `ensureTargetReadyAtCanFitRecipe(array $data)`.
    - Indok: belső számítási input, nem controller/service boundary; később saját value object jöhet, ha ismétlődik.

## Maradék kockázatok

- A `ProductListItemData` `product_ingredients` mezője még `array<string, mixed>` nested itemként szerepel. TypeScript exportnál és hosszabb távú frontend típusosságnál érdemes külön nested Data osztály.
- `IngredientSupplierTermListItemData` decimal mezői `int|float|string|null` union típusúak. Ez domain-kompatibilis, de TypeScript exportban zajos contractot ad.
- `InventoryService::recordWaste()` raw array inputja készletmozgást hoz létre, ezért ez a legfontosabb maradék mutation contract.
- User/role/permission modulok array payloadjai security szempontból érzékenyek, még ha nem is pénzügyi/készlet domain.
- A frontend továbbra is JavaScript alapú; a Data output stabilizálás runtime kompatibilitást ad, de compile-time védelmet még nem.

## Következő backlog

1. `InventoryWasteData` bevezetése.
2. `ProductIngredientStoreData` és `ProductIngredientUpdateData` bevezetése.
3. `RecipeStepStoreData` és `RecipeStepUpdateData` bevezetése.
4. `ProductCreateFlowData` vagy a flow összevezetése `ProductStoreData`-val.
5. `ProcurementIntelligenceIndexData` és `PurchaseDraftGenerationData`.
6. `RecipeIndexData`.
7. Admin user management Data csomag:
    - `UserIndexData`
    - `UserStoreData`
    - `UserUpdateData`
    - `TemporaryPermissionData`
    - `UserDiscountData`
8. Role/permission Data csomag:
    - `RoleIndexData`
    - `RoleStoreData`
    - `RoleUpdateData`
    - `RolePermissionSyncData`
    - `UserRoleSyncData`
9. Product list nested ingredient output szétválasztása:
    - `ProductIngredientListItemData`
10. TypeScript export POC külön PR-ben, csak annotált 1-2 Data osztállyal.

## Ajánlott prioritás

### P1

- `InventoryWasteData`
- `ProductIngredientStoreData` / `ProductIngredientUpdateData`
- `RecipeStepStoreData` / `RecipeStepUpdateData`

Indok: készlet, BOM és gyártási terv számítások alapját érintik.

### P2

- `ProductCreateFlowData`
- `PurchaseDraftGenerationData`
- `ProcurementIntelligenceIndexData`
- `RecipeIndexData`

Indok: admin workflow és procurement döntéstámogatás, de kisebb közvetlen készletírási kockázattal.

### P3

- User/Role/Permission Data objektumok.
- Dashboard/filter Data objektumok.
- Checkout public flow Data refaktor.

Indok: fontos tisztítás, de külön domain kör és nagyobb tesztelési igény.
