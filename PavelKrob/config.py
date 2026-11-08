import pygame
import sys
# Rozliseni okna

WIDTH = 1920
HEIGHT = 1080

# Barvy

WHITE = (255, 255, 255)
BLACK = (0, 0, 0)

GREEN = (0, 150, 0)
BLUE = (0, 100, 200)
RED = (200, 0, 0)

GRAY = (150, 150, 150)

pygame.init()

font = pygame.font.SysFont(None, 50)

play_text = font.render("Hrat", True, WHITE)
settings_text = font.render("Settings", True, WHITE)
exit_text = font.render("Konec", True, WHITE)
zpet_text = font.render("Zpet", True, BLACK)
<<<<<<< HEAD
Knight_text = font.render("Knight", True, WHITE)
Assasin_text = font.render("Assasin", True, WHITE)
=======
>>>>>>> 9cfa72d (Stranka)

play_button = pygame.Rect(860, 350, 200, 60)
settings_button = pygame.Rect(860, 450, 200, 60)
exit_button = pygame.Rect(860, 550, 200, 60)

# Tlacitka settings

back_button = pygame.Rect(1700, 950, 150, 60)

# Tlacitka vyberu postavy

knight_button = pygame.Rect(810, 350, 300, 60)
assassin_button = pygame.Rect(810, 450, 300, 60)

player_size = 0.35
player_speed = 8
player_x = 200
player_y = 200

