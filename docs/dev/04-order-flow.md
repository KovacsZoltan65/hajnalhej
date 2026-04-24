# Order flow

## Cart

A kosár public oldalon gyűjti a vásárló termékeit. A frontend limit nem biztonsági határ; checkoutkor szerver oldalon kell validálni.

## Checkout

Checkout során rendelés és rendelési tételek jönnek létre. A szerver számolja a végösszegeket.

## Order creation

Létrejön:

- `orders`
- `order_items`

Az order item snapshotot tartalmaz.

## Snapshot strategy

Order item mezők:

- `product_name_snapshot`
- `unit_price`
- `line_total`

Ez biztosítja, hogy későbbi termék- vagy árváltozás ne írja át a múltbeli rendelés üzleti jelentését.

## Completed státusz

Amikor rendelés `completed` állapotba kerül, a rendszer BOM alapján alapanyag-felhasználást könyvel.

```text
order completed
  -> product BOM mennyiségek
  -> inventory_movements production_out
  -> material_cost_total frissítés
```

## Audit

Rendelés létrehozás, státuszfrissítés és admin módosítás auditálható esemény.

