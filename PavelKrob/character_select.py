import pygame
from config import *

def draw_character_select(screen, font, knight_button, assassin_button, back_button):

    text = font.render("VYBER POSTAVU", True, WHITE)
    screen.blit(text, (790, 150))


    

    
    pygame.draw.rect(screen, GREEN, assassin_button)
    pygame.draw.rect(screen, BLUE, knight_button)
    zpet_button=pygame.draw.rect(screen, GRAY, back_button)

    screen.blit(Assasin_text, Assasin_text.get_rect(center=assassin_button.center))
    screen.blit(Knight_text, Knight_text.get_rect(center=knight_button.center))
    screen.blit(zpet_text, zpet_text.get_rect(center=zpet_button.center))

