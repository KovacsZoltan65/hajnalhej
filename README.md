# Hajnalhéj Bakery

**Ropogós reggelek.**

Hajnalhéj Bakery egy production-közeli pékségi rendelési és adminisztrációs platform. A rendszer egyszerre szolgálja ki a vásárlói rendelési élményt és a pékség napi operációját: termékek, heti menük, rendelések, alapanyagok, receptek, készlet, beszerzés, audit és vezetői riportok.

## Stack

- Laravel 13
- PHP 8.4+
- MySQL
- Vue 3
- Inertia.js
- PrimeVue
- Tailwind CSS
- Pest
- Vitest
- Spatie Permission
- Spatie Activitylog

## Fő modulok

Public:

- Landing oldal
- Heti menü
- Kosár és checkout
- Regisztráció, belépés, fiókom

Admin:

- Dashboard
- Termékek és kategóriák
- Alapanyagok
- Receptek / BOM
- Rendelések
- Beszállítók
- Beszerzések
- Inventory és leltár
- Procurement Intelligence
- Audit Logs
- Security Dashboard
- Conversion Analytics
- Profit Dashboard
- CEO Dashboard

## Architektúra

A backend a következő mintát követi:

```text
Route -> Controller -> FormRequest -> Policy -> Service -> Repository -> Model
```

Az Inertia oldalakat Vue komponensek építik fel. Üzleti logika nem kerül Vue komponensbe; a frontend feladata a megjelenítés, interakció és Inertia navigáció.

## Dokumentáció

- Felhasználói kézikönyv: [docs/user/01-attekintes.md](docs/user/01-attekintes.md)
- Fejlesztői dokumentáció: [docs/dev/01-architektura.md](docs/dev/01-architektura.md)
- Fogalomtár: [docs/glossary.md](docs/glossary.md)
- Változásnapló: [CHANGELOG.md](CHANGELOG.md)

## Lokális indítás

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## Minőségbiztosítás

```bash
php artisan test
npm test
npm run build
```

## Üzleti alapelvek

- A minimum készlet a hivatalos utánrendelési szint.
- A készletmozgások az inventory motor forrásigazságai.
- Beszerzés draft állapotban nem módosít készletet.
- Posted beszerzés hoz létre `purchase_in` készletmozgást.
- Completed rendelés vonja le a BOM szerinti alapanyagokat.
- Jogosultságot backend policy és route middleware is véd.
- Zajos read-only audit spam kerülendő; üzleti műveletek auditálandók.

