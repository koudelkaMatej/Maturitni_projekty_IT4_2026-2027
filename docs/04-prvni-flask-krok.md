# První funkční krok: Flask, šablony a navigace

## Co se děje při otevření stránky

1. Prohlížeč požádá o `/katalog`.
2. Flask najde dekorátor `@pages.get("/katalog")` v `app/pages.py`.
3. Funkce `catalog()` přečte parametry URL a vybere ukázkové produkty.
4. `render_template()` předá data šabloně `catalog.html`.
5. Šablona rozšíří `base.html`, takže dostane stejnou hlavičku i patičku jako ostatní stránky.
6. Jinja nahradí proměnné a cykly hotovým HTML. Prohlížeč dostane HTML, nikoli zdrojový Python.

## Proč tyto soubory

| Soubor | Odpovědnost |
|---|---|
| `run.py` | Vstup pro místní spuštění. |
| `app/__init__.py` | Vytvoření Flask aplikace a registrace stránek. |
| `app/pages.py` | Routy veřejných stránek a dočasná data. |
| `app/templates/base.html` | Jediná společná navigace, hlavička a patička. |
| Ostatní `.html` šablony | Obsah konkrétní stránky. |
| `app/static/css/style.css` | Jednotný responzivní design. |
| `app/static/images/room.svg` | Lokální vektorová ilustrace, zatím náhrada za budoucí AI vizualizace. |
| `requirements.txt` | Přímá závislost na Flasku. |
| `requirements-lock.txt` | Přesné verze instalovaných knihoven pro opakovatelnou instalaci. |
| `.venv/` | Místní prostředí Pythonu, které nepatří do Gitu. |

Blueprint je skupina souvisejících rout. Zatím máme jen `pages`; další přidáme až s účty a projekty. Data z ukázkového seznamu později nahradí repository s SQL; vzhled stránek zůstane oddělený.

## Výrazy Jinja

- `{% extends "base.html" %}`: převezmi společnou kostru stránky.
- `{% block content %}`: sem patří vlastní obsah stránky.
- `{{ product.name }}`: vlož název produktu jako text s automatickým escapováním.
- `{% for product in products %}`: opakuj HTML pro každý produkt.
- `url_for('pages.catalog')`: vytvoř URL podle jména routy, místo opakování pevného textu adresy.

Hledání používá GET, protože nemění uložená data. Je proto vidět v URL a jde sdílet. Budoucí změny projektů budou používat POST a CSRF ochranu.

## Co si ověřit pro porozumění

1. Proč změna hlavičky v `base.html` ovlivní všech pět stránek?
2. Co se stane při otevření `/katalog?q=židle`?
3. Proč zatím nepotřebujeme JavaScript ani MySQL ke zobrazení katalogu?
4. Jaký je rozdíl mezi souborem `.html` šablony a HTML odeslaným prohlížeči?

## Stav a omezení

Jde o první spustitelný základ, nikoliv hotový e-shop. MySQL, přihlášení, správa projektů, ukládání nabídek a PDF zatím chybí. Filtry pracují nad třemi ukázkami v Pythonu. Ilustrace jsou schematické, ne produktové fotografie. Školní prostředí je nutné samostatně ověřit.

Autor projektu musí respektovat pravidla školy: školní README omezuje AI generování bloků kódu. Tento základ vznikl s asistencí AI a jeho původ se nemá vydávat za samostatné ruční napsání.
