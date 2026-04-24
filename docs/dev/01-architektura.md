# Architektúra

## Áttekintés

A Hajnalhéj Laravel + Vue + Inertia alkalmazás. A backend domain logikát Laravel szolgáltatásokban és repositorykban tartja, a frontend Vue komponensekben admin és public felületeket épít.

## Backend minta

```text
routes/web.php
  -> Controller
  -> FormRequest
  -> Policy
  -> Service
  -> Repository
  -> Eloquent Model
```

Controller feladata:

- authorize,
- FormRequest fogadása,
- Service hívás,
- Inertia vagy redirect válasz.

Service feladata:

- üzleti szabály,
- tranzakció,
- workflow,
- inventory és order folyamatok.

Repository feladata:

- query,
- filter,
- pagination,
- aggregate,
- N+1 elkerülése.

## Inertia flow

Admin oldal példa:

```text
GET /admin/procurement-intelligence
  -> ProcurementIntelligenceController@index
  -> ProcurementIntelligenceService::buildDashboard()
  -> Inertia::render('Admin/ProcurementIntelligence/Index')
```

## PrimeVue UI rendszer

Admin listák jellemzően:

- DataTable,
- Select,
- Button,
- modal komponensek,
- magyar feliratok,
- minimum 44px interakciós cél.

## Jogosultsági réteg

- Spatie Permission adja a permission és role tárolást.
- `PermissionRegistry` adja a rendszer oldali definíciót.
- Policy védi a domain műveleteket.
- Route middleware védi az admin URL-eket.
- Frontend csak megjelenítési segítség, nem biztonsági határ.

## Audit réteg

Spatie Activitylog alapú. Service réteg auditál üzleti műveleteket, például rendelés státuszfrissítés, beszerzés könyvelés, készletkorrekció.

