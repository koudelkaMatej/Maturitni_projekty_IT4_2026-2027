from flask import Flask

# Tímto říkáme: vytvoř novou webovou aplikaci
app = Flask(__name__)

# Tímto říkáme: Když uživatel přijde na hlavní stránku ("/"), spusť tuto funkci
@app.route("/")
def domovska_stranka():
    return "<h1>Vítej v našem novém kině!</h1>"

# Tímto říkáme: Pokud se tento soubor spustí, zapni náš webový server
if __name__ == "__main__":
    app.run(debug=True)