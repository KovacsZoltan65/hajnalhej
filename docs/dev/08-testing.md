# Testing

## Pest szabványok

Feature tesztek ellenőrizzék:

- jogosultság,
- validáció,
- üzleti számítás,
- adatbázis mellékhatás,
- audit vagy inventory movement, ha releváns.

Példa fókusz:

```text
admin can generate purchase drafts
customer cannot generate purchase drafts
posted purchase creates purchase_in movement
completed order creates production_out movement
```

## Vitest szabványok

Frontend tesztek ellenőrizzék:

- oldal renderel,
- táblák és kártyák megjelennek,
- empty state látszik,
- gomb kattintás Inertia hívást indít.

## Fixture minta

Tesztadat legyen üzletileg érthető:

- Liszt,
- Vaj,
- Malom Kft.,
- posted purchase,
- production_out movement.

## Mit teszteljünk először?

1. Jogosultsági határ.
2. Pénzügyi vagy készletet módosító művelet.
3. Állapotváltás.
4. Dashboard számítás.
5. Frontend fő CTA.

## Tiltott tesztek

- Nulla assertion.
- Csak renderel és semmit nem ellenőriz.
- Túl általános snapshot, amely üzleti hibát nem fog meg.

