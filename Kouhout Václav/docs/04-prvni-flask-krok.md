# Jak funguje současný web

**Prohlížeč → Flask → Python funkce → Jinja šablona → HTML odpověď.**

Katalog zatím čte tři produkty ze seznamu v Pythonu. MySQL, účty ani ukládání projektů nejsou připojené.

## Soubory

| Soubor / složka | Účel |
|---|---|
| `run.py` | Spuštění serveru |
| `app/__init__.py` | Vytvoření Flask aplikace |
| `app/pages.py` | Veřejné routy a ukázková data |
| `app/templates/base.html` | Společná navigace a kostra HTML |
| `app/templates/*.html` | Obsah stránek |
| `app/static/` | CSS a ilustrace |
| `.venv/` | Místní Python prostředí, nepatří do Gitu |
| `requirements*.txt` | Závislosti a přesné verze |

## Jinja

- `extends`: převzetí společné šablony.
- `block`: místo pro obsah stránky.
- `{{ product.name }}`: vložení hodnoty jako escapovaného textu.
- `for`: opakování HTML pro každou položku.
- `url_for()`: sestavení odkazu podle názvu routy.

## Hledání

Formulář odešle GET `/katalog?q=židle`. Funkce `catalog()` přečte `q`, vyfiltruje seznam a předá výsledek do `catalog.html`. JavaScript není potřeba.

Spuštění popisuje [manuál](../manual.md). Další krok: připojení MySQL a skutečná data.

Základ vznikl s asistencí AI; školní README její použití omezuje.
