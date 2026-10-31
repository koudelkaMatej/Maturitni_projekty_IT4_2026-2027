from flask import Flask, render_template

app = Flask(__name__)

@app.route("/")
def home():
    return render_template("main.html")

@app.route("/Manga")
def manga():
    return render_template("manga.html")

if __name__ == "__main__":
    app.run(debug=True)