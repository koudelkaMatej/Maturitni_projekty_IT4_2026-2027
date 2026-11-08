import unittest

# TODO: Celý úkol je na vás — třída Kosik + testovací třída TestKosik →


class Kosik:

    def __init__(self):
        self.polozky = []

    def pridej(self, nazev, cena, pocet=1):
        polozka = {
            "nazev": nazev,
            "cena": cena,
            "pocet": pocet
        }

        self.polozky.append(polozka)

    def celkova_cena(self):
        celkem = 0

        for polozka in self.polozky:
            celkem += polozka["cena"] * polozka["pocet"]

        return celkem

    def pocet_polozek(self):
        celkem = 0

        for polozka in self.polozky:
            celkem += polozka["pocet"]

        return celkem

    def odeber(self, nazev):
        for polozka in self.polozky:
            if polozka["nazev"] == nazev:
                self.polozky.remove(polozka)
                return

        raise ValueError("Polozka neexistuje")


class TestKosik(unittest.TestCase):

    def setUp(self):
        self.k = Kosik()

    def test_pridani_polozky(self):
        self.k.pridej("Kaktus", 100, 2)
        self.assertEqual(self.k.celkova_cena(), 200)

    def test_vice_polozek(self):
        self.k.pridej("Kaktus", 100, 2)
        self.k.pridej("Kvetinac", 50, 1)
        self.assertEqual(self.k.celkova_cena(), 250)

    def test_pocet_polozek(self):
        self.k.pridej("Kaktus", 100, 3)
        self.k.pridej("Kvetinac", 50, 2)
        self.assertEqual(self.k.pocet_polozek(), 5)

    def test_odebrani_polozky(self):
        self.k.pridej("Kaktus", 100)
        self.k.odeber("Kaktus")
        self.assertEqual(self.k.celkova_cena(), 0)

    def test_neexistujici_polozka(self):
        with self.assertRaises(ValueError):
            self.k.odeber("Kvetinac")

    def test_prazdny_kosik(self):
        self.assertEqual(self.k.celkova_cena(), 0)


if __name__ == "__main__":
    unittest.main()