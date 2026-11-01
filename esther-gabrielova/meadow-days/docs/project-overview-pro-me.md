# Meadow Days - přehled projektu

Nejedná se o finální dokumentaci!

## 1. Základní informace

**Název projektu:** Meadow Days\
**Typ projektu:** 2D farmářská hra s databází\
**Použité technologie:** Python + Pygame, SQL\

## 2. Popis projektu

Meadow Days je 2D farmářská hra inspirovaná žánrem farming RPG a hrami,
jako je Stardew Valley. Hráč ovládá vlastní postavu, pohybuje se v
herním světě a postupně buduje a spravuje svou farmu.

Hlavní náplní hry je obdělávání půdy, sázení a zalévání plodin, jejich
pěstování a následné sklízení. Hráč bude mít k dispozici inventář, herní
nástroje a systém herního času. Součástí projektu bude také databáze,
která umožní ukládat a načítat průběh hry.

Hra bude určena pro jednoho hráče a bude fungovat offline.

## 3. Cíle projektu

- Vytvořit funkční 2D hru v prostředí Pygame
- Implementovat ovládání postavy a interakci s herním prostředím
- Vytvořit základní herní mechanismy spojené s provozem farmy
- Implementovat inventář a systém herního času
- Navrhnout a vytvořit databázi
- Propojit hru s databází tak, aby bylo možné ukládat a načítat herní
  postup
- Vytvořit přehlednou strukturu projektu, ve které bude možné
  jednotlivé části dále upravovat

## 4. Základní funkce hry

### 4.1 Ovládání postavy

- Pohyb hráče ve čtyřech směrech
- Animace postavy při pohybu
- Interakce s herním prostředím

### 4.2 Herní prostředí

- Zobrazení herního světa pomocí tiles
- Kamera sledující pohyb hráče
- Kolize s objekty, kterými hráč nemůže procházet

### 4.3 Používání nástrojů

- Výběr a přepínání mezi dostupnými nástroji
- Používání nástrojů na konkrétních místech herního světa

### 4.4 Pěstování plodin

- Příprava půdy pomocí motyky
- Zalévání připravené půdy
- Sázení semen
- Postupný růst rostlin
- Sklízení vypěstovaných plodin

### 4.5 Inventář

- Zobrazování předmětů, které hráč vlastní
- Přidávání a odebírání předmětů z inventáře
- Evidence množství jednotlivých předmětů

### 4.6 Herní čas

- Postupné plynutí času během hraní
- Střídání dnů
- Aktualizace stavu plodin podle herního času

### 4.7 Obchodování

- Možnost prodat vypěstované plodiny
- Získávání herní měny za prodané předměty
- Využívání získaných peněz v rámci hry

### 4.8 Ukládání a načítání hry

- Ukládání aktuálního postupu hráče do databáze
- Načítání uloženého postupu při opětovném spuštění hry
- Uchovávání důležitých herních údajů, například pozice hráče, herního
  dne, inventáře a stavu farmy

## 5. Hlavní části projektu

Projekt se skládá ze dvou hlavních částí:

1.  **Hra** -- samotná 2D hra vytvořená v Pythonu pomocí knihovny Pygame.
2.  **Databáze** -- propojená s hrou, která zajišťuje ukládání a načítání herního postupu.

notes: ER diagram upravovat a později finálně dokončit
