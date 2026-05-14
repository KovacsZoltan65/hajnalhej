HAJNALHÉJ – MIGRATION COLUMN COMMENT AUDIT & REFACTOR

Projekt:
Hajnalhéj Bakery

Stack:

Laravel 13
MySQL/MariaDB
PHP 8.4
Vue 3
Inertia

Feladat:
Auditáld a teljes database/migrations könyvtárat, majd egészítsd ki a migrációkat hiányzó mező kommentekkel ->comment('...') használatával.

Cél:
A teljes adatbázis önmagát dokumentáló legyen MySQL szinten is.

SZABÁLYOK

1. Minden üzleti mező kapjon kommentet

Példák:

$table->string('name')->comment('Termék neve');
$table->decimal('price')->comment('Bruttó eladási ár');
$table->foreignId('user_id')->comment('Létrehozó felhasználó');
$table->timestamp('published_at')->nullable()->comment('Publikálás időpontja'); 2. Ne kommenteld túl a framework mezőket

NEM szükséges komment:

id
created_at
updated_at
deleted_at
remember_token
email_verified_at
migrations batch mezők
cache / queue technikai mezők

KIVÉTEL:
ha üzleti jelentése van.

3. Foreign key mezők kommentje

Mindig írja le a kapcsolatot:

->comment('Kapcsolódó termék')
->comment('Kapcsolódó rendelés')
->comment('Beszállító azonosító')

NE:

->comment('product id') 4. Enum / státusz mezők

Mindig legyen dokumentálva a jelentés.

Példa:

$table->string('status')
->comment('Rendelés státusza: pending, paid, completed, cancelled'); 5. Boolean mezők

Mindig egyértelmű magyar jelentés:

->comment('Publikus-e')
->comment('Aktív-e')
->comment('Alapértelmezett beszállító-e') 6. JSON mezők

Mindig írja le a struktúra célját:

->comment('Snapshotolt szállítási cím adatok')
->comment('Dolgozó havi naptár JSON struktúra') 7. Pivot táblák

Kapcsolati jelentés legyen:

->comment('Kapcsolódó alapanyag')
->comment('Felhasznált mennyiség') 8. Már meglévő oszlop módosítása

Ha meglévő mezőhöz kerül komment:

használd a ->change() metódust
őrizd meg az eredeti nullable/default/unsigned attribútumokat
NE törj meg indexeket

Példa:

$table->string('status')
->default('draft')
->comment('Publikáció státusza')
->change();

Laravel dokumentáció szerint a módosításkor minden modifier-t explicit meg kell tartani.

KIMENET

1. Audit lista

Mutasd meg:

mely migrációk lettek módosítva
mely táblák maradtak komment nélkül
mely mezők maradtak szándékosan komment nélkül 2. Kódmódosítás

Végezze el ténylegesen a refaktort.

3. Konzisztencia ellenőrzés

Figyelj:

egységes magyar nyelv
rövid, szakmai kommentek
ne legyenek duplikált vagy értelmetlen kommentek
ne legyen angol/magyar keveredés 4. Extra audit

A végén készíts riportot:

Hiányzó problémák:
enum mezők dokumentálatlansága
JSON mezők dokumentálatlansága
homályos mezőnevek (data, value, meta, payload)
túl hosszú kommentek
copy-paste kommentek
FONTOS
NE hozz létre új migrációt, ha az eredeti create migration még nem production historical migration
Ha historical migration:
készíts új alter migrationt
A migration history maradjon tiszta és rollback-safe
Ne módosíts üzleti logikát
Ne módosíts indexet vagy constraintet szükségtelenül
ELVÁRT EREDMÉNY

A projekt adatbázisa:

önmagát dokumentáló
DBA-barát
AI/Codex-barát
könnyen auditálható
hosszútávon karbantartható legyen

Extra ajánlás:

Érdemes külön standardot kialakítani:

->comment(\_\_('db.products.name'))

helyett inkább:

->comment('Termék neve')
