import pygame, sys
from settings import *
from menu import Menu

<<<<<<< HEAD
<<<<<<< HEAD
#TODO: Dodělat komentáře :-)
=======
>>>>>>> d14b0a0 (Add main menu)
=======
#TODO: Dodělat komentáře :-)
>>>>>>> c7e69f0 (Add todo)

class Game:
    def __init__(self):
        pygame.init()
        self.fullscreen = False
        self.screen = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT), pygame.SCALED)
        pygame.display.set_caption("Meadow Days")
        self.clock = pygame.time.Clock()

        self.state = 'menu'
        self.menu = Menu(self)
        self.font = pygame.font.Font(FONT_PATH, 32)

    def toggle_fullscreen(self):
        if self.fullscreen == True:
            self.fullscreen = False
            self.screen = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT), pygame.SCALED)
        else:
            self.fullscreen = True
            self.screen = pygame.display.set_mode((SCREEN_WIDTH, SCREEN_HEIGHT), pygame.SCALED | pygame.FULLSCREEN) # "|" = OR, aka spojení dvou nastavení okna do jednoho (docela fancy)

    def quit(self):
        pygame.quit()
        sys.exit()

    def run_game(self, events, dt):
        for event in events:
            if event.type == pygame.KEYDOWN and event.key == pygame.K_ESCAPE:
                self.state = 'menu'
                self.menu.screen_name = 'main'

        self.screen.fill((90, 160, 70))
        text = self.font.render("Hra běží. Stiskněte ESC (zpět do menu).", False, (255, 255, 255))
        self.screen.blit(text, (30, 30))

    def run(self):
        while True:
            events = pygame.event.get()
            for event in events:
                if event.type == pygame.QUIT:
                    self.quit()

            dt = self.clock.tick(60) / 1000

            if self.state == 'menu':
                self.menu.update(events)
                self.menu.draw()
            elif self.state == 'game':
                self.run_game(events, dt)

            pygame.display.update()


if __name__ == '__main__':
    game = Game()
    game.run()