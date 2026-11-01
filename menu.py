import os

import pygame
from settings import *
from settings_menu import run_settings

pygame.init()


PLAY_BUTTON_RECT = pygame.Rect(0, 0, 220, 70)
PLAY_BUTTON_RECT.center = (300, 310)

SETTINGS_BUTTON_RECT = pygame.Rect(0, 0, 220, 70)
SETTINGS_BUTTON_RECT.center = (300, 395)

QUIT_BUTTON_RECT = pygame.Rect(0, 0, 220, 70)
QUIT_BUTTON_RECT.center = (300, 480)

button_font = pygame.font.SysFont("segoeui", 32, bold=True)


def draw_title():
    screen.blit(title_image, TITLE_RECT)


def _draw_menu_button(rect, text, mouse_pos):
    hovered = rect.collidepoint(mouse_pos)
    shadow_offset = 3 if hovered else 6

    shadow_rect = rect.move(0, shadow_offset)
    pygame.draw.rect(screen, SHADOW, shadow_rect, border_radius=18)
    pygame.draw.rect(screen, theme["button"], rect, border_radius=18)
    pygame.draw.rect(screen, WHITE, rect, width=2, border_radius=18)

    label = button_font.render(text, True, WHITE)
    screen.blit(label, label.get_rect(center=rect.center))


def run_menu():
    clock = pygame.time.Clock()

    while True:
        mouse_pos = pygame.mouse.get_pos()
        screen.fill(theme["menu_bg"])

        draw_title()
        _draw_menu_button(PLAY_BUTTON_RECT, "PLAY", mouse_pos)
        _draw_menu_button(SETTINGS_BUTTON_RECT, "SETTINGS", mouse_pos)
        _draw_menu_button(QUIT_BUTTON_RECT, "QUIT", mouse_pos)

        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                return "quit"

            if event.type == pygame.MOUSEBUTTONDOWN:
                if PLAY_BUTTON_RECT.collidepoint(event.pos):
                    return "play"
                if SETTINGS_BUTTON_RECT.collidepoint(event.pos):
                    if run_settings() == "quit":
                        return "quit"
                if QUIT_BUTTON_RECT.collidepoint(event.pos):
                    return "quit"

        pygame.display.flip()
        clock.tick(60)


if __name__ == "__main__":
    if run_menu() == "play":
        import gameS  # noqa: running this starts the dice game loop

    pygame.quit()
