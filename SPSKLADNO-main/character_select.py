import pygame
from config import *

def draw_character_select(screen, font, knight_button, assassin_button, back_button):

    text = font.render("VYBER POSTAVU", True, WHITE)
    screen.blit(text, (790, 150))

    pygame.draw.rect(screen, BLUE, knight_button)
    pygame.draw.rect(screen, RED, assassin_button)
    pygame.draw.rect(screen, GRAY, back_button)

    screen.blit(font.render("Knight", True, WHITE), (920, 365))
    screen.blit(font.render("Assassin", True, WHITE), (900, 465))
    screen.blit(font.render("Zpet", True, BLACK), (1735, 965))