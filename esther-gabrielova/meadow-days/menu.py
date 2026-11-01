import pygame
from settings import *

# barvy nadpisu
BROWN_DARK = (60, 38, 22)
CREAM = (255, 244, 214)


class Button:
    def __init__(self, name, center):
        self.normal = pygame.image.load(BUTTON_DIR + "/" + name + "_normal.png").convert_alpha() # name = začátek nazvu souboru
        self.hover = pygame.image.load(BUTTON_DIR + "/" + name + "_hover.png").convert_alpha()
        self.rect = self.normal.get_rect(center=center) # na rozpoznání hover

    def draw(self, screen):
        mouse_pos = pygame.mouse.get_pos()
        if self.rect.collidepoint(mouse_pos):
            screen.blit(self.hover, self.rect) # kurzor hover
        else:
            screen.blit(self.normal, self.rect) # kurzor not hover

# nadpis + stín
def draw_title(screen, font, text, y):
    shadow = font.render(text, False, BROWN_DARK) # tmavý stín
    title = font.render(text, False, CREAM) # světlý nadpis
    x = SCREEN_WIDTH // 2
    screen.blit(shadow, shadow.get_rect(center=(x + 4, y + 4)))
    screen.blit(title, title.get_rect(center=(x, y)))


class Menu:
    def __init__(self, game):
        self.game = game
        self.screen_name = 'main'

        bg = pygame.transform.scale(BG_MENU, (SCREEN_WIDTH, SCREEN_HEIGHT))
        self.bg = bg.convert()

        self.title_font = pygame.font.Font(FONT_PATH, 80)

        x = SCREEN_WIDTH // 2  # střed obrazovky logicky

        # tlačítka hlavní menu
        self.play_button = Button('play', (x, 375))
        self.settings_button = Button('settings', (x, 457))
        self.quit_button = Button('quit', (x, 539))

        # tlačítka nastavení
        self.windowed_button = Button('mode_windowed', (x, 375))
        self.fullscreen_button = Button('mode_fullscreen', (x, 375))
        self.back_button = Button('back', (x, 457))

    # check mouse clicků a zobrazování stránek menu
    def update(self, events):
        for event in events:
            if event.type == pygame.MOUSEBUTTONDOWN and event.button == 1:
                pos = event.pos

                if self.screen_name == 'main':
                    if self.play_button.rect.collidepoint(pos):
                        self.game.state = 'game'

                    if self.settings_button.rect.collidepoint(pos):
                        self.screen_name = 'settings'

                    if self.quit_button.rect.collidepoint(pos):
                        self.game.quit()

                elif self.screen_name == 'settings':
                    if self.game.fullscreen == True:
                        mode_button = self.fullscreen_button
                    else:
                        mode_button = self.windowed_button

                    if mode_button.rect.collidepoint(pos):
                        self.game.toggle_fullscreen()

                    if self.back_button.rect.collidepoint(pos):
                        self.screen_name = 'main'

    # vykreslení všeho
    def draw(self):
        screen = self.game.screen
        screen.blit(self.bg, (0, 0))

        if self.screen_name == 'main':
            draw_title(screen, self.title_font, 'MEADOW DAYS', 182)
            self.play_button.draw(screen)
            self.settings_button.draw(screen)
            self.quit_button.draw(screen)

        elif self.screen_name == 'settings':
            draw_title(screen, self.title_font, 'SETTINGS', 182)
            if self.game.fullscreen == True:
                self.fullscreen_button.draw(screen)
            else:
                self.windowed_button.draw(screen)
            self.back_button.draw(screen)