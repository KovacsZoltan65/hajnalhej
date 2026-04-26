# Beszerzési intelligencia

## Mire való?

A Beszerzési intelligencia oldal abban segít, hogy időben lásd, miből kell rendelni, mi drágult, és melyik alapanyag fogyhat el hamarosan.

Az oldal nem találgat kézzel beírt adatokból. A rendszer a korábbi könyvelt beszerzésekből, a készletmozgásokból, a receptek alapanyagigényéből és a beszállítói beállításokból számol.

## Hol találod?

Az admin felületen nyisd meg:

```text
Beszerzés -> Beszerzési intelligencia
```

Ha nem látod a menüpontot, valószínűleg nincs hozzá jogosultságod. Ilyenkor kérj segítséget egy adminisztrátortól.

## Mit mutat az oldal?

### Felső összesítők

Az oldal tetején gyors számokat látsz:

- Aktív figyelmeztetés: hány dologra érdemes most figyelni.
- Kritikus utánrendelés: hány alapanyagnál sürgős a beszerzés.
- Áremelkedés: hány alapanyagnál volt nagyobb drágulás.
- Elfogyási kockázat: hány alapanyag fogyhat el rövid időn belül.

### Beszerzési figyelmeztetések

Itt jelennek meg a fontos jelzések. Például:

- kevés van készleten,
- hamar elfogyhat,
- drágult az alapanyag,
- rég volt belőle beszerzés,
- hiányzik a becsült ár,
- nincs beállítva minimum készlet,
- receptben használjátok, de nincs belőle készleten.

Ezek a jelzések segítenek eldönteni, mi igényel gyors figyelmet.

### Utánrendelési javaslatok

Ez a legfontosabb rész a napi munkában. A rendszer megmutatja:

- melyik alapanyagot érdemes rendelni,
- mennyi van most készleten,
- mennyi fogy átlagosan,
- hány napra elég a készlet,
- mennyit javasol rendelni,
- melyik beszállítót ajánlja,
- milyen sürgős a beszerzés.

A javasolt mennyiség nem mindig pontosan a hiányzó mennyiség. Ha a beszállító csak nagyobb csomagban vagy minimum mennyiséggel szállít, a rendszer felfelé kerekít.

Példa:

```text
Szükség lenne 7 kg lisztre.
A beszállító 25 kg-os zsákban szállít.
A minimum rendelés 50 kg.
A rendszer 50 kg rendelést javasol.
```

### Ártrendek

Az ártrendek megmutatják, hogyan változott egy alapanyag ára beszállítónként.

Hasznos kérdések:

- Drágább lett ugyanaz az alapanyag?
- Van olcsóbb beszállító?
- Stabil az ár, vagy gyorsan változik?

Az ártrend csak könyvelt beszerzésekből számol. A még szerkeszthető draft beszerzések nem számítanak bele.

### Költségtrendek

Ez részletesebb árkép. Dátum, alapanyag és beszállító szerint mutat átlagárakat, súlyozott átlagot és mennyiséget.

A súlyozott átlag azért hasznos, mert nem mindegy, hogy egy drágább áron vettél 1 kg-ot vagy 100 kg-ot.

### Heti fogyási előrejelzés

Ez azt mutatja, mennyi fogyott az elmúlt időszakban, és várhatóan mennyi kellhet a következő hétre.

A rendszer a gyártási felhasználásokból számol, vagyis abból, ami ténylegesen kiment a készletből termeléshez.

## Szűrők használata

Az oldal tetején több szűrő van:

- Időszak: 7, 30, 90 vagy 180 nap.
- Alapanyag: csak egy alapanyag adatai.
- Beszállító: csak egy beszállító adatai.
- Sürgősség: például kritikus vagy magas.
- Figyelmeztetés típusa: például áremelkedés vagy elfogyási kockázat.

A Szűrők törlése gomb visszaállítja az alap nézetet.

## Beszerzési tervezet készítése

Az utánrendelési javaslatokból gyorsan készíthetsz draft beszerzést.

Lépések:

1. Nyisd meg a Beszerzési intelligencia oldalt.
2. Ellenőrizd az utánrendelési javaslatokat.
3. Ha csak néhány alapanyagot szeretnél rendelni, jelöld ki azokat.
4. Ha mindent szeretnél generálni, ne jelölj ki semmit.
5. Kattints a Beszerzési tervezet készítése gombra.
6. A rendszer átvált a Beszerzések oldalra, draft állapotra szűrve.
7. Nyisd meg a draftokat, ellenőrizd az árakat és mennyiségeket.
8. Csak akkor könyveld a beszerzést, amikor valóban beérkezett vagy biztosan rögzíthető.

Fontos: a draft még nem módosít készletet. A készlet csak könyvelés után nő.

## Hogyan választ beszállítót a rendszer?

A rendszer ezt a sorrendet követi:

1. Ha van preferált beszállító az alapanyaghoz, azt választja.
2. Ha nincs, a legutóbbi beszerzés beszállítóját használja.
3. Ha ez sem jó, az adott időszak legolcsóbb friss beszállítóját keresi.
4. Ha nincs elég adat, beszállító nélkül készít javaslatot.

Beszállító nélkül is készülhet draft, de azt érdemes kézzel ellenőrizni és kiegészíteni.

## Mit jelent a sürgősség?

- Kritikus: nincs készlet, vagy legfeljebb pár napra elég.
- Magas: minimum készleten vagy az alatt van, illetve hamar elfogyhat.
- Közepes: még nem vészhelyzet, de figyelni kell.
- Alacsony: van adat, de nincs sürgős teendő.

## Mikor pontatlan a javaslat?

A javaslat akkor lehet kevésbé pontos, ha:

- nincs rendszeresen könyvelve a beszerzés,
- nincs pontos készletmozgás,
- hiányzik a minimum készlet,
- nincs becsült egységköltség,
- rossz a recept alapanyag mennyisége,
- nincs beállítva beszállítói csomagméret vagy minimum rendelés.

Ha furcsa számot látsz, először ezeket érdemes ellenőrizni.

## Jó napi munkamenet

1. Reggel nyisd meg a Beszerzési intelligencia oldalt.
2. Nézd át a kritikus és magas jelzéseket.
3. Ellenőrizd az utánrendelési javaslatokat.
4. Készíts draft beszerzést.
5. Nézd át a draftot, különösen az árakat és a mennyiségeket.
6. Egyeztess a beszállítóval.
7. Beérkezéskor könyveld a beszerzést.

Így a rendszer segít előre látni a hiányt, de a végső döntés mindig ellenőrzött admin művelet marad.
