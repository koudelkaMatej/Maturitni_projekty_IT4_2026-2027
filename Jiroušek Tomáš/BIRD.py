import pygame
import random

pygame.init()
clock = pygame.time.Clock()
fps = 60

# Screen Dimensions
ScreenWidth = 800
ScreenHeight = 900
screen = pygame.display.set_mode((ScreenWidth, ScreenHeight))
pygame.display.set_caption("Flappy Bird")

# Fonts
font = pygame.font.SysFont('Bauhaus 93', 40)
font_large = pygame.font.SysFont('Bauhaus 93', 70)

# Game States
# "menu", "settings", "playing", "gameover"
game_state = "menu"

# Configurable Settings Defaults
Space = 200        # Gap between pipes
Speed = 4          # Pipe and background scroll speed
MetalTime = 1500   # Milliseconds between pipe spawns

# Assets (Scaled to match full screen dimensions to remove black bars)
Backpick = pygame.image.load('Pics/bg.png').convert()
Backpick = pygame.transform.scale(Backpick, (ScreenWidth, ScreenHeight))

Ground = pygame.image.load('Pics/ground.png')

class Bird(pygame.sprite.Sprite):
    def __init__(self, x, y):
        pygame.sprite.Sprite.__init__(self)
        self.images = []
        self.index = 0
        self.counter = 0
        for num in range(1, 4):
            img = pygame.image.load(f'Pics/bird{num}.png')
            self.images.append(img)
        self.image = self.images[self.index]
        self.rect = self.image.get_rect()
        self.rect.center = [x, y]
        self.vel = 0
        self.clicked = False

    def update(self, fly, game_over):
        if fly:
            self.vel += 0.5
            if self.vel > 8:
                self.vel = 8
            if self.rect.bottom < 765:
                self.rect.y += int(self.vel)

        if not game_over:
            # Jump control
            if pygame.mouse.get_pressed()[0] == 1 and not self.clicked:
                self.clicked = True
                self.vel = -10
            if pygame.mouse.get_pressed()[0] == 0:
                self.clicked = False

            # Animation
            self.counter += 1
            cooldown = 5
            if self.counter > cooldown:
                self.counter = 0
                self.index += 1
                if self.index >= len(self.images):
                    self.index = 0
            self.image = self.images[self.index]

    def reset(self, x, y):
        self.rect.center = [x, y]
        self.vel = 0
        self.clicked = False

class Metal(pygame.sprite.Sprite):
    def __init__(self, x, y, position):
        pygame.sprite.Sprite.__init__(self)
        self.image = pygame.image.load('Pics/pipe.png')
        self.rect = self.image.get_rect()   
        if position == 1:
            self.image = pygame.transform.flip(self.image, False, True)
            self.rect.bottomleft = [x, y - int(Space / 2)]
        if position == -1:
            self.rect.topleft = [x, y + int(Space / 2)]

    def update(self):
        self.rect.x -= Speed
        if self.rect.right < 0:
            self.kill()

# Helper function to draw buttons
def draw_button(text, x, y, width, height, inactive_color, active_color):
    mouse_pos = pygame.mouse.get_pos()
    click = pygame.mouse.get_pressed()
    
    action = False
    if x < mouse_pos[0] < x + width and y < mouse_pos[1] < y + height:
        pygame.draw.rect(screen, active_color, (x, y, width, height), border_radius=12)
        if click[0] == 1:
            action = True
    else:
        pygame.draw.rect(screen, inactive_color, (x, y, width, height), border_radius=12)

    text_surf = font.render(text, True, (255, 255, 255))
    text_rect = text_surf.get_rect(center=(x + width / 2, y + height / 2))
    screen.blit(text_surf, text_rect)
    return action

# Initialize Groups and Entities
BIRDS = pygame.sprite.Group()
METALS = pygame.sprite.Group()
TFBTH = Bird(100, int(ScreenHeight / 2))
BIRDS.add(TFBTH)

Gmove = 0
lMetal = pygame.time.get_ticks() - MetalTime
fly = False
Gameover = False

Game = True
while Game:
    clock.tick(fps)

    # 1. EVENT HANDLING
    events = pygame.event.get()
    for event in events:
        if event.type == pygame.QUIT:
            Game = False

    # 2. STATE MACHINE LOGIC & RENDERING
    if game_state == "menu":
        screen.blit(Backpick, (0, 0))
        
        # Title
        title_surf = font_large.render("PTÁK", True, (255, 255, 255))
        title_rect = title_surf.get_rect(center=(ScreenWidth // 2, 250))
        screen.blit(title_surf, title_rect)

        # Menu Buttons
        if draw_button("Hrát", ScreenWidth // 2 - 150, 400, 300, 60, (50, 200, 50), (70, 230, 70)):
            # Reset game variables to start fresh
            METALS.empty()
            TFBTH.reset(100, int(ScreenHeight / 2))
            fly = False
            Gameover = False
            Gmove = 0
            lMetal = pygame.time.get_ticks() - MetalTime
            game_state = "playing"

        if draw_button("Nastavení", ScreenWidth // 2 - 150, 480, 300, 60, (50, 100, 200), (70, 130, 230)):
            game_state = "settings"

        if draw_button("Odejít", ScreenWidth // 2 - 150, 560, 300, 60, (200, 50, 50), (230, 70, 70)):
            Game = False

    elif game_state == "settings":
        screen.blit(Backpick, (0, 0))

        title_surf = font_large.render("Nastavení", True, (255, 255, 255))
        title_rect = title_surf.get_rect(center=(ScreenWidth // 2, 150))
        screen.blit(title_surf, title_rect)

        # Gap Size Control (Space)
        gap_text = font.render(f"Vzdálenost trubek: {Space}", True, (255, 255, 255))
        screen.blit(gap_text, (ScreenWidth // 2 - 150, 270))
        if draw_button("-", ScreenWidth // 2 - 150, 320, 60, 50, (150, 150, 150), (180, 180, 180)):
            Space = max(140, Space - 10)
            pygame.time.delay(150) # Debounce click
        if draw_button("+", ScreenWidth // 2 + 90, 320, 60, 50, (150, 150, 150), (180, 180, 180)):
            Space = min(300, Space + 10)
            pygame.time.delay(150)

        # Speed Control
        speed_text = font.render(f"Rychlost ptáka: {Speed}", True, (255, 255, 255))
        screen.blit(speed_text, (ScreenWidth // 2 - 150, 420))
        if draw_button("-", ScreenWidth // 2 - 150, 470, 60, 50, (150, 150, 150), (180, 180, 180)):
            Speed = max(2, Speed - 1)
            pygame.time.delay(150)
        if draw_button("+", ScreenWidth // 2 + 90, 470, 60, 50, (150, 150, 150), (180, 180, 180)):
            Speed = min(8, Speed + 1)
            pygame.time.delay(150)

        # Back to Menu Button
        if draw_button("Zpet", ScreenWidth // 2 - 150, 620, 300, 60, (100, 100, 100), (130, 130, 130)):
            game_state = "menu"

    elif game_state == "playing" or game_state == "gameover":
        # Draw background elements
        screen.blit(Backpick, (0, 0))
        METALS.draw(screen)
        screen.blit(Ground, (Gmove, 765))
        BIRDS.draw(screen)

        # Updates during active play
        if not Gameover:
            BIRDS.update(fly, Gameover)
            METALS.update()

            timeNow = pygame.time.get_ticks()
            if fly:
                if timeNow - lMetal > MetalTime:
                    MetalHIGHT = random.randint(-100, 100)
                    BMETALS = Metal(ScreenWidth, int(ScreenHeight / 2) + MetalHIGHT, -1)
                    TMETALS = Metal(ScreenWidth, int(ScreenHeight / 2) + MetalHIGHT, 1)
                    METALS.add(BMETALS)
                    METALS.add(TMETALS)
                    lMetal = timeNow

            # Scroll Ground
            if abs(Gmove) > 35:
                Gmove = 0
            Gmove -= Speed

            # Check Collisions
            if pygame.sprite.groupcollide(BIRDS, METALS, False, False) or TFBTH.rect.top < 0:
                Gameover = True
                fly = False
            if TFBTH.rect.bottom > 765:
                Gameover = True
                fly = False

            # Start flying on click
            for event in events:
                if event.type == pygame.MOUSEBUTTONDOWN and not fly and not Gameover:
                    fly = True

        # Game Over Screen Overlay
        if Gameover:
            over_surf = font_large.render("Doletels", True, (255, 50, 50))
            over_rect = over_surf.get_rect(center=(ScreenWidth // 2, 300))
            screen.blit(over_surf, over_rect)

            if draw_button("Znova?", ScreenWidth // 2 - 150, 420, 300, 60, (50, 200, 50), (70, 230, 70)):
                METALS.empty()
                TFBTH.reset(100, int(ScreenHeight / 2))
                fly = False
                Gameover = False
                Gmove = 0
                lMetal = pygame.time.get_ticks() - MetalTime

            if draw_button("Zpet do menu", ScreenWidth // 2 - 150, 500, 300, 60, (100, 100, 100), (130, 130, 130)):
                game_state = "menu"

    pygame.display.update()

pygame.quit()