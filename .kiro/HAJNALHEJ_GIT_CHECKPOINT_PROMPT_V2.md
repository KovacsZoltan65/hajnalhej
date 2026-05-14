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

Ha már conflict van:

```bash
git diff --name-only --diff-filter=U
```

Állj meg, és kérdezd meg:

1. abort merge/rebase
2. manual resolve
3. AI-assisted resolve attempt

Ne oldj meg automatikusan conflictot kérdezés nélkül.

---

## 1. Repo állapot vizsgálata

Futtasd le és értékeld:

```bash
git status
git diff --stat
git diff --name-only
```

Ha szükséges:

```bash
git diff
```

Azonosítsd:

- új fájlok
- módosított fájlok
- törölt fájlok
- gyanús, ideiglenes vagy nem commitolandó fájlok

---

## 2. Conflict elővizsgálat branch-frissítés esetén

Ha branch-frissítés szükséges, először:

```bash
git fetch origin
```

Majd ellenőrizd a cél branch-et:

```bash
git checkout <target-branch>
git status --short
```

Ha a working tree nem tiszta, előbb kezeld a helyi módosításokat.

Ezután futtasd a biztonságos merge próbát:

```bash
git merge --no-commit --no-ff <source-branch>
```

Példa:

```bash
git merge --no-commit --no-ff origin/main
```

---

## 3. Ha NINCS merge conflict

Ha a merge sikeres:

### `check` módban

Csak jelentést készíts, majd vond vissza a próbamerge-et:

```bash
git merge --abort
```

Jelentsd:

- nincs conflict
- melyik source branch lett tesztelve
- melyik target branch volt érintett

### `apply` módban

A merge maradhat staged/working állapotban, majd folytatható a validáció és commit.

---

## 4. Ha VAN merge conflict

Állj meg.

Listázd a conflicted fájlokat:

```bash
git diff --name-only --diff-filter=U
git status
```

Adj döntési lehetőséget:

```text
Merge conflict detected.

Options:
1. Abort merge
2. Manual resolve
3. AI-assisted resolve attempt
```

### 1. Abort

Ha ezt választom:

```bash
git merge --abort
```

Majd reportold a visszaállított állapotot.

### 2. Manual resolve

Ha ezt választom:

- ne módosíts automatikusan fájlt
- add meg a conflicted fájlokat
- adj rövid javaslatot a kézi feloldás sorrendjére
- commit csak akkor történhet, ha a conflictok eltűntek

Ellenőrzés:

```bash
git diff --name-only --diff-filter=U
git status
```

### 3. AI-assisted resolve attempt

Csak akkor használd, ha ezt kifejezetten választom.

Szabályok:

- előbb olvasd el az érintett fájlokat
- értsd meg mindkét oldal szándékát
- ne használj vak `ours` vagy `theirs` stratégiát
- migration, auth, security, payment, stock, order vagy permission conflict esetén különösen óvatosan járj el
- minden módosítást magyarázz meg
- validáció nélkül ne commitolj

Tilos automatikusan használni:

```bash
git checkout --ours
git checkout --theirs
git merge -X ours
git merge -X theirs
```

kivéve, ha erre külön engedélyt adok.

---

## 5. Ellenőrizd, hogy NEM kerülnek be véletlenül

Különösen figyelj, hogy ezek ne kerüljenek commitba, ha nem indokolt:

- `.env`
- `.env.*`, kivéve biztonságos példa fájlok, pl. `.env.example`
- `node_modules/`
- `vendor/`
- build outputok
- lokális IDE fájlok
- logok
- cache fájlok
- ideiglenes jegyzetek
- screenshotok / exportok / random md dumpok
- teszt artifactok
- nagy bináris fájlok
- adatbázis dumpok
- privát kulcsok vagy tokenek

Ha ilyet találsz:

- ne stage-eld
- szükség esetén javasolj `.gitignore` frissítést
- röviden indokold a végső reportban

---

## 6. Csoportosítsd a változásokat

Állapítsd meg, hogy a módosítások:

- egyetlen logikus feature-höz tartoznak
- vagy több különálló témára bonthatók

Szabály:

- ha egyben értelmes, készíts egy tiszta commitot
- ha több jól elkülöníthető változás van, készíts külön logikus commitokat
- ne csinálj `misc fixes`, `update files`, `changes` típusú zaj commitot

---

## 7. Commit message szabály

A commit message legyen:

- rövid
- szakmai
- visszakereshető
- feature-alapú

Ajánlott formátumok:

- `feat: implement ingredients and product recipe domain`
- `feat: add weekly menu admin foundation`
- `fix: stabilize admin product modal flow`
- `test: add feature and vitest coverage for ingredients`
- `refactor: align admin ui patterns across product modules`
- `chore: checkpoint production planning progress`

Ha több commit készül, mindegyik kapjon saját korrekt üzenetet.

---

## 8. Commit előtti validáció

Commit előtt futtasd le, ha releváns:

```bash
php artisan test
npm test
npm run build
```

Ha minden átmegy:

- mehet a commit

Ha valami elhasal:

- előbb javítsd
- csak zöld állapotot commitolj

Kivétel:
WIP checkpoint csak akkor készülhet, ha ezt kifejezetten indokoltnak látod, és a commit message ezt jelzi.

---

## 9. Stage + commit

Csak a releváns fájlokat stage-eld.

Ne használj vak `git add .` parancsot addig, amíg nem ellenőrizted a státuszt.

Használj inkább célzott stage-elést:

```bash
git add <file>
```

vagy logikus csoportonként:

```bash
git add app/ resources/js/ tests/
```

Ezután készíts commitot.

---

## 10. Opcionális push

Ha push szükséges, és a branch/remote rendben van:

```bash
git push
```

Ha új branch:

```bash
git push -u origin <branch-name>
```

NE hozz létre új branchet, hacsak nem indokolt.

NE force pusholj, hacsak:

- nem elkerülhetetlen
- pontosan megindokoltad
- és külön engedélyt kaptál rá

Ha mégis szükséges:

```bash
git push --force-with-lease
```

Sima `--force` használata tilos.

---

# ELVÁRT KIMENET

A végén add meg ezt a reportot:

## 1. Git Summary

Röviden:

- mi került commitba
- hány commit készült
- történt-e push
- történt-e merge/conflict check

## 2. Conflict Check

Add meg:

- source branch
- target branch
- mód: `check` vagy `apply`
- volt-e conflict
- ha volt, mely fájlok érintettek
- mi lett a döntés:
    - abort
    - manual resolve
    - AI-assisted resolve

## 3. Staged/Committed files

Sorold fel logikusan:

- created
- modified
- deleted

## 4. Ignored / skipped files

Mi maradt ki és miért.

## 5. Commit message(s)

Pontosan add meg az elkészült commit üzenet(ek)et.

## 6. Validation

Add meg az eredményt:

- `php artisan test`
- `npm test`
- `npm run build`

Ha valamelyik nem futott, indokold.

## 7. Final Git state

Add meg:

- working tree clean-e
- melyik branch-en vagy
- push megtörtént-e
- van-e unresolved conflict
- van-e folyamatban lévő merge/rebase

---

# FONTOS SZABÁLYOK

- Ne commitolj félkész, törött állapotot, ha nem muszáj
- Ne commitolj érzékeny adatot
- Ne commitolj generált szemetet
- Ne használj értelmetlen commit message-et
- Ha a módosítás túl nagy, inkább bontsd több logikus commitra
- Conflict esetén ne dönts automatikusan üzleti logikáról
- Ne használj vak `ours/theirs` stratégiát
- A cél tiszta, professzionális Git történet

---

# HA WIP CHECKPOINT SZÜKSÉGES

Ha a munkafa értékes, de még nem teljesen kész, akkor megengedett egy ideiglenes checkpoint commit is, de csak kulturált formában:

- `chore: checkpoint ingredients domain progress`
- `chore: save weekly menu implementation in progress`
- `chore: checkpoint production planning refactor`

Ezt csak akkor használd, ha tényleg indokolt.

---

# PRIORITÁS

1. Biztonság
2. Conflict korai felismerése
3. Tisztaság
4. Követhetőség
5. Zöld validáció
6. Szép commit history
