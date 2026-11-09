import pygame
from settings import *

pygame.init()

button_rect = pygame.Rect(0, 0, 220, 70)
button_rect.center = (300, 180)

add_die_button_rect = pygame.Rect(0, 0, 220, 50)
add_die_button_rect.center = (300, 250)

font = pygame.font.SysFont("segoeui", 32, bold=True)
small_font = pygame.font.SysFont("segoeui", 24, bold=True)


def draw_button(mouse_pos):
    hovered = button_rect.collidepoint(mouse_pos)
    color = theme["button"]
    shadow_offset = 3 if hovered else 6

    shadow_rect = button_rect.move(0, shadow_offset)
    pygame.draw.rect(screen, SHADOW, shadow_rect, border_radius=18)
    pygame.draw.rect(screen, color, button_rect, border_radius=18)
    pygame.draw.rect(screen, WHITE, button_rect, width=2, border_radius=18)

    text = font.render("ROLL", True, WHITE)
    text_rect = text.get_rect(center=button_rect.center)
    screen.blit(text, text_rect)


def draw_add_die_button(mouse_pos):
    hovered = add_die_button_rect.collidepoint(mouse_pos)
    color = theme["button"]
    shadow_offset = 2 if hovered else 4

    shadow_rect = add_die_button_rect.move(0, shadow_offset)
    pygame.draw.rect(screen, SHADOW, shadow_rect, border_radius=14)
    pygame.draw.rect(screen, color, add_die_button_rect, border_radius=14)
    pygame.draw.rect(screen, WHITE, add_die_button_rect, width=2, border_radius=14)

    text = small_font.render("+ ADD DIE", True, WHITE)
    text_rect = text.get_rect(center=add_die_button_rect.center)
    screen.blit(text, text_rect)
