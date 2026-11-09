"""Regresní testy první fáze; později doplníme ceny a oprávnění."""

import unittest
from html.parser import HTMLParser
from urllib.parse import urlsplit

from app import create_app


class LinkCollector(HTMLParser):
    """Získá odkazy a zdroje z vyrenderovaného HTML bez další knihovny."""

    def __init__(self):
        """Připraví seznam nalezených URL."""
        super().__init__()
        self.urls = []

    def handle_starttag(self, tag, attrs):
        """Zaznamená href a src používané navigací, obrázky a CSS."""
        for name, value in attrs:
            if name in ("href", "src") and value:
                self.urls.append(value)


class PagesTest(unittest.TestCase):
    """Ověří propojení stránek, chování filtru a skutečný stav 404."""

    def setUp(self):
        """Každý test má vlastní aplikaci, nepotřebuje běžící server."""
        app = create_app()
        app.config["TESTING"] = True
        self.client = app.test_client()

    def test_pages_and_local_links(self):
        """Všechny veřejné stránky, jejich odkazy i statické zdroje existují."""
        for path in ("/", "/katalog", "/sestavy", "/jak-to-funguje", "/o-projektu"):
            with self.subTest(path=path):
                response = self.client.get(path)
                self.assertEqual(response.status_code, 200)
                html = response.get_data(as_text=True)
                self.assertEqual(html.count('aria-current="page"'), 1)
                parser = LinkCollector()
                parser.feed(html)
                for url in parser.urls:
                    if url.startswith("/"):
                        with self.client.get(urlsplit(url).path) as linked_response:
                            self.assertEqual(linked_response.status_code, 200, url)

    def test_catalog_filters_and_escaped_input(self):
        """Filtry se kombinují a hledaný text se nevkládá jako spustitelné HTML."""
        response = self.client.get("/katalog", query_string={"q": "ŽIDLE", "kategorie": "Židle"})
        html = response.get_data(as_text=True)
        self.assertIn("Židle Forma", html)
        self.assertNotIn("Stůl Linie", html)
        empty = self.client.get("/katalog", query_string={"q": "židle", "kategorie": "Stoly"})
        self.assertIn("Tomuto výběru nic neodpovídá", empty.get_data(as_text=True))
        escaped = self.client.get("/katalog", query_string={"q": '<script>alert(1)</script>'})
        self.assertNotIn("<script>", escaped.get_data(as_text=True))
        self.assertIn("&lt;script&gt;", escaped.get_data(as_text=True))

    def test_missing_page(self):
        """Neznámá adresa vrátí vlastní stránku se stavem 404, nikoli 200."""
        response = self.client.get("/neexistuje")
        self.assertEqual(response.status_code, 404)
        self.assertIn("Tady je zatím prázdno", response.get_data(as_text=True))


if __name__ == "__main__":
    unittest.main()
