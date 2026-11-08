import os
from decimal import Decimal, InvalidOperation
from pathlib import Path
from uuid import uuid4

from PIL import Image, UnidentifiedImageError
from functools import wraps
from hmac import compare_digest

from dotenv import dotenv_values
from flask import (
    Flask, abort, flash, redirect, render_template,
    request, session, url_for,
)

from database import (
    pripojeni,
    nacti_produkty, nacti_produkt, pridej_produkt,
    uprav_produkt, vytvor_objednavku, nacti_objednavky,
)

# Načti nastavení ze souboru config.txt vedle tohoto souboru.
# Soubor obsahuje údaje k MySQL a heslo administrátora.
# Nikdy ho neposílej na GitHub nebo spolužákům.
NASTAVENI = dotenv_values(os.path.join(os.path.dirname(__file__), "config.txt"))

for klic, hodnota in NASTAVENI.items():
    if hodnota is not None:
        os.environ[klic] = hodnota

app = Flask(__name__)
app.secret_key = os.getenv("SECRET_KEY", "")

# Obrázky patří do složky static/uploads, v MySQL je jen název souboru.
SLOZKA_OBRAZKU = Path(app.static_folder) / "uploads"
SLOZKA_OBRAZKU.mkdir(parents=True, exist_ok=True)
app.config["MAX_CONTENT_LENGTH"] = 5 * 1024 * 1024  # Max. 5 MB na požadavek.

# Úmyslně nepoužíváme výchozí heslo, které by šlo snadno uhodnout.
if not app.secret_key or not os.getenv("ADMIN_PASSWORD"):
    raise RuntimeError("Vyplň SECRET_KEY a ADMIN_PASSWORD do souboru config.txt")


def jen_admin(funkce):
    """Dekorátor: chrání stránky administrace."""
    @wraps(funkce)
    def kontrola(*args, **kwargs):
        if not session.get("admin"):
            return redirect(url_for("prihlaseni"))
        return funkce(*args, **kwargs)
    return kontrola


def precti_formular():
    """Z formuláře přečte a zkontroluje data produktu."""
    nazev = request.form.get("name", "").strip()
    popis = request.form.get("description", "").strip()
    if not nazev or len(nazev) > 100:
        raise ValueError("Název musí mít 1 až 100 znaků.")
    try:
        cena = Decimal(request.form.get("price", ""))
        sklad = int(request.form.get("stock", ""))
    except (InvalidOperation, ValueError):
        raise ValueError("Zadej platnou cenu a počet kusů.")
    if not cena.is_finite() or cena < 0 or cena > 99999999 or sklad < 0:
        raise ValueError("Cena a sklad musí být nezáporné a cena přiměřená.")
    return nazev, cena, sklad, popis


def uloz_obrazek():
    """Ověří nahraný obrázek a uloží ho pod náhodným názvem."""
    soubor = request.files.get("image")

    # Když uživatel obrázek nevybral, nic neukládáme.
    if soubor is None or not soubor.filename:
        return None

    # Ověřujeme skutečný obsah, ne jen příponu souboru.
    try:
        obrazek = Image.open(soubor.stream)
        obrazek.verify()
        soubor.stream.seek(0)
        obrazek = Image.open(soubor.stream)
        if obrazek.format not in ("JPEG", "PNG", "WEBP"):
            raise ValueError("Vyber obrázek JPG, PNG nebo WEBP.")
        if obrazek.width * obrazek.height > 20_000_000:
            raise ValueError("Obrázek má příliš velké rozlišení.")
        # Obrázek překódujeme na JPG: neskladujeme cizí metadata.
        obrazek = obrazek.convert("RGB")
        nazev = f"{uuid4().hex}.jpg"
        obrazek.save(SLOZKA_OBRAZKU / nazev, format="JPEG", quality=85)
        return nazev
    except (UnidentifiedImageError, OSError) as chyba:
        raise ValueError("Soubor není platný obrázek.") from chyba


@app.get("/")
def katalog():
    # Funkce vrací HTML šablonu a seznam produktů z databáze.
    produkty = nacti_produkty()
    return render_template("index.html", products=produkty)


@app.get("/produkt/<int:product_id>")
def detail(product_id):
    produkt = nacti_produkt(product_id)
    if produkt is None:
        abort(404)
    return render_template("detail.html", product=produkt)


@app.post("/do-kosiku/<int:product_id>")
def do_kosiku(product_id):
    produkt = nacti_produkt(product_id)
    if produkt is None or produkt["stock"] <= 0:
        abort(404)
    kosik = dict(session.get("kosik", {}))
    klic = str(product_id)
    kosik[klic] = min(kosik.get(klic, 0) + 1, produkt["stock"])
    session["kosik"] = kosik
    flash("Přidáno do košíku.")
    return redirect(url_for("katalog"))


def obsah_kosiku():
    """Vrátí položky a cenu podle aktuálních cen v databázi."""
    polozky = []
    celkem = Decimal("0.00")
    for klic, pocet in session.get("kosik", {}).items():
        produkt = nacti_produkt(int(klic))
        if produkt:
            mezisoucet = produkt["price"] * pocet
            polozky.append((produkt, pocet, mezisoucet))
            celkem += mezisoucet
    return polozky, celkem


@app.get("/kosik")
def kosik():
    polozky, celkem = obsah_kosiku()
    return render_template("kosik.html", items=polozky, total=celkem)


@app.post("/kosik/odebrat/<int:product_id>")
def odebrat(product_id):
    kosik = dict(session.get("kosik", {}))
    kosik.pop(str(product_id), None)
    session["kosik"] = kosik
    return redirect(url_for("kosik"))


@app.post("/objednat")
def objednat():
    jmeno = request.form.get("name", "").strip()
    email = request.form.get("email", "").strip()
    if not 1 <= len(jmeno) <= 100 or not 3 <= len(email) <= 150 or "@" not in email:
        flash("Vyplň jméno a platný e-mail.")
        return redirect(url_for("kosik"))
    try:
        cislo = vytvor_objednavku(jmeno, email, session.get("kosik", {}))
    except ValueError as chyba:
        flash(str(chyba))
        return redirect(url_for("kosik"))
    session.pop("kosik", None)
    return render_template("hotovo.html", order_id=cislo)


@app.route("/admin/login", methods=["GET", "POST"])
def prihlaseni():
    if request.method == "POST":
        zadane = request.form.get("password", "")
        if compare_digest(zadane, os.environ["ADMIN_PASSWORD"]):
            session.clear()
            session["admin"] = True
            return redirect(url_for("admin"))
        flash("Nesprávné heslo.")
    return render_template("login.html")


@app.post("/admin/logout")
@jen_admin
def odhlaseni():
    session.clear()
    return redirect(url_for("katalog"))


@app.get("/admin")
@jen_admin
def admin():
    return render_template(
        "admin.html", products=nacti_produkty(), orders=nacti_objednavky()
    )


@app.route("/admin/novy", methods=["GET", "POST"])
@jen_admin
def novy_produkt():
    if request.method == "POST":
        try:
            udaje = precti_formular()
            obrazek = uloz_obrazek()
            try:
                pridej_produkt(*udaje, obrazek)
            except Exception:
                # Když selže databáze, nepotřebný nový soubor smažeme.
                if obrazek:
                    (SLOZKA_OBRAZKU / obrazek).unlink(missing_ok=True)
                raise
            flash("Kaktus byl přidán.")
            return redirect(url_for("admin"))
        except ValueError as chyba:
            flash(str(chyba))
    return render_template("formular.html", product=None)


@app.route("/admin/upravit/<int:product_id>", methods=["GET", "POST"])
@jen_admin
def editace(product_id):
    produkt = nacti_produkt(product_id)
    if produkt is None:
        abort(404)
    if request.method == "POST":
        try:
            udaje = precti_formular()
            novy_obrazek = uloz_obrazek()
            obrazek = novy_obrazek or produkt["image_url"]
            try:
                uprav_produkt(product_id, *udaje, obrazek)
            except Exception:
                if novy_obrazek:
                    (SLOZKA_OBRAZKU / novy_obrazek).unlink(missing_ok=True)
                raise
            flash("Změny byly uloženy.")
            return redirect(url_for("admin"))
        except ValueError as chyba:
            flash(str(chyba))
    return render_template("formular.html", product=produkt)


def vytvor_tabulky():
    """Při spuštění vytvoří chybějící tabulky ze schema.sql."""

    # Cesta ke schema.sql ve stejné složce jako app.py.
    cesta = Path(__file__).resolve().parent / "schema.sql"

    with open(cesta, "r", encoding="utf-8") as soubor:
        text_sql = soubor.read()

    # Odstraníme komentáře začínající --.
    # Pak rozdělíme tři CREATE TABLE příkazy podle středníku.
    radky = []
    for radek in text_sql.splitlines():
        if not radek.strip().startswith("--"):
            radky.append(radek)

    prikazy = "\n".join(radky).split(";")

    db = pripojeni()
    cursor = db.cursor()

    try:
        for prikaz in prikazy:
            prikaz = prikaz.strip()

            if not prikaz:
                continue

            # Bezpečnost: při spuštění nepovolíme mazání ani jiné změny.
            if not prikaz.upper().startswith("CREATE TABLE IF NOT EXISTS "):
                raise ValueError("schema.sql smí obsahovat jen CREATE TABLE IF NOT EXISTS.")

            cursor.execute(prikaz)

        db.commit()
        print("Databázové tabulky jsou připravené.")

    finally:
        cursor.close()
        db.close()


if __name__ == "__main__":
    # Vytvoří jen chybějící tabulky (nevymaže uložené produkty).
    vytvor_tabulky()

    # Debug používej pouze na vlastním počítači, nikoli na veřejném serveru.
    app.run(debug=True)
