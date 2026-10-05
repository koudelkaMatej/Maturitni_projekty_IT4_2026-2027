import pygame
from config import *

def draw_settings(screen, font, back_button):

    text = font.render("SETTINGS", True, WHITE)
    screen.blit(text, (850, 150))

    pygame.draw.rect(screen, GRAY, back_button)

    screen.blit(font.render("Zpet", True, BLACK), (1735, 965))
