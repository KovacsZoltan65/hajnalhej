# Belépés és jogosultságok

## Bejelentkezés

1. Nyisd meg a belépési oldalt.
2. Add meg az e-mail címet és jelszót.
3. Sikeres belépés után a szerepköröd alapján látod a saját felületeidet.

## Jelszó reset

Ha a telepítésben engedélyezett a jelszó-visszaállítás, a belépési oldalon található reset folyamat használható. Ha nincs ilyen link, kérj új jelszót vagy fiókellenőrzést egy admintól.

## Szerepkörök

Alap szerepkörök:

- Admin: admin felület és üzleti modulok kezelése jogosultság szerint.
- Customer: vásárlói fiók és saját rendelések.

## Ki mit lát?

A rendszer jogosultság alapján jeleníti meg a modulokat. Ha nem látsz egy menüpontot, valószínűleg nincs hozzá jogosultságod.

Példák:

- Beszerzések megtekintéséhez `purchases.view` szükséges.
- Beszerzések létrehozásához és könyveléséhez `purchases.manage` szükséges.
- Procurement Intelligence oldalhoz `procurement-intelligence.view` szükséges.
- Inventory műveletekhez külön készlet jogosultságok szükségesek.

## Biztonsági tanácsok

- Ne oszd meg a jelszavad.
- Közös gépen mindig jelentkezz ki.
- Admin jogosultságot csak annak adj, akinek tényleg szükséges.
- Jogosultság módosítás után ellenőrizd a Security Dashboardot.
- Gyanús audit eseményt jelezz a rendszer felelősének.

