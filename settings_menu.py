import pygame
from settings import *

pygame.init()


# (klíč v theme, popisek)
COLOR_OPTIONS = [
    ("dice", "Dice color"),
    ("pips", "Pip color"),
    ("menu_bg", "Menu background"),
    ("button", "Button color"),
]

ROW_START_Y = 130
ROW_GAP = 70

ARROW_SIZE = 40
LEFT_ARROW_X = 330
RIGHT_ARROW_X = 540

PREVIEW_RECT = pygame.Rect(0, 0, 100, 100)
PREVIEW_RECT.center = (150, 500)

BACK_BUTTON_RECT = pygame.Rect(0, 0, 220, 70)
BACK_BUTTON_RECT.center = (400, 500)

title_font = pygame.font.SysFont("segoeui", 44, bold=True)
label_font = pygame.font.SysFont("segoeui", 24, bold=True)
button_font = pygame.font.SysFont("segoeui", 32, bold=True)


def text_color_for(bg):
    # Tmavé pozadí -> bílý text, světlé pozadí -> černý text
    r, g, b = bg
    return BLACK if (r * 299 + g * 587 + b * 114) / 1000 > 140 else WHITE


def _palette_index(color):
    for i, (_, rgb) in enumerate(COLOR_PALETTE):
        if rgb == color:
            return i
    return 0


def _change_color(key, step):
    index = (_palette_index(theme[key]) + step) % len(COLOR_PALETTE)
    theme[key] = COLOR_PALETTE[index][1]


def _arrow_rects(row):
    y = ROW_START_Y + row * ROW_GAP
    left = pygame.Rect(0, 0, ARROW_SIZE, ARROW_SIZE)
    left.center = (LEFT_ARROW_X, y)
    right = pygame.Rect(0, 0, ARROW_SIZE, ARROW_SIZE)
    right.center = (RIGHT_ARROW_X, y)
    return left, right


def _draw_arrow(rect, text, mouse_pos):
    shadow_offset = 2 if rect.collidepoint(mouse_pos) else 4
    pygame.draw.rect(screen, SHADOW, rect.move(0, shadow_offset), border_radius=10)
    pygame.draw.rect(screen, theme["button"], rect, border_radius=10)
    pygame.draw.rect(screen, WHITE, rect, width=2, border_radius=10)

    label = label_font.render(text, True, WHITE)
    screen.blit(label, label.get_rect(center=rect.center))


def _draw_row(row, key, label, mouse_pos):
    y = ROW_START_Y + row * ROW_GAP
    text_color = text_color_for(theme["menu_bg"])

    label_surface = label_font.render(label, True, text_color)
    screen.blit(label_surface, label_surface.get_rect(midleft=(40, y)))

    left, right = _arrow_rects(row)
    _draw_arrow(left, "<", mouse_pos)
    _draw_arrow(right, ">", mouse_pos)

    # Vzorek barvy + název mezi šipkami
    name = COLOR_PALETTE[_palette_index(theme[key])][0]
    swatch = pygame.Rect(0, 0, 30, 30)
    swatch.midleft = (left.right + 15, y)
    pygame.draw.rect(screen, theme[key], swatch, border_radius=6)
    pygame.draw.rect(screen, text_color, swatch, width=2, border_radius=6)

    name_surface = label_font.render(name, True, text_color)
    screen.blit(name_surface, name_surface.get_rect(midleft=(swatch.right + 10, y)))


def _draw_preview_die():
    pygame.draw.rect(screen, theme["dice"], PREVIEW_RECT, border_radius=16)
    pygame.draw.rect(screen, theme["pips"], PREVIEW_RECT, width=3, border_radius=16)

    cx, cy = PREVIEW_RECT.center
    off = PREVIEW_RECT.width // 4
    pip_radius = PREVIEW_RECT.width // 12
    for dx, dy in [(-1, -1), (1, -1), (0, 0), (-1, 1), (1, 1)]:
        pygame.draw.circle(screen, theme["pips"], (cx + dx * off, cy + dy * off), pip_radius)


def _draw_back_button(mouse_pos):
    shadow_offset = 3 if BACK_BUTTON_RECT.collidepoint(mouse_pos) else 6
    pygame.draw.rect(screen, SHADOW, BACK_BUTTON_RECT.move(0, shadow_offset), border_radius=18)
    pygame.draw.rect(screen, theme["button"], BACK_BUTTON_RECT, border_radius=18)
    pygame.draw.rect(screen, WHITE, BACK_BUTTON_RECT, width=2, border_radius=18)

    label = button_font.render("BACK", True, WHITE)
    screen.blit(label, label.get_rect(center=BACK_BUTTON_RECT.center))


def run_settings():
    clock = pygame.time.Clock()

    while True:
        mouse_pos = pygame.mouse.get_pos()
        screen.fill(theme["menu_bg"])

        title = title_font.render("SETTINGS", True, text_color_for(theme["menu_bg"]))
        screen.blit(title, title.get_rect(center=(300, 55)))

        for row, (key, label) in enumerate(COLOR_OPTIONS):
            _draw_row(row, key, label, mouse_pos)

        _draw_preview_die()
        _draw_back_button(mouse_pos)

        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                return "quit"

            if event.type == pygame.KEYDOWN and event.key == pygame.K_ESCAPE:
                return "back"

            if event.type == pygame.MOUSEBUTTONDOWN:
                if BACK_BUTTON_RECT.collidepoint(event.pos):
                    return "back"

                for row, (key, _) in enumerate(COLOR_OPTIONS):
                    left, right = _arrow_rects(row)
                    if left.collidepoint(event.pos):
                        _change_color(key, -1)
                    if right.collidepoint(event.pos):
                        _change_color(key, 1)

        pygame.display.flip()
        clock.tick(60)
