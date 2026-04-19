# HAJNALHÉJ – GIT CHECKPOINT PROMPT

## Safe local checkpoint + clean commit + optional push

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
- a commit üzenet szakmai és visszakereshető legyen
- a repo tiszta és követhető maradjon

---

# FELADAT

Vizsgáld át a repository aktuális állapotát, majd készíts egy rendezett Git mentést.

---

# LÉPÉSEK

## 1. Repo állapot vizsgálata

Futtasd le és értékeld:

- `git status`
- `git diff --stat`
- `git diff --name-only`
- ha szükséges: `git diff`

Azonosítsd:

- új fájlok
- módosított fájlok
- törölt fájlok
- gyanús, ideiglenes vagy nem commitolandó fájlok

---

## 2. Ellenőrizd, hogy NEM kerülnek be véletlenül

Különösen figyelj, hogy ezek ne kerüljenek commitba, ha nem indokolt:

- `.env`
- `node_modules/`
- `vendor/`
- build outputok
- lokális IDE fájlok
- logok
- cache fájlok
- ideiglenes jegyzetek
- screenshotok / exportok / random md dumpok
- teszt artifactok

Ha ilyet találsz:

- ne stage-eld
- szükség esetén frissítsd a `.gitignore`-t
- röviden indokold a végső reportban

---

## 3. Csoportosítsd a változásokat

Állapítsd meg, hogy a módosítások:

- egyetlen logikus feature-höz tartoznak
- vagy több különálló témára bonthatók

Szabály:

- ha egyben értelmes, készíts **egy tiszta commitot**
- ha több jól elkülöníthető változás van, készíts **külön logikus commitokat**
- ne csinálj “misc fixes” vagy “update files” típusú zaj commitot

---

## 4. Commit message szabály

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

Ha több commit készül, mindegyik kapjon saját korrekt üzenetet.

---

## 5. Commit előtti validáció

Commit előtt futtasd le, ha releváns:

- `php artisan test`
- `npm test`
- `npm run build`

Ha minden átmegy:

- mehet a commit

Ha valami elhasal:

- előbb javítsd
- csak zöld állapotot commitolj, kivéve ha kifejezetten WIP checkpoint szükséges

---

## 6. Stage + commit

Csak a releváns fájlokat stage-eld.
Ne használj vak `git add .`-t addig, amíg nem ellenőrizted a státuszt.

Ezután készíts commitot.

---

## 7. Opcionális push

Ha a repo branch és remote rendben van, pushold is fel az aktuális branch-et.

Használj normál push-t:

- `git push`
  vagy ha új branch:
- `git push -u origin <branch-name>`

NE hozz létre új branchet, hacsak nem indokolt.
NE force pusholj, hacsak nem elkerülhetetlen és nincs rá külön indok.

---

# ELVÁRT KIMENET

A végén add meg ezt a reportot:

## 1. Git Summary

Röviden:

- mi került commitba
- hány commit készült
- történt-e push

## 2. Staged/Committed files

Sorold fel logikusan:

- created
- modified
- deleted

## 3. Ignored / skipped files

Mi maradt ki és miért.

## 4. Commit message(s)

Pontosan add meg az elkészült commit üzenet(ek)et.

## 5. Validation

Add meg az eredményt:

- `php artisan test`
- `npm test`
- `npm run build`

## 6. Final Git state

Add meg, hogy:

- working tree clean-e
- melyik branch-en vagy
- push megtörtént-e

---

# FONTOS SZABÁLYOK

- Ne commitolj félkész, törött állapotot, ha nem muszáj
- Ne commitolj érzékeny adatot
- Ne commitolj generált szemetet
- Ne használj értelmetlen commit message-et
- Ha a módosítás túl nagy, inkább bontsd több logikus commitra
- A cél tiszta, professzionális Git történet

---

# HA WIP CHECKPOINT SZÜKSÉGES

Ha a munkafa értékes, de még nem teljesen kész, akkor megengedett egy ideiglenes checkpoint commit is, de csak kulturált formában:

- `chore: checkpoint ingredients domain progress`
- `chore: save weekly menu implementation in progress`

Ezt csak akkor használd, ha tényleg indokolt.

---

# PRIORITÁS

1. Biztonság
2. Tisztaság
3. Követhetőség
4. Zöld validáció
5. Szép commit history
