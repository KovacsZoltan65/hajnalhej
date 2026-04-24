# Changelog

Ez a változásnapló a Hajnalhéj Bakery fő fejlesztési fázisait foglalja össze.

## Inventory / Procurement

- Beszállítók kezelése.
- Beszerzések `draft`, `posted`, `cancelled` státuszokkal.
- Posted beszerzésből automatikus `purchase_in` készletmozgás.
- Inventory ledger és dashboard.
- Selejt, korrekció és leltárkezelés.
- `minimum_stock` egységesítése hivatalos utánrendelési szintként.
- Procurement Intelligence:
  - beszállítói ártrend,
  - ingredient költség-idősor,
  - heti fogyás forecast,
  - minimum készlet alapú utánrendelési javaslat,
  - beszerzési figyelmeztetések.
- Purchase draft generálás utánrendelési javaslatból.

## Security / Audit

- Spatie Permission alapú szerepkör- és jogosultságkezelés.
- `PermissionRegistry` mint központi jogosultság definíció.
- Policy és route middleware védelem admin modulokhoz.
- Spatie Activitylog alapú audit napló.
- Security Dashboard jogosultsági és audit kockázati nézettel.

## PHASE 5 Orders

- Kosár és checkout folyamat.
- Rendelés létrehozás snapshot mezőkkel.
- Admin rendeléskezelés.
- Rendelés státusz életciklus.
- Completed rendeléshez BOM alapú alapanyag-felhasználás.

## PHASE 4 Auth

- Regisztráció és belépés.
- Customer és admin szerepkörök.
- Fiókom oldal.
- Admin panel hozzáférés jogosultsághoz kötve.

## PHASE 3 Ingredients / BOM

- Alapanyag törzsadatok.
- Becsült egységköltség és készlet mezők.
- Receptek / BOM termék-alapanyag kapcsolatokkal.
- Recipe step támogatás.

## PHASE 2 Products

- Termékek admin kezelése.
- Kategóriák.
- Termék státuszok és publikálhatóság.
- Heti menü alapok.

## PHASE 1 Foundation

- Laravel + Vue + Inertia alapstruktúra.
- Admin layout és navigáció.
- Public layout.
- Tailwind és PrimeVue integráció.
- Tesztkörnyezet Pest és Vitest alapon.

