import pygame
from button import *
from settings import *
from diceroll import diceroll
from diceroll import PIP_LAYOUT


pygame.init()
clock = pygame.time.Clock()
score_font = pygame.font.SysFont(None, 40)

def draw_die(surface, rect, value, color=BLACK, bg=WHITE):
    pygame.draw.rect(surface, bg, rect, border_radius=20)
    pygame.draw.rect(surface, color, rect, width=3, border_radius=20)

    cx, cy = rect.center
    off_x = rect.width // 4
    off_y = rect.height // 4
    pip_radius = max(4, rect.width // 12)

    for dx, dy in PIP_LAYOUT[value]:
        pip_pos = (cx + dx * off_x, cy + dy * off_y)
        pygame.draw.circle(surface, color, pip_pos, pip_radius)


def get_die_rects(count):
    size = min(DICE_SIZE, (DICE_ROW_WIDTH - (count - 1) * DICE_GAP) // count)
    total_width = count * size + (count - 1) * DICE_GAP
    start_x = 300 - total_width // 2
    rects = []
    for i in range(count):
        rect = pygame.Rect(0, 0, size, size)
        rect.topleft = (start_x + i * (size + DICE_GAP), 380 - size // 2)
        rects.append(rect)
    return rects


while running:
    screen.fill(BG)

    mouse_pos = pygame.mouse.get_pos()
    draw_button(mouse_pos)
    draw_add_die_button(mouse_pos)

    for event in pygame.event.get():
        if event.type == pygame.QUIT:
            running = False

        if event.type == pygame.MOUSEBUTTONDOWN:
            if button_rect.collidepoint(event.pos) and not rolling:
                final_values = [diceroll() for _ in range(dice_count)]
                current_values = [diceroll() for _ in range(dice_count)]

                rolling = True
                roll_start = pygame.time.get_ticks()
                last_flicker = roll_start

                print("Rolling...")

            if add_die_button_rect.collidepoint(event.pos) and not rolling:
                if dice_count < MAX_DICE:
                    dice_count += 1
                    current_values.append(1)
                    final_values.append(1)
                    print(f"Dice count: {dice_count}")

    now = pygame.time.get_ticks()
    if rolling:
        if now - roll_start < ROLL_DURATION * 1000:
            if now - last_flicker > 80:
                current_values = [diceroll() for _ in range(dice_count)]
                last_flicker = now
        else:
            current_values = list(final_values)
            rolling = False
            print(f"Result: {final_values}")
            Score = Score + sum(final_values)
            print(f"Skóre: {Score}")

    for rect, value in zip(get_die_rects(dice_count), current_values):
        draw_die(screen, rect, value, color=theme["pips"], bg=theme["dice"])

    score_surface = score_font.render(f"Score: {Score}", True, WHITE)
    screen.blit(score_surface, (20, 20))

    pygame.display.flip()
    clock.tick(60)


pygame.quit()
