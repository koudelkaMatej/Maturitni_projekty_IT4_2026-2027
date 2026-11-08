import pygame
from config import *

def draw_settings(screen, font, back_button):

    text = font.render("SETTINGS", True, WHITE)
    screen.blit(text, (850, 150))

    zpet_button=pygame.draw.rect(screen, GRAY, back_button)

    screen.blit(zpet_text, zpet_text.get_rect(center=zpet_button.center))
