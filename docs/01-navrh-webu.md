# Návrh webu – 1. fáze

Pracovní název: **B2B nábytek – projekty a cenové nabídky**.

Web pomáhá firemnímu zákazníkovi sestavit vybavení prostoru z katalogu a předat cenovou nabídku obchodníkovi. Neobsahuje košík, platby ani fakturaci. AI obrázky budou předem připravená statická média; aplikace nepotřebuje připojení k AI službě. Produkty a ceny jsou demonstrační.

## Rozsah a rozhodnutí

- Jeden účet představuje jednoho zákazníka. Firemní údaje patří účtu; sdílené firemní účty, týmy a více zaměstnanců jedné organizace zatím neřešíme.
- Zákazník vytváří více projektů a do každého vkládá produkty s množstvím.
- Vzorové sestavy spravuje administrátor jako projekty s typem `template`. Zákazník dostane vlastní kopii, nikoliv přístup k úpravě šablony.
- Každá barevná varianta nábytku je samostatný produkt se SKU. Tím se vyhneme složitému systému variant a skladových kombinací.
- Všechny částky jsou v CZK, bez DPH. Nabídka je orientační, nezahrnuje dopravu a montáž a není fakturou ani platební výzvou. Toto označení bude i v UI.
- Předběžné pravidlo slevy: 1–9 kusů stejného produktu = 0 %, 10–19 = 5 %, 20 a více = 10 %. Počítá se pro každou položku zvlášť, ne podle celého projektu. Jde o navržené demonstrační pravidlo.
- Sleva se aplikuje na cenu × množství, výsledek řádku se zaokrouhlí na dvě desetinná místa (`ROUND_HALF_UP`); součet nabídky je součtem zaokrouhlených řádků. Python použije `Decimal`.
- PDF a e-mail jsou další etapa. Základ ukládá nabídku a kontakt do DB a zpřístupní je obchodníkovi v administraci.

## Stránky a navigace

| Stránka | URL (návrh) | Obsah a akce | Přístup |
|---|---|---|---|
| Úvod | `/` | Showcase prostorů, vysvětlení služby, odkazy na sestavy a katalog | Veřejný |
| Katalog | `/katalog` | Karty produktů, hledání, kategorie, barva, prostředí, tagy, řazení | Veřejný |
| Detail produktu | `/produkty/<id>` | Obrázek, popis, cena, vlastnosti, přidání do zvoleného projektu | Veřejné čtení |
| Vzorové sestavy | `/sestavy` | Ukázkové projekty a volba „Použít sestavu“ | Veřejné čtení |
| Jak to funguje | `/jak-to-funguje` | Pravidla, slevy a postup poptávky | Veřejný |
| O projektu | `/o-projektu` | Autor, školní účel, technologie a vývojové diagramy | Veřejný |
| Registrace / přihlášení | `/registrace`, `/prihlaseni` | Účet a přihlášení | Nepřihlášený |
| Moje projekty | `/projekty` | Seznam vlastních projektů a vytvoření nového | Zákazník |
| Detail projektu | `/projekty/<id>` | Název, položky, množství, průběžná cena, archivace | Vlastník |
| Kontrola nabídky | `/projekty/<id>/nabidka` | Souhrn a ověření kontaktních údajů před potvrzením | Vlastník |
| Nabídka | `/nabidky/<id>` | Uložené ceny, slevy, kontakt a stav vyřízení | Vlastník / admin |
| Administrace | `/admin` | Produkty, tagy, šablony a přehled nabídek | Admin |

Horní navigace: Úvod · Katalog · Sestavy · Jak to funguje · O projektu · Přihlášení / Moje projekty. Administrátor navíc uvidí Administraci. Hlavička a patička budou ve společné HTML šabloně `base.html`; jednotlivé stránky převezmou její vzhled. Jeden hlavní CSS soubor zajistí jednotný design.

## Rozložení klíčových stránek

- Úvod: hlavička → hlavní vizualizace s výzvou k prohlédnutí sestav → ukázky prostředí → tři kroky služby → patička. Showcase může v první verzi odkazovat na pevně připravené sestavy, nepotřebuje vlastní redakční systém.
- Katalog: nadpis a hledání → vlevo filtry, vpravo mřížka produktů → stránkování. Na telefonu budou filtry nad výsledky.
- Projekt: název a popis → řádky produktů s množstvím a odstraněním → cenový souhrn → vytvoření nabídky. V prvním provedení běžné formuláře s obnovením stránky; JavaScript může později ovládání zpříjemnit.
- Nabídka: číslo nabídky → firemní kontakt zachycený při vytvoření → cenové řádky → celková částka a stav → kontakt obchodníka.

## Plán struktury Flask aplikace

Následující struktura je návrh, dosud není implementovaná:

```text
app/
  __init__.py          vytvoření a konfigurace Flask aplikace
  db.py                připojení k MySQL, transakce, uzavření spojení
  auth.py              registrace, přihlášení, odhlášení
  catalog.py           katalog a filtry
  projects.py          projekty, položky a kopírování šablon
  quotes.py            tvorba a zobrazení nabídek
  admin.py             administrační stránky
  services/
    pricing.py         čisté výpočty cen a slev bez HTML a SQL
  repositories/
    catalog.py         parametrizované SQL pro katalog
    users.py           parametrizované SQL pro účty
    projects.py        parametrizované SQL pro projekty
    quotes.py          parametrizované SQL pro nabídky
  templates/
    base.html
    home.html
    catalog.html
    product.html
    templates.html
    how_it_works.html
    about.html
    auth/
    projects/
    quotes/
    admin/
  static/
    css/style.css
    images/
tests/
sql/
docs/
run.py
requirements.txt
.env.example
```

Python moduly pro jednotlivé oblasti budou skupinami rout (Flask Blueprints). Routa přijme HTTP požadavek a vrátí odpověď; repository provede SQL; cenová služba spočítá cenu. Toto rozdělení usnadní vysvětlení i testování, aniž bychom přidávali ORM.

## Co má být hotové pro první kontrolu

- [x] Návrh tématu, stránek a struktury aplikace.
- [x] Návrh datových toků, ER diagram a první DDL skript.
- [x] Spustitelný základ Flasku, pět veřejných HTML stránek a funkční navigace.
- [ ] Ověření DDL v konkrétní školní verzi MySQL.
- [ ] Vzorová data, alespoň tři záznamy do každé tabulky.
- [ ] Průběžné smysluplné commity a zajištění přístupu učitele.

Původní oddělený projekt obsahoval složku `storefront` s Next.js základem. Do školního repozitáře se nepřenáší; zde vzniká samostatná aplikace Flask. Aktuální implementaci vysvětluje `04-prvni-flask-krok.md`.
