# Frontend standards

## Vue komponens szabványok

- `script setup`
- kis, célzott komponensek,
- business logic backendben,
- props és emits egyértelműen,
- magyar user-facing szövegek.

## PrimeVue DataTable standard

Admin listáknál:

- szűrők felül,
- lapozás,
- rendezés,
- üres állapot,
- kontextusos CTA,
- művelet oszlop jobb oldalon.

## Mobil fallback

Széles tábláknál `overflow-x-auto` használata kötelező. Ne törjön össze a layout mobilon.

## 44px targetek

Gombok, ikon gombok, checkboxok és fontos interakciók minimum 44px tap targetet kapjanak.

## Magyar feliratok

Admin és public user-facing szövegek magyarul jelenjenek meg. Technikai kulcsok csak fejlesztői kontextusban látszódhatnak.

## Empty state CTA

Üres állapot ne csak azt mondja, hogy nincs adat. Adjon következő lépést:

- Új beszerzés,
- Szűrők törlése,
- Új termék,
- Leltár indítása.

## Badge rendszer

Státuszokat badge formában mutassunk:

- draft / posted / cancelled,
- rendelési státusz,
- urgency: kritikus, magas, közepes, alacsony.

