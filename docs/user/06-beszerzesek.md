# Beszerzések

## Beszállítók kezelése

A Beszállítók modulban a pékség partnerei tarthatók karban. Beszállító megadása nem minden beszerzésnél kötelező, de riportokhoz és ártrendekhez erősen ajánlott.

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

1. legutóbbi beszállító az alapanyaghoz,
2. legolcsóbb friss beszállító az időablakban,
3. ha nincs adat, beszállító nélkül marad.

## Hibás beszerzés javítása

- Ha draft: szerkeszd vagy stornózd.
- Ha posted: ne töröld kézzel. Készletkorrekcióval vagy új kompenzáló mozgással javítsd a hibát.

