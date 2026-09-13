from flask import Flask, render_template

app = Flask(__name__)

@app.route("/")
def domovska_stranka():
    # Vytvoříme prázdný seznam pro náš sál
    sedacky = []
    
    # Cyklus vytvoří 25 sedaček (čísla 1 až 25)
    for i in range(1, 26):
        # Pro ukázku uděláme každou 4. sedačku obsazenou (zbytek po dělení je 0)
        je_obsazeno = (i % 4 == 0) 
        
        # Každou sedačku si zapíšeme jako slovník
        sedacky.append({"cislo": i, "obsazeno": je_obsazeno})

    # Posíláme seznam "sedacky" do našeho HTML!
    return render_template("index.html", sedacky=sedacky)

if __name__ == "__main__":
    app.run(debug=True)