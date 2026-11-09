"""Veřejné stránky první fáze. Ukázková data později nahradí MySQL."""

from flask import Blueprint, render_template, request

# Blueprint sdružuje související adresy, aby vše nebylo v jednom hlavním souboru.
pages = Blueprint("pages", __name__)

# Dočasná demonstrační data: ceny jsou celé koruny bez DPH, nikoli nabídka k prodeji.
PRODUCTS = (
    {"name": "Židle Forma", "category": "Židle", "price": 2490, "color": "Písková", "use": "Interiér", "style": "chair"},
    {"name": "Stůl Linie", "category": "Stoly", "price": 7990, "color": "Dub přírodní", "use": "Interiér", "style": "table"},
    {"name": "Křeslo Klid", "category": "Křesla", "price": 6490, "color": "Olivová", "use": "Interiér", "style": "armchair"},
)


@pages.get("/")
def home():
    """Zobrazí úvod s odkazy na katalog a inspirativní sestavy."""
    return render_template("home.html")


@pages.get("/katalog")
def catalog():
    """Filtruje ukázky podle kategorie a textu z parametrů URL."""
    query = request.args.get("q", "").strip()[:100]
    category = request.args.get("kategorie", "")
    categories = sorted({product["category"] for product in PRODUCTS})
    selected = [
        product for product in PRODUCTS
        if (not category or product["category"] == category)
        and query.casefold() in f"{product['name']} {product['color']}".casefold()
    ]
    return render_template("catalog.html", products=selected, categories=categories,
                           query=query, category=category)


@pages.get("/sestavy")
def collections():
    """Představí tři zamýšlené sestavy; kopírování vyžaduje budoucí databázi."""
    return render_template("collections.html")


@pages.get("/jak-to-funguje")
def how_it_works():
    """Vysvětlí plánovaný postup a pravidla cenové nabídky."""
    return render_template("how_it_works.html")


@pages.get("/o-projektu")
def about():
    """Popíše školní projekt a odliší hotové funkce od budoucích."""
    return render_template("about.html")
