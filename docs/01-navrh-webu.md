# Návrh webu

B2B katalog nábytku: zákazník sestaví projekt a získá cenovou nabídku. Bez košíku a platební brány. Produkty a ceny jsou demonstrační.

## Stránky

| Dostupné nyní | Plánované |
|---|---|
| Úvod `/` | Detail produktu |
| Katalog `/katalog` | Registrace a přihlášení |
| Sestavy `/sestavy` | Moje projekty a jejich položky |
| Jak to funguje `/jak-to-funguje` | Cenové nabídky |
| O projektu `/o-projektu` | Administrace |

Navigace je společná v `base.html`, vzhled v `style.css`.

## Pravidla návrhu

- Jeden účet má vlastní projekty a firemní kontakt; sdílené firmy zatím neřešíme.
- Šablonu sestavy zákazník zkopíruje do vlastního projektu.
- Barevná varianta je samostatný produkt se SKU. Filtry: kategorie, barva, prostředí a tagy.
- Nabídka zachová tehdejší ceny a kontakt. Změna katalogu ji nepřepíše.
- Ceny jsou v CZK bez DPH, dopravy a montáže. Nabídka není faktura.
- Navržené slevy za stejný produkt: 1–9 ks = 0 %, 10–19 ks = 5 %, od 20 ks = 10 %.
- Řádek = cena × množství × (1 − sleva / 100). Zaokrouhlení na haléře pomocí `Decimal` a `ROUND_HALF_UP`; celkem součet řádků.
- PDF, e-mail a AI fotografie jsou pozdější rozšíření.

## Stav první fáze

- Hotovo: návrh webu, SQL schéma, pět stránek, navigace a ukázkový katalog.
- Zbývá: ověřit MySQL a školní prostředí, doplnit vzorová data a zkontrolovat historii commitů podle zadání.

Poznámka: návrh datových toků a ER diagram požaduje první kontrolní termín; v této verzi nejsou přiložené. Minimálně dva automatizované testy doplníme pro finální odevzdání.
