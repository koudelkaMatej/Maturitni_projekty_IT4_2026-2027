from flask import Flask, render_template, redirect, url_for, request
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///kino.db'
app.secret_key = "supertajnyklic" # Tohle budeme potřebovat pro přihlášení
db = SQLAlchemy(app)

# 1. TABULKA SEDAČEK
class Sedacka(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    cislo = db.Column(db.Integer, unique=True)
    obsazeno = db.Column(db.Boolean, default=False)
    vlastnik = db.Column(db.String(100), nullable=True)

# 2. TABULKA UŽIVATELŮ (NOVÉ!)
class Uzivatel(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    jmeno = db.Column(db.String(50), unique=True)
    heslo = db.Column(db.String(120)) # Ukládáme jako dlouhý tajný kód

with app.app_context():
    db.create_all() # Vytvoří i naši novou tabulku uživatelů
    if not Sedacka.query.first():
        for i in range(1, 26):
            nova_sedacka = Sedacka(cislo=i, obsazeno=False)
            db.session.add(nova_sedacka)
        db.session.commit()

@app.route("/")
def domovska_stranka():
    sedacky_z_db = Sedacka.query.all()
    pocet_obsazenych = Sedacka.query.filter_by(obsazeno=True).count()
    celkova_cena = pocet_obsazenych * 150
    return render_template("index.html", sedacky=sedacky_z_db, celkova_cena=celkova_cena)

@app.route("/rezervace/<int:cislo_sedacky>")
def rezervace(cislo_sedacky):
    sedacka = Sedacka.query.filter_by(cislo=cislo_sedacky).first()
    if sedacka:
        sedacka.obsazeno = not sedacka.obsazeno
        if sedacka.obsazeno:
            sedacka.vlastnik = "Adam" # Tohle změníme hned v další fázi!
        else:
            sedacka.vlastnik = None
        db.session.commit()
    return redirect(url_for('domovska_stranka'))

# 3. NOVÉ: STRÁNKA PRO REGISTRACI
@app.route("/registrace", methods=["GET", "POST"])
def registrace():
    # Pokud uživatel odeslal formulář (metoda POST)
    if request.method == "POST":
        jmeno = request.form.get("jmeno")
        heslo = request.form.get("heslo")
        
        # Zkontrolujeme, zda už tohle jméno někdo nemá
        existuje = Uzivatel.query.filter_by(jmeno=jmeno).first()
        if existuje:
            return "Tohle jméno už je zabrané! <a href='/registrace'>Zkus to znovu</a>"
        
        # Bezpečnost: Heslo nikdy neukládáme jako čistý text!
        zahasovane_heslo = generate_password_hash(heslo)
        novy_uzivatel = Uzivatel(jmeno=jmeno, heslo=zahasovane_heslo)
        db.session.add(novy_uzivatel)
        db.session.commit()
        
        return "Paráda, jsi zaregistrován! <a href='/'>Zpět do kina</a>"
        
    # Pokud jen přišel na stránku (metoda GET), ukážeme mu formulář
    return render_template("registrace.html")

if __name__ == "__main__":
    app.run(debug=True)