from flask import Flask, render_template, redirect, url_for, request, session
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///kino.db'
app.secret_key = "supertajnyklic" # Tohle je nutné pro fungování session
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
        return "Pro rezervaci se musíš přihlásit! <br><br> <a href='/prihlaseni'>Přihlásit se</a>"
        
    aktualni_uzivatel = session["uzivatel"]
    sedacka = Sedacka.query.filter_by(cislo=cislo_sedacky).first()
    
    if sedacka:
        if sedacka.obsazeno:
            # Pokud je obsazená, zrušit ji může jen její majitel
            if sedacka.vlastnik == aktualni_uzivatel:
                sedacka.obsazeno = False
                sedacka.vlastnik = None
            else:
                return "Tuto sedačku si zarezervoval někdo jiný! <br><br> <a href='/'>Zpět</a>"
        else:
            # Pokud je volná, zarezervujeme ji na aktuálního uživatele
            sedacka.obsazeno = True
            sedacka.vlastnik = aktualni_uzivatel
            
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
            return "Tohle jméno už je zabrané! <br><br> <a href='/registrace'>Zkus to znovu</a>"
        
        zahasovane_heslo = generate_password_hash(heslo)
        novy_uzivatel = Uzivatel(jmeno=jmeno, heslo=zahasovane_heslo)
        db.session.add(novy_uzivatel)
        db.session.commit()
        
        return "Paráda, jsi zaregistrován! <br><br> <a href='/prihlaseni'>Můžeš se přihlásit</a>"
        
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
            return redirect(url_for("domovska_stranka"))
        else:
            return "Špatné jméno nebo heslo. <br><br> <a href='/prihlaseni'>Zkus to znovu</a>"
            
    return render_template("prihlaseni.html")

# --- ODHLÁŠENÍ ---
@app.route("/odhlaseni")
def odhlaseni():
    session.pop("uzivatel", None)
    return redirect(url_for("domovska_stranka"))

if __name__ == "__main__":
    app.run(debug=True)