"""Všechno, co souvisí s MySQL databází, je v tomto souboru."""

import os
import mysql.connector
# Nastavení se načítá v app.py ze souboru config.txt.


def pripojeni():
    """Vrátí nové spojení se školní databází."""
    return mysql.connector.connect(
        host=os.environ["DB_HOST"],
        port=int(os.getenv("DB_PORT", "3306")),
        user=os.environ["DB_USER"],
        password=os.environ["DB_PASSWORD"],
        database=os.environ["DB_NAME"],
        connection_timeout=5,
    )


def nacti_produkty():
    """SELECT = čtení dat z tabulky."""
    db = pripojeni()
    try:
        cursor = db.cursor(dictionary=True)
        cursor.execute("SELECT * FROM products ORDER BY id DESC")
        return cursor.fetchall()
    finally:
        db.close()


def nacti_produkt(product_id):
    db = pripojeni()
    try:
        cursor = db.cursor(dictionary=True)
        # %s chrání před SQL injection (nepřipojuj vstup přímo do SQL).
        cursor.execute("SELECT * FROM products WHERE id = %s", (product_id,))
        return cursor.fetchone()
    finally:
        db.close()


def pridej_produkt(nazev, cena, sklad, popis, obrazek=None):
    """INSERT = vložení nového řádku."""
    db = pripojeni()
    try:
        cursor = db.cursor()
        cursor.execute(
            "INSERT INTO products (name, price, stock, description, image_url) "
            "VALUES (%s, %s, %s, %s, %s)",
            (nazev, cena, sklad, popis, obrazek),
        )
        db.commit()  # Potvrzení změny.
    finally:
        db.close()


def uprav_produkt(product_id, nazev, cena, sklad, popis, obrazek=None):
    """UPDATE = úprava existujícího řádku."""
    db = pripojeni()
    try:
        cursor = db.cursor()
        cursor.execute(
            "UPDATE products SET name=%s, price=%s, stock=%s, description=%s, "
            "image_url=%s WHERE id=%s",
            (nazev, cena, sklad, popis, obrazek, product_id),
        )
        db.commit()
    finally:
        db.close()


def vytvor_objednavku(jmeno, email, kosik):
    """Uloží objednávku a její položky; odečte sklad bezpečně v jedné transakci."""
    db = pripojeni()
    try:
        cursor = db.cursor(dictionary=True)
        polozky = []
        celkem = 0

        for product_id, pocet in kosik.items():
            cursor.execute(
                "SELECT id, name, price, stock FROM products WHERE id=%s FOR UPDATE",
                (int(product_id),),
            )
            produkt = cursor.fetchone()
            if produkt is None or pocet < 1 or produkt["stock"] < pocet:
                raise ValueError("Některý produkt už není skladem v požadovaném množství.")
            polozky.append((produkt, pocet))
            celkem += produkt["price"] * pocet

        if not polozky:
            raise ValueError("Košík je prázdný.")

        cursor.execute(
            "INSERT INTO orders (customer_name, customer_email, total_price) VALUES (%s, %s, %s)",
            (jmeno, email, celkem),
        )
        order_id = cursor.lastrowid
        for produkt, pocet in polozky:
            cursor.execute(
                "INSERT INTO order_items (order_id, product_id, quantity, unit_price) "
                "VALUES (%s, %s, %s, %s)",
                (order_id, produkt["id"], pocet, produkt["price"]),
            )
            cursor.execute(
                "UPDATE products SET stock = stock - %s WHERE id = %s",
                (pocet, produkt["id"]),
            )
        db.commit()
        return order_id
    except Exception:
        db.rollback()
        raise
    finally:
        db.close()


def nacti_objednavky():
    db = pripojeni()
    try:
        cursor = db.cursor(dictionary=True)
        cursor.execute("SELECT * FROM orders ORDER BY id DESC")
        return cursor.fetchall()
    finally:
        db.close()
