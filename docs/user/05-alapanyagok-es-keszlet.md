# Alapanyagok és készlet

## Alapanyagok kezelése

Az Alapanyagok modulban találhatók a pékség gyártási alapanyagai. Fontos mezők:

- név,
- egység,
- aktuális készlet,
- minimum készlet,
- becsült egységköltség,
- aktív állapot.

## Minimum készlet

A minimum készlet az a szint, amely alá csökkenve a rendszer utánrendelést javasol.

Példa:

- BL80 liszt aktuális készlet: 8 kg
- Minimum készlet: 10 kg
- A rendszer alacsony készletként kezeli.

## Készletérték fogalma

A készletérték az aktuális készlet és az egységköltség alapján számolt érték. Pontosabb értéket ad, ha a beszerzések posted állapotban könyvelve vannak.

## Inventory Dashboard használata

Az Inventory Dashboard segít:

- alacsony készletet látni,
- készletértéket követni,
- selejtet és mozgásokat értelmezni,
- gyorsan reagálni készlethiányra.

## Készlethiány jelzések

Készlethiány akkor jelenhet meg, ha:

- az aktuális készlet minimum készlet alatt van,
- a várható fogyás alapján hamar elfogy,
- BOM-ban használt alapanyagból nincs készlet.

## Fontos

Draft beszerzés nem növeli a készletet. Csak posted beszerzés után történik készletbevétel.

