import pygame
from config import *

def draw_menu(screen, font, play_button, settings_button, exit_button):

    title = font.render("ENDLESS CINDERIA", True, WHITE)
    screen.blit(title, (730, 150))

    pygame.draw.rect(screen, GREEN, play_button)
    pygame.draw.rect(screen, BLUE, settings_button)
    pygame.draw.rect(screen, RED, exit_button)

    screen.blit(play_text, play_text.get_rect(center=play_button.center))
    screen.blit(settings_text, settings_text.get_rect(center=settings_button.center))
    screen.blit(exit_text, exit_text.get_rect(center=exit_button.center))