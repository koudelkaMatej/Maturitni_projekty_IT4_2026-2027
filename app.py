from flask import Flask, render_template, redirect, url_for
from flask_sqlalchemy import SQLAlchemy

app = Flask(__name__)

# 1. NASTAVENÍ DATABÁZE: Říkáme, že se má vytvořit soubor kino.db
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///kino.db'
db = SQLAlchemy(app)

# 2. VZHLED DATABÁZE (tzv. Model): Jak vypadá jedna sedačka v tabulce
class Sedacka(db.Model):
    id = db.Column(db.Integer, primary_key=True)      # Unikátní ID v databázi
    cislo = db.Column(db.Integer, unique=True)        # Číslo sedačky (1-25)
    obsazeno = db.Column(db.Boolean, default=False)   # Je volná? (Ano/Ne)

# 3. VYTVOŘENÍ SÁLU PŘI STARTU: 
with app.app_context():
    db.create_all() # Vytvoří tabulky, pokud ještě neexistují
    
    # Pokud v databázi není zatím ani jedna sedačka, vyrobíme jich 25 volných
    if not Sedacka.query.first():
        for i in range(1, 26):
            nova_sedacka = Sedacka(cislo=i, obsazeno=False)
            db.session.add(nova_sedacka)
        db.session.commit() # Tohle databázi řekne "Ulož to natrvalo"

# 4. NAŠE HLAVNÍ STRÁNKA
@app.route("/")
def domovska_stranka():
    # Vytáhneme VŠECHNY sedačky z databáze a pošleme je do HTML
    sedacky_z_db = Sedacka.query.all()
    return render_template("index.html", sedacky=sedacky_z_db)

# 5. REZERVACE SEDAČKY
@app.route("/rezervace/<int:cislo_sedacky>")
def rezervace(cislo_sedacky):
    sedacka = Sedacka.query.filter_by(cislo=cislo_sedacky).first()
    
    if sedacka:
        # MAGIE: Pokud je True, "not" z toho udělá False. Pokud je False, udělá True.
        sedacka.obsazeno = not sedacka.obsazeno
        db.session.commit()
        
    return redirect(url_for('domovska_stranka'))
        
    # Místo hlášky "Zpracovávám" uživatele rovnou přesměrujeme zpět do sálu
    return redirect(url_for('domovska_stranka'))

if __name__ == "__main__":
    app.run(debug=True)