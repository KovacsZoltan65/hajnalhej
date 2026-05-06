# HAJNALHÉJ – GIT CHECKPOINT PROMPT

## Safe local checkpoint + automated pre-commit checks + clean commit + optional push

Projekt:
Hajnalhéj Bakery

Stack:

- Laravel 13
- Vue 3
- Inertia
- PrimeVue
- Tailwind

Cél:
Készíts biztonságos Git checkpointot úgy, hogy a manuális ellenőrzések mellett a commit előtt automatikusan fusson:

- Prettier `.vue`, `.js`, `.json`, `.css`, `.md` fájlokra
- Laravel Pint `.php` fájlokra
- staged fájlokra optimalizált lint-staged workflow
- commit előtt ne kerülhessen be formázatlan kód

---

# FELADAT

Alakítsd ki vagy ellenőrizd a Husky + lint-staged alapú pre-commit workflow-t, majd készíts rendezett Git mentést.

---

# 0. Előfeltétel

Ellenőrizd:

- nincs merge conflict
- working tree kezelhető állapotban van
- `composer install` és `npm install` korábban lefutott
- Laravel Pint elérhető:
    - `vendor/bin/pint`
- Prettier elérhető:
    - `npx prettier --version`

---

# 1. Husky + lint-staged telepítés / ellenőrzés

Ha nincs telepítve:

```bash
npm install -D husky lint-staged prettier
```
