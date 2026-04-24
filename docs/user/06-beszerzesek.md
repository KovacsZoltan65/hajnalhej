# Beszerzések

## Beszállítók kezelése

A Beszállítók modulban a pékség partnerei tarthatók karban. Beszállító megadása nem minden beszerzésnél kötelező, de riportokhoz és ártrendekhez erősen ajánlott.

Beszállítónál megadható az alap lead time is, vagyis hogy általában hány nap alatt szállít. Ez fallback érték, ha egy konkrét alapanyag-beszállító kapcsolatnál nincs külön lead time.

## Beszerzés rögzítése

1. Nyisd meg a Beszerzések modult.
2. Hozz létre új beszerzést.
3. Add meg a beszállítót, dátumot és tételeket.
4. Mentsd draftként.
5. Ellenőrzés után könyveld.

## Draft / Posted jelentése

- Draft: szerkeszthető tervezet, nem módosít készletet.
- Posted: könyvelt beszerzés, készletbevételt hoz létre.
- Cancelled: stornózott draft.

## Purchase draft generálás utánrendelési javaslatból

1. Nyisd meg a Beszerzési intelligencia oldalt.
2. Ellenőrizd az utánrendelési javaslatokat.
3. Jelöld ki a szükséges sorokat, vagy hagyd kijelölés nélkül az összes javasolt tételhez.
4. Kattints a **Beszerzési tervezet készítése** gombra.
5. A rendszer beszállítónként külön draft beszerzést hoz létre.
6. Nyisd meg a draftot, ellenőrizd és szerkeszd a tételeket.
7. Beérkezéskor könyveld posted állapotba.

## Beszállító választás logikája

A rendszer automatikusan próbál beszállítót választani:

1. preferált alapanyag-beszállító kapcsolat,
2. legutóbbi beszállító az alapanyaghoz,
3. legolcsóbb friss beszállító az időablakban,
4. ha nincs adat, beszállító nélkül marad.

## Csomagolási egység és minimum rendelés

Egyes beszállítók csak csomagolási egységben rendelhetők. Példa:

- liszt: 25 kg-os zsák,
- vaj: 10 kg-os karton,
- mag: 5 kg-os zsák.

Ha van pack size vagy minimum rendelési mennyiség, a rendszer a javasolt rendelést felfelé kerekíti. Példa: 7 kg lisztigény, 25 kg pack size és 50 kg minimum rendelés esetén a draftban 50 kg szerepel.

## Hibás beszerzés javítása

- Ha draft: szerkeszd vagy stornózd.
- Ha posted: ne töröld kézzel. Készletkorrekcióval vagy új kompenzáló mozgással javítsd a hibát.
