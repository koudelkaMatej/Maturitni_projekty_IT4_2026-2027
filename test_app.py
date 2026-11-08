import unittest
from app import app  # Načteme naši vlastní aplikaci z app.py

class TestNasehoKina(unittest.TestCase):
    
    # Tato funkce se spustí před každým testem
    def setUp(self):
        # Vytvoříme testovacího "klienta" (virtuální prohlížeč bez grafiky)
        app.config['TESTING'] = True
        self.klient = app.test_client()

    #  jestli funguje hlavní stránka (sál)
    def test_hlavni_stranka_funguje(self):
        # Robot zkusí jít na adresu "/"
        odpoved = self.klient.get('/')
        # Kód 200 znamená v řeči internetu "Všechno je OK, stránka jede"
        self.assertEqual(odpoved.status_code, 200)
        
    #  jestli funguje stránka registrace
    def test_stranka_registrace(self):
        odpoved = self.klient.get('/registrace')
        self.assertEqual(odpoved.status_code, 200)
        #  jestli se na stránce objevil text z nadpisu
        self.assertIn(b'Zaregistruj', odpoved.data)

if __name__ == '__main__':
    unittest.main()