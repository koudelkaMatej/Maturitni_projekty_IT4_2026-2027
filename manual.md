# Uživatelský manuál 

## Spuštění ve Visual Studio Code

1. Otevři složku školního repozitáře `Maturitni_projekty_IT4_2026-2027`
2. Otevři **Terminal → New Terminal** (PowerShell).
3. Spusť:

```powershell
.\.venv\Scripts\python.exe run.py
```

4. Otevři `http://127.0.0.1:5000` v prohlížeči. Server ukončíš pomocí **Ctrl+C** v terminálu.

Aktivace prostředí není nutná: úplná relativní cesta zvolí správný Python a nevyžaduje změnu PowerShell ExecutionPolicy. Ve VS Code lze přes **Python: Select Interpreter** vybrat `.venv\Scripts\python.exe`; doporučený výběr je i v `.vscode/settings.json`.

## Instalace na jiném PC

Potřebuješ funkční Python a internet pro první instalaci závislostí. Na vývojovém PC byl ověřen Python 3.15.0; školní Python ještě není ověřen. Flask 3.1 vyžaduje Python alespoň 3.9.

```powershell
python -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r requirements-lock.txt
.\.venv\Scripts\python.exe run.py
```

Pokud `python` nefunguje, použij cestu ke skutečně nainstalovanému interpretu. `.venv` se nekopíruje mezi PC ani neukládá do Gitu; vytvoří se znovu. Aktuální fáze nepotřebuje MySQL.

## Ovládání

- Horní navigace přechází mezi pěti veřejnými stránkami; aktuální stránka je podtržená.
- V katalogu zadej například `židle`, vyber kategorii a stiskni **Zobrazit výběr**.
- Hledání a kategorie platí současně. Prázdný výsledek nabízí návrat do celého katalogu.
- Odkaz **Zrušit filtry** obnoví všechny tři ukázkové produkty.
- Sestavy jsou zatím popisy konceptů; účty, projekty a cenové nabídky ještě nejsou implementované.

## Chybové stavy

- **ModuleNotFoundError: flask**: použij Python z `.venv` a nainstaluj závislosti.
- **Soubor nebyl nalezen**: ověř, že terminál je v kořeni školního repozitáře.
- **Adresa je obsazená**: ukonči předchozí server; případně spusť `.\.venv\Scripts\python.exe -m flask --app run run --port 5001` a otevři port 5001.
- **Stránka nenalezena (404)**: vrať se pomocí navigace. Neexistující URL úmyslně vrací HTTP 404.
- **Prohlížeč se nemůže připojit**: server musí stále běžet v terminálu.

Vývojový Flask server slouží jen pro lokální práci, nikoliv veřejné produkční nasazení.

## Automatizované ověření

```powershell
.\.venv\Scripts\python.exe -m unittest discover -s tests -v
```

Testy kontrolují stránky, místní odkazy a zdroje, kombinaci filtrů, escapování vstupu a chybovou stránku. V další fázi doplníme důležité obchodní funkce: výpočet slev a přístupová oprávnění.
