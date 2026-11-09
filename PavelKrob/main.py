# Import knihoven

import pygame
import sys

from config import *

from menu import draw_menu
from settings import draw_settings
from character_select import draw_character_select
from game import draw_play
from player import Player

# Inicializace pygame

pygame.init()

screen = pygame.display.set_mode((WIDTH, HEIGHT))
# Nastaveni herniho okna
BACKGROUND = pygame.image.load("Maturitni_projekty_IT4_2026-2027\PavelKrob\obrazky\OIP.png").convert()

BACKGROUND = pygame.transform.scale(BACKGROUND, (WIDTH, HEIGHT))

lvl = pygame.image.load("Maturitni_projekty_IT4_2026-2027\PavelKrob\obrazky\dungeon.png").convert()
lvl = pygame.transform.scale(lvl, (WIDTH, HEIGHT))

pygame.display.set_caption("Endless Cinderia")

# Font pro texty
player = Player()
font = pygame.font.SysFont(None, 50)

# Aktualne zobrazena obrazovka

game_state = "menu"

# Ulozeni vybrane postavy

selected_class = None

# Hlavni herni smycka

running = True

clock = pygame.time.Clock()
while running:
    clock.tick(60)
    # Vyplneni pozadi

    keyState = pygame.key.get_pressed()

    if game_state != "play" :
        screen.blit(BACKGROUND, (0,0))
    else:
        screen.blit(lvl, (0,0))


    # Zpracovani vstupu od uzivatele

    for event in pygame.event.get():

        # Zavreni okna

        if event.type == pygame.QUIT:
            running = False

        # Kliknuti mysi

        if event.type == pygame.MOUSEBUTTONDOWN:

            # Obsluha hlavniho menu

            if game_state == "menu":

                if play_button.collidepoint(event.pos):
                    game_state = "character_select"

                elif settings_button.collidepoint(event.pos):
                    game_state = "settings"

                elif exit_button.collidepoint(event.pos):
                    running = False

            # Obsluha settings

            elif game_state == "settings":

                if back_button.collidepoint(event.pos):
                    game_state = "menu"

            # Obsluha vyberu postavy

            elif game_state == "character_select":

                if knight_button.collidepoint(event.pos):

                    selected_class = "Knight"
                    game_state = "play"
                    print(f"Player picked {selected_class}")

                elif assassin_button.collidepoint(event.pos):

                    selected_class = "Assassin"
                    game_state = "play"
                    print(f"Player picked {selected_class}")

                elif back_button.collidepoint(event.pos):

                    game_state = "menu"
        elif game_state == "play":
            if keyState[pygame.K_ESCAPE]:
                print("escape")
                game_state = "menu"

    # Vykresleni hlavniho menu

    if game_state == "menu":

        draw_menu(
            screen,
            font,
            play_button,
            settings_button,
            exit_button
        )

    # Vykresleni settings

    elif game_state == "settings":

        draw_settings(
            screen,
            font,
            back_button
        )

    # Vykresleni vyberu postavy

    elif game_state == "character_select":

        draw_character_select(
            screen,
            font,
            knight_button,
            assassin_button,
            back_button
        )
    elif game_state == "play":
        screen.blit(player.image, player.pos)
        draw_play(
            selected_class,
            player.update()
        )
    # Obnoveni obrazovky

    pygame.display.flip()

# Ukonceni hry

pygame.quit()
sys.exit()