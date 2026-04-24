# Domain model

## Users

Felhasználók. Customer vagy admin szerepkörrel rendelkezhetnek. Spatie role/permission kapcsolaton keresztül kapnak jogosultságokat.

## Roles / Permissions

Spatie Permission modellek. A rendszer definíciós forrása a `PermissionRegistry`.

## Products

Eladható pékségi termékek. Kategóriához kapcsolódnak, heti menüben megjelenhetnek, és BOM-on keresztül alapanyagokat használhatnak.

## Ingredients

Gyártási alapanyagok. Fontos mezők:

- `current_stock`
- `minimum_stock`
- `estimated_unit_cost`
- `average_unit_cost`
- `stock_value`
- `unit`

## Recipes / BOM

Termék és alapanyag kapcsolatok mennyiségekkel. Completed rendelésnél ezek alapján jön létre `production_out`.

## Orders

Vásárlói rendelések. Snapshot mezők védik a rendeléskori terméknevet, árat és sorösszeget.

Státuszok:

- `pending`
- `confirmed`
- `in_preparation`
- `ready_for_pickup`
- `completed`
- `cancelled`

## Suppliers

Beszállítók. Beszerzésekhez és ártrendekhez kapcsolódnak.

## Purchases

Beszerzési fej:

- `draft`
- `posted`
- `cancelled`

Tételek: `purchase_items`.

## InventoryMovements

Készletmozgások. Az inventory motor forrásigazsága.

Típusok:

- `purchase_in`
- `production_out`
- `waste_out`
- `adjustment_in`
- `adjustment_out`
- `count_correction`
- `return_in`
- `return_out`

## StockCounts

Leltár fej és tételek. Záráskor eltérés alapján `count_correction` mozgás jön létre.

## ConversionEvents

Marketing és funnel események. Conversion Analytics és CEO Dashboard használja.

