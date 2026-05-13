Kérlek kérdezd be a frissítendő feature branch nevét, majd automatikusan frissítsd azt a latest main alapján rebase használatával.

Elvárt működés:

1. Kérdezd be a branch nevét
   Példa:
    - A_2
    - feature/auth
    - bugfix/login

2. Ellenőrizd:
    - van-e git repo
    - létezik-e a branch
    - nincs-e uncommitted change
    - nincs-e folyamatban rebase/merge

3. Workflow:
    - git checkout main
    - git pull origin main
    - git checkout <branch>
    - git rebase main

4. Konfliktus esetén:
    - álljon meg
    - írja ki:
      "Rebase konfliktus. Oldd fel a konfliktusokat, majd futtasd:
      git add .
      git rebase --continue"

5. Siker esetén:
    - írja ki:
      "Branch sikeresen frissítve a latest main alapján."

6. Használj biztonságos Bash scriptet:
    - set -e
    - színezett output
    - érthető hibaüzenetek

7. Kimenet:
    - teljes production-ready bash script
    - fájlnév: sync-branch.sh
    - Linux + Git Bash kompatibilis
