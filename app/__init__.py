"""Vytvoření Flask aplikace a připojení jejích stránek."""

from flask import Flask, render_template

from .pages import pages


def create_app():
    """Sestaví aplikaci; samostatná funkce usnadňuje pozdější testování."""
    app = Flask(__name__)
    app.register_blueprint(pages)

    @app.errorhandler(404)
    def not_found(error):
        """Vrátí srozumitelnou stránku i správný HTTP stav 404."""
        return render_template("404.html"), 404

    return app
