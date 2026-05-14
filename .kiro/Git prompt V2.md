# HAJNALHÉJ – GIT CHECKPOINT PROMPT V2

## Safe local checkpoint + conflict check + clean commit + optional push

Projekt:
Hajnalhéj Bakery

Stack:

- Laravel 13
- Vue 3
- Inertia
- PrimeVue
- Tailwind

Cél:
Készíts egy biztonságos, tiszta Git checkpointot a jelenlegi állapotról úgy, hogy:

- minden releváns módosítás fel legyen mérve
- ne kerüljön be érzékeny vagy generált szemét
- merge conflict már korán felismerhető legyen
- conflict esetén ne történjen vak automatikus megoldás
- a commit üzenet szakmai és visszakereshető legyen
- a repo tiszta és követhető maradjon

---

# FELADAT

Vizsgáld át a repository aktuális állapotát, majd készíts egy rendezett Git mentést.

Ha a feladat branch-frissítést vagy main/master ág beolvasását is igényli, akkor előtte végezz biztonságos merge conflict vizsgálatot.

---

# INTERAKTÍV INPUTOK

Kérdezd meg, ha nem egyértelmű:

- szükséges-e push?
- szükséges-e branch-frissítés main/master alapján?
- ha igen, melyik forrás ágból történjen a frissítés?
    - alapértelmezett: `origin/main`
- a conflict check módja:
    - `check` = csak vizsgálat, végén abort
    - `apply` = ha nincs conflict, a merge maradhat és commitolható

---

# LÉPÉSEK

## 0. Előfeltétel

Győződj meg róla, hogy:

- a repository elérhető
- a jelenlegi branch azonosítható
- nincs folyamatban lévő merge/rebase/cherry-pick
- nincs már meglévő unresolved conflict

Futtasd:

```bash
git status
git branch --show-current
```
