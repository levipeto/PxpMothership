# PXP Mothership - Insights & Analytics Plan

## Cél
Valós idejű és historikus betekintés a cég működésébe, hogy jobb döntéseket hozhassunk.

---

## 1. Sofőr/Futár Terhelés (Courier Load)

### Kérdések
- Melyik futár mennyire van leterhelve?
- Ki a leghatékonyabb? Ki van túlterhelve?
- Mennyi csomagot szállít naponta/hetente/havonta?
- Sikertelen kézbesítések aránya futáronként

### Metrikák
- [ ] Napi/heti/havi kiszállított csomagok száma (`kuldemeny.k_futar_ki`)
- [ ] Felvett csomagok száma (`kuldemeny.k_futar_be`)
- [ ] Sikertelen felvétel/kézbesítés arány (status 41, 81)
- [ ] Átlagos kiszállítási idő
- [ ] Futár elszámolások (`futarelszamolas`)

### Adatforrások
- `futar` - futár adatok
- `futar_statisztika` - létező statisztikák
- `kuldemeny` - k_futar_be, k_futar_ki
- `futarelszamolas` - elszámolások

---

## 2. Útvonalak / Körzetek (Routes)

### Kérdések
- Melyik körzet a legforgalmasabb?
- Hol vannak kapacitás problémák?
- Mely területeken magas a sikertelen kézbesítés?

### Metrikák
- [ ] Csomagok száma körzetenként
- [ ] Átlagos kézbesítési idő körzetenként
- [ ] Sikertelen kézbesítések körzetenként
- [ ] Távoli területek (`tavoli_teruletek`) extra költségei

### Adatforrások
- `futarkorok` - körzetek definíciója
- `iranyitoszam` - irányítószám routing
- `kuldemeny` - k_uc_cim_iranyito (címzett irányítószám)

---

## 3. Depó Teljesítmény (Depot Performance)

### Kérdések
- Melyik depó mennyire van leterhelve?
- Hol torlódnak a csomagok?
- Mennyi idő alatt megy át a csomag a depón?

### Metrikák
- [ ] Napi be/ki forgalom depónként
- [ ] Átlagos átfutási idő (beérkezés → kiküldés)
- [ ] "Raktárban marad" státuszok száma (59, 71)
- [ ] Rossz depóba érkezett csomagok (72)

### Adatforrások
- `depok` - depó lista
- `kuldemeny_tortenet_50` - központi raktár beérkezés
- `kuldemeny_tortenet_60` - központi raktár elhagyás
- `kuldemeny_tortenet_70` - depó beérkezés

---

## 4. Kiszállítási Teljesítmény (Delivery Performance)

### Kérdések
- Mennyi a sikeres kiszállítás arány?
- Mennyi az első próbálkozásra sikeres?
- Mik a leggyakoribb kézbesíthetetlen okok?

### Metrikák
- [ ] Sikeres kiszállítás % (status 90-92)
- [ ] Sikertelen % és okai (`kezbesithetetlen_kod`)
- [ ] Visszaküldött csomagok (status 81, 91)
- [ ] Árukár esetek (status 110)
- [ ] Átlagos kézbesítési idő (felvételtől kézbesítésig)

### Adatforrások
- `kuldemeny` - státuszok, dátumok
- `kezbesithetetlen_kod` - okok listája
- `kuldemeny_tortenet_90` - kézbesítés részletek

---

## 5. Ügyfél Analytics (Customer Insights)

### Kérdések
- Kik a top ügyfelek?
- Melyik ügyfélnél magas a problémás csomag arány?
- Ügyfél aktivitás trendek

### Metrikák
- [ ] Top 10/20 ügyfél volumen szerint
- [ ] Ügyfél növekedés/csökkenés trend
- [ ] Problémás csomagok aránya ügyfelenként
- [ ] Díjszabás kihasználtság

### Adatforrások
- `ugyfel` - ügyfél adatok
- `kuldemeny` - k_ugyfelkod
- `dijszabas_belfold` / `dijszabas_nemzetkozi`

---

## 6. Pénzügyi Betekintés (Financial Insights)

### Kérdések
- Mennyi a napi/heti/havi bevétel?
- Utánvét behajtás státusza?
- Futár elszámolások összesítése

### Metrikák
- [ ] Fuvardíj összesítés (k_fd_* mezők)
- [ ] Utánvét összegek (`k_utanvet`)
- [ ] Utánvét visszafizetés státusz
- [ ] Költségviselő szerinti bontás

### Adatforrások
- `kuldemeny` - k_fd_netto, k_fd_brutto, k_utanvet
- `futarelszamolas` - futár kifizetések
- `szamla_pxpkp_*` - számlák

---

## 7. Szolgáltatás Mix

### Kérdések
- Milyen szolgáltatásokat használnak leginkább?
- 24H vs Same Day vs Express arányok?
- Nemzetközi vs belföldi arány?

### Metrikák
- [ ] Szolgáltatás típus eloszlás (`k_szolgaltatas`)
- [ ] Belföldi vs nemzetközi arány
- [ ] Átvételi pontos kézbesítés (D2S) használat
- [ ] Extra szolgáltatások (SMS, biztosítás, stb.)

---

## 8. Operatív Dashboard Javaslatok

### Real-time Nézet
- [ ] Ma felvett csomagok
- [ ] Ma kiszállítandó csomagok
- [ ] Aktív futárok
- [ ] Problémás csomagok (kezbesíthetetlen, késedelmes)

### Napi Összefoglaló
- [ ] Tegnapi teljesítmény
- [ ] Sikertelen kézbesítések listája
- [ ] Visszaérkezett csomagok

### Heti/Havi Riportok
- [ ] Trend grafikonok
- [ ] Futár rangsor
- [ ] Ügyfél aktivitás
- [ ] Pénzügyi összesítő

---

## Technikai Megjegyzések

### Létező Legacy Táblák
A `legacy` connection-on keresztül érhetők el az adatok:
- `App\Models\Legacy\Kuldemeny` - fő csomag tábla
- `App\Models\Legacy\Futar` - futárok
- `App\Models\Legacy\Ugyfel` - ügyfelek
- `App\Models\Legacy\Depok` - depók
- `KuldemenyTortenet*` - történet táblák státusz szerint

### Státusz Kódok
```
10-29: Regisztrált/Címke nyomtatva
30-39: Feladásra kész
40-42: Felvétel folyamatban/sikertelen/sikeres
50-63: Központi raktár
70-72: Depó
80-82: Kiszállítás alatt
90-92: Kézbesítve
100: Törölve
110: Árukár
```

---

## Prioritások

### P1 - Első körben
1. [ ] Futár terhelés dashboard
2. [ ] Napi operatív áttekintés
3. [ ] Kiszállítási sikerráta

### P2 - Második körben
1. [ ] Ügyfél analytics
2. [ ] Pénzügyi riportok
3. [ ] Körzet/útvonal elemzés

### P3 - Későbbre
1. [ ] Prediktív analytics
2. [ ] Kapacitás tervezés
3. [ ] Automatikus riasztások

---

## Kérdések / Döntések

- [ ] Milyen időszakra kellenek az adatok? (utolsó 30 nap? 1 év?)
- [ ] Kell-e export funkció? (Excel, PDF)
- [ ] Real-time frissítés vagy napi batch?
- [ ] Ki fér hozzá az egyes riportokhoz? (jogosultság kezelés)
