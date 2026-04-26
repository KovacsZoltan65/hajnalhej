# Inventory engine

## Forrásigazság

Az inventory motor eseményalapú. A készletváltozások forrásigazsága az `inventory_movements` tábla.

Az `ingredients.current_stock`, `average_unit_cost` és `stock_value` összegzett állapotmezők, amelyeket inventory műveletek frissítenek.

## Weighted average cost

`purchase_in` esetén az új átlagköltség a meglévő készletérték és az új beszerzési érték súlyozott átlaga.

Egyszerű képlet:

```text
new_average = (old_stock_value + incoming_total_cost) / new_quantity
```

## Minimum stock logika

`minimum_stock` a hivatalos utánrendelési szint. Low stock feltétel:

```text
current_stock <= minimum_stock
```

Nem használunk párhuzamos `reorder_level` üzleti fogalmat.

## Supplier terms és reorder 2.0

Az alapanyag-beszállító beszerzési feltételeket az `ingredient_supplier_terms` tábla tárolja:

- `supplier_id`
- `ingredient_id`
- `lead_time_days`
- `minimum_order_quantity`
- `pack_size`
- `preferred`
- `unit_cost_override`

Reorder formula:

```text
daily_consumption = 28_day_consumption / 28
lead_time_demand = daily_consumption * lead_time_days
safety_stock = daily_consumption * 3
target_stock = max(minimum_stock, lead_time_demand + safety_stock)
raw_suggested_quantity = target_stock - current_stock
suggested_quantity = raw_suggested_quantity rounded up by minimum_order_quantity and pack_size
```

Supplier választás:

1. preferred supplier term,
2. latest supplier,
3. cheapest fresh supplier,
4. no supplier.

## Movement típusok

### purchase_in

Posted beszerzésből jön létre. Növeli a készletet, frissíti az átlagköltséget és készletértéket.

### production_out

Completed rendelés vagy gyártási felhasználás alapján csökkenti a készletet. BOM mennyiségekből számolódik.

### waste_out

Selejt vagy veszteség. Csökkenti a készletet, auditált művelet.

### adjustment_in / adjustment_out

Kézi készletkorrekció. Indoklás szükséges.

### count_correction

Leltár zárásakor keletkező eltérés korrekció.

## Edge case-ek

- Draft purchase nem hoz létre inventory movementet.
- Posted purchase duplikált könyvelését védeni kell referencia alapján.
- Negatív készlet üzleti jelzés: okát inventory ledgerben kell vizsgálni.
- Hiányzó egységköltség esetén profit és készletérték pontatlan lehet.
- Posted adatot közvetlenül módosítani veszélyes; korrekciós mozgás ajánlott.
