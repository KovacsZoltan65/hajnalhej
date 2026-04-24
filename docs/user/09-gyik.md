# GYIK

## 1. Miért negatív a készlet?

Mert több fogyás, selejt vagy korrekció lett könyvelve, mint amennyi készletbevétel volt. Ellenőrizd az inventory mozgásokat.

## 2. Miért nem látok egy modult?

Nincs hozzá jogosultságod, vagy nem admin szerepkörrel vagy belépve.

## 3. Miért nincs készletmozgás draft beszerzés után?

Draft beszerzés nem módosít készletet. Csak posted állapot után keletkezik `purchase_in`.

## 4. Miért változott a profit?

Változhatott a rendelésállomány, anyagköltség, BOM, beszerzési ár vagy rendelés státusz.

## 5. Mit jelent a minimum készlet?

Ez az utánrendelési szint. Ha a készlet ez alá esik, a rendszer figyelmeztet.

## 6. Mit jelent a posted beszerzés?

Könyvelt beszerzés, amely növeli az alapanyag készletet.

## 7. Lehet posted beszerzést szerkeszteni?

Nem ajánlott és a rendszer draft szerkesztési logikát támogat. Hibát korrekcióval kezelj.

## 8. Miért nincs beszállító egy drafton?

Az alapanyaghoz nem volt korábbi vagy friss beszállítói ár adat.

## 9. Miért több draft készült egyszerre?

A rendszer beszállító szerint csoportosítja a tételeket.

## 10. Miért más az egységár a draftban?

A rendszer utolsó ismert egységárat használ, vagy ennek hiányában becsült egységköltséget.

## 11. Miért nincs utánrendelési javaslat?

Lehet, hogy a készlet elegendő, nincs fogyási adat, vagy a szűrők kizárják a tételeket.

## 12. Miért kapok áremelkedés figyelmeztetést?

Az utolsó ár legalább 10%-kal magasabb az előző beszerzési árnál.

## 13. Mit jelent a készlet fedezet napokban?

Azt becsüli, hogy jelenlegi fogyási ütem mellett hány napig elég a készlet.

## 14. Miért nincs friss beszerzési adat?

Az alapanyaghoz nincs posted beszerzés az utóbbi időszakban.

## 15. Miért kell becsült egységköltség?

A becsült költség segít profitot és draft egységárat számolni, ha nincs friss beszerzés.

## 16. Mikor használjak selejtet?

Ha ismert okból elveszett vagy használhatatlan készletet kell levonni.

## 17. Mikor használjak leltárt?

Ha a teljes vagy részleges valós készletet akarod egyeztetni a rendszerrel.

## 18. Miért nem látszik egy termék a heti menüben?

Lehet inaktív, nincs hozzáadva a menühöz, vagy a menü nincs publikálva.

## 19. Miért nem frissül az ártrend draft beszerzéstől?

Ártrend posted beszerzési tételekből számolódik.

## 20. Mi az a BOM?

Recept alapanyaglistája mennyiségekkel.

## 21. Mikor vonódik le a BOM szerinti készlet?

Rendelés completed állapotba kerülésekor.

## 22. Mit tegyek, ha rossz alapanyag szerepel egy receptben?

Javítsd a termék receptjét, majd ellenőrizd a jövőbeli rendelések és profit számítások hatását.

## 23. Miért látok Security Dashboard figyelmeztetést?

Jogosultsági, audit vagy registry kockázatot észlelt a rendszer.

## 24. Miért nem minden megtekintés auditált?

Read-only dashboard megtekintéseket nem logolunk, hogy ne legyen audit spam.

## 25. Mit ellenőrizzek zárás előtt?

Rendelések státuszát, posted beszerzéseket, selejtet, készletfigyelmeztetéseket és profit dashboardot.

