from flask import Flask, render_template, redirect, url_for, request, session, flash
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///kino.db'
app.secret_key = "supertajnyklic" # Důležité pro session i pro flash messages!
db = SQLAlchemy(app)

# --- DATABÁZE ---
class Sedacka(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    cislo = db.Column(db.Integer, unique=True)
    obsazeno = db.Column(db.Boolean, default=False)
    vlastnik = db.Column(db.String(100), nullable=True)


class Uzivatel(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    jmeno = db.Column(db.String(50), unique=True)
    heslo = db.Column(db.String(120))

with app.app_context():
    db.create_all()
    if not Sedacka.query.first():
        for i in range(1, 26):
            nova_sedacka = Sedacka(cislo=i, obsazeno=False)
            db.session.add(nova_sedacka)
        db.session.commit()

# --- HLAVNÍ STRÁNKA ---
@app.route("/")
def domovska_stranka():
    sedacky_z_db = Sedacka.query.all()
    pocet_obsazenych = Sedacka.query.filter_by(obsazeno=True).count()
    celkova_cena = pocet_obsazenych * 150
    
    prihlaseny_uzivatel = session.get("uzivatel")
    
    return render_template("index.html", sedacky=sedacky_z_db, celkova_cena=celkova_cena, prihlaseny_uzivatel=prihlaseny_uzivatel)

# --- REZERVACE ---
@app.route("/rezervace/<int:cislo_sedacky>")
def rezervace(cislo_sedacky):
    if "uzivatel" not in session:
        flash("Pro rezervaci sedačky se musíš nejprve přihlásit!", "error")
        return redirect(url_for("prihlaseni"))
        
    aktualni_uzivatel = session["uzivatel"]
    sedacka = Sedacka.query.filter_by(cislo=cislo_sedacky).first()
    
    if sedacka:
        if sedacka.obsazeno:
            if sedacka.vlastnik == aktualni_uzivatel:
                sedacka.obsazeno = False
                sedacka.vlastnik = None
                flash(f"Tvoje rezervace sedačky {cislo_sedacky} byla zrušena.", "info")
            else:
                flash("Tuto sedačku si zarezervoval někdo jiný, nemůžeš ji zrušit!", "error")
        else:
            sedacka.obsazeno = True
            sedacka.vlastnik = aktualni_uzivatel
            flash(f"Úspěšně jsi zarezervoval sedačku {cislo_sedacky}!", "success")
            
        db.session.commit()
        
    return redirect(url_for('domovska_stranka'))

# --- REGISTRACE ---
@app.route("/registrace", methods=["GET", "POST"])
def registrace():
    if request.method == "POST":
        jmeno = request.form.get("jmeno")
        heslo = request.form.get("heslo")
        
        existuje = Uzivatel.query.filter_by(jmeno=jmeno).first()
        if existuje:
            flash("Tohle jméno už je zabrané! Zkus si vymyslet jiné.", "error")
            return redirect(url_for("registrace"))
        
        zahasovane_heslo = generate_password_hash(heslo)
        novy_uzivatel = Uzivatel(jmeno=jmeno, heslo=zahasovane_heslo)
        db.session.add(novy_uzivatel)
        db.session.commit()
        
        flash("Paráda, jsi zaregistrován! Nyní se můžeš přihlásit.", "success")
        return redirect(url_for("prihlaseni"))
        
    return render_template("registrace.html")

# --- PŘIHLÁŠENÍ ---
@app.route("/prihlaseni", methods=["GET", "POST"])
def prihlaseni():
    if request.method == "POST":
        jmeno = request.form.get("jmeno")
        heslo = request.form.get("heslo")
        
        uzivatel = Uzivatel.query.filter_by(jmeno=jmeno).first()
        if uzivatel and check_password_hash(uzivatel.heslo, heslo):
            session["uzivatel"] = uzivatel.jmeno
            flash(f"Vítej zpět, {uzivatel.jmeno}! Jsi úspěšně přihlášen.", "success")
            return redirect(url_for("domovska_stranka"))
        else:
            flash("Špatné jméno nebo heslo. Zkus to znovu.", "error")
            return redirect(url_for("prihlaseni"))
            
    return render_template("prihlaseni.html")

# --- ODHLÁŠENÍ ---
@app.route("/odhlaseni")
def odhlaseni():
    session.pop("uzivatel", None)
    flash("Byl jsi úspěšně odhlášen.", "info")
    return redirect(url_for("domovska_stranka"))
# --- TAJNÉ TLAČÍTKO PRO UČITELE / ADMINA ---
@app.route("/vycistit")
def vycistit_sal():
    # 1. Zkontrolujeme, jestli je přihlášený "admin"
    if session.get("uzivatel") != "admin":
        flash("Na tuto akci nemáš práva!", "error")
        return redirect(url_for('domovska_stranka'))
        
    # 2. Načteme všechny sedačky
    vsechny_sedacky = Sedacka.query.all()
    
    # 3. Cyklem projdeme jednu po druhé a uvolníme je
    for sedacka in vsechny_sedacky:
        sedacka.obsazeno = False
        sedacka.vlastnik = None
        
    db.session.commit()
    flash("Admin vyčistil sál! Všechny rezervace byly smazány.", "success")
    return redirect(url_for('domovska_stranka'))
# --- O KINĚ  4. stránka ---
@app.route("/o-kine")
def o_kine():
    return render_template("o_kine.html")

if __name__ == "__main__":
    app.run(debug=True)