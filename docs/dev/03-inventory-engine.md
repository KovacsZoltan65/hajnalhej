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

