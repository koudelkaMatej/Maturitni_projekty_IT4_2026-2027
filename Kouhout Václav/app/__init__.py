"""Flask aplikace."""

from flask import Flask, render_template

from .pages import pages


def create_app():
    """Vytvoření aplikace."""
    app = Flask(__name__)
    app.register_blueprint(pages)

    @app.errorhandler(404)
    def not_found(error):
        """Stránka 404."""
        return render_template("404.html"), 404

    return app
