from flask import Flask, render_template

app = Flask(__name__)

@app.route("/")
def domovska_stranka():
    # Teď už nevracíme text, ale náš nový HTML soubor
    return render_template("index.html")

if __name__ == "__main__":
    app.run(debug=True)