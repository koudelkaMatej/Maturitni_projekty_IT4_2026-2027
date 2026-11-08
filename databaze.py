from flask import Flask, render_template, request, jsonify
from flask_cors import CORS
import sqlite3

app = Flask(__name__)
CORS(app)

# Vytvoření databáze při prvním spuštění
def init_db():
    conn = sqlite3.connect("scores.db")
    cursor = conn.cursor()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS scores (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            score INTEGER NOT NULL
        )
    """)
    conn.commit()
    conn.close()

init_db()

# 1. Zobrazení webové stránky s žebříčkem
@app.route("/")
def index():
    conn = sqlite3.connect("scores.db")
    cursor = conn.cursor()
    # Načte TOP 10 nejlepších výsledků
    cursor.execute("SELECT username, score FROM scores ORDER BY score DESC LIMIT 10")
    scores = cursor.fetchall()
    conn.close()
    return render_template("index.html", scores=scores)

# 2. API endpoint, kam tvoje Pygame hra pošle skóre
@app.route("/api/score", methods=["POST"])
def add_score():
    data = request.json
    username = data.get("username")
    score = data.get("score")

    if not username or score is None:
        return jsonify({"status": "error", "message": "Chybí jméno nebo skóre"}), 400

    conn = sqlite3.connect("scores.db")
    cursor = conn.cursor()
    cursor.execute("INSERT INTO scores (username, score) VALUES (?, ?)", (username, score))
    conn.commit()
    conn.close()

    return jsonify({"status": "success"}), 200

if __name__ == "__main__":
    app.run(port=5000, debug=True)