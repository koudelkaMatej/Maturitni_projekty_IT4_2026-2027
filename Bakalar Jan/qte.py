import pygame
import sys

class QTE:
    def __init__(self, x, y, width=300, height=20):
        self.rect = pygame.Rect(x, y, width, height)
        self.active = False

        green_width = 60
        green_x = x + (width // 2) - (green_width // 2)
        self.green_zone = pygame.Rect(green_x, y, green_width, height)

        self.pointer_x = float(x)
        self.pointer_speed = 5.0
        self.pointer_dir = 1

        self.qte_start_time = 0
        self.time_limit = 1000

    def start(self):
        """Aktivuje QTE, resetuje ukazatel a spustí časovač."""
        self.active = True
        self.pointer_x = float(self.rect.left)
        self.pointer_dir = 1
        self.qte_start_time = pygame.time.get_ticks() # Uložíme čas začátku QTE

    def update(self):
        if not self.active:
            return

        # 1. KONTROLA VYPRŠENÍ ČASU (2 sekundy od objevení QTE)
        current_time = pygame.time.get_ticks()
        if current_time - self.qte_start_time >= self.time_limit:
            pygame.quit()
            sys.exit() # Nestihl stisknout včas -> Vypnutí hry

        # 2. Pohyb jezdce
        self.pointer_x += self.pointer_speed * self.pointer_dir
        if self.pointer_x >= self.rect.right:
            self.pointer_x = float(self.rect.right)
            self.pointer_dir = -1
        elif self.pointer_x <= self.rect.left:
            self.pointer_x = float(self.rect.left)
            self.pointer_dir = 1

    def check_hit(self):
        """Ověří, zda je ukazatel v zelené zóně po stisku Mezerníku."""
        if not self.active:
            return True
            
        self.active = False
        
        # Pokud je v zelené zóně -> Success, jinak vypnout hru
        if self.green_zone.left <= self.pointer_x <= self.green_zone.right:
            return True 
        else:
            pygame.quit()
            sys.exit() # Chybné načasování -> Vypnutí hry

    def draw(self, surface):
        if not self.active:
            return

        # Vykreslení lišty a zóny
        pygame.draw.rect(surface, (200, 40, 40), self.rect, border_radius=4)
        pygame.draw.rect(surface, (40, 220, 80), self.green_zone)
        pygame.draw.rect(surface, (255, 255, 255), self.rect, width=2, border_radius=4)

        # Vykreslení žluté šipky
        px = int(self.pointer_x)
        py = self.rect.bottom + 5
        arrow_points = [(px, py), (px - 8, py + 12), (px + 8, py + 12)]
        pygame.draw.polygon(surface, (255, 255, 0), arrow_points)