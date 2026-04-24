# Fogalomtár

## BOM

Bill of Materials. A termék receptúrája alapanyag mennyiségekkel. Példa: egy kenyérhez 0,6 kg liszt és 0,4 l víz.

## Weighted Average Cost

Súlyozott átlagköltség. Beszerzéseknél a mennyiséggel súlyozott egységár, amely pontosabb készletértéket ad, mint egy egyszerű átlag.

## Minimum készlet

Az a készletszint, amely alá csökkenve a rendszer utánrendelést javasol. A Hajnalhéj rendszerben ez a hivatalos reorder level.

## Draft

Piszkozat állapot. Beszerzésnél még nem módosít készletet. Heti menünél még nem látható publikált menüként.

## Posted

Könyvelt állapot. Beszerzésnél készletbevételt jelent, és `purchase_in` inventory movement keletkezik.

## Gross Profit

Bruttó profit. Bevétel mínusz becsült vagy könyvelt anyagköltség.

## LTV

Lifetime Value. Egy vásárló becsült hosszú távú értéke.

## Repeat Customer

Visszatérő vásárló, aki egynél több rendelést adott le.

## Conversion Rate

Konverziós arány. Példa: checkout indításból hány sikeres rendelés lett.

## Inventory Movement

Készletmozgás. Minden készletváltozás naplózott eseménye: beszerzés, gyártási felhasználás, selejt, korrekció vagy leltár eltérés.

## Procurement Intelligence

Beszerzési intelligencia. Ártrendeket, fogyási előrejelzést, minimum készlet alapú javaslatokat és figyelmeztetéseket mutató admin modul.

## Lead Time

Beszállítási átfutási idő napokban. A reorder javaslat figyelembe veszi, hogy a várható fogyás alatt mennyi készlet fogy el, amíg a beszállító szállít.

## Pack Size

Csomagolási vagy rendelési egység. Példa: 25 kg-os lisztes zsák. A rendszer a javasolt rendelést felfelé kerekíti erre az egységre.

## Minimum Order Quantity

Minimum rendelési mennyiség. Ha a beszállító legalább 50 kg rendelést kér, a rendszer ennél kisebb javaslatot nem ad az adott supplier term alapján.

## Preferred Supplier

Preferált beszállító egy adott alapanyaghoz. Reorder és purchase draft generáláskor elsőbbséget élvez.

## Purchase Draft

Beszerzési tervezet. Szerkeszthető, még nem könyvelt beszerzés.

## Stock Count

Leltár. A tényleges készlet felvétele, majd eltérés esetén készletkorrekció létrehozása.
