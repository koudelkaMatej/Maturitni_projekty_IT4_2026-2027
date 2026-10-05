import pygame
from config import *

def draw_menu(screen, font, play_button, settings_button, exit_button):

    title = font.render("ENDLESS CINDERIA", True, WHITE)
    screen.blit(title, (730, 150))

    pygame.draw.rect(screen, GREEN, play_button)
    pygame.draw.rect(screen, BLUE, settings_button)
    pygame.draw.rect(screen, RED, exit_button)

    screen.blit(font.render("Hrat", True, WHITE), (920, 365))
    screen.blit(font.render("Settings", True, WHITE), (885, 465))
    screen.blit(font.render("Konec", True, WHITE), (910, 565))