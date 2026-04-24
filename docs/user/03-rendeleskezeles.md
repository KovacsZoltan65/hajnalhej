# Rendeléskezelés

## Rendelések megtekintése

Az admin Rendelések modulban láthatók a beérkezett rendelések. A lista célja, hogy a pékség gyorsan kövesse, mely rendelés milyen feldolgozási állapotban van.

## Státuszok jelentése

- Pending: beérkezett, még nincs feldolgozva.
- Confirmed: visszaigazolt rendelés.
- In preparation: előkészítés alatt.
- Ready for pickup: átvehető.
- Completed: lezárt, teljesített.
- Cancelled: lemondott vagy törölt rendelés.

## Pickup módosítás

Ha a rendeléshez átvételi információ tartozik, azt admin oldalon lehet kezelni, ha az adott modul és jogosultság ezt engedi.

## Belső jegyzetek

Belső jegyzetet akkor használj, ha a pékség munkatársainak kell információt hagyni. Példa:

- vevő telefonált,
- későbbi átvételt kér,
- külön csomagolás szükséges.

## Lemondás

Rendelést csak indokolt esetben állíts `cancelled` állapotba. A lemondott rendelés nem számít teljesített forgalomnak.

## Tipikus hibák

- Rossz státuszba léptetés: ellenőrizd a rendelés életciklusát.
- Completed túl korán: készletlevonás és riportok is érintettek lehetnek.
- Hiányzó belső jegyzet: a műszakváltásnál félreértést okozhat.

