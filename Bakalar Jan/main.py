import pygame
import sys
import random
import requests
from boss import Boss
from player import Player

def odeslat_skore(jmeno_hrace, skore_hrace):
    try:
        url = "http://127.0.0.1:5000/api/score"
        data = {
            "username": jmeno_hrace,
            "score": skore_hrace
        }
        response = requests.post(url, json=data)
        if response.status_code == 200:
            print("Skóre bylo úspěšně odesláno na web!")
    except Exception as e:
        print("Chyba při připojení k serveru:", e)


pygame.init()
screen = pygame.display.set_mode((800, 600))
clock = pygame.time.Clock()

bg_image = pygame.image.load("assets/background.png").convert()
bg_image = pygame.transform.scale(bg_image, (800, 600))
# --- FONT A SKÓRE ---
font = pygame.font.Font("assets/pixelfont.ttf", 60)  # Výchozí font Pygame, velikost 60
skore = 0
zvuk_seknuti = pygame.mixer.Sound("assets/sword.wav")
zvuk_smrti = pygame.mixer.Sound("assets/slice.wav")
zvuk_seknuti.set_volume(0.7)
zvuk_smrti.set_volume(0.7)

# ==========================================
# 1. PŘIHLAŠOVACÍ OBRAZOVKA (ZADÁNÍ JMÉNA)
# ==========================================
def obrazovka_prihlaseni():
    jmeno = ""
    zadavani_bezi = True

    while zadavani_bezi:
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                pygame.quit()
                sys.exit()

            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_RETURN and jmeno.strip() != "":
                    zadavani_bezi = False  # Potvrzení jména a start hry
                elif event.key == pygame.K_BACKSPACE:
                    jmeno = jmeno[:-1]  # Mazání znaku
                else:
                    # Omezení délky jména na 8 znaků
                    if len(jmeno) < 8 and event.unicode.isprintable():
                        jmeno += event.unicode

        # Vykreslení UI přihlášení
        screen.fill((0, 100, 0))

        # Nadpis
        text_vyzva = font.render("Zadej své jméno:", True, (255, 255, 255))
        screen.blit(text_vyzva, (400 - text_vyzva.get_width() // 2, 200))

        # Políčko s psaným jménem
        pygame.draw.rect(screen, (0, 0, 0), (250, 280, 300, 80))
        pygame.draw.rect(screen, (255, 255, 0), (250, 280, 300, 80), width=3)

        text_jmeno = font.render(jmeno, True, (255, 255, 255))
        screen.blit(text_jmeno, (260, 290))

        # Nápověda pro potvrzení
        text_napoveda = font.render("Press ENTER", True, (200, 200, 200))
        screen.blit(text_napoveda, (400 - text_napoveda.get_width() // 2, 400))

        pygame.display.flip()
        clock.tick(60)

    return jmeno


# Spuštění přihlašovací obrazovky před hrou
jmeno_hrace = obrazovka_prihlaseni()

boss = Boss(350, 250)
player = Player(150, 250)

# --- QTE PROMĚNNÉ ---
qte_aktivni = False
qte_cas_startu = 0
sipka_x = 250.0
sipka_rychlost = 6.0

zelena_sirka = 30
zelena_x = 385

posledni_qte_cas = pygame.time.get_ticks()
nahodny_cekani_ms = random.randint(1000, 1000)


def aktualizuj_rychlost_qte():
    global sipka_rychlost
    if skore >= 500:
        sipka_rychlost = 7.0
    elif skore >= 1000:
        sipka_rychlost = 10.0
    elif skore >= 1500:
        sipka_rychlost = 12.0
    elif skore >= 2000:
        sipka_rychlost = 15.0
    elif skore >= 2500:
        sipka_rychlost = 20.0
    else:
        sipka_rychlost = 5.0


def vykresli_scenu(skore_hodnota):
    """Pomocná funkce pro vykreslení pozadí, podlahy, postav a skóre"""
    screen.blit(bg_image, (0, 0))
    
    # Podlaha (Světlý a tmavý pás)
    pygame.draw.rect(screen, (100, 100, 100), (0, 450, 800, 150))
    pygame.draw.rect(screen, (40, 40, 40), (0, 450, 800, 70))

    # Postavy
    screen.blit(boss.image, boss.rect)
    screen.blit(player.image, player.rect)

    # Vykreslení Skóre v levém horním rohu
    text_skore = font.render(f"{skore_hodnota}", True, (255, 255, 0))
    screen.blit(text_skore, (20, 100))

    text_hrac = font.render(f"{jmeno_hrace}", True, (255, 255, 0))
    screen.blit(text_hrac, (20, 20))


def plynuly_konec(boss, player, screen, aktualni_skore):
    """Přehrání animace po prohře bez zamrznutí hry"""
    for _ in range(30):
        boss.update()
        player.update()

        vykresli_scenu(aktualni_skore)  # Vykreslí scénu se skóre, ale BEZ QTE lišty

        pygame.display.flip()
        clock.tick(60)
        
    odeslat_skore(jmeno_hrace, aktualni_skore)
    pygame.quit()
    sys.exit()

while True:
    aktualni_cas = pygame.time.get_ticks()

    # --- 1. SPUŠTĚNÍ QTE V NÁHODNÉM ČASE ---
    if not qte_aktivni and (aktualni_cas - posledni_qte_cas >= nahodny_cekani_ms):
        qte_aktivni = True
        qte_cas_startu = aktualni_cas
        sipka_x = 250.0
        aktualizuj_rychlost_qte()
        sipka_rychlost = abs(sipka_rychlost)

        zelena_sirka = random.randint(30, 80)
        max_x = 550 - zelena_sirka
        zelena_x = random.randint(250, max_x)

    # --- 2. LOGIKA BĚŽÍCÍHO QTE ---
    if qte_aktivni:
        if aktualni_cas - qte_cas_startu >= 2500:
            qte_aktivni = False
            zvuk_smrti.play()
            boss.attack()
            plynuly_konec(boss, player, screen, skore)

        sipka_x += sipka_rychlost
        if sipka_x >= 550 or sipka_x <= 250:
            sipka_rychlost *= -1

    # --- 3. ODCHYTÁVÁNÍ KLÁVES ---
    for event in pygame.event.get():
        if event.type == pygame.QUIT:
            pygame.quit()
            sys.exit()

        elif event.type == pygame.KEYDOWN and event.key == pygame.K_SPACE:
            if qte_aktivni:
                if zelena_x <= sipka_x <= (zelena_x + zelena_sirka):
                    player.attack()
                    zvuk_seknuti.play()
                    boss.attack()
                    
                    skore += 100  # <-- Přičtení 100 bodů za úspěšné QTE
                    aktualizuj_rychlost_qte()
                    
                    qte_aktivni = False
                    posledni_qte_cas = aktualni_cas
                    nahodny_cekani_ms = random.randint(1000, 1000)
                else:
                    qte_aktivni = False
                    zvuk_smrti.play()
                    boss.attack()
                    plynuly_konec(boss, player, screen, skore)
            else:
                player.attack()

    # --- 4. VYKRESLOVÁNÍ SCÉNY ---
    player.update()
    boss.update()

    vykresli_scenu(skore)

    # Vykreslení QTE prvku navrch
    if qte_aktivni:
        pygame.draw.rect(screen, (200, 40, 40), (250, 150, 300, 20))
        pygame.draw.rect(screen, (40, 220, 80), (zelena_x, 150, zelena_sirka, 20))
        pygame.draw.rect(screen, (255, 255, 0), (250, 150, 300, 20), width=3)
        pygame.draw.polygon(screen, (255, 255, 0), [
            (int(sipka_x), 175), 
            (int(sipka_x) - 8, 187), 
            (int(sipka_x) + 8, 187)
        ])

    pygame.display.flip()
    clock.tick(60)