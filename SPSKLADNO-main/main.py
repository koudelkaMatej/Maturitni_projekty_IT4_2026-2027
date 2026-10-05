# Import knihoven

import pygame
import sys

from config import *

from menu import draw_menu
from settings import draw_settings
from character_select import draw_character_select

# Inicializace pygame

pygame.init()

# Nastaveni herniho okna

screen = pygame.display.set_mode((WIDTH, HEIGHT))
pygame.display.set_caption("Endless Cinderia")

# Font pro texty

font = pygame.font.SysFont(None, 50)

# Aktualne zobrazena obrazovka

game_state = "menu"

# Ulozeni vybrane postavy

selected_class = None

# Hlavni herni smycka

running = True

while running:

    # Vyplneni pozadi

    screen.fill(BACKGROUND)

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

                    print(f"Player picked {selected_class}")

                elif assassin_button.collidepoint(event.pos):

                    selected_class = "Assassin"

                    print(f"Player picked {selected_class}")

                elif back_button.collidepoint(event.pos):

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

    # Obnoveni obrazovky

    pygame.display.flip()

# Ukonceni hry

pygame.quit()
sys.exit()